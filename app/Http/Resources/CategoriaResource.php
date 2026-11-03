<?php

namespace App\Http\Resources;

use App\Models\Categoria;
use Dedoc\Scramble\Attributes\SchemaName;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Categoria
 */
#[SchemaName('Categoria')]
class CategoriaResource extends JsonResource
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
            // Posição dentro do serviço (só quando a categoria vem de um serviço).
            'ordem' => $this->whenPivotLoaded('servico_categoria', fn (): int => $this->pivot->ordem),
            'temas' => TemaResource::collection($this->whenLoaded('temas')),
        ];
    }
}
