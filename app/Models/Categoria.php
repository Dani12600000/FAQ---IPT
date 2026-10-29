<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

#[Fillable(['nome', 'slug', 'descricao', 'ativo'])]
class Categoria extends Model
{
    /**
     * Serviços onde esta categoria aparece.
     *
     * @return BelongsToMany<Servico, $this>
     */
    public function servicos(): BelongsToMany
    {
        return $this->belongsToMany(Servico::class, 'servico_categoria')
            ->withPivot('ordem');
    }

    /**
     * Temas da categoria, pela ordem definida nesta categoria.
     *
     * @return BelongsToMany<Tema, $this>
     */
    public function temas(): BelongsToMany
    {
        return $this->belongsToMany(Tema::class, 'categoria_tema')
            ->withPivot('ordem')
            ->orderByPivot('ordem');
    }

    /**
     * Só as categorias visíveis no site e na API.
     */
    #[Scope]
    protected function ativas(Builder $query): void
    {
        $query->where('ativo', true);
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'ativo' => 'boolean',
        ];
    }
}
