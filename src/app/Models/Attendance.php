<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Attendance extends Model
{
    use HasFactory;

    /* =========================
        定数定義
    ========================= */
    const STATUS_OFF = '勤務外';
    const STATUS_WORKING = '出勤中';
    const STATUS_BREAK = '休憩中';
    const STATUS_DONE = '退勤済';

    /* =========================
    時間関連の定数
    ========================= */
    const MINUTES_IN_HOUR = 60;
    const MIN_WORK_TIME = 0;
    const DEFAULT_WORK_TIME = '0:00';

    /* =========================
        リレーション定義
    ========================= */
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

    /* =========================
        アクセサ定義
    ========================= */
    /* ----- 日付関連のフォーマット ----- */
    // 「MM/DD(曜日)」形式で日付をフォーマット
    public function getFormattedDateAttribute()
    {
        $date = Carbon::parse($this->date);
        return $date->locale('ja')->format('m/d') . '(' . $date->isoFormat('ddd') . ')';
    }

    // 「YYYY年」形式で日付をフォーマット
    public function getFormattedYearAttribute()
    {
        return Carbon::parse($this->date)->format('Y') . '年';
    }

    // 「n月j日」形式で日付をフォーマット
    public function getFormattedMonthdayAttribute()
    {
        return Carbon::parse($this->date)->format('n') . '月' . Carbon::parse($this->date)->format('j') . '日';
    }

    // 「YYYY/MM/DD」形式で日付をフォーマット
    public function getFormattedFullDateAttribute()
    {
        return Carbon::parse($this->date)->format('Y/m/d');
    }

    /* ----- 修正申請日時のフォーマット ----- */
    // 「YYYY/MM/DD」形式で修正申請日時をフォーマット
    public function getFormattedRequestDateAttribute()
    {
        return Carbon::parse($this->request_date)->format('Y/m/d');
    }

    /* =========================
        ビジネスロジック
    ========================= */
    /* ----- 出退勤時間のフォーマット ----- */
    // 「HH:mm」形式で出勤時間をフォーマット
    public function getFormattedStartTimeAttribute()
    {
        return $this->start_time ? Carbon::parse($this->start_time)->format('H:i') : '';
    }

    // 「HH:mm」形式で退勤時間をフォーマット
    public function getFormattedEndTimeAttribute()
    {
        return $this->end_time ? Carbon::parse($this->end_time)->format('H:i') : '';
    }

    /* ----- 休憩時間の整形・集計 ----- */
    // 休憩時間を「HH:mm」形式で整形
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

    // 退勤後のみ休憩時間の合計を「時間:分」形式で表示
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

    /* ----- 労働時間の計算 ----- */
    // 退勤後のみ、休憩時間を差し引いた労働時間の合計を「時間:分」形式で表示
    public function getTotalHoursAttribute()
    {
        if ($this->status !== self::STATUS_DONE || !$this->start_time || !$this->end_time) {
            return ''; // 退勤していない場合は空白にする
        }

        return $this->getFormattedWorkTime($this->start_time, $this->end_time, $this->breakTimes);
    }

    /* ----- 内部処理 ----- */
    // 休憩時間の合計（分単位）を計算
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

    // 出勤時間から退勤時間までの差分から休憩時間を差し引き、労働時間を「時間:分」形式で表示
    private function getFormattedWorkTime($startTime, $endTime, $breakTimes)
    {
        if ($this->status !== self::STATUS_DONE || !$startTime || !$endTime) {
            return '0:00';
        }

        $start = Carbon::parse($startTime);
        $end = Carbon::parse($endTime);

        $totalMinutes = $start->diffInMinutes($end);
        $totalBreakMinutes = $this->calculateTotalBreakTime($breakTimes);

        $totalMinutes -= $totalBreakMinutes;

        if ($totalMinutes < self::MIN_WORK_TIME) {
            $totalMinutes = self::MIN_WORK_TIME;
        }

        $hours = floor($totalMinutes / self::MINUTES_IN_HOUR);
        $minutes = $totalMinutes % self::MINUTES_IN_HOUR;

        return sprintf("%d:%02d", $hours, $minutes);
    }

    /* =========================
        スコープ定義
    ========================= */
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

    /* =========================
        更新処理
    ========================= */
    public function updateAttendance($validated, $isAdmin = false)
    {
        // 出勤・退勤時間、備考の更新
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

        $this->save();

        // 休憩時間の更新・追加
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

    /* =========================
        CSVエクスポート
    ========================= */
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