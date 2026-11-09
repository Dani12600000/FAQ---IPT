<?php

namespace Tests\Feature;

use App\Models\Pergunta;
use App\Models\Pesquisa;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PesquisaTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->pergunta('Como pago as propinas?', 'como-pago-as-propinas', '<p>Por <strong>referência</strong> multibanco.</p>');
        $this->pergunta('Como renovo um empréstimo?', 'como-renovo', '<p>Na biblioteca ou online. As propinas não contam.</p>');
        $this->pergunta('Rascunho sobre propinas', 'rascunho', '<p>Ainda por rever.</p>', publicada: false);
    }

    public function test_encontra_na_pergunta_e_na_resposta(): void
    {
        $this->get('/pesquisa?q=multibanco')->assertOk()->assertSee('Como pago as propinas?')->assertDontSee('Como renovo');
        $this->get('/pesquisa?q=online')->assertOk()->assertSee('Como renovo um empréstimo?');
    }

    public function test_todas_as_palavras_tem_de_aparecer(): void
    {
        $this->get('/pesquisa?q=propinas multibanco')
            ->assertSee('Como pago as propinas?')
            ->assertDontSee('Como renovo');
    }

    public function test_as_que_tem_o_termo_na_pergunta_aparecem_primeiro(): void
    {
        $this->get('/pesquisa?q=propinas')
            ->assertSee('2 resultados')
            ->assertSeeInOrder(['Como pago as propinas?', 'Como renovo um empréstimo?']);
    }

    public function test_nao_procura_dentro_das_etiquetas_html(): void
    {
        $this->get('/pesquisa?q=strong')->assertSee('Não encontrámos perguntas');
    }

    public function test_so_mostra_perguntas_publicadas(): void
    {
        $this->get('/pesquisa?q=rascunho')->assertSee('Não encontrámos perguntas')->assertDontSee('Ainda por rever');
    }

    public function test_termos_demasiado_curtos_nao_pesquisam(): void
    {
        $this->get('/pesquisa?q=a')->assertOk()->assertSee('Escreva pelo menos 2 caracteres');
    }

    public function test_os_resultados_ligam_ao_url_canonico(): void
    {
        $this->get('/pesquisa?q=multibanco')->assertSee('href="'.url('/p/como-pago-as-propinas').'"', false);
    }

    public function test_a_pesquisa_enquanto_se_escreve_recebe_so_os_resultados(): void
    {
        $resposta = $this->get('/pesquisa?q=multibanco', ['X-Fragmento' => '1']);

        $resposta->assertOk()
            ->assertSee('data-total="1"', false)
            ->assertSee('Como pago as propinas?')
            ->assertDontSee('<html', false);
    }

    public function test_regista_a_pesquisa_e_o_numero_de_resultados(): void
    {
        $this->get('/pesquisa?q=Multibanco');
        $this->get('/pesquisa?q=erasmus');

        $this->assertDatabaseHas('pesquisas', ['termo' => 'multibanco', 'resultados' => 1]);
        $this->assertDatabaseHas('pesquisas', ['termo' => 'erasmus', 'resultados' => 0]);
    }

    public function test_enquanto_se_escreve_so_regista_quando_a_pessoa_para(): void
    {
        $this->get('/pesquisa?q=mul', ['X-Fragmento' => '1']);
        $this->get('/pesquisa?q=multi', ['X-Fragmento' => '1']);
        $this->assertSame(0, Pesquisa::count());

        $this->get('/pesquisa?q=multibanco', ['X-Fragmento' => '1', 'X-Registar' => '1']);
        $this->assertSame(['multibanco'], Pesquisa::pluck('termo')->all());
    }

    public function test_nao_regista_termos_vazios_ou_curtos(): void
    {
        $this->get('/pesquisa');
        $this->get('/pesquisa?q=a');

        $this->assertSame(0, Pesquisa::count());
    }

    public function test_apaga_as_pesquisas_mais_antigas_do_que_o_prazo(): void
    {
        $this->travelTo('2025-01-10 12:00:00');
        Pesquisa::create(['termo' => 'antiga', 'resultados' => 0]);

        $this->travelTo('2025-06-10 12:00:00');
        Pesquisa::create(['termo' => 'recente', 'resultados' => 0]);

        $this->travelTo('2026-02-10 12:00:00');
        $this->artisan('model:prune')->assertSuccessful();

        $this->assertSame(['recente'], Pesquisa::pluck('termo')->all());

        config(['faq.pesquisas_meses_a_guardar' => 6]);
        $this->artisan('model:prune')->assertSuccessful();

        $this->assertSame(0, Pesquisa::count());
    }

    public function test_a_limpeza_das_pesquisas_esta_agendada(): void
    {
        $comandos = collect(app(Schedule::class)->events())->map(fn ($evento) => $evento->command);

        $this->assertTrue($comandos->contains(fn (?string $comando) => str_contains((string) $comando, 'model:prune')));
    }

    private function pergunta(string $texto, string $slug, string $resposta, bool $publicada = true): Pergunta
    {
        return Pergunta::create(['pergunta' => $texto, 'slug' => $slug, 'resposta' => $resposta, 'publicada' => $publicada]);
    }
}
