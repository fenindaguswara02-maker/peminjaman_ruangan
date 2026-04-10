<?php

namespace App\Http\Controllers\Pegawai;

use App\Http\Controllers\Controller;
use App\Models\LogActivity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;
use App\Models\User;
use Carbon\Carbon;

class ProfilController extends Controller
{
    /**
     * Display halaman profil pegawai (single page)
     */
    public function index()
    {
        $user = auth()->user();
        
        return view('pegawai.profil.index', compact('user'));
    }

    /**
     * API: Update profil (termasuk username)
     */
    public function update(Request $request)
    {
        $user = auth()->user();
        
        // Simpan data lama untuk log
        $oldData = [
            'username' => $user->username,
            'name' => $user->name,
            'email' => $user->email,
            'nim_nip' => $user->nim_nip,
            'no_telepon' => $user->no_telepon,
        ];
        
        $request->validate([
            'username' => [
                'required',
                'string',
                'min:3',
                'max:255',
                'regex:/^(?=.*[a-zA-Z])(?=.*\d)[a-zA-Z0-9]+$/',
                Rule::unique('users')->ignore($user->id),
            ],
            'name' => 'required|string|max:255',
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('users')->ignore($user->id),
            ],
            'nim_nip' => 'nullable|string|max:50',
            'no_telepon' => 'nullable|string|max:20',
        ], [
            'username.required' => 'Username wajib diisi',
            'username.min' => 'Username minimal 3 karakter',
            'username.max' => 'Username maksimal 255 karakter',
            'username.regex' => 'Username harus mengandung huruf DAN angka (contoh: john123, andri90)',
            'username.unique' => 'Username sudah digunakan, silakan pilih username lain',
            'name.required' => 'Nama lengkap wajib diisi',
            'email.required' => 'Email wajib diisi',
            'email.email' => 'Format email tidak valid',
            'email.unique' => 'Email sudah terdaftar',
        ]);

        $data = $request->only([
            'username',
            'name', 
            'email', 
            'nim_nip', 
            'no_telepon',
        ]);

        $user->update($data);

        // 🔥 LOG ACTIVITY: Update Profil
        try {
            $changes = [];
            if ($oldData['username'] != $user->username) $changes[] = 'username';
            if ($oldData['name'] != $user->name) $changes[] = 'nama';
            if ($oldData['email'] != $user->email) $changes[] = 'email';
            if ($oldData['nim_nip'] != $user->nim_nip) $changes[] = 'NIM/NIP';
            if ($oldData['no_telepon'] != $user->no_telepon) $changes[] = 'no telepon';
            
            $changesText = !empty($changes) ? ' (mengubah ' . implode(', ', $changes) . ')' : '';
            
            LogActivity::create([
                'user_id' => auth()->id(),
                'tipe' => 'update',
                'aktivitas' => 'Update Profil Pegawai',
                'deskripsi' => 'Pegawai ' . $user->username . ' memperbarui data profil pribadi' . $changesText,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent()
            ]);
        } catch (\Exception $logError) {
            \Log::error('Gagal membuat log: ' . $logError->getMessage());
        }

        return response()->json([
            'success' => true,
            'message' => 'Profil berhasil diperbarui.',
            'data' => [
                'username' => $user->username,
                'name' => $user->name,
                'email' => $user->email,
                'nim_nip' => $user->nim_nip,
                'no_telepon' => $user->no_telepon,
            ]
        ]);
    }

    /**
     * API: Update username saja
     */
    public function updateUsername(Request $request)
    {
        $user = auth()->user();
        
        // Simpan username lama untuk log
        $oldUsername = $user->username;
        
        $request->validate([
            'username' => [
                'required',
                'string',
                'min:3',
                'max:255',
                'regex:/^(?=.*[a-zA-Z])(?=.*\d)[a-zA-Z0-9]+$/',
                Rule::unique('users')->ignore($user->id),
            ],
        ], [
            'username.required' => 'Username wajib diisi',
            'username.min' => 'Username minimal 3 karakter',
            'username.max' => 'Username maksimal 255 karakter',
            'username.regex' => 'Username harus mengandung huruf DAN angka (contoh: john123, andri90)',
            'username.unique' => 'Username sudah digunakan, silakan pilih username lain',
        ]);

        $user->username = $request->username;
        $user->save();

        // 🔥 LOG ACTIVITY: Update Username
        try {
            LogActivity::create([
                'user_id' => auth()->id(),
                'tipe' => 'update',
                'aktivitas' => 'Ubah Username',
                'deskripsi' => 'Pegawai ' . $oldUsername . ' mengubah username menjadi ' . $user->username,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent()
            ]);
        } catch (\Exception $logError) {
            \Log::error('Gagal membuat log: ' . $logError->getMessage());
        }

        return response()->json([
            'success' => true,
            'message' => 'Username berhasil diperbarui.',
            'data' => [
                'username' => $user->username
            ]
        ]);
    }

    /**
     * API: Cek ketersediaan username (untuk live validation)
     */
    public function checkUsernameAvailability(Request $request)
    {
        $username = $request->input('username');
        $userId = auth()->id();
        
        // Validasi format username
        if (!preg_match('/^(?=.*[a-zA-Z])(?=.*\d)[a-zA-Z0-9]+$/', $username)) {
            return response()->json([
                'available' => false,
                'message' => 'Username harus mengandung huruf DAN angka (contoh: john123)'
            ]);
        }
        
        $exists = User::where('username', $username)
                      ->where('id', '!=', $userId)
                      ->exists();
        
        return response()->json([
            'available' => !$exists,
            'message' => $exists ? 'Username sudah digunakan' : 'Username tersedia'
        ]);
    }

    /**
     * API: Update profil pribadi
     */
    public function updatePribadi(Request $request)
    {
        $user = auth()->user();
        
        // Simpan data lama untuk log
        $oldData = [
            'username' => $user->username,
            'name' => $user->name,
            'email' => $user->email,
            'no_telepon' => $user->no_telepon,
        ];
        
        $request->validate([
            'username' => [
                'required',
                'string',
                'min:3',
                'max:255',
                'regex:/^(?=.*[a-zA-Z])(?=.*\d)[a-zA-Z0-9]+$/',
                Rule::unique('users')->ignore($user->id),
            ],
            'name' => 'required|string|max:255',
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('users')->ignore($user->id),
            ],
            'no_telepon' => 'nullable|string|max:20',
        ]);

        $user->username = $request->username;
        $user->name = $request->name;
        $user->email = $request->email;
        $user->no_telepon = $request->no_telepon;
        $user->save();

        // 🔥 LOG ACTIVITY: Update Data Pribadi
        try {
            $changes = [];
            if ($oldData['username'] != $user->username) $changes[] = 'username';
            if ($oldData['name'] != $user->name) $changes[] = 'nama';
            if ($oldData['email'] != $user->email) $changes[] = 'email';
            if ($oldData['no_telepon'] != $user->no_telepon) $changes[] = 'no telepon';
            
            $changesText = !empty($changes) ? ' (mengubah ' . implode(', ', $changes) . ')' : '';
            
            LogActivity::create([
                'user_id' => auth()->id(),
                'tipe' => 'update',
                'aktivitas' => 'Update Data Pribadi',
                'deskripsi' => 'Pegawai ' . $user->username . ' memperbarui data pribadi' . $changesText,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent()
            ]);
        } catch (\Exception $logError) {
            \Log::error('Gagal membuat log: ' . $logError->getMessage());
        }

        return response()->json([
            'success' => true,
            'message' => 'Data pribadi berhasil diperbarui.',
            'data' => $user
        ]);
    }

    /**
     * API: Update data akademik (NIM/NIP, fakultas, prodi)
     */
    public function updateAkademik(Request $request)
    {
        $user = auth()->user();
        
        // Simpan data lama untuk log
        $oldData = [
            'nim_nip' => $user->nim_nip,
            'fakultas' => $user->fakultas,
            'prodi' => $user->prodi,
        ];
        
        $request->validate([
            'nim_nip' => 'nullable|string|max:50',
            'fakultas' => 'nullable|string|max:255',
            'prodi' => 'nullable|string|max:255',
        ]);

        $user->nim_nip = $request->nim_nip;
        $user->fakultas = $request->fakultas;
        $user->prodi = $request->prodi;
        $user->save();

        // 🔥 LOG ACTIVITY: Update Data Akademik
        try {
            $changes = [];
            if ($oldData['nim_nip'] != $user->nim_nip) $changes[] = 'NIM/NIP';
            if ($oldData['fakultas'] != $user->fakultas) $changes[] = 'fakultas';
            if ($oldData['prodi'] != $user->prodi) $changes[] = 'program studi';
            
            $changesText = !empty($changes) ? ' (mengubah ' . implode(', ', $changes) . ')' : '';
            
            LogActivity::create([
                'user_id' => auth()->id(),
                'tipe' => 'update',
                'aktivitas' => 'Update Data Akademik',
                'deskripsi' => 'Pegawai ' . $user->username . ' memperbarui data akademik' . $changesText,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent()
            ]);
        } catch (\Exception $logError) {
            \Log::error('Gagal membuat log: ' . $logError->getMessage());
        }

        return response()->json([
            'success' => true,
            'message' => 'Data akademik berhasil diperbarui.',
            'data' => $user
        ]);
    }

    /**
     * API: Upload foto profil
     */
    public function uploadPhoto(Request $request)
    {
        $request->validate([
            'foto' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $user = auth()->user();
        $oldPhoto = $user->foto;

        // Hapus foto lama jika ada
        if ($user->foto && Storage::disk('public')->exists($user->foto)) {
            Storage::disk('public')->delete($user->foto);
        }

        // Upload foto baru
        $path = $request->file('foto')->store('foto-profil', 'public');
        $user->foto = $path;
        $user->save();

        // 🔥 LOG ACTIVITY: Upload Foto Profil
        try {
            LogActivity::create([
                'user_id' => auth()->id(),
                'tipe' => 'update',
                'aktivitas' => 'Ubah Foto Profil',
                'deskripsi' => 'Pegawai ' . $user->username . ' mengubah foto profil' . ($oldPhoto ? ' (mengganti foto lama)' : ' (menambah foto baru)'),
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent()
            ]);
        } catch (\Exception $logError) {
            \Log::error('Gagal membuat log: ' . $logError->getMessage());
        }

        return response()->json([
            'success' => true,
            'message' => 'Foto profil berhasil diupload.',
            'photo_url' => asset('storage/' . $path)
        ]);
    }

    /**
     * API: Hapus foto profil
     */
    public function deletePhoto(Request $request)
    {
        $user = auth()->user();

        if ($user->foto && Storage::disk('public')->exists($user->foto)) {
            Storage::disk('public')->delete($user->foto);
            $user->foto = null;
            $user->save();
        }

        // 🔥 LOG ACTIVITY: Hapus Foto Profil
        try {
            LogActivity::create([
                'user_id' => auth()->id(),
                'tipe' => 'delete',
                'aktivitas' => 'Hapus Foto Profil',
                'deskripsi' => 'Pegawai ' . $user->username . ' menghapus foto profil',
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent()
            ]);
        } catch (\Exception $logError) {
            \Log::error('Gagal membuat log: ' . $logError->getMessage());
        }

        return response()->json([
            'success' => true,
            'message' => 'Foto profil berhasil dihapus.'
        ]);
    }

    /**
     * API: Ubah password
     */
    public function changePassword(Request $request)
    {
        $user = auth()->user();
        
        $request->validate([
            'current_password' => 'required|current_password',
            'new_password' => 'required|string|min:6|confirmed|different:current_password',
            'new_password_confirmation' => 'required'
        ], [
            'current_password.required' => 'Password lama wajib diisi',
            'current_password.current_password' => 'Password lama tidak sesuai',
            'new_password.required' => 'Password baru wajib diisi',
            'new_password.min' => 'Password baru minimal 6 karakter',
            'new_password.confirmed' => 'Konfirmasi password baru tidak cocok',
            'new_password.different' => 'Password baru harus berbeda dengan password lama',
        ]);

        $user->password = Hash::make($request->new_password);
        $user->save();

        // 🔥 LOG ACTIVITY: Ubah Password
        try {
            LogActivity::create([
                'user_id' => auth()->id(),
                'tipe' => 'update',
                'aktivitas' => 'Ubah Password',
                'deskripsi' => 'Pegawai ' . $user->username . ' mengubah password akun',
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent()
            ]);
        } catch (\Exception $logError) {
            \Log::error('Gagal membuat log: ' . $logError->getMessage());
        }

        return response()->json([
            'success' => true,
            'message' => 'Password berhasil diubah.'
        ]);
    }

    /**
     * API: Update notifikasi
     */
    public function updateNotifikasi(Request $request)
    {
        $user = auth()->user();
        
        // Simpan data lama untuk log
        $oldEmailNotif = $user->email_notification ?? false;
        $oldWhatsappNotif = $user->whatsapp_notification ?? false;
        
        $request->validate([
            'email_notification' => 'boolean',
            'whatsapp_notification' => 'boolean',
        ]);

        $changes = [];
        
        // Jika ada kolom notifikasi di tabel users
        if (Schema::hasColumn('users', 'email_notification')) {
            $user->email_notification = $request->email_notification ?? false;
            if ($oldEmailNotif != $user->email_notification) {
                $changes[] = 'notifikasi email menjadi ' . ($user->email_notification ? 'Aktif' : 'Nonaktif');
            }
        }
        if (Schema::hasColumn('users', 'whatsapp_notification')) {
            $user->whatsapp_notification = $request->whatsapp_notification ?? false;
            if ($oldWhatsappNotif != $user->whatsapp_notification) {
                $changes[] = 'notifikasi WhatsApp menjadi ' . ($user->whatsapp_notification ? 'Aktif' : 'Nonaktif');
            }
        }
        $user->save();

        // 🔥 LOG ACTIVITY: Update Notifikasi
        if (!empty($changes)) {
            try {
                LogActivity::create([
                    'user_id' => auth()->id(),
                    'tipe' => 'update',
                    'aktivitas' => 'Update Preferensi Notifikasi',
                    'deskripsi' => 'Pegawai ' . $user->username . ' mengubah preferensi notifikasi: ' . implode(', ', $changes),
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent()
                ]);
            } catch (\Exception $logError) {
                \Log::error('Gagal membuat log: ' . $logError->getMessage());
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Preferensi notifikasi berhasil diperbarui.'
        ]);
    }
}