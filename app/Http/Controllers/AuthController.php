<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\LogActivity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Schema;

class AuthController extends Controller
{
    /**
     * Show Login Form
     */
    public function showLogin()
    {
        // Cek apakah user sudah login
        if (Auth::check()) {
            return $this->redirectBasedOnRole();
        }
        
        return view('auth.login');
    }

    /**
     * Handle Login - Menggunakan USERNAME sebagai field login (BUKAN EMAIL)
     * DENGAN CEK STATUS NONAKTIF
     */
    public function login(Request $request)
    {
        // Validasi input - HANYA USERNAME (bukan email)
        $credentials = $request->validate([
            'username' => 'required|string|min:3',
            'password' => 'required|string|min:6'
        ], [
            'username.required' => 'Username wajib diisi',
            'username.min' => 'Username minimal 3 karakter',
            'password.required' => 'Password wajib diisi',
            'password.min' => 'Password minimal 6 karakter'
        ]);

        // ========== CEK APAKAH USER ADA DAN STATUSNYA ==========
        $user = User::where('username', $request->username)->first();
        
        // Cek jika user ditemukan tapi statusnya nonaktif
        if ($user && $user->status !== 'active') {
            \Log::warning('Percobaan login oleh akun nonaktif:', [
                'username' => $request->username,
                'status' => $user->status,
                'ip' => $request->ip(),
                'time' => now()
            ]);
            
            return back()->withErrors([
                'username' => 'Akun Anda telah dinonaktifkan. Silakan hubungi administrator.',
            ])->withInput();
        }
        
        // Login HANYA menggunakan username
        if (Auth::attempt(['username' => $request->username, 'password' => $request->password], $request->filled('remember'))) {
            $request->session()->regenerate();
            
            $user = Auth::user();
            
            // CEK ULANG STATUS SETELAH LOGIN (untuk jaga-jaga)
            if ($user->status !== 'active') {
                Auth::logout();
                return back()->withErrors([
                    'username' => 'Akun Anda telah dinonaktifkan. Silakan hubungi administrator.',
                ])->withInput();
            }
            
            // CEK APAKAH KOLOM last_login_at ADA
            if (Schema::hasColumn('users', 'last_login_at')) {
                $user->last_login_at = now();
                $user->save();
            }
            
            // Log successful login ke LogActivity
            try {
                LogActivity::create([
                    'user_id' => $user->id,
                    'tipe' => 'login',
                    'aktivitas' => 'LOGIN',
                    'deskripsi' => "User {$user->username} ({$user->name}) berhasil login ke sistem",
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent()
                ]);
            } catch (\Exception $e) {
                \Log::error('Gagal menyimpan log login: ' . $e->getMessage());
            }
            
            \Log::info('User login berhasil:', [
                'id' => $user->id,
                'username' => $user->username,
                'name' => $user->name,
                'role' => $user->role,
                'time' => now()
            ]);
            
            // Redirect based on role dengan header anti-cache
            return $this->redirectBasedOnRole()
                ->withHeaders([
                    'Cache-Control' => 'no-cache, no-store, must-revalidate, max-age=0',
                    'Pragma' => 'no-cache',
                    'Expires' => 'Sat, 01 Jan 1990 00:00:00 GMT'
                ]);
        }

        // Log failed login
        \Log::warning('Login gagal:', ['username' => $request->username, 'time' => now()]);

        // Login gagal
        return back()->withErrors([
            'username' => 'Username atau password yang Anda masukkan salah.',
        ])->withInput();
    }

    /**
     * Show Register Form
     */
    public function showRegister()
    {
        if (Auth::check()) {
            return $this->redirectBasedOnRole();
        }
        
        return view('auth.register');
    }

    /**
     * Handle User Registration - Dengan Validasi:
     * - username: UNIQUE, HANYA HURUF DAN ANGKA, WAJIB MENGANDUNG ANGKA
     * - email: UNIQUE (tidak boleh sama)
     * - no_telepon: UNIQUE (tidak boleh sama)
     * - name: BOLEH SAMA (tidak perlu unique)
     */
    public function register(Request $request)
    {
        // Validasi lengkap - name TIDAK UNIQUE
        $validator = Validator::make($request->all(), [
            'username' => [
                'required',
                'string',
                'min:3',
                'max:255',
                'unique:users,username',
                'regex:/^[a-zA-Z0-9]+$/',     // HANYA HURUF DAN ANGKA, TANPA SPASI
                'regex:/[0-9]/'               // WAJIB MENGANDUNG ANGKA
            ],
            'name' => 'required|string|max:255', // TIDAK UNIQUE - boleh sama dengan user lain
            'email' => 'required|email|max:255|unique:users,email',
            'no_telepon' => 'required|string|max:255|unique:users,no_telepon',
            'password' => 'required|string|min:6|confirmed',
        ], [
            // Username validation messages
            'username.required' => 'Username wajib diisi',
            'username.min' => 'Username minimal 3 karakter',
            'username.max' => 'Username maksimal 255 karakter',
            'username.unique' => 'Username sudah digunakan, silakan pilih username lain',
            'username.regex' => 'Username harus mengandung huruf dan angka (minimal 1 angka). Contoh: john123, andri90, user2024',
            
            // Name validation messages (TIDAK UNIQUE - hanya required)
            'name.required' => 'Nama lengkap wajib diisi',
            'name.max' => 'Nama lengkap maksimal 255 karakter',
            
            // Email validation messages
            'email.required' => 'Email wajib diisi',
            'email.email' => 'Format email tidak valid',
            'email.max' => 'Email maksimal 255 karakter',
            'email.unique' => 'Email sudah terdaftar, silakan gunakan email lain',
            
            // Phone validation messages
            'no_telepon.required' => 'Nomor telepon wajib diisi',
            'no_telepon.max' => 'Nomor telepon maksimal 255 karakter',
            'no_telepon.unique' => 'Nomor telepon sudah digunakan, silakan gunakan nomor lain',
            
            // Password validation messages
            'password.required' => 'Password wajib diisi',
            'password.min' => 'Password minimal 6 karakter',
            'password.confirmed' => 'Konfirmasi password tidak cocok',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            // Buat user dengan semua field
            $user = User::create([
                'username' => strtolower($request->username), // Simpan dalam lowercase
                'name' => $request->name,
                'email' => strtolower($request->email),
                'no_telepon' => $request->no_telepon,
                'password' => Hash::make($request->password),
                'role' => 'user',           // Default role
                'status' => 'active',        // Default status (AKTIF)
                'jenis_pengaju' => null,
                'nim_nip' => null,
                'fakultas' => null,
                'prodi' => null,
                'foto' => null,
            ]);

            // Log successful registration
            \Log::info('Registrasi berhasil:', [
                'id' => $user->id,
                'username' => $user->username,
                'name' => $user->name,
                'email' => $user->email,
                'no_telepon' => $user->no_telepon,
                'status' => $user->status,
                'time' => now()
            ]);

            // ==================== CATAT KE LOG ACTIVITY DENGAN DATA LENGKAP ====================
            $this->logRegistrationActivity($user, $request);
            // =================================================================================

            // Registrasi berhasil, redirect ke halaman login dengan pesan sukses
            return redirect()->route('login')
                ->with('success', 'Registrasi berhasil! Silakan login dengan username dan password Anda.')
                ->withHeaders([
                    'Cache-Control' => 'no-cache, no-store, must-revalidate',
                    'Pragma' => 'no-cache',
                    'Expires' => '0'
                ]);

        } catch (\Illuminate\Database\QueryException $e) {
            // Tangkap error database
            $errorCode = $e->errorInfo[1] ?? null;
            
            if ($errorCode == 1062) { // Duplicate entry
                return redirect()->back()
                    ->withErrors(['error' => 'Username, email, atau nomor telepon sudah terdaftar.'])
                    ->withInput();
            }
            
            // Log error untuk debugging
            \Log::error('Registration error: ' . $e->getMessage());
            
            return redirect()->back()
                ->withErrors(['error' => 'Terjadi kesalahan database. Silakan coba lagi.'])
                ->withInput();
            
        } catch (\Exception $e) {
            // Log error untuk debugging
            \Log::error('Registration error: ' . $e->getMessage());
            
            return redirect()->back()
                ->withErrors(['error' => 'Terjadi kesalahan: ' . $e->getMessage()])
                ->withInput();
        }
    }

    /**
     * Handle Logout
     */
    public function logout(Request $request)
    {
        // Log user sebelum logout (untuk audit)
        if (Auth::check()) {
            $user = Auth::user();
            
            // Simpan ke LogActivity
            try {
                LogActivity::create([
                    'user_id' => $user->id,
                    'tipe' => 'logout',
                    'aktivitas' => 'LOGOUT',
                    'deskripsi' => "User {$user->username} ({$user->name}) logout dari sistem",
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent()
                ]);
            } catch (\Exception $e) {
                \Log::error('Gagal menyimpan log logout: ' . $e->getMessage());
            }
            
            \Log::info('User logout:', [
                'id' => $user->id,
                'username' => $user->username,
                'name' => $user->name,
                'role' => $user->role,
                'time' => now()
            ]);
        }
        
        // Logout user
        Auth::logout();
        
        // HAPUS SEMUA SESSION
        $request->session()->flush();
        
        // INVALIDATE SESSION
        $request->session()->invalidate();
        
        // REGENERATE CSRF TOKEN
        $request->session()->regenerateToken();
        
        // HAPUS SEMUA COOKIE
        $cookies = [
            'XSRF-TOKEN',
            'laravel_session',
            'remember_web_59ba36addc2b2f9401580f014c7f58ea4e30989d'
        ];
        
        foreach ($cookies as $cookie) {
            Cookie::queue(Cookie::forget($cookie));
        }
        
        // LANGSUNG REDIRECT KE HOME DENGAN HEADER ANTI-CACHE
        return redirect('/')
            ->withHeaders([
                'Cache-Control' => 'no-cache, no-store, must-revalidate, max-age=0, pre-check=0, post-check=0',
                'Pragma' => 'no-cache',
                'Expires' => 'Sat, 01 Jan 1990 00:00:00 GMT',
                'Clear-Site-Data' => '"cache", "cookies", "storage"'
            ]);
    }
    
    /**
     * Redirect berdasarkan role user
     */
    private function redirectBasedOnRole()
    {
        $user = Auth::user();
        
        // Log redirect untuk debugging
        \Log::info('Redirecting user:', [
            'id' => $user->id,
            'username' => $user->username,
            'name' => $user->name,
            'role' => $user->role,
            'status' => $user->status,
            'redirect_to' => $this->getRedirectUrl($user->role)
        ]);
        
        return redirect($this->getRedirectUrl($user->role));
    }
    
    /**
     * Get redirect URL based on role
     */
    private function getRedirectUrl($role)
    {
        switch ($role) {
            case 'admin':
                return '/admin/dashboard';
            case 'pegawai':
                return '/pegawai/dashboard';
            case 'user':
            default:
                return '/user/dashboard';
        }
    }
    
    /**
     * Cek session (untuk AJAX)
     */
    public function checkSession(Request $request)
    {
        if (Auth::check()) {
            $user = Auth::user();
            return response()->json([
                'authenticated' => true,
                'user' => [
                    'id' => $user->id,
                    'username' => $user->username,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $user->role,
                    'status' => $user->status,
                    'no_telepon' => $user->no_telepon,
                    'nim_nip' => $user->nim_nip,
                    'jenis_pengaju' => $user->jenis_pengaju,
                    'foto' => $user->foto,
                ]
            ]);
        }
        
        return response()->json([
            'authenticated' => false,
            'user' => null
        ]);
    }
    
    /**
     * Cek ketersediaan username (AJAX) - untuk register
     * HANYA HURUF DAN ANGKA, WAJIB MENGANDUNG ANGKA
     */
    public function checkUsername(Request $request)
    {
        $username = $request->input('username');
        
        // Validasi format username harus hanya huruf dan angka
        if (!preg_match('/^[a-zA-Z0-9]+$/', $username)) {
            return response()->json([
                'exists' => false,
                'available' => false,
                'message' => 'Username hanya boleh terdiri dari huruf dan angka (tanpa spasi, tanpa karakter khusus)'
            ]);
        }
        
        // Validasi WAJIB mengandung angka
        if (!preg_match('/[0-9]/', $username)) {
            return response()->json([
                'exists' => false,
                'available' => false,
                'message' => 'Username harus mengandung minimal 1 angka. Contoh: john123, andri90, user2024'
            ]);
        }
        
        if (strlen($username) < 3) {
            return response()->json([
                'exists' => false,
                'available' => false,
                'message' => 'Username minimal 3 karakter'
            ]);
        }
        
        $exists = User::where('username', $username)->exists();
        
        return response()->json([
            'exists' => $exists,
            'available' => !$exists,
            'message' => $exists ? 'Username sudah digunakan' : 'Username tersedia'
        ]);
    }
    
    /**
     * Cek ketersediaan email (AJAX) - untuk register
     */
    public function checkEmail(Request $request)
    {
        $email = $request->input('email');
        $exists = User::where('email', $email)->exists();
        
        return response()->json([
            'exists' => $exists,
            'available' => !$exists,
            'message' => $exists ? 'Email sudah terdaftar' : 'Email tersedia'
        ]);
    }
    
    /**
     * Cek ketersediaan nomor telepon (AJAX) - untuk register
     */
    public function checkPhone(Request $request)
    {
        $no_telepon = $request->input('no_telepon');
        $exists = User::where('no_telepon', $no_telepon)->exists();
        
        return response()->json([
            'exists' => $exists,
            'available' => !$exists,
            'message' => $exists ? 'Nomor telepon sudah digunakan' : 'Nomor telepon tersedia'
        ]);
    }
    
    /**
     * Cek ketersediaan nama (AJAX) - TIDAK UNIQUE, selalu tersedia
     */
    public function checkName(Request $request)
    {
        // Nama boleh sama, jadi selalu tersedia
        return response()->json([
            'exists' => false,
            'available' => true,
            'message' => 'Nama tersedia'
        ]);
    }
    
    /**
     * Force logout untuk GET request
     */
    public function forceLogout(Request $request)
    {
        Auth::logout();
        $request->session()->flush();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        
        return redirect('/')
            ->withHeaders([
                'Cache-Control' => 'no-cache, no-store, must-revalidate, max-age=0',
                'Pragma' => 'no-cache',
                'Expires' => 'Sat, 01 Jan 1990 00:00:00 GMT'
            ]);
    }
    
    // ==================== LOG ACTIVITY METHODS ====================

    /**
     * Catat aktivitas registrasi ke LogActivity dengan data LENGKAP
     * 
     * @param User $user
     * @param Request $request
     */
    private function logRegistrationActivity($user, $request)
    {
        try {
            // AMBIL DATA LENGKAP DARI FORM REGISTER (bukan dari database)
            $inputData = $request->only(['username', 'name', 'email', 'no_telepon']);
            
            // Format deskripsi dengan data LENGKAP dan JELAS
            $description = "========================================\n";
            $description .= "📝 REGISTRASI USER BARU\n";
            $description .= "========================================\n";
            $description .= "🆔 Username    : " . ($user->username ?? $inputData['username'] ?? '-') . "\n";
            $description .= "👤 Nama Lengkap: " . ($user->name ?? $inputData['name'] ?? '-') . "\n";
            $description .= "📧 Email       : " . ($user->email ?? $inputData['email'] ?? '-') . "\n";
            $description .= "📱 No. Telepon : " . ($user->no_telepon ?? $inputData['no_telepon'] ?? '-') . "\n";
            $description .= "👔 Role        : " . ($user->role ?? 'user') . "\n";
            $description .= "🔒 Status      : " . ($user->status ?? 'active') . "\n";
            $description .= "🆔 User ID     : " . ($user->id ?? '-') . "\n";
            $description .= "⏰ Waktu       : " . now()->format('d-m-Y H:i:s') . "\n";
            $description .= "🌐 IP Address  : " . $request->ip() . "\n";
            $description .= "========================================";
            
            // Simpan ke LogActivity
            $log = LogActivity::create([
                'user_id' => null, // Registrasi oleh guest
                'tipe' => 'create',
                'aktivitas' => 'REGISTER - User Baru',
                'deskripsi' => $description,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent()
            ]);
            
            // Log success untuk debugging
            \Log::info('Registration activity saved to LogActivity:', [
                'log_id' => $log->id,
                'username' => $user->username,
                'name' => $user->name,
                'ip' => $request->ip()
            ]);
            
        } catch (\Exception $e) {
            // Log error tapi jangan ganggu proses registrasi
            \Log::error('Failed to log registration activity: ' . $e->getMessage());
        }
    }
}