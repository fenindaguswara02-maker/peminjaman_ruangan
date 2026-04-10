<?php
// database/seeders/RuanganSeeder.php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RuanganSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('ruangan')->delete();

        $ruangan = [
            [
                'kode_ruangan' => 'A101',
                'nama_ruangan' => 'Ruang Rapat Kecil',
                'kapasitas' => 10,
                'fasilitas' => 'LCD Projector, Whiteboard, AC',
                'status' => 'tersedia',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'kode_ruangan' => 'A102',
                'nama_ruangan' => 'Ruang Rapat Besar',
                'kapasitas' => 30,
                'fasilitas' => 'Sound System, AC, LCD Projector',
                'status' => 'tersedia',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'kode_ruangan' => 'B201',
                'nama_ruangan' => 'Aula Utama',
                'kapasitas' => 100,
                'fasilitas' => 'Panggung, Sound System, AC, Lighting',
                'status' => 'tersedia',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'kode_ruangan' => 'B202',
                'nama_ruangan' => 'Ruang Seminar',
                'kapasitas' => 50,
                'fasilitas' => 'LCD Projector, Sound System, AC',
                'status' => 'tersedia',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'kode_ruangan' => 'C301',
                'nama_ruangan' => 'Lab Komputer',
                'kapasitas' => 25,
                'fasilitas' => 'Komputer, LCD Projector, AC',
                'status' => 'tersedia',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        DB::table('ruangan')->insert($ruangan);
        
        $this->command->info('Ruangan seeded successfully!');
        $this->command->info('Total ruangan: ' . count($ruangan));
    }
}