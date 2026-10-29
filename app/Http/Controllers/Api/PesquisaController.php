<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\PerguntaResource;
use App\Services\PesquisaFaq;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class PesquisaController extends Controller
{
    /**
     * GET /api/pesquisa?q= — perguntas publicadas que contêm todas as palavras.
     *
     * Só fica registada nas estatísticas com ?registar=1, para quem usar a API numa
     * pesquisa enquanto se escreve não gravar pedaços de palavras.
     */
    public function __invoke(Request $request, PesquisaFaq $pesquisa): AnonymousResourceCollection
    {
        $termo = $pesquisa->normalizar($request->query('q'));
        $resultados = $pesquisa->resultados($termo);

        if ($request->boolean('registar')) {
            $pesquisa->registar($termo, $resultados->count());
        }

        return PerguntaResource::collection($resultados)->additional([
            'termo' => $termo,
            'total' => $resultados->count(),
            'minimo' => PesquisaFaq::MINIMO,
        ]);
    }
}
