<?php

namespace Database\Seeders;

use App\Models\Auth\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run()
    {
        $user = User::firstOrCreate(
            ['email' => 'teste@teste.com'],
            [
                'name'      => 'Teste admin',
                'password'  => Hash::make('teste123'),
                'is_active' => true,
                'email_verified_at' => now(),
            ]
        );
    }
}
