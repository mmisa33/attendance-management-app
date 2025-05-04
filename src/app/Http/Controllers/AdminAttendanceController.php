<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Attendance;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class AdminAttendanceController extends Controller
{
    // 管理者用勤怠一覧ページを表示
    public function adminAttendanceList(Request $request)
    {
        if (!Auth::guard('admin')->check()) {
            return redirect()->route('admin.login');
        }

        // クエリパラメータから日付を取得
        $date = $request->input('date')
            ? Carbon::parse($request->input('date'))
            : Carbon::today();

        // 日付ベースで全ユーザーの勤怠情報を取得
        $attendances = Attendance::with('user')
            ->whereDate('date', $date->toDateString())
            ->orderBy('user_id')
            ->get();

        // 日付・時間のフォーマット処理
        foreach ($attendances as $attendance) {
            $attendance->start_time_formatted = $attendance->start_time
                ? Carbon::parse($attendance->start_time)->format('H:i')
                : '';
            $attendance->end_time_formatted = $attendance->end_time
                ? Carbon::parse($attendance->end_time)->format('H:i')
                : '';

            // 休憩時間合計の計算
            $totalBreakMinutes = $attendance->breakTimes->sum(function ($breakTime) {
                return $breakTime->break_start && $breakTime->break_end
                    ? Carbon::parse($breakTime->break_start)->diffInMinutes(Carbon::parse($breakTime->break_end))
                    : 0;
            });

            // 休憩時間がゼロの場合は空白にする
            $attendance->total_break_time = $totalBreakMinutes > 0
                ? floor($totalBreakMinutes / 60) . ':' . str_pad($totalBreakMinutes % 60, 2, '0', STR_PAD_LEFT)
                : '';  // 休憩時間がゼロなら空白にする

            // 労働時間合計の計算
            $attendance->total_hours = $attendance->start_time && $attendance->end_time
                ? Carbon::parse($attendance->start_time)->diffInMinutes(Carbon::parse($attendance->end_time))
                : 0;

            // 時間形式に変換
            if ($attendance->total_hours) {
                $hours = floor($attendance->total_hours / 60);
                $minutes = $attendance->total_hours % 60;
                $attendance->total_hours = "{$hours}:" . str_pad($minutes, 2, '0', STR_PAD_LEFT);
            } else {
                $attendance->total_hours = '';
            }
        }

        return view('admin.attendance.list', [
            'attendances' => $attendances,
            'currentDate' => $date,
            'previousDate' => $date->copy()->subDay(),
            'nextDate' => $date->copy()->addDay(),
        ]);
    }
}