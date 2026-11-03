<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\PerguntaResource;
use App\Services\PesquisaFaq;
use Dedoc\Scramble\Attributes\Group;
use Dedoc\Scramble\Attributes\QueryParameter;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

#[Group('Pesquisa', weight: 3)]
class PesquisaController extends Controller
{
    /**
     * Pesquisar perguntas
     *
     * Perguntas publicadas que contêm todas as palavras do termo, na pergunta ou na
     * resposta (no máximo 20). Com menos de 2 caracteres devolve uma lista vazia.
     *
     * Só fica registada nas estatísticas com `registar=1`, para quem usar a API numa
     * pesquisa enquanto se escreve não gravar pedaços de palavras.
     */
    #[QueryParameter('q', description: 'Termo a pesquisar (mínimo 2 caracteres).', type: 'string', example: 'renovar empréstimo')]
    #[QueryParameter('registar', description: 'Com `1`, grava a pesquisa nas estatísticas do backoffice.', type: 'boolean', default: false)]
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
