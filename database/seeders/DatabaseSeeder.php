<?php

namespace Database\Seeders;

use App\Models\Language;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $languages = [
            ['code' => 'en', 'name' => 'English'],
            ['code' => 'ar', 'name' => 'العربية'],
        ];

        foreach ($languages as $language) {
            Language::updateOrCreate(
                ['code' => $language['code']],
                $language
            );
        }

        User::updateOrCreate(
            ['email' => 'admin@pass.local'],
            [
                'username'  => 'superadmin',
                'full_name' => 'Super Administrator',
                'password'  => Hash::make('Admin@12345'),
                'role'      => 'admin',
                'status'    => 'active',
            ]
        );
    }
}
