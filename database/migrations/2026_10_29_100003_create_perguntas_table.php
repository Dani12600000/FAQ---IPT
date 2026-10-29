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
        Schema::create('perguntas', function (Blueprint $table) {
            $table->id();
            $table->string('pergunta');
            $table->string('slug')->unique();
            // A resposta é HTML vindo do editor de texto rico do backoffice.
            $table->longText('resposta');
            // Uma pergunta nova nasce como rascunho e só aparece no site depois de publicada.
            $table->boolean('publicada')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('perguntas');
    }
};
