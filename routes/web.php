<?php

use App\Http\Controllers\AvaliacaoController;
use App\Http\Controllers\FaqController;
use App\Http\Controllers\PesquisaController;
use App\Http\Controllers\SitemapController;
use Dedoc\Scramble\Scramble;
use Illuminate\Support\Facades\Route;

/*
| Site público do FAQ.
|
| As rotas com caminho ficam no fim porque "/{servico}" apanharia qualquer
| endereço de um só nível. Os slugs de serviços não podem coincidir com os
| endereços reservados (ver App\Models\Servico::SLUGS_RESERVADOS).
*/

// Documentação da API (Scramble): página interativa e especificação OpenAPI em JSON.
Scramble::registerUiRoute('docs/api')->name('scramble.docs.ui');
Scramble::registerJsonSpecificationRoute('docs/api.json')->name('scramble.docs.document');

// Para os motores de busca.
Route::get('/sitemap.xml', [SitemapController::class, 'sitemap'])->name('sitemap');
Route::get('/robots.txt', [SitemapController::class, 'robots'])->name('robots');

Route::get('/', [FaqController::class, 'inicio'])->name('faq.inicio');
Route::get('/pesquisa', PesquisaController::class)->name('faq.pesquisa');
Route::get('/p/{pergunta}', [FaqController::class, 'canonica'])->name('faq.canonica');
Route::post('/p/{pergunta}/avaliacao', AvaliacaoController::class)
    ->middleware('throttle:avaliacoes') // limite por IP, ver config/faq.php
    ->name('faq.avaliar');

Route::get('/{servico}', [FaqController::class, 'servico'])->name('faq.servico');
Route::get('/{servico}/{categoria}', [FaqController::class, 'categoria'])->name('faq.categoria');
Route::get('/{servico}/{categoria}/{tema}', [FaqController::class, 'tema'])->name('faq.tema');
Route::get('/{servico}/{categoria}/{tema}/{pergunta}', [FaqController::class, 'pergunta'])->name('faq.pergunta');
