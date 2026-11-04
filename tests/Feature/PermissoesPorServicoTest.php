<?php

namespace Tests\Feature;

use App\Filament\Resources\Perguntas\Pages\CreatePergunta;
use App\Filament\Resources\Perguntas\Pages\EditPergunta;
use App\Filament\Resources\Perguntas\Pages\ListPerguntas;
use App\Filament\Resources\Users\Pages\CreateUser;
use App\Models\Pergunta;
use App\Models\Servico;
use App\Models\Tema;
use App\Models\User;
use Filament\Actions\DeleteBulkAction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class PermissoesPorServicoTest extends TestCase
{
    use RefreshDatabase;

    private Servico $biblioteca;

    private Servico $acaoSocial;

    private User $editor;

    protected function setUp(): void
    {
        parent::setUp();

        $this->biblioteca = Servico::create(['nome' => 'Biblioteca', 'slug' => 'biblioteca']);
        $this->acaoSocial = Servico::create(['nome' => 'Ação Social', 'slug' => 'acao-social']);

        // O editor da Biblioteca.
        $this->editor = User::factory()->create();
        $this->editor->servicos()->attach($this->biblioteca);
        $this->actingAs($this->editor);
    }

    public function test_o_editor_so_abre_o_conteudo_do_seu_servico(): void
    {
        $propria = $this->pergunta('Como renovo um empréstimo?', $this->biblioteca);
        $alheia = $this->pergunta('Como peço uma bolsa?', $this->acaoSocial);

        $this->get("/admin/perguntas/{$propria->id}/edit")->assertOk();
        // Uma pergunta de outro serviço que não usa nem aparece na lista.
        $this->get("/admin/perguntas/{$alheia->id}/edit")->assertNotFound();

        // Se a usar num tema seu, vê-a, mas não a pode alterar.
        $this->tema('Requisições', $this->biblioteca)->perguntas()->attach($alheia);
        $this->get("/admin/perguntas/{$alheia->id}/edit")->assertForbidden();
    }

    public function test_a_lista_mostra_o_seu_conteudo_e_o_que_usa_de_outros_servicos(): void
    {
        $propria = $this->pergunta('Como renovo um empréstimo?', $this->biblioteca);
        $usada = $this->pergunta('Quem pode usar as cantinas?', $this->acaoSocial);
        $alheia = $this->pergunta('Como peço uma bolsa?', $this->acaoSocial);
        $this->tema('Requisições', $this->biblioteca)->perguntas()->attach($usada);

        Livewire::test(ListPerguntas::class)
            ->assertCanSeeTableRecords([$propria, $usada])
            ->assertCanNotSeeTableRecords([$alheia]);
    }

    public function test_nao_publica_nem_apaga_perguntas_de_outros_servicos(): void
    {
        $propria = $this->pergunta('Como renovo um empréstimo?', $this->biblioteca);
        $usada = $this->pergunta('Quem pode usar as cantinas?', $this->acaoSocial, publicada: true);
        $this->tema('Requisições', $this->biblioteca)->perguntas()->attach($usada);

        Livewire::test(ListPerguntas::class)
            ->call('updateTableColumnState', 'publicada', (string) $usada->id, false)
            ->callTableBulkAction(DeleteBulkAction::class, [$propria, $usada]);

        $this->assertTrue($usada->fresh()->publicada);
        $this->assertModelExists($usada);
        $this->assertModelMissing($propria);
    }

    public function test_ao_criar_so_pode_escolher_os_seus_servicos_e_temas(): void
    {
        $temaAlheio = $this->tema('Bolsas', $this->acaoSocial);

        Livewire::test(CreatePergunta::class)
            ->assertFormSet(['servico_id' => $this->biblioteca->id])
            ->fillForm([
                'pergunta' => 'Onde fica a biblioteca?',
                'slug' => 'onde-fica-a-biblioteca',
                'resposta' => '<p>Resposta de exemplo.</p>',
                'servico_id' => $this->acaoSocial->id,
                'temas' => [$temaAlheio->id],
            ])
            ->call('create')
            ->assertHasFormErrors(['servico_id', 'temas.0']);

        $this->assertSame(0, Pergunta::count());
    }

    public function test_ao_editar_mantem_as_ligacoes_feitas_por_outros_servicos(): void
    {
        $pergunta = $this->pergunta('Como renovo um empréstimo?', $this->biblioteca);
        $temaProprio = $this->tema('Requisições', $this->biblioteca);
        $temaAlheio = $this->tema('Bolsas', $this->acaoSocial);
        $pergunta->temas()->attach([$temaProprio->id, $temaAlheio->id]);

        // O editor só vê (e só pode tirar) os temas do seu serviço.
        Livewire::test(EditPergunta::class, ['record' => $pergunta->getRouteKey()])
            ->assertFormSet(['temas' => [(string) $temaProprio->id]])
            ->fillForm(['temas' => []])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertSame([$temaAlheio->id], $pergunta->temas()->pluck('temas.id')->all());
    }

    public function test_o_editor_so_ve_e_altera_os_seus_servicos(): void
    {
        $this->get('/admin/servicos')->assertOk()->assertSee('Biblioteca')->assertDontSee('Ação Social');
        $this->get("/admin/servicos/{$this->biblioteca->id}/edit")->assertOk();
        $this->get("/admin/servicos/{$this->acaoSocial->id}/edit")->assertNotFound();
    }

    public function test_um_editor_sem_servico_nao_cria_conteudo(): void
    {
        $this->actingAs(User::factory()->create());

        $this->get('/admin/perguntas/create')->assertForbidden();
    }

    public function test_o_admin_altera_tudo_incluindo_conteudo_sem_responsavel(): void
    {
        $this->actingAs(User::factory()->admin()->create());
        $alheia = $this->pergunta('Como peço uma bolsa?', $this->acaoSocial);
        $semResponsavel = Pergunta::create(['pergunta' => 'Órfã?', 'slug' => 'orfa', 'resposta' => '<p>Ok</p>']);

        $this->get("/admin/perguntas/{$alheia->id}/edit")->assertOk();
        $this->get("/admin/perguntas/{$semResponsavel->id}/edit")->assertOk();
        $this->get('/admin/servicos/create')->assertOk();
    }

    public function test_o_admin_atribui_servicos_aos_editores(): void
    {
        $this->actingAs(User::factory()->admin()->create());

        Livewire::test(CreateUser::class)
            ->fillForm([
                'name' => 'Editora da Ação Social',
                'email' => 'acao-social@ipt.test',
                'papel' => 'editor',
                'servicos' => [$this->acaoSocial->id],
                'password' => 'uma-password-longa',
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertSame(
            [$this->acaoSocial->id],
            User::firstWhere('email', 'acao-social@ipt.test')->servicos->modelKeys(),
        );
    }

    private function pergunta(string $texto, Servico $servico, bool $publicada = false): Pergunta
    {
        return Pergunta::create([
            'servico_id' => $servico->id,
            'pergunta' => $texto,
            'slug' => str()->slug($texto),
            'resposta' => '<p>Resposta de exemplo.</p>',
            'publicada' => $publicada,
        ]);
    }

    private function tema(string $nome, Servico $servico): Tema
    {
        return Tema::create(['servico_id' => $servico->id, 'nome' => $nome, 'slug' => str()->slug($nome)]);
    }
}
