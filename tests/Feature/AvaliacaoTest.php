<?php

namespace Tests\Feature;

use App\Models\Avaliacao;
use App\Models\Pergunta;
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
