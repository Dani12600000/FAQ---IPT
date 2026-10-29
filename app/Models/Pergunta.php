<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

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
     * Ao guardar a resposta (HTML), guarda também uma cópia em texto simples para a pesquisa.
     */
    protected function resposta(): Attribute
    {
        return Attribute::make(
            set: fn (string $html): array => [
                'resposta' => $html,
                'resposta_texto' => static::textoSemHtml($html),
            ],
        );
    }

    public static function textoSemHtml(string $html): string
    {
        // O espaço antes de cada "<" evita colar palavras de parágrafos diferentes.
        $texto = html_entity_decode(strip_tags(str_replace('<', ' <', $html)), ENT_QUOTES | ENT_HTML5, 'UTF-8');

        return trim(preg_replace('/\s+/u', ' ', $texto));
    }

    /**
     * Resposta pronta a mostrar no site, sem HTML perigoso (scripts, atributos de eventos...).
     */
    public function respostaSegura(): string
    {
        return Str::sanitizeHtml($this->resposta);
    }

    /**
     * Todos os caminhos visíveis (serviço → categoria → tema) onde esta pergunta aparece.
     *
     * @return Collection<int, array{servico: Servico, categoria: Categoria, tema: Tema}>
     */
    public function caminhos(): Collection
    {
        $temas = $this->temas()
            ->ativos()
            ->with(['categorias' => fn (BelongsToMany $categorias) => $categorias
                ->ativas()
                ->with(['servicos' => fn (BelongsToMany $servicos) => $servicos->ativos()]),
            ])
            ->get();

        return $temas
            ->flatMap(fn (Tema $tema) => $tema->categorias->flatMap(
                fn (Categoria $categoria) => $categoria->servicos->map(
                    fn (Servico $servico) => ['servico' => $servico, 'categoria' => $categoria, 'tema' => $tema],
                ),
            ))
            ->sortBy(fn (array $caminho) => [$caminho['servico']->ordem, $caminho['categoria']->nome, $caminho['tema']->nome])
            ->values();
    }

    /**
     * Só as perguntas visíveis no site e na API.
     */
    #[Scope]
    protected function publicadas(Builder $query): void
    {
        $query->where($query->qualifyColumn('publicada'), true);
    }

    /**
     * Perguntas que contêm todas as palavras do termo, na pergunta ou na resposta.
     */
    #[Scope]
    protected function pesquisa(Builder $query, string $termo): void
    {
        foreach (preg_split('/\s+/u', trim($termo), -1, PREG_SPLIT_NO_EMPTY) as $palavra) {
            $padrao = '%'.addcslashes($palavra, '%_\\').'%';

            $query->where(fn (Builder $q) => $q
                ->where($q->qualifyColumn('pergunta'), 'like', $padrao)
                ->orWhere($q->qualifyColumn('resposta_texto'), 'like', $padrao));
        }
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
