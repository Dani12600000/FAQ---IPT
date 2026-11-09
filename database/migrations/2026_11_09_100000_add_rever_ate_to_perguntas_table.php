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
        // Data opcional até à qual a resposta deve ser revista (prazos e regras mudam).
        Schema::table('perguntas', function (Blueprint $table) {
            $table->date('rever_ate')->nullable()->after('publicada');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('perguntas', function (Blueprint $table) {
            $table->dropColumn('rever_ate');
        });
    }
};
