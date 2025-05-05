<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class StampCorrectionRequestController extends Controller
{
    // 承認待ち申請一覧ページを表示
    public function list()
    {
        // 承認待ちの打刻修正申請を取得
        $pendingRequests = Attendance::where('user_id', Auth::id())
            ->where('is_modified', true)
            ->where('is_approved', false)
            ->get()
            ->map(function ($request) {
                $request->formatted_date = Carbon::parse($request->date)->format('Y/m/d');
                $request->formatted_created_at = Carbon::parse($request->created_at)->format('Y/m/d');
                return $request;
            });

        // 承認済みの打刻修正申請を取得
        $approvedRequests = Attendance::where('user_id', Auth::id())
            ->where('is_approved', true)
            ->get()
            ->map(function ($request) {
                $request->formatted_date = Carbon::parse($request->date)->format('Y/m/d');
                $request->formatted_created_at = Carbon::parse($request->created_at)->format('Y/m/d');
                return $request;
            });

        return view('stamp_correction_request.list', compact('pendingRequests', 'approvedRequests'));
    }
}