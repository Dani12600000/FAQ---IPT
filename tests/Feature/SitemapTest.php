<?php

namespace Tests\Feature;

use App\Models\Categoria;
use App\Models\Pergunta;
use App\Models\Servico;
use App\Models\Tema;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SitemapTest extends TestCase
{
    use RefreshDatabase;

    private Servico $servico;

    private Categoria $categoria;

    private Tema $tema;

    protected function setUp(): void
    {
        parent::setUp();

        $this->servico = Servico::create(['nome' => 'Ação Social', 'slug' => 'acao-social']);
        $this->categoria = Categoria::create(['nome' => 'Bolsas', 'slug' => 'bolsas']);
        $this->tema = Tema::create(['nome' => 'Candidatura', 'slug' => 'candidatura']);

        $this->servico->categorias()->attach($this->categoria);
        $this->categoria->temas()->attach($this->tema);
        $this->tema->perguntas()->attach(Pergunta::create([
            'pergunta' => 'Como me candidato?',
            'slug' => 'como-me-candidato',
            'resposta' => '<p>Resposta de exemplo.</p>',
            'publicada' => true,
        ]));
    }

    public function test_o_sitemap_tem_o_conteudo_ativo_e_as_perguntas_pelo_url_canonico(): void
    {
        $resposta = $this->get('/sitemap.xml')->assertOk()->assertHeader('Content-Type', 'application/xml');

        $this->assertNotFalse(simplexml_load_string($resposta->getContent()));

        foreach (['/', '/acao-social', '/acao-social/bolsas', '/acao-social/bolsas/candidatura', '/p/como-me-candidato'] as $caminho) {
            $resposta->assertSee('<loc>'.url($caminho).'</loc>', false);
        }

        $resposta->assertDontSee(url('/acao-social/bolsas/candidatura/como-me-candidato'), false);
    }

    public function test_o_sitemap_nao_tem_o_que_esta_inativo_ou_por_publicar(): void
    {
        $inativa = Categoria::create(['nome' => 'Inativa', 'slug' => 'inativa', 'ativo' => false]);
        $this->servico->categorias()->attach($inativa);
        Servico::create(['nome' => 'Inativo', 'slug' => 'inativo', 'ativo' => false]);
        Pergunta::create(['pergunta' => 'Rascunho?', 'slug' => 'rascunho', 'resposta' => '<p>Ok</p>', 'publicada' => false]);

        $this->get('/sitemap.xml')
            ->assertDontSee('inativa')
            ->assertDontSee('inativo')
            ->assertDontSee('rascunho');
    }

    public function test_o_robots_aponta_para_o_sitemap_sem_bloquear_servicos(): void
    {
        $this->get('/robots.txt')
            ->assertOk()
            ->assertHeader('Content-Type', 'text/plain; charset=UTF-8')
            ->assertSee('Disallow: /admin/')
            ->assertSee('Sitemap: '.url('/sitemap.xml'));
    }

    public function test_nao_ha_um_robots_estatico_a_tapar_a_rota(): void
    {
        $this->assertFileDoesNotExist(public_path('robots.txt'));
    }
}
