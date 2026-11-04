<?php

namespace App\Models;

use App\Models\Concerns\TemServicoResponsavel;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

#[Fillable(['servico_id', 'nome', 'slug', 'descricao', 'ativo'])]
class Tema extends Model
{
    use TemServicoResponsavel;

    /**
     * Categorias onde este tema aparece.
     *
     * @return BelongsToMany<Categoria, $this>
     */
    public function categorias(): BelongsToMany
    {
        return $this->belongsToMany(Categoria::class, 'categoria_tema')
            ->using(CategoriaTema::class)
            ->withPivot('ordem');
    }

    /**
     * Perguntas do tema, pela ordem definida neste tema.
     *
     * @return BelongsToMany<Pergunta, $this>
     */
    public function perguntas(): BelongsToMany
    {
        return $this->belongsToMany(Pergunta::class, 'tema_pergunta')
            ->using(TemaPergunta::class)
            ->withPivot('ordem')
            ->orderByPivot('ordem');
    }

    /**
     * Só os temas visíveis no site e na API.
     */
    #[Scope]
    protected function ativos(Builder $query): void
    {
        $query->where($query->qualifyColumn('ativo'), true);
    }

    /**
     * Temas que a pessoa vê no backoffice: os do seu serviço e os que usa nas suas categorias.
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
            ->orWhereHas('categorias', fn (Builder $c) => $c->whereIn('categorias.servico_id', $servicos)));
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
