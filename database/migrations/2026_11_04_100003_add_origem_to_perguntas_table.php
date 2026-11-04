<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Versão própria de um serviço ("ramo"): quando um serviço quer uma resposta diferente
        // para uma pergunta de outro serviço, fica com uma cópia que guarda de onde veio.
        Schema::table('perguntas', function (Blueprint $table) {
            // Pergunta de onde esta nasceu. Se a original for apagada, esta fica uma pergunta normal.
            $table->foreignId('origem_id')->nullable()->after('servico_id')->constrained('perguntas')->nullOnDelete();
            // Número da versão da original em que as duas eram iguais (a base comum).
            $table->unsignedInteger('origem_versao')->nullable()->after('origem_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('perguntas', function (Blueprint $table) {
            $table->dropConstrainedForeignId('origem_id');
            $table->dropColumn('origem_versao');
        });
    }
};
