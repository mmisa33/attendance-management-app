<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    use HasFactory;

    // ステータス定数定義
    const STATUS_OFF = '勤務外';
    const STATUS_WORKING = '出勤中';
    const STATUS_BREAK = '休憩中';
    const STATUS_DONE = '退勤済';

    protected $fillable = ['user_id', 'date', 'status', 'start_time', 'end_time', 'note', 'is_modified', 'is_approved'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function breakTimes()
    {
        return $this->hasMany(BreakTime::class);
    }
}