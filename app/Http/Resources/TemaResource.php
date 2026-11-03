<?php

namespace App\Http\Resources;

use App\Models\Tema;
use Dedoc\Scramble\Attributes\SchemaName;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Tema
 */
#[SchemaName('Tema')]
class TemaResource extends JsonResource
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
            'nome' => $this->nome,
            'descricao' => $this->descricao,
            // Posição dentro da categoria (só quando o tema vem de uma categoria).
            'ordem' => $this->whenPivotLoaded('categoria_tema', fn (): int => $this->pivot->ordem),
            'perguntas' => PerguntaResource::collection($this->whenLoaded('perguntas')),
        ];
    }
}
