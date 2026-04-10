<?php

use App\Http\Controllers\HomeController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\PeminjamanRuanganController;
use App\Http\Controllers\Admin\JadwalPeminjamanController;
use App\Http\Controllers\Admin\LaporanController;
use App\Http\Controllers\Admin\ProfilController as AdminProfilController;
use App\Http\Controllers\Pegawai\JadwalController;
use App\Http\Controllers\Pegawai\DashboardController as PegawaiDashboardController;
use App\Http\Controllers\Pegawai\JadwalRuanganController;
use App\Http\Controllers\Pegawai\LaporanController as PegawaiLaporanController;
use App\Http\Controllers\Pegawai\ProfilController as PegawaiProfilController;
use App\Http\Controllers\Pegawai\RuanganController as PegawaiRuanganController;
use App\Http\Controllers\User\KegiatanController;
use App\Http\Controllers\Admin\KegiatanController as AdminKegiatanController;
use App\Http\Controllers\RuanganController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\User\DashboardController as UserDashboardController;
use App\Http\Controllers\User\RuanganController as UserRuanganController;
use App\Http\Controllers\ProfilController;
use App\Http\Controllers\Admin\LogActivityController;
use Illuminate\Support\Facades\Route;

// =================== ROUTE CHECK SESSION (TANPA MIDDLEWARE) ===================
Route::get('/check-session', function() {
    if (auth()->check()) {
        return response()->json([
            'authenticated' => true,
            'user' => [
                'id' => auth()->user()->id,
                'username' => auth()->user()->username ?? null,
                'name' => auth()->user()->name,
                'email' => auth()->user()->email,
                'role' => auth()->user()->role
            ]
        ]);
    }
    
    return response()->json([
        'authenticated' => false,
        'user' => null
    ]);
})->name('check.session');

// =================== ROUTE CHECK KETERSEDIAAN (AJAX) - UNTUK REGISTER ===================
Route::prefix('check')->name('check.')->group(function () {
    Route::post('/username', [AuthController::class, 'checkUsername'])->name('username');
    Route::post('/email', [AuthController::class, 'checkEmail'])->name('email');
    Route::post('/phone', [AuthController::class, 'checkPhone'])->name('phone');
    Route::get('/name', [AuthController::class, 'checkName'])->name('name');
});

// =================== PUBLIC ROUTES ===================
Route::get('/', [HomeController::class, 'index'])->name('home');

// Auth Routes
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
Route::post('/register', [AuthController::class, 'register']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// =================== AUTHENTICATED ROUTES ===================
Route::middleware(['auth', 'prevent-back'])->group(function () {
    
    // =================== PROFIL ROUTES (SATU KALI SAJA) ===================
    Route::prefix('profil')->name('profil.')->group(function () {
        Route::get('/', [ProfilController::class, 'index'])->name('index');
        Route::get('/edit', [ProfilController::class, 'edit'])->name('edit');
        Route::put('/update', [ProfilController::class, 'update'])->name('update');
        Route::put('/update-pribadi', [ProfilController::class, 'updatePribadi'])->name('update-pribadi');
        Route::put('/update-akademik', [ProfilController::class, 'updateAkademik'])->name('update-akademik');
        Route::post('/upload-photo', [ProfilController::class, 'uploadPhoto'])->name('upload-photo');
        Route::post('/change-password', [ProfilController::class, 'changePassword'])->name('change-password');
        Route::put('/update-password', [ProfilController::class, 'updatePassword'])->name('update-password');
        Route::delete('/delete-photo', [ProfilController::class, 'deletePhoto'])->name('delete-photo');
        Route::get('/akademik', [ProfilController::class, 'editAkademik'])->name('akademik');
        
        // Route untuk username
        Route::put('/update-username', [ProfilController::class, 'updateUsername'])->name('update-username');
        Route::post('/check-username', [ProfilController::class, 'checkUsernameAvailability'])->name('check-username');
    });
    
    // Peminjaman ruangan (global edit/delete)
    Route::prefix('peminjaman-ruangan')->name('peminjaman-ruangan.')->group(function () {
        Route::get('/{id}/edit', [PeminjamanRuanganController::class, 'edit'])->name('edit');
        Route::put('/{id}', [PeminjamanRuanganController::class, 'update'])->name('update');
        Route::patch('/{id}', [PeminjamanRuanganController::class, 'update']);
        Route::delete('/{id}', [PeminjamanRuanganController::class, 'destroy'])->name('destroy');
        
        // Route untuk public availability check
        Route::post('/check-availability', [PeminjamanRuanganController::class, 'checkAvailability'])->name('check-availability');
    });
});

// =================== USER ROUTES ===================
Route::middleware(['auth', 'checkrole:user', 'prevent-back'])->prefix('user')->name('user.')->group(function () {
    Route::get('/dashboard', [UserDashboardController::class, 'index'])->name('dashboard');
    
    // Peminjaman Ruangan
    Route::prefix('peminjaman-ruangan')->name('peminjaman-ruangan.')->group(function () {
        Route::get('/create', [PeminjamanRuanganController::class, 'create'])->name('create');
        Route::post('/', [PeminjamanRuanganController::class, 'store'])->name('store');
        Route::get('/riwayat', [PeminjamanRuanganController::class, 'riwayat'])->name('riwayat');
        Route::get('/detail/{id}', [PeminjamanRuanganController::class, 'detailUser'])->name('detail');
        Route::post('/cancel/{id}', [PeminjamanRuanganController::class, 'cancelUser'])->name('cancel');
        Route::get('/download-surat/{id}', [PeminjamanRuanganController::class, 'downloadSurat'])->name('download');
        
        // Route untuk user availability check
        Route::post('/check-availability-user', [PeminjamanRuanganController::class, 'checkAvailabilityUser'])->name('check-availability-user');
    });
    
    // Daftar Ruangan
    Route::get('/ruangan', [UserRuanganController::class, 'index'])->name('ruangan.index');
    Route::get('/ruangan/{id}', [UserRuanganController::class, 'show'])->name('ruangan.show');
    Route::post('/ruangan/{id}/check-availability', [UserRuanganController::class, 'checkAvailability'])->name('ruangan.check-availability');
    
    // Halaman statis
    Route::get('/lihat-jadwal', function () {
        return view('user.lihat-jadwal');
    })->name('lihat-jadwal');
    
    Route::get('/daftar-kegiatan', [KegiatanController::class, 'index'])->name('daftar-kegiatan');
});

// =================== PEGAWAI ROUTES ===================
Route::middleware(['auth', 'checkrole:pegawai', 'prevent-back'])->prefix('pegawai')->name('pegawai.')->group(function () {
    // Dashboard Pegawai
    Route::get('/dashboard', [PegawaiDashboardController::class, 'index'])->name('dashboard');
    
    // =================== MANAJEMEN RUANGAN UNTUK PEGAWAI ===================
    Route::prefix('ruangan')->name('ruangan.')->group(function () {
        // ========== ROUTE UTAMA UNTUK UPDATE STATUS RUANGAN ==========
        // Route ini dipanggil oleh JavaScript di jadwal-ruangan.blade.php
        Route::post('/{id}/update-status', [JadwalRuanganController::class, 'updateRuanganStatus'])->name('update-status');
        
        // Route tambahan untuk keperluan lain
        Route::get('/{id}/status-history', [PegawaiRuanganController::class, 'getStatusHistory'])->name('status-history');
        Route::get('/{id}', [PegawaiRuanganController::class, 'show'])->name('show');
    });
    
    // Peminjaman Ruangan Management untuk Pegawai
    Route::prefix('peminjaman-ruangan')->name('peminjaman-ruangan.')->group(function () {
        Route::get('/', [PeminjamanRuanganController::class, 'indexPegawai'])->name('index');
        Route::get('/detail/{id}', [PeminjamanRuanganController::class, 'detailPegawai'])->name('detail');
        Route::get('/download-surat/{id}', [PeminjamanRuanganController::class, 'downloadSurat'])->name('download-surat');
        Route::post('/update-status', [PeminjamanRuanganController::class, 'updateStatusRealTime'])->name('update-status');
        Route::post('/{id}/update-status-real-time', [JadwalRuanganController::class, 'updateStatusRealTime'])->name('update-status-real-time');
        Route::post('/approve/{id}', [PeminjamanRuanganController::class, 'approvePegawai'])->name('approve');
        Route::post('/reject/{id}', [PeminjamanRuanganController::class, 'rejectPegawai'])->name('reject');
        Route::post('/cancel/{id}', [PeminjamanRuanganController::class, 'cancelPegawai'])->name('cancel');
        
        // Route untuk melihat riwayat catatan
        Route::get('/{id}/catatan', [JadwalRuanganController::class, 'getCatatan'])->name('catatan.get');
    });
    
    // Jadwal Management
    Route::prefix('jadwal')->name('jadwal.')->group(function () {
        Route::get('/', [JadwalController::class, 'index'])->name('index');
        Route::get('/create', [JadwalController::class, 'create'])->name('create');
        Route::get('/semua', [JadwalController::class, 'semuaKegiatan'])->name('semua');
        Route::get('/{jadwal}', [JadwalController::class, 'show'])->name('show');
        Route::get('/{id}/edit', [JadwalController::class, 'edit'])->name('edit');
        Route::get('/{id}/data', [JadwalController::class, 'getJadwalData'])->name('data');
        Route::post('/', [JadwalController::class, 'store'])->name('store');
        Route::post('/check-availability', [JadwalController::class, 'checkAvailability'])->name('checkAvailability');
        Route::put('/{id}', [JadwalController::class, 'update'])->name('update');
        Route::patch('/{id}', [JadwalController::class, 'update']);
        Route::delete('/{id}', [JadwalController::class, 'destroy'])->name('destroy');
    });
    
    // Route alternatif
    Route::get('/jadwal-staff', [JadwalController::class, 'index'])->name('jadwal-staff');
    Route::get('/buat-jadwal', [JadwalController::class, 'create'])->name('buat-jadwal');
    Route::get('/semua-kegiatan', [JadwalController::class, 'semuaKegiatan'])->name('semua-kegiatan');

    // Jadwal Ruangan
    Route::prefix('jadwal-ruangan')->name('jadwal-ruangan.')->group(function () {
        Route::get('/', [JadwalRuanganController::class, 'index'])->name('index');
        Route::get('/get-schedule', [JadwalRuanganController::class, 'getSchedule'])->name('get-schedule');
        Route::get('/availability', [JadwalRuanganController::class, 'getRuanganAvailability'])->name('availability');
        Route::post('/check-availability', [JadwalRuanganController::class, 'checkAvailability'])->name('check-availability');
        Route::post('/{id}/set-dipakai', [JadwalRuanganController::class, 'setRuanganDipakai'])->name('set-dipakai');
        Route::post('/{id}/set-selesai', [JadwalRuanganController::class, 'setRuanganSelesai'])->name('set-selesai');
    });
    
    // Laporan Pegawai
    Route::prefix('laporan')->name('laporan.')->group(function () {
        Route::get('/', [PegawaiLaporanController::class, 'index'])->name('index');
        Route::post('/export/pdf', [PegawaiLaporanController::class, 'exportPDF'])->name('export.pdf');
        Route::post('/export/excel', [PegawaiLaporanController::class, 'exportExcel'])->name('export.excel');
        Route::get('/export/pdf', [PegawaiLaporanController::class, 'exportPDF'])->name('export.pdf.get');
        Route::get('/export/excel', [PegawaiLaporanController::class, 'exportExcel'])->name('export.excel.get');
        Route::get('/dashboard-data', [PegawaiLaporanController::class, 'dashboardData'])->name('dashboard-data');
        Route::get('/ruangan-stats', [PegawaiLaporanController::class, 'getRuanganStats'])->name('ruangan-stats');
    });
    
    // =================== PROFIL PEGAWAI ===================
    Route::prefix('profil')->name('profil.')->group(function () {
        Route::get('/', [PegawaiProfilController::class, 'index'])->name('index');
        Route::put('/', [PegawaiProfilController::class, 'update'])->name('update');
        Route::post('/upload-photo', [PegawaiProfilController::class, 'uploadPhoto'])->name('upload-photo');
        Route::delete('/delete-photo', [PegawaiProfilController::class, 'deletePhoto'])->name('delete-photo');
        Route::post('/change-password', [PegawaiProfilController::class, 'changePassword'])->name('change-password');
        Route::put('/update-notifikasi', [PegawaiProfilController::class, 'updateNotifikasi'])->name('update-notifikasi');
    });
});

// =================== ADMIN ROUTES ===================
Route::middleware(['auth', 'checkrole:admin', 'prevent-back'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        
        Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');
        Route::get('/log-aktivitas', [LogActivityController::class, 'index'])->name('log-aktivitas');
        Route::get('/log-aktivitas/{id}/detail', [LogActivityController::class, 'detail'])->name('log-aktivitas.detail');
        
        // Laporan Routes
        Route::prefix('laporan')->name('laporan.')->group(function () {
            Route::get('/', [LaporanController::class, 'index'])->name('index');
            Route::get('/cetak-pdf', [LaporanController::class, 'cetakPdf'])->name('cetak-pdf');
            Route::get('/cetak-excel', [LaporanController::class, 'cetakExcel'])->name('cetak-excel');
            Route::post('/simpan-filter', [LaporanController::class, 'simpanFilter'])->name('simpan-filter');
            Route::get('/statistik-ruangan', [LaporanController::class, 'statistikRuangan'])->name('statistik-ruangan');
            Route::get('/chart-data', [LaporanController::class, 'getChartData'])->name('chart-data');
        });
        
        // Users Management
        Route::prefix('users')->name('users.')->group(function () {
            Route::get('/', [AdminController::class, 'daftarUsers'])->name('index');
            Route::get('/{user}/data', [AdminController::class, 'getUserData'])->name('data');
            Route::post('/', [AdminController::class, 'storeUser'])->name('store');
            Route::put('/{user}', [AdminController::class, 'updateUserData'])->name('update');
            Route::patch('/{user}', [AdminController::class, 'updateUserData']);
            Route::delete('/{user}', [AdminController::class, 'deleteUser'])->name('destroy');
            Route::patch('/{user}/toggle-status', [AdminController::class, 'toggleUserStatus'])->name('toggle-status');
            Route::get('/{user}/akademik', [AdminController::class, 'editAkademik'])->name('akademik');
            Route::put('/{user}/update-akademik', [AdminController::class, 'updateAkademik'])->name('update-akademik');
        });
        
        // Ruangan Management
        Route::prefix('ruangan')->name('ruangan.')->group(function () {
            Route::get('/', [RuanganController::class, 'index'])->name('index');
            Route::get('/statistics', [RuanganController::class, 'getStatistics'])->name('statistics');
            Route::get('/{id}/detail', [RuanganController::class, 'show'])->name('detail');
            Route::post('/', [RuanganController::class, 'store'])->name('store');
            Route::put('/{id}', [RuanganController::class, 'update'])->name('update');
            Route::patch('/{id}', [RuanganController::class, 'update']);
            Route::delete('/{id}', [RuanganController::class, 'destroy'])->name('destroy');
            Route::get('/{id}/check-status', [RuanganController::class, 'checkStatus'])->name('check-status');
            Route::get('/check-kode', [RuanganController::class, 'checkKode'])->name('check-kode');
        });
        
        // Peminjaman Ruangan Management
        Route::prefix('peminjaman-ruangan')->name('peminjaman-ruangan.')->group(function () {
            Route::get('/', [PeminjamanRuanganController::class, 'indexAdmin'])->name('index');
            Route::get('/detail/{id}', [PeminjamanRuanganController::class, 'detailAdmin'])->name('detail');
            Route::post('/approve/{id}', [PeminjamanRuanganController::class, 'approveAdmin'])->name('approve');
            Route::post('/reject/{id}', [PeminjamanRuanganController::class, 'rejectAdmin'])->name('reject');
            Route::post('/cancel/{id}', [PeminjamanRuanganController::class, 'cancelAdmin'])->name('cancel');
            Route::get('/download-surat/{id}', [PeminjamanRuanganController::class, 'downloadSurat'])->name('download-surat');
            Route::post('/update-status', [PeminjamanRuanganController::class, 'updateStatusRealTime'])->name('update-status');
            Route::get('/export', [PeminjamanRuanganController::class, 'export'])->name('export');
            Route::get('/jadwal-ruangan', [PeminjamanRuanganController::class, 'getJadwalRuangan'])->name('jadwal-ruangan');
            
            // Routes untuk modal API
            Route::get('/api/detail/{id}', [JadwalPeminjamanController::class, 'getDetail'])->name('api.detail');
            Route::post('/api/{id}/status', [JadwalPeminjamanController::class, 'updateStatus'])->name('api.status.update');
            Route::delete('/api/{id}', [JadwalPeminjamanController::class, 'delete'])->name('api.delete');
            
            // Route untuk mengambil data edit (GET)
            Route::get('/{id}/edit-data', [JadwalPeminjamanController::class, 'editData'])->name('edit-data');
            
            // Route untuk proses update
            Route::post('/{id}', [JadwalPeminjamanController::class, 'update'])->name('update');
            Route::put('/{id}', [JadwalPeminjamanController::class, 'update'])->name('update.put');
            
            // Routes untuk catatan admin
            Route::get('/{id}/catatan', [JadwalPeminjamanController::class, 'getCatatan'])->name('catatan.get');
            Route::post('/{id}/catatan', [JadwalPeminjamanController::class, 'updateCatatan'])->name('catatan.update');
            Route::delete('/{id}/catatan', [JadwalPeminjamanController::class, 'deleteCatatan'])->name('catatan.delete');
        });
        
        // Jadwal & Kegiatan
        Route::get('/jadwal-pegawai', [AdminController::class, 'jadwalPegawai'])->name('jadwal-pegawai');
        Route::get('/daftar-kegiatan', [AdminKegiatanController::class, 'index'])->name('daftar-kegiatan');
        Route::get('/semua-jadwal', [JadwalPeminjamanController::class, 'semuaData'])->name('semua-jadwal');
        Route::get('/jadwal-peminjaman', [JadwalPeminjamanController::class, 'index'])->name('jadwal-peminjaman');
        Route::get('/semua-peminjaman', [JadwalPeminjamanController::class, 'semuaPeminjaman'])->name('semua-peminjaman');
        
        // =================== PROFIL ADMIN ===================
        Route::prefix('profil')->name('profil.')->group(function () {
            Route::get('/', [AdminProfilController::class, 'index'])->name('index');
            Route::put('/', [AdminProfilController::class, 'update'])->name('update');
            Route::post('/upload-photo', [AdminProfilController::class, 'uploadPhoto'])->name('upload-photo');
            Route::delete('/delete-photo', [AdminProfilController::class, 'deletePhoto'])->name('delete-photo');
            Route::post('/change-password', [AdminProfilController::class, 'changePassword'])->name('change-password');
            Route::put('/update-notifikasi', [AdminProfilController::class, 'updateNotifikasi'])->name('update-notifikasi');
        });
    });

// =================== FALLBACK ===================
Route::fallback(function () {
    return view('errors.404');
});