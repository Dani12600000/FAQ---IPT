<?php

namespace App\Models;

use App\Models\Concerns\TemServicoResponsavel;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

#[Fillable(['servico_id', 'pergunta', 'slug', 'resposta', 'publicada', 'rever_ate'])]
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
     * Versão mais recente. A sua data é a da última alteração do texto, ao contrário de
     * updated_at, que muda com qualquer gravação (o slug, a publicação...).
     *
     * @return HasOne<PerguntaVersao, $this>
     */
    public function ultimaVersao(): HasOne
    {
        return $this->hasOne(PerguntaVersao::class)->latestOfMany('numero');
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
     * Número da versão mais recente (a do texto atual).
     */
    public function versaoAtual(): int
    {
        return (int) $this->versoes()->max('numero');
    }

    /**
     * Pergunta de onde esta nasceu, quando é a versão própria de um serviço.
     *
     * @return BelongsTo<Pergunta, $this>
     */
    public function origem(): BelongsTo
    {
        return $this->belongsTo(self::class, 'origem_id');
    }

    /**
     * Versões próprias que outros serviços criaram a partir desta pergunta.
     *
     * @return HasMany<Pergunta, $this>
     */
    public function derivadas(): HasMany
    {
        return $this->hasMany(self::class, 'origem_id');
    }

    /**
     * Cria a versão própria de um serviço: uma cópia desta pergunta, desse serviço, que a
     * substitui nos temas do serviço (na mesma posição). A original não muda para os outros.
     * Fica registada a versão da original em que as duas eram iguais (a base comum).
     */
    public function derivarPara(Servico $servico): self
    {
        return DB::transaction(function () use ($servico): self {
            $copia = new self([
                'servico_id' => $servico->id,
                'pergunta' => $this->pergunta,
                'slug' => static::slugLivre("{$this->slug}-{$servico->slug}"),
                'resposta' => $this->resposta,
                'publicada' => $this->publicada,
            ]);
            $copia->origem_id = $this->id;
            $copia->origem_versao = $this->versaoAtual();
            // Sem eventos, para a versão 1 da cópia dizer de onde veio.
            $copia->saveQuietly();
            $copia->registarVersao("Criada a partir de «{$this->pergunta}» (versão {$copia->origem_versao})");

            foreach ($this->temas()->where('temas.servico_id', $servico->id)->get() as $tema) {
                $tema->perguntas()->detach($this);
                $tema->perguntas()->attach($copia, ['ordem' => $tema->pivot->ordem]);
            }

            return $copia;
        });
    }

    /**
     * Se a original mudou desde que esta se separou dela (ou desde a última vez que se viram as alterações).
     */
    public function origemMudou(): bool
    {
        return $this->origem !== null && $this->origem->versaoAtual() > $this->origem_versao;
    }

    /**
     * Copia o texto atual da original para esta pergunta. Fica como uma versão nova.
     */
    public function trazerAlteracoesDaOrigem(): void
    {
        $this->fill(['pergunta' => $this->origem->pergunta, 'resposta' => $this->origem->resposta]);
        $this->origem_versao = $this->origem->versaoAtual();
        $this->saveQuietly();
        $this->registarVersao("Texto trazido da original (versão {$this->origem_versao})");
    }

    /**
     * Mantém o texto desta pergunta e dá por vistas as alterações da original.
     */
    public function ignorarAlteracoesDaOrigem(): void
    {
        $this->origem_versao = $this->origem->versaoAtual();
        $this->saveQuietly();
    }

    /**
     * Deixa de usar esta versão própria: onde ela aparece passa a aparecer a original, e esta
     * fica por publicar e sem temas, com o seu histórico (pode ser apagada depois).
     */
    public function voltarAOrigem(): void
    {
        DB::transaction(function (): void {
            foreach ($this->temas as $tema) {
                if (! $this->origem->temas()->whereKey($tema->id)->exists()) {
                    $tema->perguntas()->attach($this->origem, ['ordem' => $tema->pivot->ordem]);
                }

                $tema->perguntas()->detach($this);
            }

            $this->publicada = false;
            $this->saveQuietly();
            $this->registarVersao("Substituída pela original «{$this->origem->pergunta}»");
        });
    }

    /**
     * O slug pedido ou, se já existir, o mesmo com -2, -3...
     */
    private static function slugLivre(string $slug): string
    {
        $livre = $slug;

        for ($i = 2; static::where('slug', $livre)->exists(); $i++) {
            $livre = "{$slug}-{$i}";
        }

        return $livre;
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
     * Perguntas cuja data de revisão já passou ou passa nos próximos dias.
     */
    #[Scope]
    protected function aRever(Builder $query, int $dias = 30): void
    {
        $query->whereDate($query->qualifyColumn('rever_ate'), '<=', today()->addDays($dias));
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
            'origem_versao' => 'integer',
            'rever_ate' => 'date',
        ];
    }
}
