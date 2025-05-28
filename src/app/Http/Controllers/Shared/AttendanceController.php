<?php

namespace App\Http\Controllers\Shared;

use App\Http\Controllers\Controller;
use App\Http\Requests\AttendanceDetailRequest;
use App\Models\Attendance;
use Illuminate\Support\Facades\Auth;

class AttendanceController extends Controller
{
    // 勤怠詳細ページを表示
    public function show($id)
    {
        if (Auth::guard('admin')->check()) {
            // 管理者がログインしている場合
            $attendance = Attendance::findOrFail($id);
            $user = $attendance->user;
        } elseif (Auth::guard('web')->check()) {
            // 一般ユーザーがログインしている場合
            $user = Auth::guard('web')->user();
            $attendance = Attendance::where('user_id', $user->id)->findOrFail($id);
        } else {
            // ログインしていない場合はアクセス禁止
            abort(403);
        }
        return view('attendance.show', [
            'user' => $user,
            'attendance' => $attendance,
            'breakRows' => $attendance->formatted_break_rows,
        ]);
    }

    // 勤怠詳細の更新処理
    public function update(AttendanceDetailRequest $request, $id)
    {
        $validated = $request->validated();

        if (Auth::guard('admin')->check()) {
            // 管理者がログインしている場合
            $attendance = Attendance::findOrFail($id);
            $attendance->updateAttendance($validated, true);
            $attendance->is_modified = true;
            $attendance->is_approved = true;
            $attendance->request_date = now();
            $attendance->save();
            $redirectRoute = 'stamp_correction_request.list';
        } elseif (Auth::guard('web')->check()) {
            // 一般ユーザーがログインしている場合
            $attendance = Attendance::where('user_id', Auth::id())->findOrFail($id);
            $attendance->updateAttendance($validated, false);
            $attendance->is_modified = true;
            $attendance->request_date = now();
            $attendance->save();
            $redirectRoute = 'stamp_correction_request.list';
        } else {
            // ログインしていない場合はログインページへリダイレクト
            return redirect()->route('login');
        }

        // 勤怠一覧ページへリダイレクト
        return redirect()->route($redirectRoute);
    }
}