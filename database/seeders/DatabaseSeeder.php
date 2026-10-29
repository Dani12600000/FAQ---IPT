<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Contas de teste com a password "password". Nunca são criadas em produção.
        if (app()->isProduction()) {
            return;
        }

        User::factory()->admin()->create([
            'name' => 'Administrador de Teste',
            'email' => 'admin@ipt.test',
        ]);

        User::factory()->create([
            'name' => 'Editor de Teste',
            'email' => 'editor@ipt.test',
        ]);
    }
}
