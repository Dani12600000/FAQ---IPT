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
        // Contactos opcionais de cada serviço, para quem não encontra a resposta no FAQ.
        Schema::table('servicos', function (Blueprint $table) {
            $table->string('email')->nullable()->after('descricao');
            $table->string('telefone', 50)->nullable()->after('email');
            $table->string('horario')->nullable()->after('telefone');
            $table->string('pagina_url')->nullable()->after('horario');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('servicos', function (Blueprint $table) {
            $table->dropColumn(['email', 'telefone', 'horario', 'pagina_url']);
        });
    }
};
