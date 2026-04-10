<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\LogActivity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class AdminController extends Controller
{
    /**
     * Display admin dashboard
     */
    public function dashboard()
    {
        $totalUsers = User::where('role', 'user')->count();
        $totalPegawai = User::where('role', 'pegawai')->count();
        $totalAdmin = User::where('role', 'admin')->count();
        
        $pendingBookings = 8;
        $activeBookings = 24;

        $recentUsers = User::orderBy('created_at', 'desc')->take(5)->get();

        $pendingApprovals = [
            (object)[
                'type' => 'room',
                'title' => 'Peminjaman Ruangan A101',
                'user_name' => 'John Doe',
                'date' => '15 Nov 2023'
            ],
            (object)[
                'type' => 'video',
                'title' => 'Peminjaman Video Trone',
                'user_name' => 'Jane Smith',
                'date' => '16 Nov 2023'
            ]
        ];

        return view('admin.dashboard', compact(
            'totalUsers',
            'totalPegawai',
            'totalAdmin',
            'pendingBookings',
            'activeBookings',
            'recentUsers',
            'pendingApprovals'
        ));
    }

    /**
     * Display account management page
     */
    public function daftarUsers(Request $request)
    {
        $query = User::query();
        
        // Filter berdasarkan pencarian
        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('username', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('no_telepon', 'like', "%{$search}%")
                  ->orWhere('nim_nip', 'like', "%{$search}%");
            });
        }
        
        // Filter berdasarkan status
        if ($request->has('status') && $request->status != '') {
            $query->where('status', $request->status);
        }
        
        // Filter berdasarkan role
        if ($request->has('role') && $request->role != '') {
            $query->where('role', $request->role);
        }

        $users = $query->orderBy('created_at', 'desc')->paginate(10);

        $stats = [
            'total' => User::count(),
            'active' => User::where('status', 'active')->count(),
            'inactive' => User::where('status', 'inactive')->count(),
            'admin' => User::where('role', 'admin')->count(),
            'pegawai' => User::where('role', 'pegawai')->count(),
            'user' => User::where('role', 'user')->count(),
        ];

        return view('admin.daftar-users', compact('users', 'stats'));
    }

    /**
     * Get user data for editing and detail (API)
     */
    public function getUserData($id)
    {
        try {
            $user = User::findOrFail($id);
            
            return response()->json([
                'success' => true,
                'user' => [
                    'id' => $user->id,
                    'username' => $user->username,
                    'name' => $user->name,
                    'email' => $user->email,
                    'nim_nip' => $user->nim_nip,
                    'no_telepon' => $user->no_telepon,
                    'role' => $user->role,
                    'status' => $user->status,
                    'jenis_pengaju' => $user->jenis_pengaju,
                    'fakultas' => $user->fakultas,
                    'prodi' => $user->prodi,
                    'created_at' => $user->created_at,
                    'updated_at' => $user->updated_at,
                    'last_login_at' => $user->last_login_at,
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'User tidak ditemukan'
            ], 404);
        }
    }

    /**
     * Store new user - DENGAN VALIDASI USERNAME HARUS HURUF DAN ANGKA
     */
    public function storeUser(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'username' => [
                'required',
                'string',
                'min:3',
                'max:50',
                'regex:/^(?=.*[A-Za-z])(?=.*\d)[A-Za-z0-9]+$/', // HARUS mengandung huruf DAN angka
                'unique:users,username',
            ],
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:6|confirmed',
            'role' => 'required|in:user,pegawai,admin',
            'no_telepon' => 'nullable|string|max:15',
            'nim_nip' => 'nullable|string|max:50',
            'jenis_pengaju' => 'nullable|in:mahasiswa,dosen,staff',
            'fakultas' => 'nullable|string|max:255',
            'prodi' => 'nullable|string|max:255',
        ], [
            'username.regex' => 'Username harus mengandung HURUF dan ANGKA (contoh: john123, admin2024)',
            'username.unique' => 'Username sudah digunakan, silakan pilih username lain',
            'username.min' => 'Username minimal 3 karakter',
            'username.required' => 'Username wajib diisi',
            'password.min' => 'Password minimal 6 karakter',
            'password.confirmed' => 'Konfirmasi password tidak cocok',
            'email.unique' => 'Email sudah digunakan, silakan pilih email lain',
            'email.required' => 'Email wajib diisi',
            'email.email' => 'Format email tidak valid',
            'name.required' => 'Nama lengkap wajib diisi',
            'role.required' => 'Role wajib dipilih',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput()
                ->with('show_modal', true);
        }

        try {
            $data = [
                'username' => $request->username,
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'role' => $request->role,
                'no_telepon' => $request->no_telepon,
                'nim_nip' => $request->nim_nip,
                'status' => 'active',
            ];

            // Jika role user, tambahkan field khusus user
            if ($request->role === 'user') {
                $data['jenis_pengaju'] = $request->jenis_pengaju ?? 'mahasiswa';
                $data['fakultas'] = $request->fakultas ?? null;
                $data['prodi'] = $request->prodi ?? null;
            } else {
                $data['jenis_pengaju'] = null;
                $data['fakultas'] = null;
                $data['prodi'] = null;
            }

            $user = User::create($data);

            // LOG ACTIVITY
            $deskripsiLog = "========================================\n";
            $deskripsiLog .= "➕ TAMBAH USER BARU\n";
            $deskripsiLog .= "========================================\n";
            $deskripsiLog .= "🆔 Username    : " . $user->username . "\n";
            $deskripsiLog .= "👤 Nama       : " . $user->name . "\n";
            $deskripsiLog .= "📧 Email      : " . $user->email . "\n";
            $deskripsiLog .= "📱 No. Telepon : " . ($user->no_telepon ?? '-') . "\n";
            $deskripsiLog .= "🆔 NIM/NIP    : " . ($user->nim_nip ?? '-') . "\n";
            $deskripsiLog .= "👔 Role       : " . $user->role . "\n";
            $deskripsiLog .= "🔒 Status     : " . $user->status . "\n";
            if ($user->role === 'user') {
                $deskripsiLog .= "📚 Jenis      : " . ($user->jenis_pengaju ?? '-') . "\n";
                $deskripsiLog .= "🏛️ Fakultas   : " . ($user->fakultas ?? '-') . "\n";
                $deskripsiLog .= "📖 Prodi      : " . ($user->prodi ?? '-') . "\n";
            }
            $deskripsiLog .= "👤 Dibuat Oleh : " . (Auth::user()->name ?? Auth::user()->username ?? 'System') . "\n";
            $deskripsiLog .= "⏰ Waktu       : " . now()->format('d-m-Y H:i:s') . "\n";
            $deskripsiLog .= "========================================";

            LogActivity::create([
                'user_id' => Auth::id(),
                'tipe' => 'create',
                'aktivitas' => 'TAMBAH USER BARU',
                'deskripsi' => $deskripsiLog,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent()
            ]);

            return redirect()->route('admin.users.index')->with('success', 'User berhasil dibuat!');

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Gagal membuat user: ' . $e->getMessage())
                ->with('show_modal', true)
                ->withInput();
        }
    }

    /**
     * Update user data - DENGAN VALIDASI USERNAME HARUS HURUF DAN ANGKA
     */
    public function updateUserData(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'username' => [
                'required',
                'string',
                'min:3',
                'max:50',
                'regex:/^(?=.*[A-Za-z])(?=.*\d)[A-Za-z0-9]+$/', // HARUS mengandung huruf DAN angka
                Rule::unique('users', 'username')->ignore($id),
            ],
            'name' => 'required|string|max:255',
            'email' => [
                'required',
                'email',
                Rule::unique('users', 'email')->ignore($id),
            ],
            'role' => 'required|in:user,pegawai,admin',
            'status' => 'required|in:active,inactive',
            'no_telepon' => 'nullable|string|max:15',
            'nim_nip' => 'nullable|string|max:50',
            'jenis_pengaju' => 'nullable|in:mahasiswa,dosen,staff',
            'fakultas' => 'nullable|string|max:255',
            'prodi' => 'nullable|string|max:255',
            'password' => 'nullable|min:6|confirmed',
        ], [
            'username.regex' => 'Username harus mengandung HURUF dan ANGKA (contoh: john123, admin2024)',
            'username.unique' => 'Username sudah digunakan, silakan pilih username lain',
            'username.min' => 'Username minimal 3 karakter',
            'username.required' => 'Username wajib diisi',
            'password.min' => 'Password minimal 6 karakter',
            'password.confirmed' => 'Konfirmasi password tidak cocok',
            'email.unique' => 'Email sudah digunakan, silakan pilih email lain',
            'email.required' => 'Email wajib diisi',
            'email.email' => 'Format email tidak valid',
            'name.required' => 'Nama lengkap wajib diisi',
            'role.required' => 'Role wajib dipilih',
            'status.required' => 'Status wajib dipilih',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput()
                ->with('show_edit_modal', true)
                ->with('edit_user_id', $id);
        }

        try {
            $user = User::findOrFail($id);
            
            // Cegah mengubah akun sendiri menjadi nonaktif
            if ($user->id === auth()->id() && $request->status === 'inactive') {
                return redirect()->back()
                    ->with('error', 'Tidak dapat menonaktifkan akun sendiri!')
                    ->with('show_edit_modal', true)
                    ->with('edit_user_id', $id);
            }
            
            // Simpan data lama untuk log
            $oldData = [
                'username' => $user->username,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
                'status' => $user->status,
                'no_telepon' => $user->no_telepon,
                'nim_nip' => $user->nim_nip,
                'jenis_pengaju' => $user->jenis_pengaju,
                'fakultas' => $user->fakultas,
                'prodi' => $user->prodi,
            ];
            
            // Data dasar yang selalu diupdate
            $data = [
                'username' => $request->username,
                'name' => $request->name,
                'email' => $request->email,
                'role' => $request->role,
                'status' => $request->status,
                'no_telepon' => $request->no_telepon,
                'nim_nip' => $request->nim_nip,
            ];

            // HANDLE FIELD KHUSUS USER
            if ($request->role === 'user') {
                $data['jenis_pengaju'] = $request->jenis_pengaju ?? 'mahasiswa';
                $data['fakultas'] = $request->fakultas ?? null;
                $data['prodi'] = $request->prodi ?? null;
            } else {
                $data['jenis_pengaju'] = null;
                $data['fakultas'] = null;
                $data['prodi'] = null;
            }

            // Update password HANYA JIKA DIISI
            $passwordChanged = false;
            if ($request->filled('password')) {
                $data['password'] = Hash::make($request->password);
                $passwordChanged = true;
            }

            $user->update($data);

            // LOG ACTIVITY
            $changeDetails = [];
            
            if ($oldData['username'] != $user->username) {
                $changeDetails[] = "username: {$oldData['username']} → {$user->username}";
            }
            if ($oldData['name'] != $user->name) {
                $changeDetails[] = "nama: {$oldData['name']} → {$user->name}";
            }
            if ($oldData['email'] != $user->email) {
                $changeDetails[] = "email: {$oldData['email']} → {$user->email}";
            }
            if ($oldData['role'] != $user->role) {
                $changeDetails[] = "role: {$oldData['role']} → {$user->role}";
            }
            if ($oldData['status'] != $user->status) {
                $changeDetails[] = "status: {$oldData['status']} → {$user->status}";
            }
            if ($oldData['no_telepon'] != $user->no_telepon) {
                $changeDetails[] = "no telepon: " . ($oldData['no_telepon'] ?? '-') . " → " . ($user->no_telepon ?? '-');
            }
            if ($oldData['nim_nip'] != $user->nim_nip) {
                $changeDetails[] = "NIM/NIP: " . ($oldData['nim_nip'] ?? '-') . " → " . ($user->nim_nip ?? '-');
            }
            if ($oldData['jenis_pengaju'] != $user->jenis_pengaju) {
                $changeDetails[] = "jenis pengaju: " . ($oldData['jenis_pengaju'] ?? '-') . " → " . ($user->jenis_pengaju ?? '-');
            }
            if ($oldData['fakultas'] != $user->fakultas) {
                $changeDetails[] = "fakultas: " . ($oldData['fakultas'] ?? '-') . " → " . ($user->fakultas ?? '-');
            }
            if ($oldData['prodi'] != $user->prodi) {
                $changeDetails[] = "prodi: " . ($oldData['prodi'] ?? '-') . " → " . ($user->prodi ?? '-');
            }
            if ($passwordChanged) {
                $changeDetails[] = "password: [DIUBAH]";
            }
            
            $deskripsiLog = "========================================\n";
            $deskripsiLog .= "✏️ UPDATE DATA USER\n";
            $deskripsiLog .= "========================================\n";
            $deskripsiLog .= "🆔 User ID     : " . $user->id . "\n";
            $deskripsiLog .= "🆔 Username    : " . $user->username . "\n";
            $deskripsiLog .= "👤 Nama       : " . $user->name . "\n";
            
            if (!empty($changeDetails)) {
                $deskripsiLog .= "\n📝 PERUBAHAN:\n";
                foreach ($changeDetails as $detail) {
                    $deskripsiLog .= "   • " . $detail . "\n";
                }
            } else {
                $deskripsiLog .= "\n📝 Tidak ada perubahan data\n";
            }
            
            $deskripsiLog .= "\n👤 Diupdate Oleh : " . (Auth::user()->name ?? Auth::user()->username ?? 'System') . "\n";
            $deskripsiLog .= "⏰ Waktu         : " . now()->format('d-m-Y H:i:s') . "\n";
            $deskripsiLog .= "========================================";

            LogActivity::create([
                'user_id' => Auth::id(),
                'tipe' => 'update',
                'aktivitas' => 'UPDATE DATA USER',
                'deskripsi' => $deskripsiLog,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent()
            ]);

            return redirect()->route('admin.users.index')->with('success', 'Data user berhasil diperbarui!');

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Gagal memperbarui data user: ' . $e->getMessage())
                ->with('show_edit_modal', true)
                ->with('edit_user_id', $id);
        }
    }

    /**
     * Delete user account
     */
    public function deleteUser($id)
    {
        try {
            $user = User::findOrFail($id);
            
            if ($user->id === auth()->id()) {
                return redirect()->back()->with('error', 'Tidak dapat menghapus akun sendiri!');
            }

            $userUsername = $user->username;
            $userName = $user->name;
            $userEmail = $user->email;
            $userRole = $user->role;
            $userNimNip = $user->nim_nip;
            $userNoTelepon = $user->no_telepon;

            $user->delete();

            $deskripsiLog = "========================================\n";
            $deskripsiLog .= "🗑️ HAPUS USER\n";
            $deskripsiLog .= "========================================\n";
            $deskripsiLog .= "🆔 Username    : " . $userUsername . "\n";
            $deskripsiLog .= "👤 Nama       : " . $userName . "\n";
            $deskripsiLog .= "📧 Email      : " . $userEmail . "\n";
            $deskripsiLog .= "📱 No. Telepon : " . ($userNoTelepon ?? '-') . "\n";
            $deskripsiLog .= "🆔 NIM/NIP    : " . ($userNimNip ?? '-') . "\n";
            $deskripsiLog .= "👔 Role       : " . $userRole . "\n";
            $deskripsiLog .= "👤 Dihapus Oleh : " . (Auth::user()->name ?? Auth::user()->username ?? 'System') . "\n";
            $deskripsiLog .= "⏰ Waktu         : " . now()->format('d-m-Y H:i:s') . "\n";
            $deskripsiLog .= "========================================";

            LogActivity::create([
                'user_id' => Auth::id(),
                'tipe' => 'delete',
                'aktivitas' => 'HAPUS USER',
                'deskripsi' => $deskripsiLog,
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent()
            ]);

            return redirect()->route('admin.users.index')->with('success', 'Akun berhasil dihapus!');

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal menghapus akun: ' . $e->getMessage());
        }
    }

    /**
     * Toggle user status
     */
    public function toggleUserStatus($id)
    {
        try {
            $user = User::findOrFail($id);
            
            if ($user->id === auth()->id()) {
                return redirect()->back()->with('error', 'Tidak dapat mengubah status akun sendiri!');
            }

            $oldStatus = $user->status;
            $newStatus = $user->status == 'active' ? 'inactive' : 'active';
            $user->update(['status' => $newStatus]);

            $statusText = $newStatus == 'active' ? 'diaktifkan' : 'dinonaktifkan';
            
            $deskripsiLog = "========================================\n";
            $deskripsiLog .= "🔄 UBAH STATUS USER\n";
            $deskripsiLog .= "========================================\n";
            $deskripsiLog .= "🆔 Username    : " . $user->username . "\n";
            $deskripsiLog .= "👤 Nama       : " . $user->name . "\n";
            $deskripsiLog .= "📧 Email      : " . $user->email . "\n";
            $deskripsiLog .= "👔 Role       : " . $user->role . "\n";
            $deskripsiLog .= "🔄 Status Lama   : " . ($oldStatus == 'active' ? 'AKTIF' : 'NONAKTIF') . "\n";
            $deskripsiLog .= "🆕 Status Baru   : " . ($newStatus == 'active' ? 'AKTIF' : 'NONAKTIF') . "\n";
            $deskripsiLog .= "👤 Diubah Oleh   : " . (Auth::user()->name ?? Auth::user()->username ?? 'System') . "\n";
            $deskripsiLog .= "⏰ Waktu         : " . now()->format('d-m-Y H:i:s') . "\n";
            $deskripsiLog .= "========================================";

            LogActivity::create([
                'user_id' => Auth::id(),
                'tipe' => 'update',
                'aktivitas' => 'UBAH STATUS USER',
                'deskripsi' => $deskripsiLog,
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent()
            ]);

            return redirect()->back()->with('success', "User berhasil $statusText!");

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal mengubah status user: ' . $e->getMessage());
        }
    }

    /**
     * Display employee schedule page
     */
    public function jadwalPegawai()
    {
        $pegawai = [
            (object)[
                'id' => 1,
                'name' => 'Budi Santoso',
                'position' => 'Staff IT',
                'employee_id' => 'IT001',
                'shift' => 'Pagi (08:00-16:00)',
                'status' => 'Hadir'
            ]
        ];

        $todaySchedule = [
            (object)[
                'name' => 'Budi Santoso',
                'position' => 'Staff IT',
                'shift' => 'Shift Pagi (08:00-16:00)',
                'status' => 'Hadir',
                'check_in' => '07:55'
            ],
            (object)[
                'name' => 'Siti Rahayu',
                'position' => 'Staff HR',
                'shift' => 'Shift Siang (12:00-20:00)',
                'status' => 'Belum Check-in',
                'check_in' => null
            ]
        ];

        return view('admin.jadwal-pegawai', compact('pegawai', 'todaySchedule'));
    }
    
    /**
     * Approve booking
     */
    public function approveBooking($id)
    {
        try {
            $deskripsiLog = "========================================\n";
            $deskripsiLog .= "✅ PERSETUJUAN PEMINJAMAN\n";
            $deskripsiLog .= "========================================\n";
            $deskripsiLog .= "🆔 ID Peminjaman : " . $id . "\n";
            $deskripsiLog .= "📋 Status        : DISETUJUI\n";
            $deskripsiLog .= "👤 Disetujui Oleh: " . (Auth::user()->name ?? Auth::user()->username ?? 'System') . "\n";
            $deskripsiLog .= "⏰ Waktu         : " . now()->format('d-m-Y H:i:s') . "\n";
            $deskripsiLog .= "========================================";
            
            LogActivity::create([
                'user_id' => Auth::id(),
                'tipe' => 'approve',
                'aktivitas' => 'PERSETUJUAN PEMINJAMAN',
                'deskripsi' => $deskripsiLog,
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent()
            ]);
        } catch (\Exception $logError) {
            \Log::error('Gagal membuat log: ' . $logError->getMessage());
        }
        
        return redirect()->back()->with('success', 'Peminjaman berhasil disetujui!');
    }

    /**
     * Reject booking
     */
    public function rejectBooking($id)
    {
        try {
            $deskripsiLog = "========================================\n";
            $deskripsiLog .= "❌ PENOLAKAN PEMINJAMAN\n";
            $deskripsiLog .= "========================================\n";
            $deskripsiLog .= "🆔 ID Peminjaman : " . $id . "\n";
            $deskripsiLog .= "📋 Status        : DITOLAK\n";
            $deskripsiLog .= "👤 Ditolak Oleh  : " . (Auth::user()->name ?? Auth::user()->username ?? 'System') . "\n";
            $deskripsiLog .= "⏰ Waktu         : " . now()->format('d-m-Y H:i:s') . "\n";
            $deskripsiLog .= "========================================";
            
            LogActivity::create([
                'user_id' => Auth::id(),
                'tipe' => 'reject',
                'aktivitas' => 'PENOLAKAN PEMINJAMAN',
                'deskripsi' => $deskripsiLog,
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent()
            ]);
        } catch (\Exception $logError) {
            \Log::error('Gagal membuat log: ' . $logError->getMessage());
        }
        
        return redirect()->back()->with('success', 'Peminjaman berhasil ditolak!');
    }
    
    /**
     * Edit akademik user
     */
    public function editAkademik($id)
    {
        try {
            $user = User::findOrFail($id);
            return view('admin.users.edit-akademik', compact('user'));
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'User tidak ditemukan');
        }
    }
    
    /**
     * Update akademik user
     */
    public function updateAkademik(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'jenis_pengaju' => 'required|in:mahasiswa,dosen,staff',
            'fakultas' => 'nullable|string|max:255',
            'prodi' => 'nullable|string|max:255',
            'nim_nip' => 'nullable|string|max:50',
        ], [
            'jenis_pengaju.required' => 'Jenis pengaju wajib dipilih',
            'jenis_pengaju.in' => 'Jenis pengaju tidak valid',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            $user = User::findOrFail($id);
            
            $oldData = [
                'jenis_pengaju' => $user->jenis_pengaju,
                'fakultas' => $user->fakultas,
                'prodi' => $user->prodi,
                'nim_nip' => $user->nim_nip,
            ];
            
            $user->update([
                'jenis_pengaju' => $request->jenis_pengaju,
                'fakultas' => $request->fakultas,
                'prodi' => $request->prodi,
                'nim_nip' => $request->nim_nip,
            ]);
            
            $changeDetails = [];
            if ($oldData['jenis_pengaju'] != $user->jenis_pengaju) {
                $changeDetails[] = "jenis pengaju: {$oldData['jenis_pengaju']} → {$user->jenis_pengaju}";
            }
            if ($oldData['fakultas'] != $user->fakultas) {
                $changeDetails[] = "fakultas: " . ($oldData['fakultas'] ?? '-') . " → " . ($user->fakultas ?? '-');
            }
            if ($oldData['prodi'] != $user->prodi) {
                $changeDetails[] = "prodi: " . ($oldData['prodi'] ?? '-') . " → " . ($user->prodi ?? '-');
            }
            if ($oldData['nim_nip'] != $user->nim_nip) {
                $changeDetails[] = "NIM/NIP: " . ($oldData['nim_nip'] ?? '-') . " → " . ($user->nim_nip ?? '-');
            }
            
            $deskripsiLog = "========================================\n";
            $deskripsiLog .= "📚 UPDATE DATA AKADEMIK USER\n";
            $deskripsiLog .= "========================================\n";
            $deskripsiLog .= "🆔 Username    : " . $user->username . "\n";
            $deskripsiLog .= "👤 Nama       : " . $user->name . "\n";
            
            if (!empty($changeDetails)) {
                $deskripsiLog .= "\n📝 PERUBAHAN:\n";
                foreach ($changeDetails as $detail) {
                    $deskripsiLog .= "   • " . $detail . "\n";
                }
            } else {
                $deskripsiLog .= "\n📝 Tidak ada perubahan data\n";
            }
            
            $deskripsiLog .= "\n👤 Diupdate Oleh : " . (Auth::user()->name ?? Auth::user()->username ?? 'System') . "\n";
            $deskripsiLog .= "⏰ Waktu         : " . now()->format('d-m-Y H:i:s') . "\n";
            $deskripsiLog .= "========================================";
            
            LogActivity::create([
                'user_id' => Auth::id(),
                'tipe' => 'update',
                'aktivitas' => 'UPDATE DATA AKADEMIK USER',
                'deskripsi' => $deskripsiLog,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent()
            ]);
            
            return redirect()->route('admin.users.index')->with('success', 'Data akademik user berhasil diperbarui!');
            
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal memperbarui data akademik: ' . $e->getMessage());
        }
    }
}