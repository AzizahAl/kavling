<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class AdminSeeder extends Seeder
{
    /** Akun admin awal. Email & kata sandi bisa diatur lewat .env (ADMIN_EMAIL, ADMIN_PASSWORD). Segera ganti setelah login pertama. */
    public function run(): void
    {
        User::firstOrCreate(
            ['email' => env('ADMIN_EMAIL', 'admin@tectona.test')],
            [
                'name'     => env('ADMIN_NAME', 'Administrator'),
                'password' => env('ADMIN_PASSWORD', 'tectona2026'),
                'role'     => 'admin',
                'aktif'    => true,
            ]
        );
    }
}
