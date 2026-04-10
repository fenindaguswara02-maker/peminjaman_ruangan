<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run()
    {
        User::create([
            'name' => 'Administrator System',
            'email' => 'admin@inventory.com',
            'password' => Hash::make('password123'),
            'role' => 'admin',
            'no_telepon' => '081234567890',
        ]);

        User::create([
            'name' => 'Budi Santoso',
            'email' => 'pegawai@inventory.com',
            'password' => Hash::make('password123'),
            'role' => 'pegawai',
            'no_telepon' => '081234567891',
        ]);

        User::create([
            'name' => 'Ahmad Wijaya',
            'email' => 'user@inventory.com',
            'password' => Hash::make('password123'),
            'role' => 'user',
            'no_telepon' => '081234567892',
        ]);

        $this->command->info('✅ Demo users created successfully!');
        $this->command->info('👑 Admin: admin@inventory.com / password123');
        $this->command->info('👔 Pegawai: pegawai@inventory.com / password123');
        $this->command->info('👤 User: user@inventory.com / password123');
    }
}