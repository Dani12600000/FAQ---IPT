<?php

namespace App\Http\Resources;

use App\Models\Servico;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Servico
 */
class ServicoResource extends JsonResource
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
            'ordem' => $this->ordem,
            'url' => route('faq.servico', $this->slug),
            'categorias' => CategoriaResource::collection($this->whenLoaded('categorias')),
        ];
    }
}
