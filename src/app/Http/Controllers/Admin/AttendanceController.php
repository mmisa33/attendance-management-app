<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\User;
use Illuminate\Http\Request;
use Carbon\Carbon;

class AttendanceController extends Controller
{
    // 管理者用勤怠一覧ページを表示
    public function adminAttendanceList(Request $request)
    {
        $date = Carbon::parse($request->input('date', Carbon::today()));

        $attendances = Attendance::with('user')
            ->byDate($date->toDateString())
            ->orderBy('user_id')
            ->get();

        $formattedCurrentDate = $date->format('Y年n月j日');
        $formattedDate = $date->format('Y/m/d');
        $previousDate = $date->copy()->subDay()->format('Y-m-d');
        $nextDate = $date->copy()->addDay()->format('Y-m-d');

        return view('admin.attendance.list', [
            'formattedCurrentDate' => $formattedCurrentDate,
            'attendances' => $attendances,
            'formattedDate' => $formattedDate,
            'previousDate' => $previousDate,
            'nextDate' => $nextDate,
        ]);
    }

    // スタッフの勤怠一覧表示
    public function showStaffAttendance(Request $request, $id)
    {
        $staff = User::findOrFail($id);
        $currentMonth = $request->input('month', Carbon::now()->format('Y-m'));

        $attendances = Attendance::ofUser($id)
            ->ofMonth($currentMonth)
            ->get();

        $formattedMonth = Carbon::parse($currentMonth)->format('Y/m');
        $previousMonth = Carbon::parse($currentMonth)->subMonth()->format('Y-m');
        $nextMonth = Carbon::parse($currentMonth)->addMonth()->format('Y-m');

        return view('admin.attendance.staff', compact(
            'attendances',
            'currentMonth',
            'previousMonth',
            'nextMonth',
            'formattedMonth',
            'staff'
        ));
    }

    // CSVエクスポート処理
    public function exportStaffAttendanceCsv(Request $request, $id)
    {
        $staff = User::findOrFail($id);
        $currentMonth = $request->input('month', Carbon::now()->format('Y-m'));
        $attendances = Attendance::ofUser($id)
            ->ofMonth($currentMonth)
            ->get();

        $csvData = [['日付', '出勤', '退勤', '休憩', '合計']];
        foreach ($attendances as $attendance) {
            $csvData[] = $attendance->toCsvRow();
        }

        $filename = Carbon::parse($currentMonth)->format('Y-m') . '_勤怠_' . $staff->name . '.csv';
        $handle = fopen('php://output', 'w');
        ob_start();
        foreach ($csvData as $row) {
            fputcsv($handle, $row);
        }
        $csvContent = ob_get_clean();

        return response($csvContent)
            ->header('Content-Type', 'text/csv')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
    }
}
