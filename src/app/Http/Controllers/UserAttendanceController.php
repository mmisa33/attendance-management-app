<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Http\Requests\AttendanceDetailRequest;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserAttendanceController extends Controller
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

        // 月の初日と最終日を計算
        $startOfMonth = Carbon::parse($currentMonth)->startOfMonth();
        $endOfMonth = Carbon::parse($currentMonth)->endOfMonth();

        // 勤怠情報をデータベースから取得
        $attendances = Attendance::where('user_id', Auth::id())
            ->whereBetween('date', [$startOfMonth, $endOfMonth])
            ->orderBy('date', 'asc')
            ->get();

        foreach ($attendances as $attendance) {
            // 勤怠の日付をフォーマット
            $date = Carbon::parse($attendance->date);

            // 出勤時刻、退勤時刻をフォーマット
            $attendance->formatted_date = $date->locale('ja')->format('m/d') . '(' . $date->isoFormat('ddd') . ')';
            $attendance->start_time_formatted = $attendance->start_time
                ? Carbon::parse($attendance->start_time)->format('H:i')
                : ''; // 出勤時刻があれば時間フォーマット、なければ空文字
            $attendance->end_time_formatted = $attendance->end_time
                ? Carbon::parse($attendance->end_time)->format('H:i')
                : ''; // 退勤時刻があれば時間フォーマット、なければ空文字

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

        // 当月の表示を変更
        $formattedMonth = Carbon::parse($currentMonth)->format('Y/m');

        // 前月、次月のURLを生成
        $previousMonth = Carbon::parse($currentMonth)->subMonth()->format('Y-m');
        $nextMonth = Carbon::parse($currentMonth)->addMonth()->format('Y-m');

        return view('attendance.list', compact('attendances', 'currentMonth', 'previousMonth', 'nextMonth', 'formattedMonth'));
    }

    // 勤怠詳細ページを表示
    public function attendanceDetails($attendanceId)
    {
        // ログインユーザーの勤怠情報を取得
        $attendance = Attendance::where('user_id', Auth::id())->findOrFail($attendanceId);

        // 休憩時間の情報をリレーションで取得
        $breakTimes = $attendance->breakTimes;

        // 日付を「YYYY年」と「n月j日」に分けてフォーマット
        $date = Carbon::parse($attendance->date);
        $attendance->formatted_year     = $date->format('Y') . '年';
        $attendance->formatted_monthday = $date->format('n') . '月' . $date->format('j') . '日';

        // 勤怠詳細画面を表示
        return view('attendance.details', [
            'attendance' => $attendance,
            'breakTimes' => $breakTimes,
        ]);
    }

    // 勤怠内容の修正を申請
    public function updateDetail(AttendanceDetailRequest $request, $attendanceId)
    {
        $validated = $request->validated();

        // 勤怠データ取得
        $attendance = Attendance::where('user_id', Auth::id())->findOrFail($attendanceId);

        // 出勤・退勤時間を更新
        $attendance->start_time = $attendance->date . ' ' . $validated['start_time'] . ':00';
        $attendance->end_time   = $attendance->date . ' ' . $validated['end_time'] . ':00';
        $attendance->note       = $validated['note'];
        $attendance->is_modified = true;
        $attendance->save();

        // 休憩時間を更新
        foreach ($attendance->breakTimes as $i => $breakTime) {
            $breakTime->break_start = $attendance->date . ' ' . ($validated['break_start'][$i] ?? '00:00') . ':00';
            $breakTime->break_end   = $attendance->date . ' ' . ($validated['break_end'][$i] ?? '00:00') . ':00';
            $breakTime->save();
        }

        // 更新後、申請一覧ページにリダイレクト
        return redirect()->route('stamp_correction_request.list');
    }
}