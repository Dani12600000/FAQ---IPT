<?php

namespace Tests\Feature;

use App\Models\Categoria;
use App\Models\Pergunta;
use App\Models\Pesquisa;
use App\Models\Servico;
use App\Models\Tema;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApiTest extends TestCase
{
    use RefreshDatabase;

    private Servico $servico;

    private Categoria $categoria;

    private Tema $tema;

    protected function setUp(): void
    {
        parent::setUp();

        $this->servico = Servico::create(['nome' => 'Biblioteca', 'slug' => 'biblioteca', 'ordem' => 1]);
        $this->categoria = Categoria::create(['nome' => 'Empréstimos', 'slug' => 'emprestimos']);
        $this->tema = Tema::create(['nome' => 'Renovações', 'slug' => 'renovacoes']);
        $this->servico->categorias()->attach($this->categoria);
        $this->categoria->temas()->attach($this->tema);

        $this->tema->perguntas()->attach([
            $this->pergunta('Quantos livros posso requisitar?', 'quantos-livros')->id => ['ordem' => 2],
            $this->pergunta('Como renovo um empréstimo?', 'como-renovo')->id => ['ordem' => 1],
            $this->pergunta('Rascunho', 'rascunho', publicada: false)->id => ['ordem' => 3],
        ]);
    }

    public function test_lista_os_servicos_ativos_por_ordem(): void
    {
        Servico::create(['nome' => 'Ação Social', 'slug' => 'acao-social', 'ordem' => 0]);
        Servico::create(['nome' => 'Inativo', 'slug' => 'inativo', 'ativo' => false]);

        $this->getJson('/api/servicos')
            ->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('data.0.slug', 'acao-social')
            ->assertJsonPath('data.1.slug', 'biblioteca')
            ->assertJsonPath('data.1.url', url('/biblioteca'));
    }

    public function test_devolve_a_arvore_completa_do_servico_pela_ordem_das_ligacoes(): void
    {
        $this->getJson('/api/servicos/biblioteca')
            ->assertOk()
            ->assertJsonPath('data.categorias.0.slug', 'emprestimos')
            ->assertJsonPath('data.categorias.0.temas.0.slug', 'renovacoes')
            ->assertJsonCount(2, 'data.categorias.0.temas.0.perguntas')
            ->assertJsonPath('data.categorias.0.temas.0.perguntas.0.slug', 'como-renovo')
            ->assertJsonPath('data.categorias.0.temas.0.perguntas.0.ordem', 1)
            ->assertJsonPath('data.categorias.0.temas.0.perguntas.1.slug', 'quantos-livros')
            ->assertJsonPath('data.categorias.0.temas.0.perguntas.0.url', url('/p/como-renovo'));
    }

    public function test_a_arvore_nao_inclui_o_que_esta_inativo(): void
    {
        $this->tema->update(['ativo' => false]);

        $this->getJson('/api/servicos/biblioteca')
            ->assertOk()
            ->assertJsonCount(0, 'data.categorias.0.temas');

        $this->servico->update(['ativo' => false]);
        $this->getJson('/api/servicos/biblioteca')->assertNotFound();
    }

    public function test_devolve_a_pergunta_com_resposta_e_caminhos(): void
    {
        $this->getJson('/api/perguntas/como-renovo')
            ->assertOk()
            ->assertJsonPath('data.pergunta', 'Como renovo um empréstimo?')
            ->assertJsonPath('data.resposta', '<p><strong>Resposta</strong> de exemplo.</p>')
            ->assertJsonPath('data.resposta_texto', 'Resposta de exemplo.')
            ->assertJsonPath('data.caminhos.0.servico.slug', 'biblioteca')
            ->assertJsonPath('data.caminhos.0.url', url('/biblioteca/emprestimos/renovacoes/como-renovo'));

        $this->getJson('/api/perguntas/rascunho')->assertNotFound();
        $this->getJson('/api/perguntas/nao-existe')->assertNotFound()->assertJsonStructure(['message']);
    }

    public function test_pesquisa_e_so_regista_quando_pedido(): void
    {
        $this->getJson('/api/pesquisa?q=renovo')
            ->assertOk()
            ->assertJsonPath('total', 1)
            ->assertJsonPath('data.0.slug', 'como-renovo');
        $this->assertSame(0, Pesquisa::count());

        $this->getJson('/api/pesquisa?q=renovo&registar=1');
        $this->assertDatabaseHas('pesquisas', ['termo' => 'renovo', 'resultados' => 1]);
    }

    public function test_a_resposta_da_api_e_sanitizada(): void
    {
        Pergunta::where('slug', 'como-renovo')->first()->update(['resposta' => '<p onclick="x()">Ok</p><script>alert(1)</script>']);

        $resposta = $this->getJson('/api/perguntas/como-renovo')->json('data.resposta');

        $this->assertStringNotContainsString('script', $resposta);
        $this->assertStringNotContainsString('onclick', $resposta);
    }

    public function test_permite_pedidos_de_outros_sites_cors(): void
    {
        $this->getJson('/api/servicos', ['Origin' => 'https://www.ipt.pt'])
            ->assertHeader('Access-Control-Allow-Origin', '*');
    }

    public function test_o_limite_de_pedidos_vem_da_configuracao(): void
    {
        config(['faq.limites.api_por_minuto' => 2]);

        $this->getJson('/api/servicos')->assertOk();
        $this->getJson('/api/servicos')->assertOk();
        $this->getJson('/api/servicos')->assertTooManyRequests();
    }

    public function test_os_ips_da_lista_nao_tem_limite(): void
    {
        config([
            'faq.limites.api_por_minuto' => 1,
            'faq.limites.api_ips_sem_limite' => ['192.0.2.10', '10.0.0.0/24'],
        ]);

        foreach (['192.0.2.10', '10.0.0.7'] as $ip) {
            $this->withServerVariables(['REMOTE_ADDR' => $ip]);

            $this->getJson('/api/servicos')->assertOk();
            $this->getJson('/api/servicos')->assertOk();
        }

        $this->withServerVariables(['REMOTE_ADDR' => '192.0.2.99']);

        $this->getJson('/api/servicos')->assertOk();
        $this->getJson('/api/servicos')->assertTooManyRequests();
    }

    public function test_a_documentacao_da_api_esta_disponivel_fora_do_ambiente_local(): void
    {
        $this->assertFalse($this->app->environment('local'));

        $this->get('/docs/api')
            ->assertOk()
            ->assertSee('API do FAQ do IPT');
    }

    public function test_a_especificacao_openapi_descreve_todos_os_endpoints(): void
    {
        $especificacao = $this->getJson('/docs/api.json')->assertOk();

        $this->assertSame(
            ['/servicos', '/servicos/{servico}', '/perguntas/{pergunta}', '/pesquisa'],
            array_keys($especificacao->json('paths')),
        );
        $especificacao->assertJsonPath('paths./pesquisa.get.parameters.0.name', 'q');
    }

    private function pergunta(string $texto, string $slug, bool $publicada = true): Pergunta
    {
        return Pergunta::create([
            'pergunta' => $texto,
            'slug' => $slug,
            'resposta' => '<p><strong>Resposta</strong> de exemplo.</p>',
            'publicada' => $publicada,
        ]);
    }
}
