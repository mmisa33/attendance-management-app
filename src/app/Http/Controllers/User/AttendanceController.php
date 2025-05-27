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
        $attendance = $this->getTodayAttendance();
        $now = Carbon::now();

        $attendanceStatuses = [
            'off' => Attendance::STATUS_OFF,
            'working' => Attendance::STATUS_WORKING,
            'break' => Attendance::STATUS_BREAK,
            'done' => Attendance::STATUS_DONE,
        ];

        return view('attendance.index', compact('attendance', 'now', 'attendanceStatuses'));
    }

    // 出勤時にステータスを「出勤中」に変更
    public function startWork()
    {
        $attendance = $this->getTodayAttendance();

        if (!$attendance) {
            Attendance::create([
                'user_id' => Auth::id(),
                'date' => Carbon::today(),
                'status' => Attendance::STATUS_WORKING,
                'start_time' => Carbon::now(),
            ]);
        } elseif ($attendance->status !== Attendance::STATUS_WORKING) {
            $attendance->update([
                'status' => Attendance::STATUS_WORKING,
                'start_time' => Carbon::now(),
            ]);
        }

        return redirect()->route('attendance.index');
    }

    // 休憩開始時にステータスを「休憩中」に変更
    public function startBreak()
    {
        $attendance = $this->getTodayAttendance();

        if ($attendance && $attendance->status === Attendance::STATUS_WORKING) {
            $attendance->update(['status' => Attendance::STATUS_BREAK]);
            $attendance->breakTimes()->create(['break_start' => Carbon::now()]);
        }

        return redirect()->route('attendance.index');
    }

    // 休憩終了時にステータスを「出勤中」に変更
    public function endBreak()
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

    // 退勤時時にステータスを「退勤済」に変更
    public function endWork()
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
        return Attendance::ofUser(Auth::id())
            ->ofMonth(Carbon::today()->format('Y-m'))
            ->whereDate('date', Carbon::today())
            ->first();
    }

    // 勤怠一覧ページを表示
    public function attendanceList(Request $request)
    {
        $currentMonth = $request->input('month', Carbon::now()->format('Y-m'));
        $attendances = Attendance::ofUser(Auth::id())
            ->ofMonth($currentMonth)
            ->get();

        $formattedMonth = Carbon::parse($currentMonth)->format('Y/m');
        $previousMonth = Carbon::parse($currentMonth)->subMonth()->format('Y-m');
        $nextMonth = Carbon::parse($currentMonth)->addMonth()->format('Y-m');

        return view('attendance.list', compact(
            'attendances',
            'currentMonth',
            'previousMonth',
            'nextMonth',
            'formattedMonth'
        ));
    }
}
