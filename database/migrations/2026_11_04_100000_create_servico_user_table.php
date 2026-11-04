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
        // Serviços de cada editor (por exemplo, alguém da Biblioteca). Um editor só gere o
        // conteúdo dos seus serviços. Os administradores gerem tudo e não precisam de serviço.
        Schema::create('servico_user', function (Blueprint $table) {
            $table->foreignId('servico_id')->constrained('servicos')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();

            $table->primary(['servico_id', 'user_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('servico_user');
    }
};
