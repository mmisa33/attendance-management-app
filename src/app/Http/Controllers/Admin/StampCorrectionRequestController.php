<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Attendance;

class StampCorrectionRequestController extends Controller
{
    // 修正申請承認ページを表示
    public function show($attendance_correction_request)
    {
        $attendance = Attendance::with(['user'])->findOrFail($attendance_correction_request);

        return view('admin.stamp_correction_request.show', [
            'attendance' => $attendance,
            'breakRows' => $attendance->formatted_break_rows,
        ]);
    }

    // 承認処理
    public function approve($attendance_correction_request)
    {
        $attendance = Attendance::findOrFail($attendance_correction_request);
        $attendance->is_approved = true;
        $attendance->save();

        return redirect()->route('stamp_correction_request.list');
    }
}