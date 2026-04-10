<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('peminjaman_ruangan', function (Blueprint $table) {
            $table->string('jenis_pengaju')->nullable()->after('user_id');
            $table->string('nama_pengaju')->nullable()->after('jenis_pengaju');
            $table->string('nim_nip')->nullable()->after('nama_pengaju');
            $table->string('fakultas')->nullable()->after('nim_nip');
            $table->string('prodi')->nullable()->after('fakultas');
            $table->string('email')->nullable()->after('prodi');
            $table->string('no_telepon')->nullable()->after('email');
        });
    }

    public function down(): void
    {
        Schema::table('peminjaman_ruangan', function (Blueprint $table) {
            $table->dropColumn([
                'jenis_pengaju',
                'nama_pengaju',
                'nim_nip',
                'fakultas',
                'prodi',
                'email',
                'no_telepon'
            ]);
        });
    }
};