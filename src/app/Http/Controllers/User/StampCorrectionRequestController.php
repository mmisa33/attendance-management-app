<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use Illuminate\Support\Facades\Auth;

class StampCorrectionRequestController extends Controller
{
    // 承認待ち申請一覧ページを表示
    public function list()
    {
        // 承認待ち申請を取得
        $pendingModifications = Attendance::where('user_id', Auth::id())
            ->where('is_modified', true)
            ->where('is_approved', false)  // 承認されていない
            ->get()
            ->map(function ($modification) {
            $modification->formatted_date = \Carbon\Carbon::parse($modification->date)->format('Y/m/d');
            $modification->formatted_created_at = \Carbon\Carbon::parse($modification->created_at)->format('Y/m/d');
            return $modification;
        });

        // 承認済み申請を取得
        $approvedModifications = Attendance::where('user_id', Auth::id())
            ->where('is_approved', true)  // 承認済み
            ->get()
            ->map(function ($modification) {
            $modification->formatted_date = \Carbon\Carbon::parse($modification->date)->format('Y/m/d');
            $modification->formatted_created_at = \Carbon\Carbon::parse($modification->created_at)->format('Y/m/d');
            return $modification;
        });

        return view('modification.list', compact('pendingModifications', 'approvedModifications'));
    }
}
