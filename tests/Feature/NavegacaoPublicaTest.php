<?php

namespace Tests\Feature;

use App\Models\Categoria;
use App\Models\Pergunta;
use App\Models\Servico;
use App\Models\Tema;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NavegacaoPublicaTest extends TestCase
{
    use RefreshDatabase;

    private Servico $servico;

    private Categoria $categoria;

    private Tema $tema;

    private Pergunta $pergunta;

    protected function setUp(): void
    {
        parent::setUp();

        // Ação Social → Bolsas → Candidatura → "Como me candidato?"
        $this->servico = Servico::create(['nome' => 'Ação Social', 'slug' => 'acao-social']);
        $this->categoria = Categoria::create(['nome' => 'Bolsas', 'slug' => 'bolsas']);
        $this->tema = Tema::create(['nome' => 'Candidatura', 'slug' => 'candidatura']);
        $this->pergunta = Pergunta::create([
            'pergunta' => 'Como me candidato?',
            'slug' => 'como-me-candidato',
            'resposta' => '<p>Resposta de exemplo.</p>',
            'publicada' => true,
        ]);

        $this->servico->categorias()->attach($this->categoria);
        $this->categoria->temas()->attach($this->tema);
        $this->tema->perguntas()->attach($this->pergunta);
    }

    public function test_cada_nivel_do_caminho_abre(): void
    {
        $this->get('/')->assertOk()->assertSee('Ação Social');
        $this->get('/acao-social')->assertOk()->assertSee('Bolsas');
        $this->get('/acao-social/bolsas')->assertOk()->assertSee('Candidatura');
        $this->get('/acao-social/bolsas/candidatura')->assertOk()->assertSee('Como me candidato?');
        $this->get('/acao-social/bolsas/candidatura/como-me-candidato')->assertOk()->assertSee('Resposta de exemplo.');
        $this->get('/p/como-me-candidato')->assertOk()->assertSee('Resposta de exemplo.');
    }

    public function test_o_breadcrumb_mostra_o_caminho_por_onde_se_chegou(): void
    {
        $this->get('/acao-social/bolsas/candidatura/como-me-candidato')
            ->assertSeeInOrder(['Caminho de navegação', 'Início', 'Ação Social', 'Bolsas', 'Candidatura', 'Como me candidato?']);
    }

    public function test_a_cadeia_tem_de_ser_valida(): void
    {
        $outroServico = Servico::create(['nome' => 'Biblioteca', 'slug' => 'biblioteca']);
        $outraCategoria = Categoria::create(['nome' => 'Empréstimos', 'slug' => 'emprestimos']);
        $outroTema = Tema::create(['nome' => 'Renovações', 'slug' => 'renovacoes']);
        $outroServico->categorias()->attach($outraCategoria);
        $outraCategoria->temas()->attach($outroTema);

        // Cada item existe, mas não pertence ao anterior.
        $this->get('/biblioteca/bolsas')->assertNotFound();
        $this->get('/acao-social/emprestimos')->assertNotFound();
        $this->get('/acao-social/bolsas/renovacoes')->assertNotFound();
        $this->get('/biblioteca/emprestimos/renovacoes/como-me-candidato')->assertNotFound();

        // Slugs que não existem.
        $this->get('/nao-existe')->assertNotFound()->assertSee('Página não encontrada');
        $this->get('/acao-social/bolsas/candidatura/nao-existe')->assertNotFound();
    }

    public function test_o_que_esta_inativo_ou_por_publicar_nao_aparece(): void
    {
        $this->pergunta->update(['publicada' => false]);
        $this->get('/acao-social/bolsas/candidatura')->assertOk()->assertDontSee('Como me candidato?');
        $this->get('/acao-social/bolsas/candidatura/como-me-candidato')->assertNotFound();
        $this->get('/p/como-me-candidato')->assertNotFound();

        $this->tema->update(['ativo' => false]);
        $this->get('/acao-social/bolsas')->assertOk()->assertDontSee('Candidatura');
        $this->get('/acao-social/bolsas/candidatura')->assertNotFound();

        $this->categoria->update(['ativo' => false]);
        $this->get('/acao-social/bolsas')->assertNotFound();

        $this->servico->update(['ativo' => false]);
        $this->get('/')->assertOk()->assertDontSee('Ação Social');
        $this->get('/acao-social')->assertNotFound();
    }

    public function test_a_pagina_da_pergunta_aponta_para_o_url_canonico(): void
    {
        $canonico = '<link rel="canonical" href="'.url('/p/como-me-candidato').'">';

        $this->get('/acao-social/bolsas/candidatura/como-me-candidato')->assertSee($canonico, false);
        $this->get('/p/como-me-candidato')->assertSee($canonico, false);
    }

    public function test_mostra_os_outros_caminhos_onde_a_pergunta_aparece(): void
    {
        $internacional = Servico::create(['nome' => 'Internacional', 'slug' => 'internacional']);
        $internacional->categorias()->attach($this->categoria);

        $this->get('/acao-social/bolsas/candidatura/como-me-candidato')
            ->assertSee('Também aparece em')
            ->assertSee('/internacional/bolsas/candidatura/como-me-candidato', false)
            ->assertDontSee('href="'.url('/acao-social/bolsas/candidatura/como-me-candidato').'"', false);

        // Caminhos por serviços inativos não contam.
        $internacional->update(['ativo' => false]);
        $this->get('/acao-social/bolsas/candidatura/como-me-candidato')->assertDontSee('Também aparece em');
    }

    public function test_o_html_perigoso_da_resposta_e_removido(): void
    {
        $this->pergunta->update(['resposta' => '<p onclick="alert(1)">Texto <strong>seguro</strong></p><script>alert(2)</script>']);

        $this->get('/p/como-me-candidato')
            ->assertSee('<strong>seguro</strong>', false)
            ->assertDontSee('<script>alert(2)', false)
            ->assertDontSee('onclick', false);
    }

    public function test_um_servico_nao_tapa_o_backoffice(): void
    {
        $this->get('/admin')->assertRedirect('/admin/login');
    }
}
