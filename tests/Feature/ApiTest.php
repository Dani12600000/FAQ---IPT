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

    public function test_o_cors_so_aceita_os_sites_configurados(): void
    {
        config(['cors.allowed_origins' => ['https://www.ipt.pt', 'https://portal.ipt.pt']]);

        $this->getJson('/api/servicos', ['Origin' => 'https://portal.ipt.pt'])
            ->assertHeader('Access-Control-Allow-Origin', 'https://portal.ipt.pt');

        $this->getJson('/api/servicos', ['Origin' => 'https://outro-site.example'])
            ->assertHeaderMissing('Access-Control-Allow-Origin');
    }

    public function test_com_a_lista_de_sites_vazia_o_cors_nao_aceita_nenhum_site(): void
    {
        config(['cors.allowed_origins' => []]);

        $this->getJson('/api/servicos', ['Origin' => 'https://www.ipt.pt'])
            ->assertHeaderMissing('Access-Control-Allow-Origin');
    }

    public function test_sem_a_variavel_o_cors_so_fica_aberto_em_ambiente_local(): void
    {
        $this->assertSame(['*'], $this->origensCors('local', null));
        $this->assertSame([], $this->origensCors('production', null));
        $this->assertSame(['https://www.ipt.pt'], $this->origensCors('production', 'https://www.ipt.pt'));
    }

    /**
     * Lê config/cors.php como se o .env tivesse estes valores.
     *
     * @return array<int, string>
     */
    private function origensCors(string $ambiente, ?string $origens): array
    {
        $variaveis = ['APP_ENV' => $ambiente, 'CORS_ALLOWED_ORIGINS' => $origens];
        $originais = array_map(fn (string $nome) => getenv($nome) ?: null, array_combine(array_keys($variaveis), array_keys($variaveis)));

        $definir = function (array $valores): void {
            foreach ($valores as $nome => $valor) {
                unset($_SERVER[$nome], $_ENV[$nome]);
                putenv($valor === null ? $nome : "{$nome}={$valor}");
            }
        };

        $definir($variaveis);

        try {
            return array_values((require config_path('cors.php'))['allowed_origins']);
        } finally {
            $definir($originais);
        }
    }

    public function test_inclui_a_data_da_ultima_alteracao_do_texto(): void
    {
        $this->travelTo('2026-03-10 12:00:00');
        $pergunta = Pergunta::firstWhere('slug', 'como-renovo');
        $pergunta->update(['resposta' => '<p>Resposta de exemplo, revista.</p>']);

        $this->travelTo('2026-05-20 12:00:00');
        $pergunta->update(['rever_ate' => '2027-01-01']);

        $data = now()->setDateTime(2026, 3, 10, 12, 0)->toIso8601String();

        $this->getJson('/api/perguntas/como-renovo')->assertJsonPath('data.atualizado_em', $data);
        $this->getJson('/api/servicos/biblioteca')->assertJsonPath('data.categorias.0.temas.0.perguntas.0.atualizado_em', $data);
        $this->getJson('/api/pesquisa?q=renovo')->assertJsonPath('data.0.atualizado_em', $data);
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
