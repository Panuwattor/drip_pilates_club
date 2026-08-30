<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@admin.com'],
            [
                'name' => 'ผู้ดูแลระบบ',
                'password' => 'a12345678',
                'role' => 'owner',
                'is_active' => true,
            ]
        );
    }
}
