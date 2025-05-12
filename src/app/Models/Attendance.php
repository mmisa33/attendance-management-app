<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Attendance extends Model
{
    use HasFactory;

    // ステータス定数定義
    const STATUS_OFF = '勤務外';
    const STATUS_WORKING = '出勤中';
    const STATUS_BREAK = '休憩中';
    const STATUS_DONE = '退勤済';

    // 定数定義
    const MINUTES_IN_HOUR = 60;
    const MIN_WORK_TIME = 0;
    const DEFAULT_WORK_TIME = '0:00';

    protected $fillable = [
        'user_id',
        'date',
        'status',
        'start_time',
        'end_time',
        'note',
        'is_modified',
        'is_approved',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function breakTimes()
    {
        return $this->hasMany(BreakTime::class);
    }

    // 日付のフォーマット
    public function getFormattedDateAttribute()
    {
        $date = Carbon::parse($this->date);
        return $date->locale('ja')->format('m/d') . '(' . $date->isoFormat('ddd') . ')';
    }

    // 日付の「YYYY年」と「n月j日」のフォーマット
    public function getFormattedYearAttribute()
    {
        return Carbon::parse($this->date)->format('Y') . '年';
    }

    public function getFormattedMonthdayAttribute()
    {
        return Carbon::parse($this->date)->format('n') . '月' . Carbon::parse($this->date)->format('j') . '日';
    }

    // 開始時間のフォーマット
    public function getFormattedStartTimeAttribute()
    {
        return $this->start_time ? Carbon::parse($this->start_time)->format('H:i') : '';
    }

    // 終了時間のフォーマット
    public function getFormattedEndTimeAttribute()
    {
        return $this->end_time ? Carbon::parse($this->end_time)->format('H:i') : '';
    }

    // 休憩データの整形
    public function getFormattedBreakRowsAttribute()
    {
        $breakTimes = $this->breakTimes;
        $breakRows = $breakTimes->map(function ($breakTime, $index) {
            $endTime = $breakTime->break_end ? Carbon::parse($breakTime->break_end)->format('H:i') : '';
            return [
                'index' => $index,
                'start' => Carbon::parse($breakTime->break_start)->format('H:i'),
                'end' => $endTime,
            ];
        })->toArray();

        if (empty($breakRows)) {
            $breakRows[] = ['index' => 0, 'start' => '', 'end' => ''];
        }

        return $breakRows;
    }

    // 休憩時間の合計をフォーマット
    public function getTotalBreakTimeAttribute()
    {
        if ($this->status !== self::STATUS_DONE) {
            return ''; // 退勤していない場合は空白にする
        }

        $totalBreakMinutes = $this->calculateTotalBreakTime($this->breakTimes);

        $hours = floor($totalBreakMinutes / self::MINUTES_IN_HOUR);
        $minutes = $totalBreakMinutes % self::MINUTES_IN_HOUR;

        return sprintf("%d:%02d", $hours, $minutes);
    }

    // 労働時間の合計をフォーマット
    public function getTotalHoursAttribute()
    {
        if ($this->status !== self::STATUS_DONE || !$this->start_time || !$this->end_time) {
            return ''; // 退勤していない場合や出勤・退勤時間が未設定の場合は空白にする
        }

        return $this->getFormattedWorkTime($this->start_time, $this->end_time, $this->breakTimes);
    }

    // 休憩時間の合計を計算
    private function calculateTotalBreakTime($breakTimes)
    {
        return $breakTimes->reduce(function ($carry, $breakTime) {
            if ($breakTime->break_start && $breakTime->break_end) {
                $carry += Carbon::parse($breakTime->break_start)
                    ->diffInMinutes(Carbon::parse($breakTime->break_end));
            }
            return $carry;
        }, 0);
    }

    // 労働時間の合計を計算
    private function getFormattedWorkTime($startTime, $endTime, $breakTimes)
    {
        if ($this->status !== self::STATUS_DONE || !$startTime || !$endTime) {
            return '0:00';
        }

        $start = Carbon::parse($startTime);
        $end = Carbon::parse($endTime);

        $totalMinutes = $start->diffInMinutes($end);

        // 休憩時間の分を減算
        $totalBreakMinutes = $this->calculateTotalBreakTime($breakTimes);

        $totalMinutes -= $totalBreakMinutes;

        if ($totalMinutes < self::MIN_WORK_TIME) {
            $totalMinutes = self::MIN_WORK_TIME;
        }

        $hours = floor($totalMinutes / self::MINUTES_IN_HOUR);
        $minutes = $totalMinutes % self::MINUTES_IN_HOUR;

        return sprintf("%d:%02d", $hours, $minutes);
    }

    public function updateAttendance($validated, $isAdmin = false)
    {
        // 既存の開始時間、終了時間、備考を更新
        $this->start_time = $this->date . ' ' . $validated['start_time'] . ':00';
        $this->end_time = $this->date . ' ' . $validated['end_time'] . ':00';
        $this->note = $validated['note'];

        // 管理者が修正している場合は、修正申請状態を解除
        if ($isAdmin) {
            $this->is_modified = false;
        } else {
            // 一般ユーザーの場合は、修正申請中の状態に変更
            $this->is_modified = true;
        }

        // 勤怠情報を保存
        $this->save();

        // 既存の休憩時間を更新
        foreach ($this->breakTimes as $i => $breakTime) {
            $startInput = $validated['break_start'][$i] ?? null;
            $endInput = $validated['break_end'][$i] ?? null;

            if ($startInput && $endInput) {
                $breakTime->break_start = $this->date . ' ' . $startInput . ':00';
                $breakTime->break_end = $this->date . ' ' . $endInput . ':00';
                $breakTime->save();
            }
        }

        // 新規休憩時間を追加
        $existingCount = count($this->breakTimes);
        $additionalStarts = array_slice($validated['break_start'], $existingCount);
        $additionalEnds = array_slice($validated['break_end'], $existingCount);

        foreach ($additionalStarts as $i => $start) {
            $end = $additionalEnds[$i] ?? null;

            if ($start && $end) {
                $this->breakTimes()->create([
                    'break_start' => $this->date . ' ' . $start . ':00',
                    'break_end' => $this->date . ' ' . $end . ':00',
                ]);
            }
        }
    }

    // ユーザー指定のスコープ
    public function scopeOfUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    // 月指定のスコープ
    public function scopeOfMonth($query, $month)
    {
        $startOfMonth = Carbon::parse($month)->startOfMonth();
        $endOfMonth = Carbon::parse($month)->endOfMonth();
        return $query->whereBetween('date', [$startOfMonth, $endOfMonth])
            ->orderBy('date', 'asc');
    }

    // 日付指定のスコープ
    public function scopeByDate($query, $date)
    {
        return $query->whereDate('date', $date);
    }

    // ユーザー指定＆月指定のスコープ
    public function scopeByUserAndMonth($query, $userId, $month)
    {
        return $query->ofUser($userId)
            ->ofMonth($month);
    }

    // CSVエクスポート用データ生成
    public function toCsvRow()
    {
        return [
            $this->formatted_date,
            $this->formatted_start_time,
            $this->formatted_end_time,
            $this->total_break_time,
            $this->total_hours,
        ];
    }
}