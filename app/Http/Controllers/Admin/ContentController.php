<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ContentController extends Controller
{
    /**
     * Display content management
     */
    public function index()
    {
        return view('admin.manajemen_konten.manajemen_konten');
    }
}
