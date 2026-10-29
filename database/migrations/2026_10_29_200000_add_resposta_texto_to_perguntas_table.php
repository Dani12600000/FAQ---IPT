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
        // Cópia da resposta sem HTML, para a pesquisa não encontrar etiquetas como "strong" ou "href".
        // É preenchida pelo modelo Pergunta sempre que a resposta muda.
        Schema::table('perguntas', function (Blueprint $table) {
            $table->longText('resposta_texto')->nullable()->after('resposta');
        });

        // Preenche as perguntas que já existem (mesma regra que Pergunta::textoSemHtml()).
        DB::table('perguntas')->orderBy('id')->each(function (object $pergunta) {
            $texto = html_entity_decode(strip_tags(str_replace('<', ' <', $pergunta->resposta)), ENT_QUOTES | ENT_HTML5, 'UTF-8');

            DB::table('perguntas')->where('id', $pergunta->id)->update([
                'resposta_texto' => trim(preg_replace('/\s+/u', ' ', $texto)),
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('perguntas', function (Blueprint $table) {
            $table->dropColumn('resposta_texto');
        });
    }
};
