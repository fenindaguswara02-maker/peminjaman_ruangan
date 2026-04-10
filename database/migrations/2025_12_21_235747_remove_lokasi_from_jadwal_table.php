<?php
// database/migrations/xxxx_xx_xx_xxxxxx_remove_lokasi_from_jadwal_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // Cek apakah kolom lokasi masih ada
        if (Schema::hasColumn('jadwal', 'lokasi')) {
            Schema::table('jadwal', function (Blueprint $table) {
                $table->dropColumn('lokasi');
            });
        }
    }

    public function down()
    {
        Schema::table('jadwal', function (Blueprint $table) {
            $table->string('lokasi')->nullable();
        });
    }
};