<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use Illuminate\Http\Request;
use Carbon\Carbon;

class StampCorrectionRequestController extends Controller
{
    public function show($id)
    {
        $attendance = Attendance::with(['user', 'breakTimes'])->findOrFail($id);

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

        return view('admin.stamp_correction_request.show', [
            'attendance' => $attendance,
            'breakRows' => $breakRows,
        ]);
    }

    public function approve($id)
    {
        $attendance = Attendance::findOrFail($id);
        $attendance->is_approved = true;
        $attendance->save();

        return redirect()->route('stamp_correction_request.list');
    }
}