<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AttendanceController extends Controller
{
    // 勤怠登録ページを表示
    public function index()
    {
        $today = Carbon::today();
        $attendance = Attendance::firstOrCreate(
            ['user_id' => Auth::id(), 'date' => $today],
            ['status' => '勤務外']
        );

        $now = Carbon::now();

        return view('attendance.index', compact('attendance', 'now'));
    }

    // 出勤時にステータスを「出勤中」に変更
    public function clockIn()
    {
        $attendance = Attendance::where('user_id', Auth::id())->where('date', Carbon::today())->first();

        if ($attendance->status === '勤務外') {
            $attendance->update([
                'status' => '出勤中',
                'start_time' => Carbon::now(),
            ]);
        }

        return redirect()->route('attendance.index');
    }

    // 休憩開始時にステータスを「休憩中」に変更
    public function breakStart()
    {
        $attendance = Attendance::where('user_id', Auth::id())->where('date', Carbon::today())->first();

        if ($attendance->status === '出勤中') {
            $attendance->update(['status' => '休憩中']);
            $attendance->breakTimes()->create(['break_start' => Carbon::now()]);
        }

        return redirect()->route('attendance.index');
    }

    // 休憩終了時にステータスを「出勤中」に変更
    public function breakEnd()
    {
        $attendance = Attendance::where('user_id', Auth::id())->where('date', Carbon::today())->first();

        if ($attendance->status === '休憩中') {
            $attendance->update(['status' => '出勤中']);

            $lastBreak = $attendance->breakTimes()->whereNull('break_end')->latest()->first();
            if ($lastBreak) {
                $lastBreak->update(['break_end' => Carbon::now()]);
            }
        }

        return redirect()->route('attendance.index');
    }

    // 退勤時にステータスを「退勤済」に変更
    public function clockOut()
    {
        $attendance = Attendance::where('user_id', Auth::id())->where('date', Carbon::today())->first();

        if ($attendance->status === '出勤中') {
            $attendance->update([
                'status' => '退勤済',
                'end_time' => Carbon::now(),
            ]);
        }

        return redirect()->route('attendance.index');
    }

    // 勤怠一覧ページを表示
    public function attendanceList(Request $request)
    {
        // 現在の月を取得
        $currentMonth = $request->input('month', Carbon::now()->format('Y-m'));
        $startOfMonth = Carbon::parse($currentMonth)->startOfMonth();
        $endOfMonth = Carbon::parse($currentMonth)->endOfMonth();

        // 現在月の勤怠情報を取得
        $attendances = Attendance::where('user_id', Auth::id())
            ->whereBetween('date', [$startOfMonth, $endOfMonth])
            ->get();

        // 勤怠情報をフォーマット
        foreach ($attendances as $attendance) {
            $attendance->formatted_date = Carbon::parse($attendance->date)->format('m/d(D)');

            // 出勤時刻と退勤時刻のフォーマット
            $attendance->start_time_formatted = $attendance->start_time ? Carbon::parse($attendance->start_time)->format('H:i') : '';
            $attendance->end_time_formatted = $attendance->end_time ? Carbon::parse($attendance->end_time)->format('H:i') : '';

            // 休憩時間の計算
            $totalBreakTimeMinutes = 0;
            if ($attendance->breakTimes && $attendance->breakTimes->isNotEmpty()) {
                foreach ($attendance->breakTimes as $breakTime) {
                    $breakStart = Carbon::parse($breakTime->break_start);
                    $breakEnd = Carbon::parse($breakTime->break_end);
                    $totalBreakTimeMinutes += $breakStart->diffInMinutes($breakEnd);
                }
            }

            // 休憩時間を時間:分形式に変換
            $hours = floor($totalBreakTimeMinutes / 60);
            $minutes = $totalBreakTimeMinutes % 60;
            $attendance->total_break_time = sprintf('%02d:%02d', $hours, $minutes);

            // 合計労働時間
            if ($attendance->start_time && $attendance->end_time) {
                $startTime = Carbon::parse($attendance->start_time);
                $endTime = Carbon::parse($attendance->end_time);
                $totalHours = $endTime->diffInHours($startTime);
                $totalMinutes = $endTime->diffInMinutes($startTime) % 60;

                // 合計労働時間を時間:分形式に変換
                $attendance->total_hours = sprintf('%02d:%02d', $totalHours, $totalMinutes);
            } else {
                $attendance->total_hours = '00:00'; // 出勤退勤がない場合
            }
        }

        // フォーマットした月
        $formattedMonth = Carbon::parse($currentMonth)->format('Y/m');

        // 月の前後ボタン用に修正
        $previousMonth = Carbon::parse($currentMonth)->subMonth()->format('Y-m');
        $nextMonth = Carbon::parse($currentMonth)->addMonth()->format('Y-m');

        // ビューに渡す
        return view('attendance.list', compact('attendances', 'currentMonth', 'previousMonth', 'nextMonth', 'formattedMonth'));
    }

    // 勤怠詳細ページを表示
    public function attendanceDetails($attendanceId)
    {
        $attendance = Attendance::where('user_id', Auth::id())->findOrFail($attendanceId);
        return view('attendance.details', compact('attendance'));
    }
}