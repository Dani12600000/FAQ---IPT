<?php

namespace App\Http\Controllers;

use App\Models\Categoria;
use App\Models\Pergunta;
use App\Models\Servico;
use App\Models\Tema;
use Illuminate\View\View;

/**
 * Navegação pública do FAQ.
 *
 * O URL guarda o caminho por onde a pessoa chegou (serviço → categoria → tema → pergunta).
 * Cada passo só é encontrado dentro do anterior e só se estiver ativo/publicado; caso
 * contrário a página dá 404.
 */
class FaqController extends Controller
{
    public function inicio(): View
    {
        return view('faq.inicio', [
            'servicos' => Servico::ativos()->orderBy('ordem')->get(),
        ]);
    }

    public function servico(string $servico): View
    {
        $servico = $this->servicoAtivo($servico);

        return view('faq.servico', [
            'servico' => $servico,
            'categorias' => $servico->categorias()->ativas()->get(),
        ]);
    }

    public function categoria(string $servico, string $categoria): View
    {
        $servico = $this->servicoAtivo($servico);
        $categoria = $this->categoriaDe($servico, $categoria);

        return view('faq.categoria', [
            'servico' => $servico,
            'categoria' => $categoria,
            'temas' => $categoria->temas()->ativos()->get(),
        ]);
    }

    public function tema(string $servico, string $categoria, string $tema): View
    {
        $servico = $this->servicoAtivo($servico);
        $categoria = $this->categoriaDe($servico, $categoria);
        $tema = $this->temaDe($categoria, $tema);

        return view('faq.tema', [
            'servico' => $servico,
            'categoria' => $categoria,
            'tema' => $tema,
            'perguntas' => $tema->perguntas()->publicadas()->get(),
        ]);
    }

    public function pergunta(string $servico, string $categoria, string $tema, string $pergunta): View
    {
        $servico = $this->servicoAtivo($servico);
        $categoria = $this->categoriaDe($servico, $categoria);
        $tema = $this->temaDe($categoria, $tema);
        $pergunta = $this->perguntaDe($tema, $pergunta);

        // "Também aparece em": todos os caminhos menos o atual.
        $outrosCaminhos = $pergunta->caminhos()->reject(fn (array $caminho) => $caminho['servico']->is($servico)
            && $caminho['categoria']->is($categoria)
            && $caminho['tema']->is($tema));

        return view('faq.pergunta', [
            'pergunta' => $pergunta,
            'caminho' => compact('servico', 'categoria', 'tema'),
            'outrosCaminhos' => $outrosCaminhos,
        ]);
    }

    /**
     * URL canónico da pergunta (/p/{pergunta}), sem caminho.
     */
    public function canonica(string $pergunta): View
    {
        $pergunta = Pergunta::publicadas()->where('slug', $pergunta)->firstOrFail();

        return view('faq.pergunta', [
            'pergunta' => $pergunta,
            'caminho' => null,
            'outrosCaminhos' => $pergunta->caminhos(),
        ]);
    }

    private function servicoAtivo(string $slug): Servico
    {
        return Servico::ativos()->where('slug', $slug)->firstOrFail();
    }

    private function categoriaDe(Servico $servico, string $slug): Categoria
    {
        return $servico->categorias()->ativas()->where('slug', $slug)->firstOrFail();
    }

    private function temaDe(Categoria $categoria, string $slug): Tema
    {
        return $categoria->temas()->ativos()->where('slug', $slug)->firstOrFail();
    }

    private function perguntaDe(Tema $tema, string $slug): Pergunta
    {
        return $tema->perguntas()->publicadas()->where('slug', $slug)->firstOrFail();
    }
}
