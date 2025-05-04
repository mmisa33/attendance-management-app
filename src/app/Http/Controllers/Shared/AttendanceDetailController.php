<?php

namespace App\Http\Controllers\Shared;

use App\Http\Controllers\Controller;
use App\Http\Requests\AttendanceDetailRequest;
use App\Models\Attendance;
use Illuminate\Support\Facades\Auth;
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

        // 休憩のデータを整形（休憩の数とタイトルを動的に設定）
        $breakTimes = $attendance->breakTimes;
        $breakCount = count($breakTimes);
        $breakRows = [];

        // 休憩データを整形して格納
        for ($i = 0; $i < $breakCount + 1; $i++) {
            $breakRows[] = [
                'index' => $i,
                'start' => old("break_start.$i", isset($attendance->breakTimes[$i]) ? substr($attendance->breakTimes[$i]->break_start, 11, 5) : ''),
                'end' => old("break_end.$i", isset($attendance->breakTimes[$i]) ? substr($attendance->breakTimes[$i]->break_end, 11, 5) : '')
            ];
        }

        return view('attendance.details', [
            'user' => $user,
            'attendance' => $attendance,
            'breakRows' => $breakRows,
        ]);
    }

    public function updateDetail(AttendanceDetailRequest $request, $attendanceId)
    {
        // 一般ユーザーか管理者かを判定
        $validated = $request->validated();

        // 一般ユーザーの場合
        if (Auth::guard('web')->check()) {
            $attendance = Attendance::where('user_id', Auth::id())->findOrFail($attendanceId);

            // 修正申請状態に変更
            $attendance->is_modified = true; // 修正申請中
            $redirectRoute = 'attendance.list';
        }
        // 管理者の場合
        elseif (Auth::guard('admin')->check()) {
            $attendance = Attendance::findOrFail($attendanceId);

            // 直接修正
            $attendance->is_modified = false; // 直接修正されたので修正申請状態を解除
            $redirectRoute = 'admin.attendance.list';
        } else {
            // 認証されていない場合の処理
            return redirect()->route('login');
        }

        // 勤怠情報の修正
        $attendance->start_time = $attendance->date . ' ' . $validated['start_time'] . ':00';
        $attendance->end_time = $attendance->date . ' ' . $validated['end_time'] . ':00';
        $attendance->note = $validated['note'];
        $attendance->save();

        // 休憩時間の更新
        // 既存のbreakTimeを更新
        foreach ($attendance->breakTimes as $i => $breakTime) {
            $startInput = $validated['break_start'][$i] ?? null;
            $endInput = $validated['break_end'][$i] ?? null;

            if ($startInput && $endInput) {
                $breakTime->break_start = $attendance->date . ' ' . $startInput . ':00';
                $breakTime->break_end = $attendance->date . ' ' . $endInput . ':00';
                $breakTime->save();
            }
        }

        // 新規休憩時間を追加
        $existingCount = count($attendance->breakTimes);
        $additionalStarts = array_slice($validated['break_start'], $existingCount);
        $additionalEnds = array_slice($validated['break_end'], $existingCount);

        foreach ($additionalStarts as $i => $start) {
            $end = $additionalEnds[$i] ?? null;

            if ($start && $end) {
                $attendance->breakTimes()->create([
                    'break_start' => $attendance->date . ' ' . $start . ':00',
                    'break_end' => $attendance->date . ' ' . $end . ':00',
                ]);
            }
        }

        // リダイレクト先に移動
        return redirect()->route($redirectRoute);
    }
}