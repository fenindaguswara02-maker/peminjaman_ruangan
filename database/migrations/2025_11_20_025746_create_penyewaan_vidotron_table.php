<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('penyewaan_vidotron', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            
            // Informasi Pengaju
            $table->string('fakultas');
            $table->string('prodi');
            $table->enum('jenis_pengaju', ['dosen', 'staff', 'mahasiswa', 'organisasi']);
            $table->string('jabatan');
            $table->string('nama_pengaju');
            $table->string('nim_nidn');
            $table->string('email');
            $table->string('no_telepon');
            
            // Informasi Penyewaan
            $table->enum('jenis_vidotron', ['led_video_wall', 'led_display', 'led_screen', 'digital_billboard']);
            $table->string('ukuran');
            $table->string('lokasi_pemasangan');
            $table->string('tujuan_pemasangan');
            
            // Waktu Penyewaan
            $table->date('tanggal_mulai');
            $table->date('tanggal_selesai');
            $table->time('waktu_mulai');
            $table->time('waktu_selesai');
            
            // Konten
            $table->enum('jenis_konten', ['video', 'image', 'text', 'live_feed']);
            $table->text('deskripsi_konten');
            $table->string('link_konten')->nullable();
            
            // Fasilitas Tambahan
            $table->boolean('sound_system')->default(false);
            $table->boolean('operator')->default(false);
            $table->boolean('desain_grafis')->default(false);
            $table->boolean('instalasi')->default(false);
            
            // Dokumen
            $table->string('surat_pengajuan');
            $table->string('storyboard_konten')->nullable();
            $table->string('proposal_kegiatan')->nullable();
            $table->text('catatan')->nullable();
            
            // Status
            $table->enum('status', ['menunggu', 'disetujui', 'ditolak', 'dibatalkan'])->default('menunggu');
            $table->text('alasan_penolakan')->nullable();
            
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('penyewaan_vidotron');
    }
};