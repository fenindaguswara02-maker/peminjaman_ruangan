<?php
// database/migrations/xxxx_xx_xx_xxxxxx_update_jadwal_table_add_ruangan_id.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('jadwal', function (Blueprint $table) {
            // Hapus kolom lokasi lama
            if (Schema::hasColumn('jadwal', 'lokasi')) {
                $table->dropColumn('lokasi');
            }
            
            // Tambah kolom ruangan_id
            $table->foreignId('ruangan_id')->nullable()->after('waktu_selesai')->constrained('ruangan')->onDelete('set null');
            
            // Tambah kolom kapasitas_peserta
            $table->integer('kapasitas_peserta')->nullable()->after('ruangan_id');
        });
    }

    public function down()
    {
        Schema::table('jadwal', function (Blueprint $table) {
            $table->dropForeign(['ruangan_id']);
            $table->dropColumn('ruangan_id');
            $table->dropColumn('kapasitas_peserta');
            $table->string('lokasi')->nullable();
        });
    }
};