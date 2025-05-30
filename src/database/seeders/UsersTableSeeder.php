<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Carbon;
use App\Models\User;

class UsersTableSeeder extends Seeder
{
    public function run()
    {
        $users = [
            ['name' => '島 裕子',       'email' => 'shima@example.com'],
            ['name' => '佐々木 蓮',     'email' => 'sasaki@example.com'],
            ['name' => '山田 太郎',     'email' => 'yamada@example.com'],
            ['name' => '鈴木 次郎',    'email' => 'suzuki@example.com'],
            ['name' => '中村 加奈子',   'email' => 'nakamura@example.com'],
        ];

        foreach ($users as $user) {
            User::create([
                'name' => $user['name'],
                'email' => $user['email'],
                'password' => Hash::make('password123'),
                'email_verified_at' => Carbon::now(),
            ]);
        }
    }
}
