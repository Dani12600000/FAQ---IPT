<?php

namespace Tests\Feature;

use App\Models\Avaliacao;
use App\Models\Categoria;
use App\Models\Pergunta;
use App\Models\Servico;
use App\Models\Tema;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AvaliacaoTest extends TestCase
{
    use RefreshDatabase;

    private Pergunta $pergunta;

    protected function setUp(): void
    {
        parent::setUp();

        $this->pergunta = Pergunta::create([
            'pergunta' => 'Como renovo um empréstimo?',
            'slug' => 'como-renovo',
            'resposta' => '<p>Resposta de exemplo.</p>',
            'publicada' => true,
        ]);
    }

    /**
     * @param  array<string, mixed>  $dados
     */
    private function servicoResponsavel(array $dados = []): void
    {
        $servico = Servico::create(['nome' => 'Biblioteca', 'slug' => 'biblioteca', 'email' => 'biblioteca@example.com', ...$dados]);

        $this->pergunta->update(['servico_id' => $servico->id]);
    }

    public function test_a_pagina_da_pergunta_pergunta_se_foi_util(): void
    {
        $this->get('/p/como-renovo')
            ->assertSee('Esta resposta foi útil?')
            ->assertSee('action="'.url('/p/como-renovo/avaliacao').'"', false);
    }

    public function test_sem_javascript_grava_e_volta_com_agradecimento(): void
    {
        $this->from('/p/como-renovo')
            ->post('/p/como-renovo/avaliacao', ['util' => '1'])
            ->assertRedirect('/p/como-renovo');

        $this->assertDatabaseHas('avaliacoes', ['pergunta_id' => $this->pergunta->id, 'util' => true]);

        $this->get('/p/como-renovo')
            ->assertSee('Obrigado pela sua opinião.')
            ->assertDontSee('<button type="submit" name="util"', false);
    }

    public function test_sem_javascript_mostra_os_contactos_so_depois_de_um_nao(): void
    {
        $this->servicoResponsavel();

        $this->get('/p/como-renovo')->assertSee('data-avaliacao-ajuda hidden', false);

        $this->from('/p/como-renovo')->post('/p/como-renovo/avaliacao', ['util' => '1']);
        $this->get('/p/como-renovo')->assertSee('data-avaliacao-ajuda hidden', false);

        $this->from('/p/como-renovo')->post('/p/como-renovo/avaliacao', ['util' => '0']);
        $this->get('/p/como-renovo')
            ->assertSee('Precisa de mais ajuda?')
            ->assertSee('mailto:biblioteca@example.com', false)
            ->assertDontSee('data-avaliacao-ajuda hidden', false);
    }

    public function test_num_caminho_os_contactos_sao_os_do_servico_desse_caminho(): void
    {
        $this->servicoResponsavel();

        $outro = Servico::create(['nome' => 'Ação Social', 'slug' => 'acao-social', 'email' => 'social@example.com']);
        $categoria = Categoria::create(['nome' => 'Bolsas', 'slug' => 'bolsas']);
        $tema = Tema::create(['nome' => 'Candidatura', 'slug' => 'candidatura']);
        $outro->categorias()->attach($categoria);
        $categoria->temas()->attach($tema);
        $tema->perguntas()->attach($this->pergunta);

        $this->get('/acao-social/bolsas/candidatura/como-renovo')
            ->assertSee('social@example.com')
            ->assertDontSee('biblioteca@example.com');
    }

    public function test_sem_contactos_nao_mostra_a_ajuda(): void
    {
        $this->servicoResponsavel(['email' => null]);

        $this->get('/p/como-renovo')->assertDontSee('Precisa de mais ajuda?');
    }

    public function test_com_javascript_grava_e_responde_em_json(): void
    {
        $this->postJson('/p/como-renovo/avaliacao', ['util' => false])
            ->assertCreated()
            ->assertJsonPath('mensagem', 'Obrigado. A sua opinião ajuda-nos a melhorar esta resposta.');

        $this->assertSame(0, Avaliacao::where('util', true)->count());
        $this->assertSame(1, Avaliacao::where('util', false)->count());
    }

    public function test_so_aceita_sim_ou_nao(): void
    {
        $this->postJson('/p/como-renovo/avaliacao', ['util' => 'talvez'])->assertUnprocessable();
        $this->postJson('/p/como-renovo/avaliacao', [])->assertUnprocessable();

        $this->assertSame(0, Avaliacao::count());
    }

    public function test_nao_se_avaliam_perguntas_por_publicar(): void
    {
        $this->pergunta->update(['publicada' => false]);

        $this->postJson('/p/como-renovo/avaliacao', ['util' => true])->assertNotFound();
    }

    public function test_limita_as_avaliacoes_por_minuto_com_o_valor_da_configuracao(): void
    {
        config(['faq.limites.avaliacoes_por_minuto' => 2]);

        for ($i = 0; $i < 2; $i++) {
            $this->postJson('/p/como-renovo/avaliacao', ['util' => true])->assertCreated();
        }

        $this->postJson('/p/como-renovo/avaliacao', ['util' => true])->assertTooManyRequests();
        $this->assertSame(2, Avaliacao::count());
    }

    public function test_nao_cria_cookies_alem_dos_tecnicos_do_laravel(): void
    {
        $cookies = collect($this->get('/p/como-renovo')->headers->getCookies())->map->getName();

        $this->assertEqualsCanonicalizing([config('session.cookie'), 'XSRF-TOKEN'], $cookies->all());
    }
}
