<?php

namespace App\Http\Controllers\Shared;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use Illuminate\Support\Facades\Auth;

class StampCorrectionRequestController extends Controller
{
    // 承認待ち申請一覧ページを表示
    public function list()
    {
        if (Auth::guard('admin')->check()) {
            // 管理者は全ユーザーの申請を取得
            $pendingRequests = Attendance::where('is_modified', true)
                ->where('is_approved', false)
                ->with('user')
                ->orderBy('date', 'asc')
                ->orderBy('request_date', 'asc')
                ->get();

            $approvedRequests = Attendance::where('is_approved', true)
                ->with('user')
                ->orderBy('date', 'asc')
                ->orderBy('request_date', 'asc')
                ->get();
        } elseif (Auth::guard('web')->check()) {
            // 一般ユーザーは自分の申請のみ取得
            $userId = Auth::guard('web')->id();

            $pendingRequests = Attendance::where('user_id', $userId)
                ->where('is_modified', true)
                ->where('is_approved', false)
                ->with('user')
                ->orderBy('date', 'asc')
                ->orderBy('request_date', 'asc')
                ->get();

            $approvedRequests = Attendance::where('user_id', $userId)
                ->where('is_approved', true)
                ->with('user')
                ->orderBy('date', 'asc')
                ->orderBy('request_date', 'asc')
                ->get();
        } else {
            abort(403);
        }

        return view('stamp_correction_request.list', compact('pendingRequests', 'approvedRequests'));
    }
}