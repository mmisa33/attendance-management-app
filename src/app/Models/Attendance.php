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

    // 開始時間のフォーマット
    public function getStartTimeFormattedAttribute()
    {
        return $this->start_time ? Carbon::parse($this->start_time)->format('H:i') : '';
    }

    // 終了時間のフォーマット
    public function getEndTimeFormattedAttribute()
    {
        return $this->end_time ? Carbon::parse($this->end_time)->format('H:i') : '';
    }

    // 休憩時間の合計をフォーマット
    public function getTotalBreakTimeAttribute()
    {
        if ($this->status !== self::STATUS_DONE) {
            return ''; // 退勤していない場合や出勤・退勤時間が未設定の場合は空白にする
        }

        $totalBreakMinutes = $this->breakTimes->reduce(function ($carry, $breakTime) {
            if ($breakTime->break_start && $breakTime->break_end) {
                $carry += Carbon::parse($breakTime->break_start)
                    ->diffInMinutes(Carbon::parse($breakTime->break_end));
            }
            return $carry;
        }, 0);

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

        $startTime = Carbon::parse($this->start_time);
        $endTime = Carbon::parse($this->end_time);

        $totalMinutes = $startTime->diffInMinutes($endTime);

        // 休憩時間の分を減算
        $totalBreakMinutes = $this->breakTimes->reduce(function ($carry, $breakTime) {
            if ($breakTime->break_start && $breakTime->break_end) {
                $carry += Carbon::parse($breakTime->break_start)
                    ->diffInMinutes(Carbon::parse($breakTime->break_end));
            }
            return $carry;
        }, 0);

        $totalMinutes -= $totalBreakMinutes;

        if ($totalMinutes < self::MIN_WORK_TIME) {
            $totalMinutes = self::MIN_WORK_TIME;
        }

        $hours = floor($totalMinutes / self::MINUTES_IN_HOUR);
        $minutes = $totalMinutes % self::MINUTES_IN_HOUR;

        return sprintf("%d:%02d", $hours, $minutes);
    }
}