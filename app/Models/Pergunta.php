<?php

namespace App\Models;

use App\Models\Concerns\TemServicoResponsavel;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

#[Fillable(['servico_id', 'pergunta', 'slug', 'resposta', 'publicada'])]
class Pergunta extends Model
{
    use TemServicoResponsavel;

    protected static function booted(): void
    {
        // Guarda uma versão ao criar e sempre que o texto da pergunta ou da resposta muda.
        // Mudar só o slug, o serviço ou o estado de publicação não cria versão.
        static::created(function (self $pergunta): void {
            $pergunta->registarVersao();
        });

        static::updated(function (self $pergunta): void {
            if ($pergunta->wasChanged(['pergunta', 'resposta'])) {
                $pergunta->registarVersao();
            }
        });
    }

    /**
     * Histórico do texto da pergunta e da resposta, da versão mais recente para a mais antiga.
     *
     * @return HasMany<PerguntaVersao, $this>
     */
    public function versoes(): HasMany
    {
        return $this->hasMany(PerguntaVersao::class)->orderByDesc('numero');
    }

    /**
     * Guarda o texto atual como uma nova versão, com quem tem a sessão aberta como autor.
     */
    public function registarVersao(?string $nota = null): PerguntaVersao
    {
        return $this->versoes()->create([
            'numero' => $this->versoes()->max('numero') + 1,
            'pergunta' => $this->pergunta,
            'resposta' => $this->resposta,
            'nota' => $nota,
            'user_id' => auth()->id(),
        ]);
    }

    /**
     * Volta ao texto de uma versão antiga. Não apaga nada: a reposição fica como uma versão nova.
     */
    public function reporVersao(PerguntaVersao $versao): void
    {
        $this->fill(['pergunta' => $versao->pergunta, 'resposta' => $versao->resposta]);
        // Grava sem eventos, para registar a versão uma só vez e com a nota certa.
        $this->saveQuietly();
        $this->registarVersao("Reposição da versão {$versao->numero}");
    }

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
     * Perguntas que a pessoa vê no backoffice: as do seu serviço e as de outros serviços
     * que usa nos seus temas.
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
            ->orWhereHas('temas', fn (Builder $t) => $t->whereIn('temas.servico_id', $servicos)));
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
