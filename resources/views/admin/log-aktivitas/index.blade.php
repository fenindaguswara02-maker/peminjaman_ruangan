@extends('layouts.admin')

@section('title', 'Log Aktivitas')
@section('page-title', 'Log Aktivitas Sistem')

@section('content')
<style>
    .log-card {
        border-left: 4px solid;
        transition: all 0.3s ease;
    }
    
    .log-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
    }
    
    .badge-log-type {
        font-size: 11px;
        padding: 3px 8px;
        border-radius: 12px;
        font-weight: 500;
    }
    
    /* Style untuk detail log */
    .log-detail {
        background: #f9fafb;
        border: 1px solid #e5e7eb;
        border-radius: 8px;
        padding: 12px;
        margin-top: 8px;
        font-size: 13px;
    }
    
    .log-detail-item {
        display: flex;
        align-items: flex-start;
        margin-bottom: 12px;
        padding-bottom: 8px;
        border-bottom: 1px solid #e5e7eb;
    }
    
    .log-detail-item:last-child {
        border-bottom: none;
        margin-bottom: 0;
        padding-bottom: 0;
    }
    
    .log-detail-label {
        font-weight: 600;
        color: #4b5563;
        min-width: 130px;
        font-size: 13px;
    }
    
    .log-detail-value {
        flex: 1;
        color: #1f2937;
        word-break: break-word;
        font-size: 13px;
    }
    
    /* Badge warna berdasarkan tipe */
    .badge-login { background-color: #dbeafe; color: #1e40af; }
    .badge-logout { background-color: #e0e7ff; color: #3730a3; }
    .badge-create { background-color: #d1fae5; color: #065f46; }
    .badge-update { background-color: #fef3c7; color: #92400e; }
    .badge-delete { background-color: #fee2e2; color: #991b1b; }
    .badge-approve { background-color: #dcfce7; color: #166534; }
    .badge-reject { background-color: #ffedd5; color: #c2410c; }
</style>

<div class="bg-white rounded-xl shadow-sm p-6 mb-8">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h2 class="text-xl font-bold text-gray-900">Log Aktivitas Sistem</h2>
            <p class="text-sm text-gray-600 mt-1">Catatan semua aktivitas yang terjadi di sistem</p>
        </div>
        <div class="flex items-center space-x-2">
            <i class="fas fa-history text-primary-500"></i>
            <span class="text-sm text-gray-600">Total: {{ $logs->total() }} aktivitas</span>
        </div>
    </div>
    
    <!-- Tampilkan pesan warning jika ada -->
    @if(session('warning'))
        <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mb-6">
            <div class="flex items-center">
                <i class="fas fa-exclamation-triangle text-yellow-500 mr-3"></i>
                <div>
                    <p class="text-sm text-yellow-700">{{ session('warning') }}</p>
                </div>
            </div>
        </div>
    @endif
    
    @if(session('error'))
        <div class="bg-red-50 border border-red-200 rounded-lg p-4 mb-6">
            <div class="flex items-center">
                <i class="fas fa-exclamation-circle text-red-500 mr-3"></i>
                <div>
                    <p class="text-sm text-red-700">{{ session('error') }}</p>
                </div>
            </div>
        </div>
    @endif
    
    <!-- Filter Section -->
    <div class="bg-gray-50 p-4 rounded-lg mb-6">
        <form method="GET" action="{{ route('admin.log-aktivitas') }}">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Tanggal Mulai</label>
                    <input type="date" name="start_date" value="{{ request('start_date') }}" 
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Tanggal Selesai</label>
                    <input type="date" name="end_date" value="{{ request('end_date') }}" 
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Tipe Aktivitas</label>
                    <select name="tipe" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                        <option value="">Semua Tipe</option>
                        <option value="login" {{ request('tipe') == 'login' ? 'selected' : '' }}>Login</option>
                        <option value="logout" {{ request('tipe') == 'logout' ? 'selected' : '' }}>Logout</option>
                        <option value="create" {{ request('tipe') == 'create' ? 'selected' : '' }}>Tambah Data</option>
                        <option value="update" {{ request('tipe') == 'update' ? 'selected' : '' }}>Update Data</option>
                        <option value="delete" {{ request('tipe') == 'delete' ? 'selected' : '' }}>Hapus Data</option>
                        <option value="approve" {{ request('tipe') == 'approve' ? 'selected' : '' }}>Persetujuan</option>
                        <option value="reject" {{ request('tipe') == 'reject' ? 'selected' : '' }}>Penolakan</option>
                    </select>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">User</label>
                    <select name="user_id" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                        <option value="">Semua User</option>
                        @foreach($users as $user)
                            <option value="{{ $user->id }}" {{ request('user_id') == $user->id ? 'selected' : '' }}>
                                {{ $user->name }} ({{ $user->role }})
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
            
            <div class="flex justify-end space-x-2 mt-4">
                <a href="{{ route('admin.log-aktivitas') }}" class="px-6 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 flex items-center">
                    <i class="fas fa-redo mr-2"></i> Reset
                </a>
                <button type="submit" class="px-6 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700 flex items-center">
                    <i class="fas fa-filter mr-2"></i> Terapkan Filter
                </button>
            </div>
        </form>
    </div>
    
    <!-- Log Table -->
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Waktu</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Username</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama User</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tipe Aksi</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aktivitas</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Detail</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">IP Address</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 bg-white">
                @forelse($logs as $log)
                    <tr class="hover:bg-gray-50 transition-colors duration-150">
                        <td class="px-4 py-3 whitespace-nowrap">
                            <div class="text-sm text-gray-900">{{ $log->created_at->format('d/m/Y') }}</div>
                            <div class="text-xs text-gray-500">{{ $log->created_at->format('H:i:s') }}</div>
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap">
                            <div class="text-sm font-mono text-gray-900">
                                {{ $log->user->username ?? $log->user->name ?? 'System' }}
                            </div>
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap">
                            <div class="flex items-center">
                                <div class="flex-shrink-0 h-8 w-8 bg-primary-100 rounded-full flex items-center justify-center mr-3">
                                    <i class="fas fa-user text-primary-600 text-sm"></i>
                                </div>
                                <div>
                                    <div class="text-sm font-medium text-gray-900">
                                        {{ $log->user->name ?? 'System' }}
                                    </div>
                                    <div class="text-xs text-gray-500">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-800">
                                            {{ $log->user->role ?? '-' }}
                                        </span>
                                        <div class="mt-1">{{ $log->user->email ?? '-' }}</div>
                                    </div>
                                </div>
                            </div>
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap">
                            @php
                                $badgeClass = 'badge-login';
                                $icon = 'fas fa-sign-in-alt';
                                
                                switch($log->tipe) {
                                    case 'login':
                                        $badgeClass = 'badge-login';
                                        $icon = 'fas fa-sign-in-alt';
                                        break;
                                    case 'logout':
                                        $badgeClass = 'badge-logout';
                                        $icon = 'fas fa-sign-out-alt';
                                        break;
                                    case 'create':
                                        $badgeClass = 'badge-create';
                                        $icon = 'fas fa-plus-circle';
                                        break;
                                    case 'update':
                                        $badgeClass = 'badge-update';
                                        $icon = 'fas fa-edit';
                                        break;
                                    case 'delete':
                                        $badgeClass = 'badge-delete';
                                        $icon = 'fas fa-trash-alt';
                                        break;
                                    case 'approve':
                                        $badgeClass = 'badge-approve';
                                        $icon = 'fas fa-check-circle';
                                        break;
                                    case 'reject':
                                        $badgeClass = 'badge-reject';
                                        $icon = 'fas fa-times-circle';
                                        break;
                                    default:
                                        $badgeClass = 'bg-gray-100 text-gray-800';
                                        $icon = 'fas fa-info-circle';
                                }
                            @endphp
                            <div class="flex items-center">
                                <i class="{{ $icon }} mr-2 text-sm"></i>
                                <span class="badge-log-type {{ $badgeClass }}">
                                    {{ ucfirst($log->tipe) }}
                                </span>
                            </div>
                        </td>
                        <td class="px-4 py-3">
                            <div class="text-sm font-medium text-gray-900">{{ $log->aktivitas }}</div>
                            @if($log->deskripsi)
                                <div class="text-xs text-gray-600 mt-1">{{ Str::limit($log->deskripsi, 100) }}</div>
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            <button onclick="showLogDetail({{ $log->id }})" 
                                    class="text-primary-600 hover:text-primary-800 text-sm font-medium flex items-center">
                                <i class="fas fa-eye mr-1"></i> Lihat Detail
                            </button>
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap">
                            <span class="px-2 py-1 text-xs font-mono bg-gray-100 rounded">{{ $log->ip_address }}</span>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-4 py-8 text-center text-gray-500">
                            <div class="w-16 h-16 mx-auto mb-3 bg-gray-100 rounded-full flex items-center justify-center">
                                <i class="fas fa-history text-gray-400 text-xl"></i>
                            </div>
                            <h3 class="text-lg font-medium text-gray-900 mb-1">Belum ada data log aktivitas</h3>
                            <p class="text-sm text-gray-600">Tidak ada aktivitas yang tercatat untuk filter yang dipilih</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    
    <!-- Pagination -->
    @if($logs->hasPages())
        <div class="mt-6">
            {{ $logs->withQueryString()->links() }}
        </div>
    @endif
</div>

<!-- Modal Detail Log -->
<div id="logDetailModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
    <div class="bg-white w-full max-w-2xl rounded-xl shadow-lg max-h-[90vh] overflow-y-auto">
        <div class="px-6 py-4 border-b sticky top-0 bg-white">
            <div class="flex items-center justify-between">
                <h2 class="text-lg font-bold text-gray-800">Detail Log Aktivitas</h2>
                <button onclick="closeLogDetail()" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times text-lg"></i>
                </button>
            </div>
        </div>
        
        <div id="logDetailContent" class="p-6">
            <!-- Konten akan diisi oleh JavaScript -->
        </div>
        
        <div class="px-6 py-4 border-t bg-gray-50">
            <div class="flex justify-end">
                <button onclick="closeLogDetail()"
                        class="px-4 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700 font-medium">
                    Tutup
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Statistik Section -->
<div class="bg-white rounded-xl shadow-sm p-6 mt-6">
    <h3 class="text-lg font-bold text-gray-900 mb-4">Statistik Aktivitas</h3>
    
    @php
        // Hitung statistik dari logs
        $stats = [
            'total' => $logs->total(),
            'login' => $logs->where('tipe', 'login')->count(),
            'logout' => $logs->where('tipe', 'logout')->count(),
            'create' => $logs->where('tipe', 'create')->count(),
            'update' => $logs->where('tipe', 'update')->count(),
            'delete' => $logs->where('tipe', 'delete')->count(),
            'approve' => $logs->where('tipe', 'approve')->count(),
            'reject' => $logs->where('tipe', 'reject')->count(),
        ];
        
        // Hitung aktivitas per user
        $userStats = [];
        foreach($logs as $log) {
            if ($log->user) {
                $userId = $log->user->id;
                if (!isset($userStats[$userId])) {
                    $userStats[$userId] = [
                        'user' => $log->user,
                        'count' => 0
                    ];
                }
                $userStats[$userId]['count']++;
            }
        }
        
        // Urutkan berdasarkan aktivitas terbanyak
        usort($userStats, function($a, $b) {
            return $b['count'] <=> $a['count'];
        });
    @endphp
    
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Statistik per Tipe -->
        <div>
            <h4 class="font-medium text-gray-700 mb-3">Aktivitas per Tipe</h4>
            <div class="space-y-2">
                @foreach(['login', 'logout', 'create', 'update', 'delete', 'approve', 'reject'] as $tipe)
                    @if($stats[$tipe] > 0)
                    <div class="flex items-center justify-between">
                        <div class="flex items-center">
                            @switch($tipe)
                                @case('login') <i class="fas fa-sign-in-alt text-blue-500 mr-2"></i> @break
                                @case('logout') <i class="fas fa-sign-out-alt text-indigo-500 mr-2"></i> @break
                                @case('create') <i class="fas fa-plus-circle text-green-500 mr-2"></i> @break
                                @case('update') <i class="fas fa-edit text-yellow-500 mr-2"></i> @break
                                @case('delete') <i class="fas fa-trash-alt text-red-500 mr-2"></i> @break
                                @case('approve') <i class="fas fa-check-circle text-emerald-500 mr-2"></i> @break
                                @case('reject') <i class="fas fa-times-circle text-orange-500 mr-2"></i> @break
                            @endswitch
                            <span class="text-sm">{{ ucfirst($tipe) }}</span>
                        </div>
                        <span class="text-sm font-semibold">{{ $stats[$tipe] }}</span>
                    </div>
                    @endif
                @endforeach
            </div>
        </div>
        
        <!-- Top Users -->
        <div>
            <h4 class="font-medium text-gray-700 mb-3">Top 5 Pengguna Aktif</h4>
            <div class="space-y-3">
                @foreach(array_slice($userStats, 0, 5) as $stat)
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <div class="h-8 w-8 bg-primary-100 rounded-full flex items-center justify-center mr-3">
                            <i class="fas fa-user text-primary-600 text-xs"></i>
                        </div>
                        <div>
                            <div class="text-sm font-medium">{{ $stat['user']->name }}</div>
                            <div class="text-xs text-gray-500">{{ $stat['user']->role }}</div>
                        </div>
                    </div>
                    <span class="text-sm font-semibold bg-primary-100 text-primary-800 px-2 py-1 rounded">
                        {{ $stat['count'] }} aktivitas
                    </span>
                </div>
                @endforeach
            </div>
        </div>
    </div>
</div>

<!-- Legenda -->
<div class="bg-white rounded-xl shadow-sm p-6 mt-6">
    <h3 class="text-lg font-bold text-gray-900 mb-4">Legenda Tipe Log</h3>
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="flex items-center">
            <span class="badge-log-type badge-login mr-2">Login</span>
            <span class="text-sm text-gray-600">User masuk ke sistem</span>
        </div>
        <div class="flex items-center">
            <span class="badge-log-type badge-create mr-2">Create</span>
            <span class="text-sm text-gray-600">Pembuatan data baru</span>
        </div>
        <div class="flex items-center">
            <span class="badge-log-type badge-update mr-2">Update</span>
            <span class="text-sm text-gray-600">Perubahan data</span>
        </div>
        <div class="flex items-center">
            <span class="badge-log-type badge-delete mr-2">Delete</span>
            <span class="text-sm text-gray-600">Penghapusan data</span>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
// Fungsi untuk menampilkan detail log
function showLogDetail(id) {
    // Tampilkan loading
    document.getElementById('logDetailContent').innerHTML = `
        <div class="flex justify-center items-center py-8">
            <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-primary-600"></div>
            <span class="ml-2 text-gray-600">Memuat detail...</span>
        </div>
    `;
    
    // Tampilkan modal
    document.getElementById('logDetailModal').classList.remove('hidden');
    
    // Fetch data detail
    fetch(`/admin/log-aktivitas/${id}/detail`, {
        headers: {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => {
        if (!response.ok) throw new Error('Network response was not ok');
        return response.json();
    })
    .then(data => {
        if (data.success) {
            document.getElementById('logDetailContent').innerHTML = data.html;
        } else {
            document.getElementById('logDetailContent').innerHTML = `
                <div class="text-center py-8">
                    <i class="fas fa-exclamation-triangle text-3xl text-red-400 mb-4"></i>
                    <p class="text-gray-600 font-medium">Gagal memuat detail</p>
                    <p class="text-sm text-gray-500 mt-2">${data.message || 'Silakan coba lagi'}</p>
                </div>
            `;
        }
    })
    .catch(error => {
        console.error('Error:', error);
        document.getElementById('logDetailContent').innerHTML = `
            <div class="text-center py-8">
                <i class="fas fa-exclamation-triangle text-3xl text-red-400 mb-4"></i>
                <p class="text-gray-600 font-medium">Koneksi Error</p>
                <p class="text-sm text-gray-500 mt-2">Periksa koneksi internet Anda</p>
            </div>
        `;
    });
}

function closeLogDetail() {
    document.getElementById('logDetailModal').classList.add('hidden');
}

// Auto refresh halaman setiap 30 detik (opsional)
setTimeout(() => {
    location.reload();
}, 30000);

// Format tanggal default untuk filter
document.addEventListener('DOMContentLoaded', function() {
    // Set tanggal default untuk filter
    const today = new Date().toISOString().split('T')[0];
    const oneWeekAgo = new Date();
    oneWeekAgo.setDate(oneWeekAgo.getDate() - 7);
    const oneWeekAgoFormatted = oneWeekAgo.toISOString().split('T')[0];
    
    // Jika tidak ada tanggal yang dipilih, set default
    const startDateInput = document.querySelector('input[name="start_date"]');
    const endDateInput = document.querySelector('input[name="end_date"]');
    
    if(startDateInput && !startDateInput.value) {
        startDateInput.value = oneWeekAgoFormatted;
    }
    if(endDateInput && !endDateInput.value) {
        endDateInput.value = today;
    }
    
    // Close modal dengan ESC
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeLogDetail();
        }
    });
    
    // Close modal dengan click backdrop
    document.getElementById('logDetailModal').addEventListener('click', function(e) {
        if (e.target === this) {
            closeLogDetail();
        }
    });
});
</script>
@endpush