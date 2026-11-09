<?php

namespace App\Http\Resources;

use App\Models\Servico;
use Dedoc\Scramble\Attributes\SchemaName;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Servico
 */
#[SchemaName('Servico')]
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
            // Contactos opcionais do serviço (null quando não estão preenchidos).
            'contactos' => [
                'email' => $this->email,
                'telefone' => $this->telefone,
                'horario' => $this->horario,
                'pagina_url' => $this->pagina_url,
            ],
            'categorias' => CategoriaResource::collection($this->whenLoaded('categorias')),
        ];
    }
}
