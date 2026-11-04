<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Histórico de uma pergunta: uma cópia do texto da pergunta e da resposta sempre
        // que um dos dois muda. Serve para ver quem mudou o quê, repor uma versão antiga
        // e, mais tarde, juntar perguntas repetidas sem perder o que cada uma dizia.
        Schema::create('pergunta_versoes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pergunta_id')->constrained('perguntas')->cascadeOnDelete();
            // 1, 2, 3... dentro de cada pergunta.
            $table->unsignedInteger('numero');
            $table->string('pergunta');
            $table->longText('resposta');
            // Porque foi criada, quando não é uma simples edição (por exemplo, "Reposição da versão 2").
            $table->string('nota')->nullable();
            // Quem fez a alteração. Fica vazio para o que foi feito fora do backoffice (seeders, migrações).
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('created_at')->useCurrent();

            $table->unique(['pergunta_id', 'numero']);
        });

        // As perguntas que já existem começam com a versão 1 (o texto atual).
        DB::table('perguntas')->orderBy('id')->each(function (object $pergunta) {
            DB::table('pergunta_versoes')->insert([
                'pergunta_id' => $pergunta->id,
                'numero' => 1,
                'pergunta' => $pergunta->pergunta,
                'resposta' => $pergunta->resposta,
                'created_at' => $pergunta->updated_at ?? now(),
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pergunta_versoes');
    }
};
