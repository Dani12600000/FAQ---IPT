<?php

namespace App\Services;

use App\Models\Pergunta;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

/**
 * Encontra perguntas já existentes (de qualquer serviço) parecidas com uma pergunta nova,
 * para não se escrever duas vezes a mesma resposta.
 *
 * A comparação é feita pelas palavras da pergunta: tiram-se acentos, maiúsculas e palavras
 * sem significado ("como", "posso", "uma"...) e conta-se quantas palavras as duas têm em comum.
 */
class PerguntasSemelhantes
{
    /**
     * Semelhança mínima (de 0 a 1) para uma pergunta ser sugerida.
     */
    public const LIMIAR = 0.4;

    /**
     * Número máximo de sugestões.
     */
    public const MAXIMO = 5;

    /**
     * Palavras demasiado comuns para dizerem alguma coisa sobre o assunto da pergunta
     * (já sem acentos, porque o texto é comparado sem eles).
     */
    private const PALAVRAS_VAZIAS = [
        'que', 'qual', 'quais', 'quem', 'como', 'onde', 'quando', 'quanto', 'quanta', 'quantos', 'quantas',
        'porque', 'para', 'pelo', 'pela', 'pelos', 'pelas', 'com', 'sem', 'sobre', 'entre', 'ate', 'apos',
        'uma', 'umas', 'uns', 'dos', 'das', 'nos', 'nas', 'num', 'numa', 'aos', 'mais', 'muito', 'tambem',
        'meu', 'minha', 'meus', 'minhas', 'seu', 'sua', 'seus', 'suas', 'este', 'esta', 'estes', 'estas',
        'esse', 'essa', 'isso', 'isto', 'ser', 'sao', 'estou', 'estao', 'existe', 'existem', 'tem', 'tenho',
        'ter', 'posso', 'pode', 'podem', 'devo', 'deve', 'preciso', 'faco', 'fazer', 'faz', 'depois', 'antes',
    ];

    /**
     * Perguntas parecidas com o texto, da mais parecida para a menos parecida.
     *
     * @return Collection<int, array{pergunta: Pergunta, semelhanca: float}>
     */
    public function para(?string $texto): Collection
    {
        $palavras = $this->palavras((string) $texto);

        if ($palavras === []) {
            return collect();
        }

        // Compara com todas as perguntas. Com algumas centenas é instantâneo; se o FAQ
        // crescer muito, pode passar a usar um índice FULLTEXT do MySQL para pré-filtrar.
        return Pergunta::query()
            ->with('servico')
            ->get()
            ->map(fn (Pergunta $pergunta): array => [
                'pergunta' => $pergunta,
                'semelhanca' => $this->semelhanca($palavras, $this->palavras($pergunta->pergunta)),
            ])
            ->filter(fn (array $sugestao): bool => $sugestao['semelhanca'] >= self::LIMIAR)
            ->sortByDesc('semelhanca')
            ->take(self::MAXIMO)
            ->values();
    }

    /**
     * Palavras que contam para a comparação: sem acentos, maiúsculas nem palavras vazias,
     * e cortadas nas 5 primeiras letras, para "renovo" e "renovar" ou "bolsa" e "bolsas"
     * contarem como a mesma palavra.
     *
     * @return list<string>
     */
    public function palavras(string $texto): array
    {
        $palavras = preg_split('/[^a-z0-9]+/', Str::ascii(mb_strtolower($texto)), -1, PREG_SPLIT_NO_EMPTY);

        return collect($palavras)
            ->reject(fn (string $palavra): bool => strlen($palavra) < 3 || in_array($palavra, self::PALAVRAS_VAZIAS, true))
            ->map(fn (string $palavra): string => substr($palavra, 0, 5))
            ->unique()
            ->values()
            ->all();
    }

    /**
     * Coeficiente de Dice: 1 quando as duas listas têm as mesmas palavras, 0 quando não têm nenhuma em comum.
     *
     * @param  list<string>  $a
     * @param  list<string>  $b
     */
    public function semelhanca(array $a, array $b): float
    {
        if ($a === [] || $b === []) {
            return 0.0;
        }

        return 2 * count(array_intersect($a, $b)) / (count($a) + count($b));
    }
}
