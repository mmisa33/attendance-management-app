<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use Illuminate\Support\Facades\Auth;

class AttendanceModificationController extends Controller
{
    // 承認待ち申請一覧ページを表示
    public function list()
    {
        // ログイン中のユーザーが行った承認待ち申請を取得
        $pendingModifications = Attendance::where('user_id', Auth::id())
            ->where('is_modified', true)
            ->where('is_approved', false)  // 承認されていない
            ->get();

        // 承認済みの申請も取得
        $approvedModifications = Attendance::where('user_id', Auth::id())
            ->where('is_approved', true)  // 承認済み
            ->get();

        return view('modification.list', compact('pendingModifications', 'approvedModifications'));
    }

    // 承認待ち申請詳細ページ
    public function details($attendanceId)
    {
        $attendance = Attendance::where('user_id', Auth::id())->findOrFail($attendanceId);

        // 承認待ちの場合、詳細ページでは修正できないことを通知
        if ($attendance->is_approved) {
            return view('attendance.modification.details', compact('attendance'));
        }

        // 承認待ちの場合、修正不可のメッセージを表示
        return view('attendance.modification.details', [
            'attendance' => $attendance,
            'message' => '承認待ちのため修正はできません。',
        ]);
    }
}
