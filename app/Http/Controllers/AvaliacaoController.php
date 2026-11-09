<?php

namespace App\Http\Controllers;

use App\Models\Pergunta;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * "Esta resposta foi útil?" — guarda só a pergunta, sim/não e a data.
 * Não há contas nem cookies de rastreio; os abusos são travados por IP (throttle na rota).
 */
class AvaliacaoController extends Controller
{
    public function __invoke(Request $request, string $pergunta): JsonResponse|RedirectResponse
    {
        $pergunta = Pergunta::publicadas()->where('slug', $pergunta)->firstOrFail();
        $dados = $request->validate(['util' => ['required', 'boolean']]);

        $pergunta->avaliacoes()->create(['util' => $dados['util']]);

        if ($request->expectsJson()) {
            return response()->json(['mensagem' => self::agradecimento($dados['util'])], 201);
        }

        return back()->with('avaliacao', [
            'pergunta' => $pergunta->id,
            'util' => (bool) $dados['util'],
            'mensagem' => self::agradecimento($dados['util']),
        ]);
    }

    public static function agradecimento(bool $util): string
    {
        return $util
            ? 'Obrigado pela sua opinião.'
            : 'Obrigado. A sua opinião ajuda-nos a melhorar esta resposta.';
    }
}
