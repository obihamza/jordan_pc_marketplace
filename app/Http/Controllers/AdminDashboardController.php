<?php

namespace App\Http\Controllers;

class AdminDashboardController extends Controller
{
    public function index()
    {
        abort_unless(auth()->user()->isAdmin(), 403);

        return view('admin.dashboard');
    }
}
