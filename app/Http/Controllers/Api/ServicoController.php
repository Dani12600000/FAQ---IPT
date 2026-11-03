<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ServicoResource;
use App\Models\Servico;
use Dedoc\Scramble\Attributes\Group;
use Dedoc\Scramble\Attributes\PathParameter;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

// Os atributos #[Group], #[PathParameter], ... e os comentários dos métodos alimentam a
// documentação da API em /docs/api (gerada pelo Scramble).
#[Group('Serviços', weight: 1)]
class ServicoController extends Controller
{
    /**
     * Listar serviços
     *
     * Serviços ativos, pela ordem da página inicial. Não inclui as categorias:
     * para a árvore completa de um serviço, usar `GET /servicos/{servico}`.
     */
    public function index(): AnonymousResourceCollection
    {
        return ServicoResource::collection(Servico::ativos()->orderBy('ordem')->get());
    }

    /**
     * Árvore de um serviço
     *
     * Árvore completa: categorias → temas → perguntas, só com o que está
     * ativo/publicado e pela ordem definida em cada ligação (campo `ordem`).
     *
     * @throws ModelNotFoundException se o serviço não existir ou estiver inativo.
     */
    #[PathParameter('servico', description: 'Slug do serviço.', example: 'biblioteca')]
    public function show(string $servico): ServicoResource
    {
        $servico = Servico::ativos()
            ->where('slug', $servico)
            ->with(['categorias' => fn (BelongsToMany $categorias) => $categorias
                ->ativas()
                ->with(['temas' => fn (BelongsToMany $temas) => $temas
                    ->ativos()
                    ->with(['perguntas' => fn (BelongsToMany $perguntas) => $perguntas->publicadas()]),
                ]),
            ])
            ->firstOrFail();

        return new ServicoResource($servico);
    }
}
