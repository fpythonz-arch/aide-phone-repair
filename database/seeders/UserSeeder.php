<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $accounts = [
            ['name' => 'Abdoul Diallo', 'email' => 'abdoul@atelier.com', 'password' => 'demo1234', 'role' => 'Technicien senior'],
            ['name' => 'Ibrahim Koné', 'email' => 'ibrahim@atelier.com', 'password' => 'demo1234', 'role' => 'Technicien'],
            ['name' => 'Moussa Traoré', 'email' => 'moussa@atelier.com', 'password' => 'demo1234', 'role' => 'Admin'],
            ['name' => 'Administrateur', 'email' => 'admin@aidephone.com', 'password' => 'admin123', 'role' => 'Admin'],
        ];

        foreach ($accounts as $account) {
            User::query()->updateOrCreate(
                ['email' => $account['email']],
                [
                    'name' => $account['name'],
                    'password' => Hash::make($account['password']),
                    'role' => $account['role'],
                ]
            );
        }
    }
}
