<?php

namespace App\Http\Controllers\Shared;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Models\Attendance;
use Carbon\Carbon;

class AttendanceDetailController extends Controller
{
    public function show($attendanceId)
    {
        // 管理者または一般ユーザーがログインしている場合
        if (Auth::guard('admin')->check()) {
            $attendance = Attendance::findOrFail($attendanceId);
            $user = $attendance->user;  // 勤怠に紐づくユーザー情報を取得
        } elseif (Auth::guard('web')->check()) {
            $user = Auth::guard('web')->user();
            $attendance = Attendance::where('user_id', $user->id)->findOrFail($attendanceId);
        } else {
            abort(403);
        }

        // 日付を「YYYY年」と「n月j日」に分けてフォーマット
        $date = Carbon::parse($attendance->date);
        $attendance->formatted_year     = $date->format('Y') . '年';
        $attendance->formatted_monthday = $date->format('n') . '月' . $date->format('j') . '日';

        return view('attendance.details', [
            'user' => $user,
            'attendance' => $attendance,
            'breakTimes' => $attendance->breakTimes,
        ]);
    }
}