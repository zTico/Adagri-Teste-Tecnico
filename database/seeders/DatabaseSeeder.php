<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        User::factory()->admin()->create([
            'name' => 'Usuário Administrador',
            'email' => 'admin@agro.test',
            'password' => 'password',
        ]);

        User::factory()->viewer()->create([
            'name' => 'Usuário',
            'email' => 'viewer@agro.test',
            'password' => 'password',
        ]);
    }
}
