<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LogActivity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use App\Models\User;
use Carbon\Carbon;

class ProfilController extends Controller
{
    /**
     * Display halaman profil admin (single page)
     */
    public function index()
    {
        $user = auth()->user();
        
        return view('admin.profil.index', compact('user'));
    }

    /**
     * API: Update profil (DENGAN USERNAME)
     */
    public function update(Request $request)
    {
        $user = auth()->user();
        
        $request->validate([
            'username' => [
                'required',
                'string',
                'max:255',
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
        ]);

        // Simpan data lama untuk log
        $oldData = [
            'username' => $user->username,
            'name' => $user->name,
            'email' => $user->email,
            'nim_nip' => $user->nim_nip,
            'no_telepon' => $user->no_telepon,
        ];

        $data = $request->only([
            'username', 
            'name', 
            'email', 
            'nim_nip', 
            'no_telepon',
        ]);

        $user->update($data);

        // Refresh user untuk mendapatkan data terbaru
        $user = auth()->user();

        // ========== LOG ACTIVITY ==========
        $changes = [];
        $changeDetails = [];
        
        if ($oldData['username'] != $user->username) {
            $changes[] = 'username';
            $changeDetails[] = "username: {$oldData['username']} → {$user->username}";
        }
        if ($oldData['name'] != $user->name) {
            $changes[] = 'nama';
            $changeDetails[] = "nama: {$oldData['name']} → {$user->name}";
        }
        if ($oldData['email'] != $user->email) {
            $changes[] = 'email';
            $changeDetails[] = "email: {$oldData['email']} → {$user->email}";
        }
        if ($oldData['nim_nip'] != $user->nim_nip) {
            $changes[] = 'NIM/NIP';
            $changeDetails[] = "NIM/NIP: {$oldData['nim_nip']} → {$user->nim_nip}";
        }
        if ($oldData['no_telepon'] != $user->no_telepon) {
            $changes[] = 'no telepon';
            $changeDetails[] = "no telepon: {$oldData['no_telepon']} → {$user->no_telepon}";
        }
        
        if (!empty($changeDetails)) {
            $deskripsiLog = "========================================\n";
            $deskripsiLog .= "✏️ UPDATE PROFIL ADMIN\n";
            $deskripsiLog .= "========================================\n";
            $deskripsiLog .= "🆔 Username : " . $user->username . "\n";
            $deskripsiLog .= "👤 Nama    : " . $user->name . "\n";
            $deskripsiLog .= "👔 Role    : " . $user->role . "\n";
            $deskripsiLog .= "\n📝 PERUBAHAN:\n";
            foreach ($changeDetails as $detail) {
                $deskripsiLog .= "   • " . $detail . "\n";
            }
            $deskripsiLog .= "\n👤 Diupdate Oleh : " . ($user->name ?? $user->username ?? 'System') . " (sendiri)\n";
            $deskripsiLog .= "⏰ Waktu         : " . Carbon::now()->format('d-m-Y H:i:s') . "\n";
            $deskripsiLog .= "🌐 IP Address    : " . $request->ip() . "\n";
            $deskripsiLog .= "========================================";
            
            try {
                LogActivity::create([
                    'user_id' => $user->id,
                    'tipe' => 'update',
                    'aktivitas' => 'UPDATE PROFIL ADMIN',
                    'deskripsi' => $deskripsiLog,
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent()
                ]);
            } catch (\Exception $logError) {
                \Log::error('Gagal membuat log: ' . $logError->getMessage());
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Profil berhasil diperbarui.',
            'data' => [
                'id' => $user->id,
                'username' => $user->username,
                'name' => $user->name,
                'email' => $user->email,
                'nim_nip' => $user->nim_nip,
                'no_telepon' => $user->no_telepon,
                'role' => $user->role,
                'updated_at' => $user->updated_at
            ]
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

        // Simpan foto lama untuk log
        $oldPhoto = $user->foto;

        // Hapus foto lama jika ada
        if ($user->foto && Storage::disk('public')->exists($user->foto)) {
            Storage::disk('public')->delete($user->foto);
        }

        // Upload foto baru
        $path = $request->file('foto')->store('foto-profil', 'public');
        $user->foto = $path;
        $user->save();

        // ========== LOG ACTIVITY ==========
        $deskripsiLog = "========================================\n";
        $deskripsiLog .= "🖼️ UPLOAD FOTO PROFIL ADMIN\n";
        $deskripsiLog .= "========================================\n";
        $deskripsiLog .= "🆔 Username : " . $user->username . "\n";
        $deskripsiLog .= "👤 Nama    : " . $user->name . "\n";
        $deskripsiLog .= "👔 Role    : " . $user->role . "\n";
        $deskripsiLog .= "📁 Foto Lama: " . ($oldPhoto ? basename($oldPhoto) : 'Tidak ada foto sebelumnya') . "\n";
        $deskripsiLog .= "📁 Foto Baru: " . basename($path) . "\n";
        $deskripsiLog .= "👤 Diupload Oleh : " . ($user->name ?? $user->username ?? 'System') . " (sendiri)\n";
        $deskripsiLog .= "⏰ Waktu         : " . Carbon::now()->format('d-m-Y H:i:s') . "\n";
        $deskripsiLog .= "🌐 IP Address    : " . $request->ip() . "\n";
        $deskripsiLog .= "========================================";
        
        try {
            LogActivity::create([
                'user_id' => $user->id,
                'tipe' => 'update',
                'aktivitas' => 'UPLOAD FOTO PROFIL',
                'deskripsi' => $deskripsiLog,
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
    public function deletePhoto()
    {
        $user = auth()->user();
        
        $oldPhoto = $user->foto;

        if ($user->foto && Storage::disk('public')->exists($user->foto)) {
            Storage::disk('public')->delete($user->foto);
            $user->foto = null;
            $user->save();
        }

        // ========== LOG ACTIVITY ==========
        $deskripsiLog = "========================================\n";
        $deskripsiLog .= "🗑️ HAPUS FOTO PROFIL ADMIN\n";
        $deskripsiLog .= "========================================\n";
        $deskripsiLog .= "🆔 Username : " . $user->username . "\n";
        $deskripsiLog .= "👤 Nama    : " . $user->name . "\n";
        $deskripsiLog .= "👔 Role    : " . $user->role . "\n";
        $deskripsiLog .= "📁 Foto Dihapus: " . ($oldPhoto ? basename($oldPhoto) : 'Tidak ada foto') . "\n";
        $deskripsiLog .= "👤 Dihapus Oleh : " . ($user->name ?? $user->username ?? 'System') . " (sendiri)\n";
        $deskripsiLog .= "⏰ Waktu         : " . Carbon::now()->format('d-m-Y H:i:s') . "\n";
        $deskripsiLog .= "🌐 IP Address    : " . request()->ip() . "\n";
        $deskripsiLog .= "========================================";
        
        try {
            LogActivity::create([
                'user_id' => $user->id,
                'tipe' => 'update',
                'aktivitas' => 'HAPUS FOTO PROFIL',
                'deskripsi' => $deskripsiLog,
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent()
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
        $request->validate([
            'current_password' => 'required|current_password',
            'new_password' => 'required|string|min:8|confirmed|different:current_password',
            'new_password_confirmation' => 'required'
        ]);

        $user = auth()->user();
        $user->password = Hash::make($request->new_password);
        $user->save();

        // ========== LOG ACTIVITY ==========
        $deskripsiLog = "========================================\n";
        $deskripsiLog .= "🔐 UBAH PASSWORD ADMIN\n";
        $deskripsiLog .= "========================================\n";
        $deskripsiLog .= "🆔 Username : " . $user->username . "\n";
        $deskripsiLog .= "👤 Nama    : " . $user->name . "\n";
        $deskripsiLog .= "👔 Role    : " . $user->role . "\n";
        $deskripsiLog .= "📝 Status  : Password berhasil diubah\n";
        $deskripsiLog .= "👤 Diubah Oleh : " . ($user->name ?? $user->username ?? 'System') . " (sendiri)\n";
        $deskripsiLog .= "⏰ Waktu       : " . Carbon::now()->format('d-m-Y H:i:s') . "\n";
        $deskripsiLog .= "🌐 IP Address  : " . $request->ip() . "\n";
        $deskripsiLog .= "========================================";
        
        try {
            LogActivity::create([
                'user_id' => $user->id,
                'tipe' => 'update',
                'aktivitas' => 'UBAH PASSWORD',
                'deskripsi' => $deskripsiLog,
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
     * API: Update preferensi notifikasi
     */
    public function updateNotifikasi(Request $request)
    {
        $user = auth()->user();
        
        $request->validate([
            'notifikasi_email' => 'boolean',
            'notifikasi_whatsapp' => 'boolean',
        ]);

        // Simpan data lama untuk log
        $oldEmailNotif = $user->notifikasi_email ?? false;
        $oldWhatsappNotif = $user->notifikasi_whatsapp ?? false;

        $user->notifikasi_email = $request->boolean('notifikasi_email');
        $user->notifikasi_whatsapp = $request->boolean('notifikasi_whatsapp');
        $user->save();

        // ========== LOG ACTIVITY ==========
        $changeDetails = [];
        if ($oldEmailNotif != $user->notifikasi_email) {
            $changeDetails[] = "notifikasi email: " . ($oldEmailNotif ? 'AKTIF' : 'NONAKTIF') . " → " . ($user->notifikasi_email ? 'AKTIF' : 'NONAKTIF');
        }
        if ($oldWhatsappNotif != $user->notifikasi_whatsapp) {
            $changeDetails[] = "notifikasi WhatsApp: " . ($oldWhatsappNotif ? 'AKTIF' : 'NONAKTIF') . " → " . ($user->notifikasi_whatsapp ? 'AKTIF' : 'NONAKTIF');
        }
        
        if (!empty($changeDetails)) {
            $deskripsiLog = "========================================\n";
            $deskripsiLog .= "🔔 UPDATE PREFERENSI NOTIFIKASI ADMIN\n";
            $deskripsiLog .= "========================================\n";
            $deskripsiLog .= "🆔 Username : " . $user->username . "\n";
            $deskripsiLog .= "👤 Nama    : " . $user->name . "\n";
            $deskripsiLog .= "👔 Role    : " . $user->role . "\n";
            $deskripsiLog .= "\n📝 PERUBAHAN:\n";
            foreach ($changeDetails as $detail) {
                $deskripsiLog .= "   • " . $detail . "\n";
            }
            $deskripsiLog .= "\n👤 Diupdate Oleh : " . ($user->name ?? $user->username ?? 'System') . " (sendiri)\n";
            $deskripsiLog .= "⏰ Waktu         : " . Carbon::now()->format('d-m-Y H:i:s') . "\n";
            $deskripsiLog .= "🌐 IP Address    : " . $request->ip() . "\n";
            $deskripsiLog .= "========================================";
            
            try {
                LogActivity::create([
                    'user_id' => $user->id,
                    'tipe' => 'update',
                    'aktivitas' => 'UPDATE PREFERENSI NOTIFIKASI',
                    'deskripsi' => $deskripsiLog,
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