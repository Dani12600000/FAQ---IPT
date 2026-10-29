<?php

namespace App\Http\Controllers;

use App\Services\PesquisaFaq;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PesquisaController extends Controller
{
    /**
     * Página de pesquisa. Quando o pedido vem da pesquisa enquanto se escreve
     * (cabeçalho X-Fragmento), devolve só o bloco dos resultados.
     */
    public function __invoke(Request $request, PesquisaFaq $pesquisa): View|string
    {
        $termo = $pesquisa->normalizar($request->query('q'));
        $resultados = $pesquisa->resultados($termo);

        // Enquanto se escreve só se grava quando a pessoa pára (o JavaScript envia X-Registar),
        // para não guardar pedaços de palavras como "pro" ou "propi".
        $enquantoEscreve = $request->hasHeader('X-Fragmento');
        if (! $enquantoEscreve || $request->hasHeader('X-Registar')) {
            $pesquisa->registar($termo, $resultados->count());
        }

        return view('faq.pesquisa', [
            'termo' => $termo,
            'resultados' => $resultados,
            'minimo' => PesquisaFaq::MINIMO,
        ])->fragmentIf($enquantoEscreve, 'resultados');
    }
}
