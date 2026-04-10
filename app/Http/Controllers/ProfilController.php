<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\LogActivity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class ProfilController extends Controller
{
    /**
     * Display user profile
     */
    public function index()
    {
        $user = Auth::user();
        Log::info('User accessing profile:', ['user_id' => $user->id, 'name' => $user->name]);
        
        return view('user.profile', compact('user'));
    }

    /**
     * Show the form for editing profile
     */
    public function edit()
    {
        $user = Auth::user();
        return view('profil.edit', compact('user'));
    }

    /**
     * Edit data akademik
     */
    public function editAkademik()
    {
        $user = Auth::user();
        Log::info('User editing academic data:', [
            'user_id' => $user->id,
            'nim_nip' => $user->nim_nip,
            'fakultas' => $user->fakultas,
            'prodi' => $user->prodi,
            'no_telepon' => $user->no_telepon
        ]);
        
        return view('user.profile', compact('user'));
    }

    /**
     * Update username via AJAX
     */
    public function updateUsername(Request $request)
    {
        try {
            $user = Auth::user();
            
            Log::info('Updating username:', [
                'user_id' => $user->id,
                'current_username' => $user->username,
                'new_username' => $request->username
            ]);
            
            // Validasi username
            $validator = Validator::make($request->all(), [
                'username' => [
                    'required',
                    'string',
                    'min:3',
                    'max:255',
                    'regex:/^(?=.*[a-zA-Z])(?=.*\d)[a-zA-Z0-9]+$/',
                    'unique:users,username,' . $user->id
                ],
            ], [
                'username.required' => 'Username wajib diisi',
                'username.min' => 'Username minimal 3 karakter',
                'username.max' => 'Username maksimal 255 karakter',
                'username.regex' => 'Username harus mengandung huruf DAN angka (contoh: john123, andri90)',
                'username.unique' => 'Username sudah digunakan, silakan pilih username lain',
            ]);

            if ($validator->fails()) {
                Log::warning('Username validation failed:', [
                    'user_id' => $user->id,
                    'errors' => $validator->errors()->toArray()
                ]);
                
                return response()->json([
                    'success' => false,
                    'message' => 'Validasi gagal',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Cek apakah kolom username ada di tabel
            $columns = DB::getSchemaBuilder()->getColumnListing('users');
            
            if (!in_array('username', $columns)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Kolom username tidak ditemukan di database. Silakan jalankan migration terlebih dahulu.'
                ], 500);
            }

            // Update username
            $oldUsername = $user->username;
            $user->username = strtolower($request->username);
            $user->save();
            
            // ========== LOG ACTIVITY: UPDATE USERNAME ==========
            try {
                LogActivity::create([
                    'user_id' => $user->id,
                    'tipe' => 'update',
                    'aktivitas' => 'Update Username',
                    'deskripsi' => 'User ' . $user->username . ' (' . $user->name . ') mengubah username dari "' . $oldUsername . '" menjadi "' . $user->username . '"',
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent()
                ]);
            } catch (\Exception $e) {
                Log::error('Gagal membuat log update username: ' . $e->getMessage());
            }
            
            Log::info('Username updated successfully:', [
                'user_id' => $user->id,
                'old_username' => $oldUsername,
                'new_username' => $user->username
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Username berhasil diperbarui!',
                'data' => [
                    'username' => $user->username
                ]
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error updating username:', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Check username availability via AJAX
     */
    public function checkUsernameAvailability(Request $request)
    {
        try {
            $username = $request->input('username');
            $userId = Auth::id();
            
            if (empty($username)) {
                return response()->json([
                    'available' => false,
                    'message' => 'Username tidak boleh kosong'
                ]);
            }
            
            // Validasi format username harus mengandung huruf dan angka
            if (!preg_match('/^(?=.*[a-zA-Z])(?=.*\d)[a-zA-Z0-9]+$/', $username)) {
                return response()->json([
                    'available' => false,
                    'message' => 'Username harus mengandung huruf DAN angka (contoh: john123)'
                ]);
            }
            
            // Validasi panjang
            if (strlen($username) < 3) {
                return response()->json([
                    'available' => false,
                    'message' => 'Username minimal 3 karakter'
                ]);
            }
            
            // Cek apakah username sudah digunakan oleh user lain
            $exists = User::where('username', strtolower($username))
                ->where('id', '!=', $userId)
                ->exists();
            
            return response()->json([
                'available' => !$exists,
                'message' => $exists ? 'Username sudah digunakan' : 'Username tersedia'
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error checking username availability:', [
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'available' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update data pribadi via AJAX (dari modal di profile.blade.php)
     */
    public function updatePribadi(Request $request)
    {
        try {
            $user = Auth::user();
            
            Log::info('Updating personal data:', [
                'user_id' => $user->id,
                'request_data' => $request->except('_token', '_method')
            ]);
            
            // Validasi data
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255',
                'email' => 'required|email|unique:users,email,' . $user->id,
                'no_telepon' => 'nullable|string|max:255',
            ]);

            if ($validator->fails()) {
                Log::warning('Validation failed for personal data:', [
                    'user_id' => $user->id,
                    'errors' => $validator->errors()->toArray()
                ]);
                
                return response()->json([
                    'success' => false,
                    'message' => 'Validasi gagal',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Simpan data lama untuk log
            $oldData = [
                'name' => $user->name,
                'email' => $user->email,
                'no_telepon' => $user->no_telepon
            ];
            
            // Update hanya kolom yang ADA di tabel users
            $dataToUpdate = [];
            $updatedFields = [];
            
            if ($request->has('name') && $request->name != $user->name) {
                $dataToUpdate['name'] = $request->name;
                $updatedFields[] = 'nama';
            }
            
            if ($request->has('email') && $request->email != $user->email) {
                $dataToUpdate['email'] = $request->email;
                $updatedFields[] = 'email';
            }
            
            if ($request->has('no_telepon') && $request->no_telepon != $user->no_telepon) {
                $dataToUpdate['no_telepon'] = $request->no_telepon;
                $updatedFields[] = 'no_telepon';
            }
            
            // Update user
            if (!empty($dataToUpdate)) {
                $user->update($dataToUpdate);
                
                // ========== LOG ACTIVITY: UPDATE DATA PRIBADI ==========
                try {
                    $changes = [];
                    if (in_array('nama', $updatedFields)) {
                        $changes[] = 'Nama: "' . $oldData['name'] . '" → "' . $user->name . '"';
                    }
                    if (in_array('email', $updatedFields)) {
                        $changes[] = 'Email: "' . $oldData['email'] . '" → "' . $user->email . '"';
                    }
                    if (in_array('no_telepon', $updatedFields)) {
                        $changes[] = 'No Telepon: "' . ($oldData['no_telepon'] ?? '-') . '" → "' . ($user->no_telepon ?? '-') . '"';
                    }
                    
                    LogActivity::create([
                        'user_id' => $user->id,
                        'tipe' => 'update',
                        'aktivitas' => 'Update Data Pribadi',
                        'deskripsi' => 'User ' . $user->username . ' (' . $user->name . ') memperbarui data pribadi: ' . implode(', ', $changes),
                        'ip_address' => $request->ip(),
                        'user_agent' => $request->userAgent()
                    ]);
                } catch (\Exception $e) {
                    Log::error('Gagal membuat log update pribadi: ' . $e->getMessage());
                }
            }

            Log::info('Personal data updated successfully:', [
                'user_id' => $user->id,
                'updated_fields' => array_keys($dataToUpdate)
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Data pribadi berhasil diperbarui',
                'data' => [
                    'name' => $user->name,
                    'email' => $user->email,
                    'no_telepon' => $user->no_telepon
                ]
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error updating personal data:', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update data akademik
     */
    public function updateAkademik(Request $request)
    {
        try {
            $user = Auth::user();
            
            Log::info('Updating academic data:', [
                'user_id' => $user->id,
                'request_data' => $request->except('_token', '_method')
            ]);
            
            // Validasi data
            $validator = Validator::make($request->all(), [
                'nim_nip' => 'nullable|string|max:50',
                'fakultas' => 'nullable|string|max:100',
                'prodi' => 'nullable|string|max:100',
                'no_telepon' => 'nullable|string|max:255',
                'jenis_pengaju' => 'nullable|in:mahasiswa,dosen,staff'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validasi gagal',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Simpan data lama untuk log
            $oldData = [
                'nim_nip' => $user->nim_nip,
                'fakultas' => $user->fakultas,
                'prodi' => $user->prodi,
                'jenis_pengaju' => $user->jenis_pengaju
            ];
            
            // Update hanya kolom yang ADA
            $dataToUpdate = [];
            $updatedFields = [];
            
            if ($request->has('nim_nip') && $request->nim_nip != $user->nim_nip) {
                $dataToUpdate['nim_nip'] = $request->nim_nip;
                $updatedFields[] = 'NIM/NIP';
            }
            
            if ($request->has('fakultas') && $request->fakultas != $user->fakultas) {
                $dataToUpdate['fakultas'] = $request->fakultas;
                $updatedFields[] = 'Fakultas';
            }
            
            if ($request->has('prodi') && $request->prodi != $user->prodi) {
                $dataToUpdate['prodi'] = $request->prodi;
                $updatedFields[] = 'Program Studi';
            }
            
            if ($request->has('jenis_pengaju') && $request->jenis_pengaju != $user->jenis_pengaju) {
                $dataToUpdate['jenis_pengaju'] = $request->jenis_pengaju;
                $updatedFields[] = 'Jenis Pengaju';
            }

            if (!empty($dataToUpdate)) {
                $user->update($dataToUpdate);
                
                // ========== LOG ACTIVITY: UPDATE DATA AKADEMIK ==========
                try {
                    $changes = [];
                    if (in_array('NIM/NIP', $updatedFields)) {
                        $changes[] = 'NIM/NIP: "' . ($oldData['nim_nip'] ?? '-') . '" → "' . ($user->nim_nip ?? '-') . '"';
                    }
                    if (in_array('Fakultas', $updatedFields)) {
                        $changes[] = 'Fakultas: "' . ($oldData['fakultas'] ?? '-') . '" → "' . ($user->fakultas ?? '-') . '"';
                    }
                    if (in_array('Program Studi', $updatedFields)) {
                        $changes[] = 'Program Studi: "' . ($oldData['prodi'] ?? '-') . '" → "' . ($user->prodi ?? '-') . '"';
                    }
                    if (in_array('Jenis Pengaju', $updatedFields)) {
                        $changes[] = 'Jenis Pengaju: "' . ($oldData['jenis_pengaju'] ?? '-') . '" → "' . ($user->jenis_pengaju ?? '-') . '"';
                    }
                    
                    LogActivity::create([
                        'user_id' => $user->id,
                        'tipe' => 'update',
                        'aktivitas' => 'Update Data Akademik',
                        'deskripsi' => 'User ' . $user->username . ' (' . $user->name . ') memperbarui data akademik: ' . implode(', ', $changes),
                        'ip_address' => $request->ip(),
                        'user_agent' => $request->userAgent()
                    ]);
                } catch (\Exception $e) {
                    Log::error('Gagal membuat log update akademik: ' . $e->getMessage());
                }
            }
            
            Log::info('Academic data updated:', [
                'user_id' => $user->id,
                'updated_fields' => array_keys($dataToUpdate)
            ]);

            // Jika request AJAX, return JSON
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Data akademik berhasil diperbarui!',
                    'data' => [
                        'nim_nip' => $user->nim_nip,
                        'fakultas' => $user->fakultas,
                        'prodi' => $user->prodi,
                        'no_telepon' => $user->no_telepon,
                        'jenis_pengaju' => $user->jenis_pengaju
                    ]
                ]);
            }

            return redirect()->route('profil.index')
                ->with('success', 'Data akademik berhasil diperbarui!');
                
        } catch (\Exception $e) {
            Log::error('Error updating academic data:', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Terjadi kesalahan: ' . $e->getMessage()
                ], 500);
            }
            
            return back()->withErrors(['error' => 'Terjadi kesalahan: ' . $e->getMessage()]);
        }
    }

    /**
     * Update the user profile
     */
    public function update(Request $request)
    {
        $user = Auth::user();
        
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email,' . $user->id,
            'no_telepon' => 'nullable|string|max:255',
            'foto' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        // Simpan data lama untuk log
        $oldData = [
            'name' => $user->name,
            'email' => $user->email,
            'no_telepon' => $user->no_telepon
        ];
        
        $dataToUpdate = [
            'name' => $request->name,
            'email' => $request->email,
        ];

        if ($request->has('no_telepon')) {
            $dataToUpdate['no_telepon'] = $request->no_telepon;
        }

        // Update foto jika ada
        if ($request->hasFile('foto')) {
            // Delete old foto if exists
            if ($user->foto && Storage::exists('public/' . $user->foto)) {
                Storage::delete('public/' . $user->foto);
            }
            
            $fotoPath = $request->file('foto')->store('foto-profil', 'public');
            $dataToUpdate['foto'] = $fotoPath;
        }

        $user->update($dataToUpdate);
        
        // ========== LOG ACTIVITY: UPDATE PROFIL ==========
        try {
            $changes = [];
            if ($oldData['name'] != $user->name) {
                $changes[] = 'Nama: "' . $oldData['name'] . '" → "' . $user->name . '"';
            }
            if ($oldData['email'] != $user->email) {
                $changes[] = 'Email: "' . $oldData['email'] . '" → "' . $user->email . '"';
            }
            if ($oldData['no_telepon'] != $user->no_telepon) {
                $changes[] = 'No Telepon: "' . ($oldData['no_telepon'] ?? '-') . '" → "' . ($user->no_telepon ?? '-') . '"';
            }
            if ($request->hasFile('foto')) {
                $changes[] = 'Foto profil diperbarui';
            }
            
            if (!empty($changes)) {
                LogActivity::create([
                    'user_id' => $user->id,
                    'tipe' => 'update',
                    'aktivitas' => 'Update Profil',
                    'deskripsi' => 'User ' . $user->username . ' (' . $user->name . ') memperbarui profil: ' . implode(', ', $changes),
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent()
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Gagal membuat log update profil: ' . $e->getMessage());
        }
        
        Log::info('Profile updated:', [
            'user_id' => $user->id,
            'name' => $dataToUpdate['name'],
            'email' => $dataToUpdate['email']
        ]);

        return redirect()->route('profil.index')
            ->with('success', 'Profil berhasil diperbarui!');
    }

    /**
     * UPLOAD FOTO PROFIL VIA AJAX
     */
    public function uploadPhoto(Request $request)
    {
        try {
            $user = Auth::user();
            
            Log::info('Uploading profile photo:', [
                'user_id' => $user->id,
                'has_file' => $request->hasFile('foto'),
            ]);
            
            // Validasi file
            $validator = Validator::make($request->all(), [
                'foto' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validasi gagal',
                    'errors' => $validator->errors()
                ], 422);
            }

            if ($request->hasFile('foto')) {
                // Cek apakah kolom foto ada di tabel
                $columns = \DB::getSchemaBuilder()->getColumnListing('users');
                
                if (!in_array('foto', $columns)) {
                    // Jika kolom tidak ada, simpan sementara
                    $path = $request->file('foto')->store('temp-foto', 'public');
                    
                    return response()->json([
                        'success' => true,
                        'message' => 'Foto diupload ke folder sementara',
                        'photo_url' => asset('storage/' . $path),
                        'temp' => true
                    ]);
                }
                
                // Hapus foto lama jika ada
                $oldFoto = $user->foto;
                if ($user->foto && Storage::disk('public')->exists($user->foto)) {
                    Storage::disk('public')->delete($user->foto);
                }
                
                // Simpan foto baru
                $path = $request->file('foto')->store('foto-profil', 'public');
                $user->foto = $path;
                $user->save();
                
                // ========== LOG ACTIVITY: UPLOAD FOTO ==========
                try {
                    LogActivity::create([
                        'user_id' => $user->id,
                        'tipe' => 'update',
                        'aktivitas' => 'Upload Foto Profil',
                        'deskripsi' => 'User ' . $user->username . ' (' . $user->name . ') mengupload foto profil baru' . ($oldFoto ? ' (mengganti foto lama)' : ''),
                        'ip_address' => $request->ip(),
                        'user_agent' => $request->userAgent()
                    ]);
                } catch (\Exception $e) {
                    Log::error('Gagal membuat log upload foto: ' . $e->getMessage());
                }
                
                return response()->json([
                    'success' => true,
                    'message' => 'Foto profil berhasil diperbarui',
                    'photo_url' => asset('storage/' . $path)
                ]);
            }
            
            return response()->json([
                'success' => false,
                'message' => 'Tidak ada file yang diupload'
            ], 400);
            
        } catch (\Exception $e) {
            Log::error('Error uploading photo:', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * CHANGE PASSWORD VIA AJAX (Method utama yang digunakan)
     */
    public function changePassword(Request $request)
    {
        try {
            $user = Auth::user();
            
            Log::info('Changing password via AJAX:', ['user_id' => $user->id]);
            
            // Validasi input
            $validator = Validator::make($request->all(), [
                'current_password' => 'required',
                'new_password' => 'required|string|min:8',
                'new_password_confirmation' => 'required|same:new_password'
            ], [
                'new_password.min' => 'Password minimal 8 karakter',
                'new_password_confirmation.same' => 'Konfirmasi password tidak cocok'
            ]);

            // Tambahkan validasi untuk huruf besar, kecil, angka
            $validator->after(function ($validator) use ($request) {
                $password = $request->new_password;
                
                if (!preg_match('/[A-Z]/', $password)) {
                    $validator->errors()->add('new_password', 'Password harus mengandung huruf besar');
                }
                
                if (!preg_match('/[a-z]/', $password)) {
                    $validator->errors()->add('new_password', 'Password harus mengandung huruf kecil');
                }
                
                if (!preg_match('/[0-9]/', $password)) {
                    $validator->errors()->add('new_password', 'Password harus mengandung angka');
                }
            });

            if ($validator->fails()) {
                Log::warning('Password validation failed:', [
                    'user_id' => $user->id,
                    'errors' => $validator->errors()->toArray()
                ]);
                
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }

            // Cek password lama
            if (!Hash::check($request->current_password, $user->password)) {
                return response()->json([
                    'success' => false,
                    'errors' => [
                        'current_password' => ['Password lama tidak sesuai']
                    ]
                ], 422);
            }

            // Update password
            $user->password = Hash::make($request->new_password);
            $user->save();
            
            // ========== LOG ACTIVITY: CHANGE PASSWORD ==========
            try {
                LogActivity::create([
                    'user_id' => $user->id,
                    'tipe' => 'update',
                    'aktivitas' => 'Ganti Password',
                    'deskripsi' => 'User ' . $user->username . ' (' . $user->name . ') mengganti password akun',
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent()
                ]);
            } catch (\Exception $e) {
                Log::error('Gagal membuat log ganti password: ' . $e->getMessage());
            }
            
            Log::info('Password changed successfully via AJAX:', ['user_id' => $user->id]);

            return response()->json([
                'success' => true,
                'message' => 'Password berhasil diubah!'
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error changing password via AJAX:', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete profile photo
     */
    public function deletePhoto(Request $request)
    {
        $user = Auth::user();
        
        $oldFoto = $user->foto;
        
        if ($user->foto && Storage::exists('public/' . $user->foto)) {
            Storage::delete('public/' . $user->foto);
            $user->foto = null;
            $user->save();
            
            // ========== LOG ACTIVITY: DELETE FOTO ==========
            try {
                LogActivity::create([
                    'user_id' => $user->id,
                    'tipe' => 'delete',
                    'aktivitas' => 'Hapus Foto Profil',
                    'deskripsi' => 'User ' . $user->username . ' (' . $user->name . ') menghapus foto profil',
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent()
                ]);
            } catch (\Exception $e) {
                Log::error('Gagal membuat log hapus foto: ' . $e->getMessage());
            }
            
            Log::info('Profile photo deleted:', ['user_id' => $user->id]);
        }

        return back()->with('success', 'Foto profil berhasil dihapus!');
    }
}