<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed akun admin default dan akun kasir contoh.
     * Data menu sepenuhnya diisi melalui dashboard admin.
     */
    public function run(): void
    {
        $admin = User::firstOrCreate(
            ['email' => 'admin@kasirfood.test'],
            [
                'name' => 'Admin Kasir',
                'password' => bcrypt('password'),
            ]
        );

        if ($admin->role !== 'admin') {
            $admin->update(['role' => 'admin']);
        }

        // Akun kasir contoh tetap dipertahankan untuk pengujian role.
        User::firstOrCreate(
            ['email' => 'test@example.com'],
            [
                'name' => 'Kasir Contoh',
                'role' => 'kasir',
                'password' => bcrypt('password'),
            ]
        );
    }
}
