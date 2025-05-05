<?php

namespace App\Http\Controllers;

use App\Models\User;

class AdminStaffController extends Controller
{
    public function index()
    {
        $staffList = User::select('id', 'name', 'email')->get();

        return view('admin.staff.index', compact('staffList'));
    }
}