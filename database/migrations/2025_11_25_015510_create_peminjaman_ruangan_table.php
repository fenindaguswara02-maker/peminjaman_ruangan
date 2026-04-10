<?php
// database/migrations/2024_01_01_000002_create_peminjaman_ruangan_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('peminjaman_ruangan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('ruangan_id')->constrained('ruangan')->onDelete('cascade');
            $table->string('acara');
            $table->string('hari');
            $table->date('tanggal');
            $table->time('jam_mulai');
            $table->time('jam_selesai');
            $table->integer('jumlah_peserta');
            $table->text('keterangan')->nullable();
            $table->string('lampiran_surat')->nullable();
            $table->enum('status', ['menunggu', 'disetujui', 'ditolak', 'selesai', 'dibatalkan'])->default('menunggu');
            $table->text('alasan_penolakan')->nullable();
            $table->timestamps();

            $table->index(['ruangan_id', 'tanggal']);
            $table->index(['status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('peminjaman_ruangan');
    }
};