<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\PerguntaResource;
use App\Models\Pergunta;
use Dedoc\Scramble\Attributes\Group;
use Dedoc\Scramble\Attributes\PathParameter;
use Illuminate\Database\Eloquent\ModelNotFoundException;

#[Group('Perguntas', weight: 2)]
class PerguntaController extends Controller
{
    /**
     * Ver uma pergunta
     *
     * A pergunta, a resposta (em HTML e em texto simples) e todos os caminhos
     * (serviço → categoria → tema) onde aparece no site.
     *
     * @throws ModelNotFoundException se a pergunta não existir ou não estiver publicada.
     */
    #[PathParameter('pergunta', description: 'Slug da pergunta.', example: 'como-renovo-um-emprestimo')]
    public function show(string $pergunta): PerguntaResource
    {
        return new PerguntaResource(Pergunta::publicadas()->where('slug', $pergunta)->firstOrFail());
    }
}
