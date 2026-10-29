<?php

use App\Http\Controllers\FaqController;
use App\Http\Controllers\PesquisaController;
use Illuminate\Support\Facades\Route;

/*
| Site público do FAQ.
|
| As rotas com caminho ficam no fim porque "/{servico}" apanharia qualquer
| endereço de um só nível. Os slugs de serviços não podem coincidir com os
| endereços reservados (ver App\Models\Servico::SLUGS_RESERVADOS).
*/

Route::get('/', [FaqController::class, 'inicio'])->name('faq.inicio');
Route::get('/pesquisa', PesquisaController::class)->name('faq.pesquisa');
Route::get('/p/{pergunta}', [FaqController::class, 'canonica'])->name('faq.canonica');

Route::get('/{servico}', [FaqController::class, 'servico'])->name('faq.servico');
Route::get('/{servico}/{categoria}', [FaqController::class, 'categoria'])->name('faq.categoria');
Route::get('/{servico}/{categoria}/{tema}', [FaqController::class, 'tema'])->name('faq.tema');
Route::get('/{servico}/{categoria}/{tema}/{pergunta}', [FaqController::class, 'pergunta'])->name('faq.pergunta');
