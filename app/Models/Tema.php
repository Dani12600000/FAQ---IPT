<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

#[Fillable(['nome', 'slug', 'descricao', 'ativo'])]
class Tema extends Model
{
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
