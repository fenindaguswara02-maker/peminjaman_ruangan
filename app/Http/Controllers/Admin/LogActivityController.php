<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LogActivity;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Log;

class LogActivityController extends Controller
{
    public function index(Request $request)
    {
        try {
            // Cek apakah tabel log_activities ada
            if (!Schema::hasTable('log_activities')) {
                $logs = collect();
                $users = User::all();
                
                return view('admin.log-aktivitas.index', compact('logs', 'users'))
                    ->with('warning', 'Tabel log_activities belum dibuat. Silakan jalankan: php artisan migrate');
            }
            
            // Query log activity dengan relasi user
            $query = LogActivity::with('user')->latest();
            
            // Filter berdasarkan tanggal
            if ($request->has('start_date') && $request->start_date) {
                $query->whereDate('created_at', '>=', $request->start_date);
            }
            
            if ($request->has('end_date') && $request->end_date) {
                $query->whereDate('created_at', '<=', $request->end_date);
            }
            
            // Filter berdasarkan tipe
            if ($request->has('tipe') && $request->tipe) {
                $query->where('tipe', $request->tipe);
            }
            
            // Filter berdasarkan user
            if ($request->has('user_id') && $request->user_id) {
                $query->where('user_id', $request->user_id);
            }
            
            // Filter berdasarkan aktivitas (pencarian teks)
            if ($request->has('search') && $request->search) {
                $search = $request->search;
                $query->where(function($q) use ($search) {
                    $q->where('aktivitas', 'like', "%{$search}%")
                      ->orWhere('deskripsi', 'like', "%{$search}%")
                      ->orWhere('tipe', 'like', "%{$search}%");
                });
            }
            
            $logs = $query->paginate(20)->withQueryString();
            $users = User::orderBy('name')->get();
            
            // Statistik untuk dashboard
            $stats = [
                'total' => LogActivity::count(),
                'hari_ini' => LogActivity::whereDate('created_at', today())->count(),
                'minggu_ini' => LogActivity::whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])->count(),
                'bulan_ini' => LogActivity::whereMonth('created_at', now()->month)->count(),
            ];
            
            return view('admin.log-aktivitas.index', compact('logs', 'users', 'stats'));
            
        } catch (\Exception $e) {
            Log::error('Error in LogActivityController@index: ' . $e->getMessage());
            
            $logs = collect();
            $users = User::all();
            $stats = ['total' => 0, 'hari_ini' => 0, 'minggu_ini' => 0, 'bulan_ini' => 0];
            
            return view('admin.log-aktivitas.index', compact('logs', 'users', 'stats'))
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }
    
    /**
     * Menampilkan detail log aktivitas - LANGSUNG RETURN HTML (TANPA FILE VIEW)
     */
    public function detail($id)
    {
        try {
            if (!Schema::hasTable('log_activities')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tabel log_activities belum dibuat'
                ], 500);
            }
            
            $log = LogActivity::with('user')->findOrFail($id);
            
            // Decode data (jika ada kolom old_data dan new_data)
            $oldData = null;
            $newData = null;
            
            // Cek apakah kolom old_data ada di model/tabel
            if (property_exists($log, 'old_data') && $log->old_data) {
                $oldData = is_string($log->old_data) ? json_decode($log->old_data, true) : $log->old_data;
            }
            
            if (property_exists($log, 'new_data') && $log->new_data) {
                $newData = is_string($log->new_data) ? json_decode($log->new_data, true) : $log->new_data;
            }
            
            // ===== LANGSUNG RETURN HTML, TIDAK PAKAI FILE VIEW =====
            $html = $this->generateDetailHtml($log, $oldData, $newData);
            
            return response()->json([
                'success' => true,
                'html' => $html
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error in LogActivityController@detail: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Gagal memuat detail: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Generate HTML detail langsung dari controller - TANPA FILE VIEW
     * DIPERBAIKI: Menampilkan semua jenis log activity dengan baik
     */
    private function generateDetailHtml($log, $oldData, $newData)
    {
        // Tentukan warna dan icon berdasarkan tipe
        $icon = 'fas fa-info-circle';
        $iconColor = 'text-gray-600';
        $badgeColor = 'bg-gray-100 text-gray-800';
        $badgeText = ucfirst($log->tipe);
        
        switch($log->tipe) {
            case 'login':
                $icon = 'fas fa-sign-in-alt';
                $iconColor = 'text-blue-600';
                $badgeColor = 'bg-blue-100 text-blue-800';
                break;
            case 'logout':
                $icon = 'fas fa-sign-out-alt';
                $iconColor = 'text-indigo-600';
                $badgeColor = 'bg-indigo-100 text-indigo-800';
                break;
            case 'create':
                $icon = 'fas fa-plus-circle';
                $iconColor = 'text-green-600';
                $badgeColor = 'bg-green-100 text-green-800';
                break;
            case 'update':
                $icon = 'fas fa-edit';
                $iconColor = 'text-yellow-600';
                $badgeColor = 'bg-yellow-100 text-yellow-800';
                break;
            case 'delete':
                $icon = 'fas fa-trash-alt';
                $iconColor = 'text-red-600';
                $badgeColor = 'bg-red-100 text-red-800';
                break;
            case 'approve':
                $icon = 'fas fa-check-circle';
                $iconColor = 'text-emerald-600';
                $badgeColor = 'bg-emerald-100 text-emerald-800';
                break;
            case 'reject':
                $icon = 'fas fa-times-circle';
                $iconColor = 'text-orange-600';
                $badgeColor = 'bg-orange-100 text-orange-800';
                break;
        }
        
        // Format tanggal
        $createdAt = $log->created_at ? date('d/m/Y', strtotime($log->created_at)) : '-';
        $createdTime = $log->created_at ? date('H:i:s', strtotime($log->created_at)) : '-';
        $updatedAt = $log->updated_at ? date('d/m/Y H:i:s', strtotime($log->updated_at)) : '-';
        
        // ========== DETEKSI JENIS LOG ACTIVITY ==========
        // Cek apakah ini adalah log registrasi (user_id = NULL dan aktivitas mengandung REGISTER)
        $isRegistrationLog = ($log->user_id === null && str_contains($log->aktivitas ?? '', 'REGISTER'));
        
        // Cek apakah ini adalah log update status ruangan
        $isRuanganStatusLog = str_contains($log->aktivitas ?? '', 'UPDATE STATUS RUANGAN');
        
        // Cek apakah ini adalah log update status peminjaman
        $isPeminjamanStatusLog = str_contains($log->aktivitas ?? '', 'UPDATE STATUS PEMINJAMAN');
        
        // Cek apakah ini adalah log pembatalan peminjaman
        $isCancelPeminjamanLog = str_contains($log->aktivitas ?? '', 'BATALKAN PEMINJAMAN');
        
        // Data user
        $userName = $log->user->name ?? 'System';
        $userUsername = $log->user->username ?? 'System';
        $userEmail = $log->user->email ?? '-';
        $userRole = $log->user->role ?? '-';
        $userId = $log->user_id ?? '-';
        
        // EKSTRAK DATA REGISTRASI DARI DESKRIPSI (jika ada)
        $registrantName = null;
        $registrantUsername = null;
        $registrantEmail = null;
        $registrantPhone = null;
        $registrantRole = null;
        $registrantId = null;
        
        if ($isRegistrationLog && !empty($log->deskripsi)) {
            $deskripsiLines = explode("\n", $log->deskripsi);
            foreach ($deskripsiLines as $line) {
                if (str_contains($line, 'Username')) {
                    $registrantUsername = trim(str_replace(['🆔 Username', 'Username', ':'], '', $line));
                } elseif (str_contains($line, 'Nama Lengkap')) {
                    $registrantName = trim(str_replace(['👤 Nama Lengkap', 'Nama Lengkap', ':'], '', $line));
                } elseif (str_contains($line, 'Email')) {
                    $registrantEmail = trim(str_replace(['📧 Email', 'Email', ':'], '', $line));
                } elseif (str_contains($line, 'No. Telepon')) {
                    $registrantPhone = trim(str_replace(['📱 No. Telepon', 'No. Telepon', ':'], '', $line));
                } elseif (str_contains($line, 'Role')) {
                    $registrantRole = trim(str_replace(['👔 Role', 'Role', ':'], '', $line));
                } elseif (str_contains($line, 'User ID')) {
                    $registrantId = trim(str_replace(['🆔 User ID', 'User ID', ':'], '', $line));
                }
            }
        }
        
        // Jika ini log registrasi, gunakan data yang diekstrak dari deskripsi
        if ($isRegistrationLog && $registrantUsername) {
            $userUsername = $registrantUsername;
            $userName = $registrantName ?? $registrantUsername;
            $userEmail = $registrantEmail ?? '-';
        }
        
        // IP Address & User Agent
        $ipAddress = $log->ip_address ?? '-';
        $userAgent = $log->user_agent ?? '-';
        
        // Deskripsi
        $deskripsi = $log->deskripsi ?? '';
        
        // Mulai build HTML
        $html = '<div class="space-y-4">';
        
        // Header
        $html .= '<div class="flex items-center justify-between pb-3 border-b">';
        $html .= '<div class="flex items-center space-x-3">';
        $html .= '<div class="h-10 w-10 rounded-full ' . $badgeColor . ' flex items-center justify-center">';
        $html .= '<i class="' . $icon . ' ' . $iconColor . ' text-lg"></i>';
        $html .= '</div>';
        $html .= '<div>';
        $html .= '<span class="px-3 py-1 text-xs font-medium rounded-full ' . $badgeColor . '">' . $badgeText . '</span>';
        $html .= '<h3 class="text-lg font-semibold text-gray-900 mt-1">' . htmlspecialchars($log->aktivitas ?? '') . '</h3>';
        $html .= '</div>';
        $html .= '</div>';
        $html .= '<div class="text-right">';
        $html .= '<div class="text-sm font-medium text-gray-900">' . $createdAt . '</div>';
        $html .= '<div class="text-xs text-gray-500">' . $createdTime . '</div>';
        $html .= '</div>';
        $html .= '</div>';
        
        // Informasi User
        $html .= '<div class="bg-gray-50 p-4 rounded-lg">';
        
        if ($isRegistrationLog && $registrantUsername) {
            $html .= '<h4 class="text-sm font-semibold text-gray-700 mb-3 flex items-center">';
            $html .= '<i class="fas fa-user-plus mr-2 text-green-500"></i> Informasi Pendaftar Baru';
            $html .= '</h4>';
        } elseif ($isRuanganStatusLog) {
            $html .= '<h4 class="text-sm font-semibold text-gray-700 mb-3 flex items-center">';
            $html .= '<i class="fas fa-building mr-2 text-blue-500"></i> Informasi Petugas';
            $html .= '</h4>';
        } elseif ($isPeminjamanStatusLog || $isCancelPeminjamanLog) {
            $html .= '<h4 class="text-sm font-semibold text-gray-700 mb-3 flex items-center">';
            $html .= '<i class="fas fa-clipboard-list mr-2 text-purple-500"></i> Informasi Petugas';
            $html .= '</h4>';
        } else {
            $html .= '<h4 class="text-sm font-semibold text-gray-700 mb-3 flex items-center">';
            $html .= '<i class="fas fa-user-circle mr-2 text-gray-500"></i> Informasi User';
            $html .= '</h4>';
        }
        
        $html .= '<div class="grid grid-cols-2 gap-4">';
        
        // TAMPILKAN USERNAME
        $html .= '<div class="col-span-2 mb-2 pb-2 border-b border-gray-200">';
        $html .= '<p class="text-xs text-gray-500">Username</p>';
        if ($isRegistrationLog && $registrantUsername) {
            $html .= '<p class="text-sm font-mono font-semibold text-green-700 bg-green-50 p-1 rounded">' . htmlspecialchars($userUsername) . ' <span class="text-xs text-green-600">(Pendaftar Baru)</span></p>';
        } elseif ($userId === '-') {
            $html .= '<p class="text-sm font-mono font-semibold text-gray-500 bg-gray-100 p-1 rounded">' . htmlspecialchars($userUsername) . ' <span class="text-xs text-gray-400">(System/Guest)</span></p>';
        } else {
            $html .= '<p class="text-sm font-mono font-semibold text-primary-700 bg-primary-50 p-1 rounded">' . htmlspecialchars($userUsername) . '</p>';
        }
        $html .= '</div>';
        
        $html .= '<div>';
        $html .= '<p class="text-xs text-gray-500">Nama Lengkap</p>';
        $html .= '<p class="text-sm font-medium text-gray-900">' . htmlspecialchars($userName) . '</p>';
        $html .= '</div>';
        
        $html .= '<div>';
        $html .= '<p class="text-xs text-gray-500">Email</p>';
        $html .= '<p class="text-sm text-gray-900">' . htmlspecialchars($userEmail) . '</p>';
        $html .= '</div>';
        
        // Tampilkan No Telepon jika ada (khusus registrasi)
        if ($isRegistrationLog && $registrantPhone) {
            $html .= '<div>';
            $html .= '<p class="text-xs text-gray-500">No. Telepon</p>';
            $html .= '<p class="text-sm text-gray-900">' . htmlspecialchars($registrantPhone) . '</p>';
            $html .= '</div>';
        }
        
        $html .= '<div>';
        $html .= '<p class="text-xs text-gray-500">Role</p>';
        $html .= '<p class="text-sm"><span class="px-2 py-0.5 text-xs font-medium bg-blue-100 text-blue-800 rounded">' . htmlspecialchars($userRole) . '</span></p>';
        $html .= '</div>';
        
        $html .= '<div>';
        $html .= '<p class="text-xs text-gray-500">ID User</p>';
        $html .= '<p class="text-sm text-gray-900">' . htmlspecialchars($userId) . '</p>';
        $html .= '</div>';
        
        // Tampilkan ID Pendaftar (dari deskripsi) jika berbeda dengan user_id
        if ($isRegistrationLog && $registrantId && $registrantId != $userId) {
            $html .= '<div>';
            $html .= '<p class="text-xs text-gray-500">ID User Baru</p>';
            $html .= '<p class="text-sm font-mono text-green-700">' . htmlspecialchars($registrantId) . '</p>';
            $html .= '</div>';
        }
        
        $html .= '</div>';
        $html .= '</div>';
        
        // Detail Aktivitas
        $html .= '<div class="bg-gray-50 p-4 rounded-lg">';
        $html .= '<h4 class="text-sm font-semibold text-gray-700 mb-3 flex items-center">';
        $html .= '<i class="fas fa-info-circle mr-2 text-gray-500"></i> Detail Aktivitas';
        $html .= '</h4>';
        $html .= '<div class="space-y-3">';
        
        if (!empty($deskripsi)) {
            $html .= '<div>';
            $html .= '<p class="text-xs text-gray-500">Deskripsi</p>';
            $html .= '<div class="text-sm text-gray-900 bg-white p-2 rounded border max-h-96 overflow-y-auto">';
            
            // Format deskripsi dengan pre-wrap untuk menjaga format
            $formattedDeskripsi = nl2br(htmlspecialchars($deskripsi));
            $html .= '<div class="whitespace-pre-wrap font-mono text-xs">' . $formattedDeskripsi . '</div>';
            $html .= '</div>';
            $html .= '</div>';
        }
        
        $html .= '<div class="grid grid-cols-2 gap-4">';
        $html .= '<div>';
        $html .= '<p class="text-xs text-gray-500">IP Address</p>';
        $html .= '<p class="text-sm font-mono text-gray-900">' . htmlspecialchars($ipAddress) . '</p>';
        $html .= '</div>';
        $html .= '<div>';
        $html .= '<p class="text-xs text-gray-500">User Agent</p>';
        $html .= '<p class="text-xs text-gray-600 break-all">' . htmlspecialchars($userAgent) . '</p>';
        $html .= '</div>';
        $html .= '</div>';
        $html .= '</div>';
        $html .= '</div>';
        
        // Data Perubahan (untuk tipe create, update, delete)
        if (in_array($log->tipe, ['create', 'update', 'delete']) && (!empty($oldData) || !empty($newData))) {
            $html .= '<div class="bg-gray-50 p-4 rounded-lg">';
            $html .= '<h4 class="text-sm font-semibold text-gray-700 mb-3 flex items-center">';
            $html .= '<i class="fas fa-database mr-2 text-gray-500"></i> Data Perubahan';
            $html .= '</h4>';
            $html .= '<div class="space-y-4">';
            
            if (!empty($oldData)) {
                $html .= '<div>';
                $html .= '<p class="text-xs font-medium text-red-600 mb-2 flex items-center">';
                $html .= '<i class="fas fa-arrow-left mr-1"></i> Data Lama';
                $html .= '</p>';
                $html .= '<div class="bg-red-50 p-3 rounded border border-red-200 max-h-60 overflow-y-auto">';
                $html .= '<pre class="text-xs text-red-800 whitespace-pre-wrap font-mono">' . htmlspecialchars(json_encode($oldData, JSON_PRETTY_PRINT)) . '</pre>';
                $html .= '</div>';
                $html .= '</div>';
            }
            
            if (!empty($newData)) {
                $html .= '<div>';
                $html .= '<p class="text-xs font-medium text-green-600 mb-2 flex items-center">';
                $html .= '<i class="fas fa-arrow-right mr-1"></i> Data Baru';
                $html .= '</p>';
                $html .= '<div class="bg-green-50 p-3 rounded border border-green-200 max-h-60 overflow-y-auto">';
                $html .= '<pre class="text-xs text-green-800 whitespace-pre-wrap font-mono">' . htmlspecialchars(json_encode($newData, JSON_PRETTY_PRINT)) . '</pre>';
                $html .= '</div>';
                $html .= '</div>';
            }
            
            $html .= '</div>';
            $html .= '</div>';
        }
        
        // Metadata
        $html .= '<div class="border-t pt-3 mt-2">';
        $html .= '<div class="flex justify-between items-center text-xs text-gray-500">';
        $html .= '<div><span class="font-medium">Log ID:</span> #' . $log->id . '</div>';
        $html .= '<div><span class="font-medium">Dibuat:</span> ' . $createdAt . ' ' . $createdTime;
        
        if ($log->created_at != $log->updated_at) {
            $html .= '<span class="ml-2">(Diperbarui: ' . $updatedAt . ')</span>';
        }
        
        $html .= '</div>';
        $html .= '</div>';
        $html .= '</div>';
        $html .= '</div>';
        
        return $html;
    }
    
    /**
     * Export Log Activity ke CSV/Excel
     */
    public function export(Request $request)
    {
        try {
            if (!Schema::hasTable('log_activities')) {
                return redirect()->back()->with('error', 'Tabel log_activities belum dibuat');
            }
            
            $query = LogActivity::with('user');
            
            // Filter sama seperti di index
            if ($request->has('start_date') && $request->start_date) {
                $query->whereDate('created_at', '>=', $request->start_date);
            }
            if ($request->has('end_date') && $request->end_date) {
                $query->whereDate('created_at', '<=', $request->end_date);
            }
            if ($request->has('tipe') && $request->tipe) {
                $query->where('tipe', $request->tipe);
            }
            if ($request->has('user_id') && $request->user_id) {
                $query->where('user_id', $request->user_id);
            }
            if ($request->has('search') && $request->search) {
                $search = $request->search;
                $query->where(function($q) use ($search) {
                    $q->where('aktivitas', 'like', "%{$search}%")
                      ->orWhere('deskripsi', 'like', "%{$search}%");
                });
            }
            
            $logs = $query->get();
            
            if ($logs->isEmpty()) {
                return redirect()->back()->with('warning', 'Tidak ada data untuk diekspor');
            }
            
            // Generate CSV
            $filename = 'log-aktivitas-' . date('Y-m-d-His') . '.csv';
            $handle = fopen('php://temp', 'w+');
            
            // Header CSV (UTF-8 BOM for Excel compatibility)
            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));
            fputcsv($handle, ['ID', 'User', 'Username', 'Tipe', 'Aktivitas', 'Deskripsi', 'IP Address', 'User Agent', 'Created At']);
            
            foreach ($logs as $log) {
                fputcsv($handle, [
                    $log->id,
                    $log->user->name ?? 'System',
                    $log->user->username ?? 'System',
                    $log->tipe,
                    $log->aktivitas,
                    strip_tags(str_replace(["\n", "\r"], ' ', $log->deskripsi ?? '')),
                    $log->ip_address ?? '-',
                    $log->user_agent ?? '-',
                    $log->created_at
                ]);
            }
            
            rewind($handle);
            $csvContent = stream_get_contents($handle);
            fclose($handle);
            
            return response($csvContent, 200)
                ->header('Content-Type', 'text/csv; charset=UTF-8')
                ->header('Content-Disposition', 'attachment; filename="' . $filename . '"')
                ->header('Cache-Control', 'must-revalidate, post-check=0, pre-check=0')
                ->header('Pragma', 'public');
                
        } catch (\Exception $e) {
            Log::error('Error exporting logs: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal mengekspor data: ' . $e->getMessage());
        }
    }
    
    /**
     * Hapus log activity lama
     */
    public function deleteOld(Request $request)
    {
        try {
            if (!Schema::hasTable('log_activities')) {
                return redirect()->back()->with('error', 'Tabel log_activities belum dibuat');
            }
            
            $days = $request->input('days', 30);
            
            // Validasi days
            if ($days < 1 || $days > 365) {
                return redirect()->back()->with('error', 'Jumlah hari harus antara 1-365');
            }
            
            $deleted = LogActivity::where('created_at', '<', now()->subDays($days))->delete();
            
            // Catat aktivitas delete ke log (jika ada yang dihapus)
            if ($deleted > 0) {
                Log::info("Admin menghapus {$deleted} log activity yang lebih dari {$days} hari");
            }
            
            return redirect()->back()->with('success', "Berhasil menghapus {$deleted} log activity yang lebih dari {$days} hari");
            
        } catch (\Exception $e) {
            Log::error('Error deleting old logs: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal menghapus log: ' . $e->getMessage());
        }
    }
    
    /**
     * Hapus semua log activity (clear all)
     */
    public function clearAll()
    {
        try {
            if (!Schema::hasTable('log_activities')) {
                return redirect()->back()->with('error', 'Tabel log_activities belum dibuat');
            }
            
            $total = LogActivity::count();
            LogActivity::truncate();
            
            Log::warning("Semua log activity ({$total} record) telah dihapus oleh admin");
            
            return redirect()->back()->with('success', "Berhasil menghapus semua log activity ({$total} record)");
            
        } catch (\Exception $e) {
            Log::error('Error clearing all logs: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal menghapus log: ' . $e->getMessage());
        }
    }
}