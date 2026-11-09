<?php

namespace App\Http\Controllers;

use App\Models\Pergunta;
use App\Models\Servico;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Http\Response;
use Illuminate\Support\Collection;

/**
 * Ficheiros para os motores de busca. São gerados aqui, e não guardados em public/,
 * para estarem sempre atualizados e terem o endereço certo em cada ambiente.
 */
class SitemapController extends Controller
{
    public function sitemap(): Response
    {
        return response()
            ->view('sitemap', ['enderecos' => $this->enderecos()])
            ->header('Content-Type', 'application/xml');
    }

    public function robots(): Response
    {
        // Sem /admin nem /pesquisa sozinhos: as regras comparam o início do endereço e
        // apanhariam serviços como /administracao.
        $linhas = [
            'User-agent: *',
            'Disallow: /admin/',
            'Disallow: /pesquisa?',
            '',
            'Sitemap: '.route('sitemap'),
            '',
        ];

        return response(implode("\n", $linhas))->header('Content-Type', 'text/plain; charset=UTF-8');
    }

    /**
     * Tudo o que está visível no site: as páginas de cada caminho e as perguntas,
     * estas só pelo URL canónico (/p/{pergunta}).
     *
     * @return Collection<int, string>
     */
    private function enderecos(): Collection
    {
        $servicos = Servico::ativos()
            ->orderBy('ordem')
            ->with(['categorias' => fn (BelongsToMany $categorias) => $categorias
                ->ativas()
                ->with(['temas' => fn (BelongsToMany $temas) => $temas->ativos()]),
            ])
            ->get();

        $enderecos = collect([route('faq.inicio')]);

        foreach ($servicos as $servico) {
            $enderecos->push(route('faq.servico', $servico->slug));

            foreach ($servico->categorias as $categoria) {
                $enderecos->push(route('faq.categoria', [$servico->slug, $categoria->slug]));

                foreach ($categoria->temas as $tema) {
                    $enderecos->push(route('faq.tema', [$servico->slug, $categoria->slug, $tema->slug]));
                }
            }
        }

        return $enderecos->merge(
            Pergunta::publicadas()->orderBy('id')->pluck('slug')->map(fn (string $slug) => route('faq.canonica', $slug)),
        );
    }
}
