<?php

namespace Tests\Feature;

use App\Filament\Resources\Perguntas\Pages\EditPergunta;
use App\Filament\Resources\Perguntas\Pages\ListPerguntas;
use App\Filament\Resources\Temas\Pages\EditTema;
use App\Filament\Resources\Temas\RelationManagers\PerguntasRelationManager;
use App\Models\Pergunta;
use App\Models\Servico;
use App\Models\Tema;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * "Ramos" de uma pergunta: a versão própria de um serviço, criada a partir da pergunta de outro.
 */
class VersaoPropriaTest extends TestCase
{
    use RefreshDatabase;

    private Servico $academicos;

    private Servico $acaoSocial;

    private Pergunta $original;

    private Tema $temaAcademicos;

    private Tema $temaAcaoSocial;

    protected function setUp(): void
    {
        parent::setUp();

        $this->academicos = Servico::create(['nome' => 'Serviços Académicos', 'slug' => 'servicos-academicos']);
        $this->acaoSocial = Servico::create(['nome' => 'Ação Social', 'slug' => 'acao-social']);

        $this->original = Pergunta::create([
            'servico_id' => $this->academicos->id,
            'pergunta' => 'A bolsa de estudo cobre a propina?',
            'slug' => 'a-bolsa-de-estudo-cobre-a-propina',
            'resposta' => '<p>Resposta de exemplo: texto comum.</p>',
            'publicada' => true,
        ]);

        $this->temaAcademicos = Tema::create(['servico_id' => $this->academicos->id, 'nome' => 'Propinas', 'slug' => 'propinas']);
        $this->temaAcaoSocial = Tema::create(['servico_id' => $this->acaoSocial->id, 'nome' => 'Bolsas', 'slug' => 'bolsas']);
        $outra = Pergunta::create(['servico_id' => $this->acaoSocial->id, 'pergunta' => 'Outra?', 'slug' => 'outra', 'resposta' => '<p>Ok</p>']);

        $this->temaAcademicos->perguntas()->attach($this->original);
        // Na Ação Social, a pergunta é a 2.ª do tema.
        $this->temaAcaoSocial->perguntas()->attach([$outra->id => ['ordem' => 1], $this->original->id => ['ordem' => 2]]);
    }

    public function test_a_copia_substitui_a_original_so_nos_temas_do_servico(): void
    {
        $copia = $this->original->derivarPara($this->acaoSocial);

        $this->assertSame($this->acaoSocial->id, $copia->servico_id);
        $this->assertTrue($copia->origem->is($this->original));
        $this->assertSame(1, $copia->origem_versao);
        $this->assertSame('a-bolsa-de-estudo-cobre-a-propina-acao-social', $copia->slug);
        $this->assertSame('<p>Resposta de exemplo: texto comum.</p>', $copia->resposta);

        // Mesma posição no tema da Ação Social; os Serviços Académicos continuam com a original.
        $this->assertSame(['outra', $copia->slug], $this->temaAcaoSocial->perguntas()->pluck('slug')->all());
        $this->assertSame([$this->original->id], $this->temaAcademicos->perguntas()->pluck('perguntas.id')->all());

        $this->assertSame('Criada a partir de «A bolsa de estudo cobre a propina?» (versão 1)', $copia->versoes()->first()->nota);
    }

    public function test_cada_servico_altera_a_sua_e_sabe_quando_a_original_mudou(): void
    {
        $copia = $this->original->derivarPara($this->acaoSocial);

        $copia->update(['resposta' => '<p>Resposta de exemplo: texto da Ação Social.</p>']);
        $this->assertFalse($copia->origemMudou());
        $this->assertSame('<p>Resposta de exemplo: texto comum.</p>', $this->original->fresh()->resposta);

        $this->original->update(['resposta' => '<p>Resposta de exemplo: texto novo dos Académicos.</p>']);
        $this->assertTrue($copia->fresh()->origemMudou());
    }

    public function test_trazer_ou_ignorar_as_alteracoes_da_original(): void
    {
        $copia = $this->original->derivarPara($this->acaoSocial);
        $this->original->update(['resposta' => '<p>Resposta de exemplo: texto novo.</p>']);

        $copia->fresh()->ignorarAlteracoesDaOrigem();
        $this->assertFalse($copia->fresh()->origemMudou());
        $this->assertSame('<p>Resposta de exemplo: texto comum.</p>', $copia->fresh()->resposta);

        $this->original->update(['resposta' => '<p>Resposta de exemplo: texto ainda mais novo.</p>']);
        $copia = $copia->fresh();
        $copia->trazerAlteracoesDaOrigem();

        $this->assertSame('<p>Resposta de exemplo: texto ainda mais novo.</p>', $copia->fresh()->resposta);
        $this->assertSame(3, $copia->origem_versao);
        $this->assertFalse($copia->fresh()->origemMudou());
        $this->assertSame([2, 1], $copia->versoes()->pluck('numero')->all());
    }

    public function test_voltar_a_original_repoe_a_ligacao_e_guarda_a_copia(): void
    {
        $copia = $this->original->derivarPara($this->acaoSocial);

        $copia->voltarAOrigem();

        $this->assertSame(['outra', $this->original->slug], $this->temaAcaoSocial->perguntas()->pluck('slug')->all());
        $copia->refresh();
        $this->assertFalse($copia->publicada);
        $this->assertSame(0, $copia->temas()->count());
        $this->assertSame(2, $copia->versoes()->count());
    }

    public function test_tambem_se_cria_na_lista_de_perguntas(): void
    {
        $this->actingAs($this->editorDe($this->acaoSocial));

        Livewire::test(ListPerguntas::class)
            // Uma pergunta do seu serviço edita-se diretamente.
            ->assertTableActionHidden('criarVersaoPropria', Pergunta::firstWhere('slug', 'outra'))
            ->callTableAction('criarVersaoPropria', $this->original)
            ->assertHasNoTableActionErrors();

        $copia = Pergunta::where('origem_id', $this->original->id)->sole();
        $this->assertSame($this->acaoSocial->id, $copia->servico_id);
        $this->assertSame(['outra', $copia->slug], $this->temaAcaoSocial->perguntas()->pluck('slug')->all());
    }

    public function test_quem_tem_varios_servicos_escolhe_para_qual_e_a_copia(): void
    {
        $internacional = Servico::create(['nome' => 'Internacional', 'slug' => 'internacional']);
        $temaInternacional = Tema::create(['servico_id' => $internacional->id, 'nome' => 'Partida', 'slug' => 'partida']);
        $temaInternacional->perguntas()->attach($this->original);

        $editor = $this->editorDe($this->acaoSocial);
        $editor->servicos()->attach($internacional);
        $this->actingAs($editor);

        Livewire::test(ListPerguntas::class)
            ->mountTableAction('criarVersaoPropria', $this->original)
            ->assertTableActionDataSet(['servico_id' => null])
            ->setTableActionData(['servico_id' => $internacional->id])
            ->callMountedTableAction()
            ->assertHasNoTableActionErrors();

        $copia = Pergunta::where('origem_id', $this->original->id)->sole();
        $this->assertSame($internacional->id, $copia->servico_id);
        // Na Ação Social continua a original.
        $this->assertTrue($this->temaAcaoSocial->perguntas()->whereKey($this->original)->exists());
        $this->assertTrue($temaInternacional->perguntas()->whereKey($copia)->exists());
    }

    public function test_o_editor_cria_a_versao_propria_no_seu_tema(): void
    {
        $this->actingAs($this->editorDe($this->acaoSocial));

        Livewire::test(PerguntasRelationManager::class, ['ownerRecord' => $this->temaAcaoSocial, 'pageClass' => EditTema::class])
            ->assertTableActionHidden('criarVersaoPropria', Pergunta::firstWhere('slug', 'outra'))
            ->callTableAction('criarVersaoPropria', $this->original);

        $copia = Pergunta::where('origem_id', $this->original->id)->sole();
        $this->assertSame($this->acaoSocial->id, $copia->servico_id);
        // O editor da Ação Social pode agora editar a sua versão.
        $this->get("/admin/perguntas/{$copia->id}/edit")
            ->assertOk()
            ->assertSee('Versão própria de «A bolsa de estudo cobre a propina?» (Serviços Académicos), a partir da versão 1.');
    }

    public function test_na_pagina_da_versao_propria_compara_e_traz_o_texto_da_original(): void
    {
        $this->actingAs($this->editorDe($this->acaoSocial));
        $copia = $this->original->derivarPara($this->acaoSocial);
        $copia->update(['resposta' => '<p>Resposta de exemplo: texto da Ação Social.</p>']);
        $this->original->update(['resposta' => '<p>Resposta de exemplo: texto novo.</p>']);

        Livewire::test(EditPergunta::class, ['record' => $copia->getRouteKey()])
            ->mountAction('compararComOriginal')
            ->assertMountedActionModalSee(['Base comum (versão 1 da original)', 'texto comum', 'texto novo', 'texto da Ação Social']);

        Livewire::test(EditPergunta::class, ['record' => $copia->getRouteKey()])
            ->callAction('trazerDaOriginal')
            ->assertFormSet(['pergunta' => 'A bolsa de estudo cobre a propina?']);

        $this->assertSame('<p>Resposta de exemplo: texto novo.</p>', $copia->fresh()->resposta);
    }

    public function test_as_acoes_da_versao_propria_so_aparecem_numa_versao_propria(): void
    {
        $this->actingAs(User::factory()->admin()->create());

        Livewire::test(EditPergunta::class, ['record' => $this->original->getRouteKey()])
            ->assertActionHidden('compararComOriginal')
            ->assertActionHidden('voltarAOriginal');
    }

    public function test_desligada_nao_se_criam_nem_gerem_versoes_proprias(): void
    {
        config(['faq.versao_propria' => false]);
        $this->actingAs($this->editorDe($this->acaoSocial));

        Livewire::test(ListPerguntas::class)
            ->assertTableActionHidden('criarVersaoPropria', $this->original);

        $copia = $this->original->derivarPara($this->acaoSocial);
        $this->actingAs(User::factory()->admin()->create());

        Livewire::test(EditPergunta::class, ['record' => $copia->getRouteKey()])
            ->assertActionHidden('compararComOriginal')
            ->assertActionHidden('voltarAOriginal')
            ->assertDontSee('Versão própria de');

        Livewire::test(ListPerguntas::class)
            ->assertDontSee('Versão própria de');
    }

    private function editorDe(Servico $servico): User
    {
        $editor = User::factory()->create();
        $editor->servicos()->attach($servico);

        return $editor;
    }
}
