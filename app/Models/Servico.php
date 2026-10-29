<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

#[Fillable(['nome', 'slug', 'descricao', 'ordem', 'ativo'])]
class Servico extends Model
{
    protected static function booted(): void
    {
        // Um serviço novo sem ordem indicada vai para o fim da lista da página inicial.
        static::creating(function (self $servico): void {
            $servico->ordem ??= static::max('ordem') + 1;
        });
    }

    /**
     * Categorias do serviço, pela ordem definida neste serviço.
     *
     * @return BelongsToMany<Categoria, $this>
     */
    public function categorias(): BelongsToMany
    {
        return $this->belongsToMany(Categoria::class, 'servico_categoria')
            ->using(ServicoCategoria::class)
            ->withPivot('ordem')
            ->orderByPivot('ordem');
    }

    /**
     * Só os serviços visíveis no site e na API.
     */
    #[Scope]
    protected function ativos(Builder $query): void
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
            'ordem' => 'integer',
            'ativo' => 'boolean',
        ];
    }
}
