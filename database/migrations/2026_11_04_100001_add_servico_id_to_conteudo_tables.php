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
        // Serviço responsável: só os editores deste serviço alteram o registo. Os outros
        // serviços podem usá-lo (ligá-lo aos seus temas, por exemplo), mas não alterá-lo.
        // Se o serviço for apagado, o registo fica sem responsável e só os administradores o alteram.
        foreach (['categorias', 'temas', 'perguntas'] as $tabela) {
            Schema::table($tabela, function (Blueprint $table) {
                $table->foreignId('servico_id')->nullable()->after('id')->constrained('servicos')->nullOnDelete();
            });
        }

        // Dá um responsável ao conteúdo que já existe: o primeiro serviço onde aparece.
        // A ordem importa, porque cada nível usa o responsável do nível de cima.
        DB::table('categorias')->orderBy('id')->each(function (object $categoria) {
            DB::table('categorias')->where('id', $categoria->id)->update([
                'servico_id' => DB::table('servico_categoria')
                    ->join('servicos', 'servicos.id', '=', 'servico_categoria.servico_id')
                    ->where('servico_categoria.categoria_id', $categoria->id)
                    ->orderBy('servicos.ordem')
                    ->value('servicos.id'),
            ]);
        });

        DB::table('temas')->orderBy('id')->each(function (object $tema) {
            DB::table('temas')->where('id', $tema->id)->update([
                'servico_id' => DB::table('categoria_tema')
                    ->join('categorias', 'categorias.id', '=', 'categoria_tema.categoria_id')
                    ->where('categoria_tema.tema_id', $tema->id)
                    ->whereNotNull('categorias.servico_id')
                    ->orderBy('categorias.id')
                    ->value('categorias.servico_id'),
            ]);
        });

        DB::table('perguntas')->orderBy('id')->each(function (object $pergunta) {
            DB::table('perguntas')->where('id', $pergunta->id)->update([
                'servico_id' => DB::table('tema_pergunta')
                    ->join('temas', 'temas.id', '=', 'tema_pergunta.tema_id')
                    ->where('tema_pergunta.pergunta_id', $pergunta->id)
                    ->whereNotNull('temas.servico_id')
                    ->orderBy('temas.id')
                    ->value('temas.servico_id'),
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        foreach (['categorias', 'temas', 'perguntas'] as $tabela) {
            Schema::table($tabela, function (Blueprint $table) {
                $table->dropConstrainedForeignId('servico_id');
            });
        }
    }
};
