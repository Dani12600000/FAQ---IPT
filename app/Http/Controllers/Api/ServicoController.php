<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ServicoResource;
use App\Models\Servico;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ServicoController extends Controller
{
    /**
     * GET /api/servicos — serviços ativos, pela ordem da página inicial.
     */
    public function index(): AnonymousResourceCollection
    {
        return ServicoResource::collection(Servico::ativos()->orderBy('ordem')->get());
    }

    /**
     * GET /api/servicos/{servico} — árvore completa: categorias → temas → perguntas,
     * só com o que está ativo/publicado e pela ordem definida em cada ligação.
     */
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
