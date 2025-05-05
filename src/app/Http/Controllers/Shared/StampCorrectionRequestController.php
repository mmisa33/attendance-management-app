<?php

namespace App\Http\Controllers\Shared;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class StampCorrectionRequestController extends Controller
{
    // 承認待ち申請一覧ページを表示
    public function list()
    {
        if (Auth::guard('admin')->check()) {
            // 管理者：全ユーザーの申請を取得
            $pendingRequests = Attendance::where('is_modified', true)
                ->where('is_approved', false)
                ->with('user')
                ->get()
                ->map(function ($request) {
                    $request->formatted_date = Carbon::parse($request->date)->format('Y/m/d');
                    $request->formatted_created_at = Carbon::parse($request->created_at)->format('Y/m/d');
                    return $request;
                });

            $approvedRequests = Attendance::where('is_approved', true)
                ->with('user')
                ->get()
                ->map(function ($request) {
                    $request->formatted_date = Carbon::parse($request->date)->format('Y/m/d');
                    $request->formatted_created_at = Carbon::parse($request->created_at)->format('Y/m/d');
                    return $request;
                });
        } elseif (Auth::guard('web')->check()) {
            // 一般ユーザー：自分の申請のみ
            $userId = Auth::guard('web')->id();

            $pendingRequests = Attendance::where('user_id', $userId)
                ->where('is_modified', true)
                ->where('is_approved', false)
                ->with('user')
                ->get()
                ->map(function ($request) {
                    $request->formatted_date = Carbon::parse($request->date)->format('Y/m/d');
                    $request->formatted_created_at = Carbon::parse($request->created_at)->format('Y/m/d');
                    return $request;
                });

            $approvedRequests = Attendance::where('user_id', $userId)
                ->where('is_approved', true)
                ->with('user')
                ->get()
                ->map(function ($request) {
                    $request->formatted_date = Carbon::parse($request->date)->format('Y/m/d');
                    $request->formatted_created_at = Carbon::parse($request->created_at)->format('Y/m/d');
                    return $request;
                });
        } else {
            abort(403);
        }

        return view('stamp_correction_request.list', compact('pendingRequests', 'approvedRequests'));
    }
}