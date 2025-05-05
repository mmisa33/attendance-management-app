<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\User;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class AttendanceController extends Controller
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

    public function attendanceList(Request $request, $id)
    {
        // 管理者はスタッフIDが必要
        $userId = $id;
        $staff = User::findOrFail($userId);

        return $this->getAttendanceList($request, $userId, 'admin.attendance.staff', $staff);
    }

    // 労働時間や休憩時間をフォーマットする共通処理
    private function getAttendanceList(Request $request, $userId, $view, $staff = null)
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

        // ビューを返す
        return view($view, compact('attendances', 'currentMonth', 'previousMonth', 'nextMonth', 'formattedMonth', 'staff'));
    }

    public function exportCsv(Request $request, $id)
    {
        // スタッフIDを元にスタッフ情報を取得
        $staff = User::findOrFail($id);

        // 現在の月を取得
        $currentMonth = $request->input('month', Carbon::now()->format('Y-m'));

        // 月の初日と最終日を計算
        $startOfMonth = Carbon::parse($currentMonth)->startOfMonth();
        $endOfMonth = Carbon::parse($currentMonth)->endOfMonth();

        // 勤怠情報をデータベースから取得
        $attendances = Attendance::where('user_id', $id)
            ->whereBetween('date', [$startOfMonth, $endOfMonth])
            ->orderBy('date', 'asc')
            ->get();

        // CSVデータを準備
        $csvData = [];
        $csvData[] = ['日付', '出勤', '退勤', '休憩', '合計']; // ヘッダー

        foreach ($attendances as $attendance) {
            $date = Carbon::parse($attendance->date);

            // 勤怠情報をフォーマット
            $attendance->formatted_date = $date->locale('ja')->format('m/d') . '(' . $date->isoFormat('ddd') . ')';
            $attendance->start_time_formatted = $attendance->start_time
                ? Carbon::parse($attendance->start_time)->format('H:i')
                : '';
            $attendance->end_time_formatted = $attendance->end_time
                ? Carbon::parse($attendance->end_time)->format('H:i')
                : '';

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

            // CSVデータを追加
            $csvData[] = [
                $attendance->formatted_date,
                $attendance->start_time_formatted,
                $attendance->end_time_formatted,
                $attendance->total_break_time,
                $attendance->total_hours,
            ];
        }

        // CSVファイルとして出力
        $filename = Carbon::parse($currentMonth)->format('Y-m') . '_勤怠_' . $staff->name . '.csv';
        $handle = fopen('php://output', 'w');
        ob_start();
        foreach ($csvData as $row) {
            fputcsv($handle, $row);
        }

        $csvContent = ob_get_clean();

        // ヘッダー設定
        return response($csvContent)
            ->header('Content-Type', 'text/csv')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
    }
}