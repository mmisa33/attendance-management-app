<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;

class StaffController extends Controller
{
    // スタッフ一覧ページを表示
    public function index()
    {
        $staffList = User::select('id', 'name', 'email')->get();

        return view('admin.staff.index', compact('staffList'));
    }
}