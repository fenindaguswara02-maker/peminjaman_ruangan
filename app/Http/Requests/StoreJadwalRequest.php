<?php
// app/Http/Requests/StoreJadwalRequest.php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\Ruangan;

class StoreJadwalRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'nama_kegiatan' => 'required|string|max:255',
            'tanggal' => 'required|date|after_or_equal:today',
            'waktu_mulai' => 'required|date_format:H:i',
            'waktu_selesai' => 'required|date_format:H:i|after:waktu_mulai',
            'ruangan_id' => 'required|exists:ruangan,id',
            'kapasitas_peserta' => 'nullable|integer|min:1',
            'deskripsi' => 'nullable|string',
        ];
    }

    public function messages()
    {
        return [
            'nama_kegiatan.required' => 'Nama kegiatan wajib diisi',
            'tanggal.required' => 'Tanggal wajib dipilih',
            'tanggal.after_or_equal' => 'Tanggal tidak boleh kurang dari hari ini',
            'waktu_mulai.required' => 'Waktu mulai wajib diisi',
            'waktu_selesai.required' => 'Waktu selesai wajib diisi',
            'waktu_selesai.after' => 'Waktu selesai harus setelah waktu mulai',
            'ruangan_id.required' => 'Pilih ruangan terlebih dahulu',
            'ruangan_id.exists' => 'Ruangan tidak ditemukan',
            'kapasitas_peserta.min' => 'Kapasitas peserta minimal 1 orang',
        ];
    }
    
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            if ($this->ruangan_id && $this->tanggal && $this->waktu_mulai && $this->waktu_selesai) {
                $ruangan = Ruangan::find($this->ruangan_id);
                
                if (!$ruangan) {
                    $validator->errors()->add('ruangan_id', 'Ruangan tidak ditemukan');
                    return;
                }
                
                if (!$ruangan->isAvailable($this->tanggal, $this->waktu_mulai, $this->waktu_selesai)) {
                    $validator->errors()->add('ruangan_id', 'Ruangan tidak tersedia pada tanggal dan jam yang diminta');
                }
                
                // Cek kapasitas
                if ($this->kapasitas_peserta && $this->kapasitas_peserta > $ruangan->kapasitas) {
                    $validator->errors()->add('kapasitas_peserta', 
                        'Kapasitas peserta melebihi kapasitas ruangan (Maks: ' . $ruangan->kapasitas . ' orang)');
                }
            }
        });
    }
}