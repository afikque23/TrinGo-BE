<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class CommunityController extends Controller
{
    /**
     * Display community content management
     */
    public function index()
    {
        return view('admin.community');
    }

    /**
     * Display content monitoring page
     */
    public function konten()
    {
        return view('admin.konten_komunitas.konten_komunitas');
    }

    /**
     * Display content detail page
     */
    public function detail($id)
    {
        return view('admin.konten_komunitas.konten_detail', compact('id'));
    }
}
