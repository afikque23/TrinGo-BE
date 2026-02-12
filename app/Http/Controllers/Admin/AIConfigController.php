<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AIConfigController extends Controller
{
    /**
     * Display AI configuration page
     */
    public function index()
    {
        return view('admin.konfigurasi_AI.konfigurasi_ai');
    }
}
