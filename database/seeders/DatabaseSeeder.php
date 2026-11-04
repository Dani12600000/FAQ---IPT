<?php

namespace Database\Seeders;

use App\Models\Servico;
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
        // Contas de teste e conteúdo de exemplo. Nunca são criados em produção.
        if (app()->isProduction()) {
            return;
        }

        // Contas de teste com a password "password".
        if (! User::where('email', 'admin@ipt.test')->exists()) {
            User::factory()->admin()->create([
                'name' => 'Administrador de Teste',
                'email' => 'admin@ipt.test',
            ]);
        }

        $this->call(ConteudoExemploSeeder::class);

        // Um editor por serviço, com o slug do serviço no email (por exemplo, biblioteca@ipt.test).
        Servico::all()->each(function (Servico $servico): void {
            $editor = User::where('email', "{$servico->slug}@ipt.test")->first()
                ?? User::factory()->create([
                    'name' => "Editor de Teste ({$servico->nome})",
                    'email' => "{$servico->slug}@ipt.test",
                ]);

            $editor->servicos()->syncWithoutDetaching($servico);
        });
    }
}
