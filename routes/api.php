<?php

use App\Http\Controllers\Api\PerguntaController;
use App\Http\Controllers\Api\PesquisaController;
use App\Http\Controllers\Api\ServicoController;
use Illuminate\Support\Facades\Route;

/*
| API JSON pública e só de leitura (prefixo /api). Só devolve conteúdo ativo/publicado.
| Os sites que a podem chamar a partir do browser configuram-se em CORS_ALLOWED_ORIGINS.
*/

Route::middleware('throttle:60,1')->name('api.')->group(function () {
    Route::get('/servicos', [ServicoController::class, 'index'])->name('servicos.index');
    Route::get('/servicos/{servico}', [ServicoController::class, 'show'])->name('servicos.show');
    Route::get('/perguntas/{pergunta}', [PerguntaController::class, 'show'])->name('perguntas.show');
    Route::get('/pesquisa', PesquisaController::class)->name('pesquisa');
});
