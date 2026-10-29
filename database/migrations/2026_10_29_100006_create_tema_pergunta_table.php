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
        Schema::create('tema_pergunta', function (Blueprint $table) {
            $table->foreignId('tema_id')->constrained('temas')->cascadeOnDelete();
            $table->foreignId('pergunta_id')->constrained('perguntas')->cascadeOnDelete();
            // Posição da pergunta dentro deste tema.
            $table->unsignedInteger('ordem')->default(0);

            $table->primary(['tema_id', 'pergunta_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tema_pergunta');
    }
};
