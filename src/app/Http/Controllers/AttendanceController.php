<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class AttendanceController extends Controller
{
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

    public function breakStart()
    {
        $attendance = Attendance::where('user_id', Auth::id())->where('date', Carbon::today())->first();

        if ($attendance->status === '出勤中') {
            $attendance->update(['status' => '休憩中']);
            $attendance->breakTimes()->create(['break_start' => Carbon::now()]);
        }

        return redirect()->route('attendance.index');
    }

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
}