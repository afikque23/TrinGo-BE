<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Vehicle;
use App\Models\Trip;
use App\Models\ServiceHistory;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    /**
     * Display admin dashboard
     */
    public function index()
    {
        $data = [
            'totalUsers' => User::count(),
            'totalVehicles' => Vehicle::count(),
            'totalTrips' => Trip::count(),
            'totalServices' => ServiceHistory::count(),
            'recentUsers' => User::latest()->take(5)->get(),
        ];

        return view('admin.dashboard_admin.dashboard_admin', $data);
    }
}
