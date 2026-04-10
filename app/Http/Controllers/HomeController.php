<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Ruangan;

class HomeController extends Controller
{
    /**
     * Menampilkan halaman utama
     */
    public function index()
    {
        // Ambil 6 ruangan yang tersedia untuk ditampilkan di homepage
        $ruangan = Ruangan::where('status', 'tersedia')
                         ->limit(6)
                         ->get();
        
        return view('home', compact('ruangan'));
    }
}