<?php

namespace App\Http\Resources;

use App\Models\Categoria;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Categoria
 */
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
            'ordem' => $this->whenPivotLoaded('servico_categoria', fn () => $this->pivot->ordem),
            'temas' => TemaResource::collection($this->whenLoaded('temas')),
        ];
    }
}
