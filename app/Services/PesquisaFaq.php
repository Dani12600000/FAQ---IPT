<?php

namespace App\Services;

use App\Models\Pergunta;
use App\Models\Pesquisa;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

/**
 * Pesquisa de perguntas, partilhada pelo site e pela API.
 */
class PesquisaFaq
{
    /**
     * Número mínimo de caracteres para pesquisar.
     */
    public const MINIMO = 2;

    /**
     * Número máximo de resultados devolvidos.
     */
    public const MAXIMO = 20;

    /**
     * Limpa o termo escrito: sem espaços a mais e com no máximo 100 caracteres.
     */
    public function normalizar(?string $termo): string
    {
        $termo = trim(preg_replace('/\s+/u', ' ', (string) $termo));

        return Str::limit($termo, 100, '');
    }

    public function podePesquisar(string $termo): bool
    {
        return mb_strlen($termo) >= self::MINIMO;
    }

    /**
     * Perguntas publicadas com todas as palavras do termo. As que têm as palavras
     * na própria pergunta aparecem primeiro.
     *
     * @return Collection<int, Pergunta>
     */
    public function resultados(string $termo): Collection
    {
        if (! $this->podePesquisar($termo)) {
            return collect();
        }

        $palavras = preg_split('/\s+/u', Str::ascii(mb_strtolower($termo)), -1, PREG_SPLIT_NO_EMPTY);

        return Pergunta::publicadas()
            ->pesquisa($termo)
            ->with('ultimaVersao')
            ->get()
            ->sortByDesc(fn (Pergunta $pergunta) => collect($palavras)
                ->filter(fn (string $palavra) => Str::contains(Str::ascii(mb_strtolower($pergunta->pergunta)), $palavra))
                ->count())
            ->take(self::MAXIMO)
            ->values();
    }

    /**
     * Grava a pesquisa e quantos resultados teve (para o painel do backoffice).
     */
    public function registar(string $termo, int $resultados): void
    {
        if (! $this->podePesquisar($termo)) {
            return;
        }

        Pesquisa::create([
            'termo' => mb_strtolower($termo),
            'resultados' => $resultados,
        ]);
    }
}
