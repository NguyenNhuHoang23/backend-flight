<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Tạo tài khoản Admin
        User::updateOrCreate(
            ['email' => 'admin@example.com'], // Kiểm tra trùng lặp theo email
            [
                'userName' => 'admin',
                'email' => 'admin@example.com',
                'password' => Hash::make('12345678'), // Mật khẩu mặc định
                // Nếu bảng users có cột phân quyền (ví dụ: role hoặc is_admin), hãy bật dòng phù hợp:
                'role' => 'admin',
                // 'is_admin' => true,
            ]
        );

        // 2. Tạo tài khoản Client (Khách hàng)
        User::updateOrCreate(
            ['email' => 'client@example.com'],
            [
                'userName' => 'client',
                'email' => 'client@example.com',
                'password' => Hash::make('12345678'),
                // 'role' => 'client',
                // 'is_admin' => false,
            ]
        );
    }
}
