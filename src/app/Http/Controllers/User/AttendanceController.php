<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
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
        $attendance = Attendance::where('user_id', Auth::id())
            ->where('date', $today)
            ->first();

        // 定数をビューに渡す
        $attendanceStatuses = [
            'off' => Attendance::STATUS_OFF,
            'working' => Attendance::STATUS_WORKING,
            'break' => Attendance::STATUS_BREAK,
            'done' => Attendance::STATUS_DONE,
        ];

        $now = Carbon::now();

        return view('attendance.index', compact('attendance', 'now', 'attendanceStatuses'));
    }

    // 出勤時にステータスを「出勤中」に変更
    public function clockIn()
    {
        $attendance = $this->getTodayAttendance();

        if (!$attendance) {
            // 新しいレコードを作成
            $attendance = Attendance::create([
                'user_id' => Auth::id(),
                'date' => Carbon::today(),
                'status' => Attendance::STATUS_WORKING,
                'start_time' => Carbon::now(),
            ]);
        } else {
            // 出勤していない場合にのみステータスと開始時間を更新
            if ($attendance->status !== Attendance::STATUS_WORKING) {
                $attendance->update([
                    'status' => Attendance::STATUS_WORKING,
                    'start_time' => Carbon::now(),
                ]);
            }
        }

        return redirect()->route('attendance.index');
    }

    // 休憩開始時にステータスを「休憩中」に変更
    public function breakStart()
    {
        $attendance = $this->getTodayAttendance();

        if ($attendance && $attendance->status === Attendance::STATUS_WORKING) {
            $attendance->update(['status' => Attendance::STATUS_BREAK]);
            $attendance->breakTimes()->create(['break_start' => Carbon::now()]);
        }

        return redirect()->route('attendance.index');
    }

    // 休憩終了時にステータスを「出勤中」に変更
    public function breakEnd()
    {
        $attendance = $this->getTodayAttendance();

        if ($attendance && $attendance->status === Attendance::STATUS_BREAK) {
            $attendance->update(['status' => Attendance::STATUS_WORKING]);

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
        $attendance = $this->getTodayAttendance();

        if ($attendance && $attendance->status === Attendance::STATUS_WORKING) {
            $attendance->update([
                'status' => Attendance::STATUS_DONE,
                'end_time' => Carbon::now(),
            ]);
        }

        return redirect()->route('attendance.index');
    }

    // 本日の勤怠情報を取得
    private function getTodayAttendance()
    {
        return Attendance::where('user_id', Auth::id())
            ->where('date', Carbon::today())
            ->first();
    }

    // 勤怠一覧ページを表示
    public function attendanceList(Request $request)
    {
        $userId = Auth::id();

        return $this->getAttendanceList($request, $userId, 'attendance.list');
    }

    // 労働時間や休憩時間をフォーマットする共通処理
    private function getAttendanceList(Request $request, $userId, $view)
    {
        // 現在の月を取得
        $currentMonth = $request->input('month', Carbon::now()->format('Y-m'));

        // 月の初日と最終日を計算
        $startOfMonth = Carbon::parse($currentMonth)->startOfMonth();
        $endOfMonth = Carbon::parse($currentMonth)->endOfMonth();

        // 勤怠情報をデータベースから取得
        $attendances = Attendance::where('user_id', $userId)
            ->whereBetween('date', [$startOfMonth, $endOfMonth])
            ->orderBy('date', 'asc')
            ->get();

        foreach ($attendances as $attendance) {
            $date = Carbon::parse($attendance->date);

            // 勤怠の日付をフォーマット
            $attendance->formatted_date = $date->locale('ja')->format('m/d') . '(' . $date->isoFormat('ddd') . ')';
            $attendance->start_time_formatted = $attendance->start_time
                ? Carbon::parse($attendance->start_time)->format('H:i')
                : '';
            $attendance->end_time_formatted = $attendance->end_time
                ? Carbon::parse($attendance->end_time)->format('H:i')
                : '';

            // 退勤していない場合は休憩時間と労働時間を空白
            if ($attendance->status !== Attendance::STATUS_DONE) {
                $attendance->total_break_time = '';
                $attendance->total_hours = '';
            } else {
                // 休憩時間合計（分単位）
                $totalBreakMinutes = $attendance->breakTimes && $attendance->breakTimes->isNotEmpty()
                    ? $attendance->breakTimes->sum(function ($breakTime) {
                        return Carbon::parse($breakTime->break_start)
                            ->diffInMinutes(Carbon::parse($breakTime->break_end));
                    })
                    : 0;

                $attendance->total_break_time = floor($totalBreakMinutes / 60) . ':' . str_pad($totalBreakMinutes % 60, 2, '0', STR_PAD_LEFT);

                // 労働時間合計（分単位）
                if ($attendance->start_time && $attendance->end_time) {
                    $start = Carbon::parse($attendance->start_time);
                    $end = Carbon::parse($attendance->end_time);
                    $totalMinutes = $end->diffInMinutes($start);

                    $attendance->total_hours = floor($totalMinutes / 60) . ':' . str_pad($totalMinutes % 60, 2, '0', STR_PAD_LEFT);
                } else {
                    $attendance->total_hours = '0:00';
                }
            }
        }

        // 当月の表示を変更
        $formattedMonth = Carbon::parse($currentMonth)->format('Y/m');

        // 前月、次月のURLを生成
        $previousMonth = Carbon::parse($currentMonth)->subMonth()->format('Y-m');
        $nextMonth = Carbon::parse($currentMonth)->addMonth()->format('Y-m');

        return view($view, compact('attendances', 'currentMonth', 'previousMonth', 'nextMonth', 'formattedMonth'));
    }
}