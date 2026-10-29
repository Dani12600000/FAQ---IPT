<?php

namespace App\Http\Controllers;

use App\Models\Pergunta;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Illuminate\View\View;

class PesquisaController extends Controller
{
    /**
     * Número mínimo de caracteres para pesquisar.
     */
    public const MINIMO = 2;

    /**
     * Número máximo de resultados mostrados.
     */
    public const MAXIMO = 20;

    /**
     * Página de pesquisa. Quando o pedido vem da pesquisa enquanto se escreve
     * (cabeçalho X-Fragmento), devolve só o bloco dos resultados.
     */
    public function __invoke(Request $request): View|string
    {
        $termo = Str::limit(trim((string) $request->query('q', '')), 100, '');

        $resultados = mb_strlen($termo) >= self::MINIMO
            ? $this->pesquisar($termo)
            : collect();

        return view('faq.pesquisa', [
            'termo' => $termo,
            'resultados' => $resultados,
            'minimo' => self::MINIMO,
        ])->fragmentIf($request->hasHeader('X-Fragmento'), 'resultados');
    }

    /**
     * Perguntas publicadas com todas as palavras do termo. As que têm as palavras
     * na própria pergunta aparecem primeiro.
     */
    /**
     * @return Collection<int, Pergunta>
     */
    private function pesquisar(string $termo): Collection
    {
        $palavras = preg_split('/\s+/u', mb_strtolower($termo), -1, PREG_SPLIT_NO_EMPTY);

        return Pergunta::publicadas()
            ->pesquisa($termo)
            ->get()
            ->sortByDesc(fn (Pergunta $pergunta) => collect($palavras)
                ->filter(fn (string $palavra) => Str::contains(Str::ascii(mb_strtolower($pergunta->pergunta)), Str::ascii($palavra)))
                ->count())
            ->take(self::MAXIMO)
            ->values();
    }
}
