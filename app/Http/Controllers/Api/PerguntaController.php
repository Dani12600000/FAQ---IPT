<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\PerguntaResource;
use App\Models\Pergunta;

class PerguntaController extends Controller
{
    /**
     * GET /api/perguntas/{pergunta} — a pergunta, a resposta e os caminhos onde aparece.
     */
    public function show(string $pergunta): PerguntaResource
    {
        return new PerguntaResource(Pergunta::publicadas()->where('slug', $pergunta)->firstOrFail());
    }
}
