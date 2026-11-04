<?php

namespace App\Models;

use App\Models\Concerns\TemServicoResponsavel;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

#[Fillable(['servico_id', 'nome', 'slug', 'descricao', 'ativo'])]
class Categoria extends Model
{
    use TemServicoResponsavel;

    /**
     * Serviços onde esta categoria aparece.
     *
     * @return BelongsToMany<Servico, $this>
     */
    public function servicos(): BelongsToMany
    {
        return $this->belongsToMany(Servico::class, 'servico_categoria')
            ->using(ServicoCategoria::class)
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
            ->using(CategoriaTema::class)
            ->withPivot('ordem')
            ->orderByPivot('ordem');
    }

    /**
     * Só as categorias visíveis no site e na API.
     */
    #[Scope]
    protected function ativas(Builder $query): void
    {
        $query->where($query->qualifyColumn('ativo'), true);
    }

    /**
     * Categorias que a pessoa vê no backoffice: as do seu serviço e as que usa nos seus serviços.
     */
    #[Scope]
    protected function visiveisPara(Builder $query, User $user): void
    {
        if ($user->isAdmin()) {
            return;
        }

        $servicos = $user->servicos->modelKeys();

        $query->where(fn (Builder $q) => $q
            ->whereIn($q->qualifyColumn('servico_id'), $servicos)
            ->orWhereHas('servicos', fn (Builder $s) => $s->whereIn('servicos.id', $servicos)));
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
