<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['pergunta', 'slug', 'resposta', 'publicada'])]
class Pergunta extends Model
{
    /**
     * Temas onde esta pergunta aparece.
     *
     * @return BelongsToMany<Tema, $this>
     */
    public function temas(): BelongsToMany
    {
        return $this->belongsToMany(Tema::class, 'tema_pergunta')
            ->using(TemaPergunta::class)
            ->withPivot('ordem');
    }

    /**
     * Respostas ao "Esta resposta foi útil?".
     *
     * @return HasMany<Avaliacao, $this>
     */
    public function avaliacoes(): HasMany
    {
        return $this->hasMany(Avaliacao::class);
    }

    /**
     * Só as perguntas visíveis no site e na API.
     */
    #[Scope]
    protected function publicadas(Builder $query): void
    {
        $query->where('publicada', true);
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'publicada' => 'boolean',
        ];
    }
}
