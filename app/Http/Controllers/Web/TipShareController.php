<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Tip;
use Illuminate\Http\Request;

class TipShareController extends Controller
{
    /**
     * Tampilkan halaman preview web untuk dibagikan ke WhatsApp dll.
     */
    public function show($id)
    {
        // Ambil data Tip beserta relasi author (User)
        // failOrFail otomatis return 404 jika tidak ketemu
        $tip = Tip::with('user')->findOrFail($id);
        
        return view('tips.share', compact('tip'));
    }
}
