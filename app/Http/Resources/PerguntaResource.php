<?php

namespace App\Http\Resources;

use App\Models\Pergunta;
use Dedoc\Scramble\Attributes\SchemaName;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Pergunta
 */
#[SchemaName('Pergunta')]
class PerguntaResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'slug' => $this->slug,
            'pergunta' => $this->pergunta,
            'resposta' => $this->respostaSegura(),
            'resposta_texto' => $this->resposta_texto,
            'url' => route('faq.canonica', $this->slug),
            // Posição dentro do tema (só quando a pergunta vem de um tema).
            'ordem' => $this->whenPivotLoaded('tema_pergunta', fn (): int => $this->pivot->ordem),
            // Onde a pergunta aparece no site (só no detalhe da pergunta).
            'caminhos' => $this->when($request->routeIs('api.perguntas.show'), fn () => $this->caminhos()->map(fn (array $c) => [
                'servico' => ['slug' => $c['servico']->slug, 'nome' => $c['servico']->nome],
                'categoria' => ['slug' => $c['categoria']->slug, 'nome' => $c['categoria']->nome],
                'tema' => ['slug' => $c['tema']->slug, 'nome' => $c['tema']->nome],
                'url' => route('faq.pergunta', [$c['servico']->slug, $c['categoria']->slug, $c['tema']->slug, $this->slug]),
            ])),
        ];
    }
}
