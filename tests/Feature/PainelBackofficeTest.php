<?php

namespace Tests\Feature;

use App\Filament\Resources\Perguntas\Pages\CreatePergunta;
use App\Filament\Resources\Perguntas\Pages\EditPergunta;
use App\Filament\Widgets\PerguntasMaisUteis;
use App\Filament\Widgets\PerguntasMenosUteis;
use App\Filament\Widgets\PerguntasSemTema;
use App\Filament\Widgets\PesquisasSemResultados;
use App\Filament\Widgets\RespostasARever;
use App\Models\Pergunta;
use App\Models\Pesquisa;
use App\Models\Servico;
use App\Models\Tema;
use App\Models\User;
use Carbon\CarbonInterface;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Livewire\Livewire;
use Tests\TestCase;

class PainelBackofficeTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // As permissões por serviço são testadas em PermissoesPorServicoTest.
        $this->actingAs(User::factory()->admin()->create());
    }

    public function test_o_painel_abre_com_os_widgets(): void
    {
        $this->get('/admin')
            ->assertOk()
            ->assertSeeLivewire(PesquisasSemResultados::class)
            ->assertSeeLivewire(PerguntasMaisUteis::class)
            ->assertSeeLivewire(PerguntasMenosUteis::class)
            ->assertSeeLivewire(PerguntasSemTema::class)
            ->assertSeeLivewire(RespostasARever::class);
    }

    public function test_mostra_as_pesquisas_sem_resultados_agrupadas_por_termo(): void
    {
        Pesquisa::create(['termo' => 'erasmus', 'resultados' => 0]);
        Pesquisa::create(['termo' => 'erasmus', 'resultados' => 0]);
        Pesquisa::create(['termo' => 'estacionamento', 'resultados' => 0]);
        Pesquisa::create(['termo' => 'propinas', 'resultados' => 3]);

        Livewire::test(PesquisasSemResultados::class)
            ->assertSeeInOrder(['erasmus', '2', 'estacionamento', '1'])
            ->assertSee(now()->format('d/m/Y'))
            ->assertDontSee('propinas');
    }

    public function test_ordena_as_perguntas_pela_utilidade(): void
    {
        $boa = $this->perguntaAvaliada('Pergunta boa?', sim: 3, nao: 1);
        $ma = $this->perguntaAvaliada('Pergunta má?', sim: 0, nao: 2);
        $semAvaliacoes = $this->perguntaAvaliada('Sem avaliações?', sim: 0, nao: 0);

        Livewire::test(PerguntasMaisUteis::class)
            ->assertSeeInOrder([$boa->pergunta, '75', $ma->pergunta, '0'])
            ->assertDontSee($semAvaliacoes->pergunta);

        Livewire::test(PerguntasMenosUteis::class)
            ->assertSeeInOrder([$ma->pergunta, $boa->pergunta])
            ->assertDontSee($semAvaliacoes->pergunta);
    }

    public function test_mostra_as_perguntas_sem_tema(): void
    {
        $orfa = Pergunta::create(['pergunta' => 'Sem tema?', 'slug' => 'sem-tema', 'resposta' => '<p>Ok</p>']);
        $ligada = Pergunta::create(['pergunta' => 'Com tema?', 'slug' => 'com-tema', 'resposta' => '<p>Ok</p>']);
        Tema::create(['nome' => 'Tema', 'slug' => 'tema'])->perguntas()->attach($ligada);

        Livewire::test(PerguntasSemTema::class)
            ->assertCanSeeTableRecords([$orfa])
            ->assertCanNotSeeTableRecords([$ligada]);
    }

    public function test_mostra_as_respostas_cuja_revisao_passou_ou_esta_proxima(): void
    {
        $passada = $this->perguntaComRevisao('Passada?', today()->subDay());
        $limite = $this->perguntaComRevisao('No limite?', today()->addDays(30));
        $depois = $this->perguntaComRevisao('Depois?', today()->addDays(31));
        $semData = $this->perguntaComRevisao('Sem data?', null);

        Livewire::test(RespostasARever::class)
            ->assertCanSeeTableRecords([$passada, $limite], inOrder: true)
            ->assertCanNotSeeTableRecords([$depois, $semData]);
    }

    public function test_cada_editor_so_ve_as_respostas_a_rever_do_seu_servico(): void
    {
        $biblioteca = Servico::create(['nome' => 'Biblioteca', 'slug' => 'biblioteca']);
        $acaoSocial = Servico::create(['nome' => 'Ação Social', 'slug' => 'acao-social']);
        $sua = $this->perguntaComRevisao('Da biblioteca?', today(), $biblioteca);
        $outra = $this->perguntaComRevisao('Da ação social?', today(), $acaoSocial);

        $editor = User::factory()->create();
        $editor->servicos()->attach($biblioteca);
        $this->actingAs($editor);

        Livewire::test(RespostasARever::class)
            ->assertCanSeeTableRecords([$sua])
            ->assertCanNotSeeTableRecords([$outra]);
    }

    public function test_grava_a_data_de_revisao_no_formulario(): void
    {
        $servico = Servico::create(['nome' => 'Biblioteca', 'slug' => 'biblioteca']);
        $pergunta = $this->perguntaComRevisao('A rever?', null, $servico);

        Livewire::test(EditPergunta::class, ['record' => $pergunta->getRouteKey()])
            ->fillForm(['rever_ate' => '2027-01-15'])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertSame('2027-01-15', $pergunta->fresh()->rever_ate->format('Y-m-d'));
    }

    public function test_pre_visualiza_o_que_esta_no_formulario_antes_de_gravar(): void
    {
        $pergunta = Pergunta::create(['pergunta' => 'Original?', 'slug' => 'original', 'resposta' => '<p>Resposta guardada</p>']);

        Livewire::test(EditPergunta::class, ['record' => $pergunta->getRouteKey()])
            ->fillForm(['pergunta' => 'Título alterado?', 'resposta' => '<p>Texto <strong>novo</strong></p><script>alert(1)</script>'])
            ->mountAction('preVisualizar')
            ->assertMountedActionModalSee('Título alterado?')
            ->assertMountedActionModalSeeHtml('<strong>novo</strong>')
            ->assertMountedActionModalDontSeeHtml('<script>');

        // Nada foi gravado.
        $this->assertSame('Original?', $pergunta->fresh()->pergunta);
    }

    public function test_pre_visualiza_tambem_ao_criar(): void
    {
        Livewire::test(CreatePergunta::class)
            ->fillForm(['pergunta' => 'Pergunta nova?'])
            ->mountAction('preVisualizar')
            ->assertMountedActionModalSee('Pergunta nova?')
            ->assertMountedActionModalSee('A resposta ainda está vazia.');
    }

    public function test_ver_no_site_so_aparece_se_estiver_publicada(): void
    {
        $pergunta = Pergunta::create(['pergunta' => 'Rascunho?', 'slug' => 'rascunho', 'resposta' => '<p>Ok</p>']);

        Livewire::test(EditPergunta::class, ['record' => $pergunta->getRouteKey()])
            ->assertActionHidden('verNoSite');

        $pergunta->update(['publicada' => true]);

        Livewire::test(EditPergunta::class, ['record' => $pergunta->getRouteKey()])
            ->assertActionVisible('verNoSite')
            ->assertActionHasUrl('verNoSite', route('faq.canonica', 'rascunho'));
    }

    private function perguntaComRevisao(string $texto, ?CarbonInterface $data, ?Servico $servico = null): Pergunta
    {
        return Pergunta::create([
            'servico_id' => $servico?->id,
            'pergunta' => $texto,
            'slug' => Str::slug($texto),
            'resposta' => '<p>Resposta de exemplo.</p>',
            'rever_ate' => $data,
        ]);
    }

    private function perguntaAvaliada(string $texto, int $sim, int $nao): Pergunta
    {
        $pergunta = Pergunta::create(['pergunta' => $texto, 'slug' => str()->slug($texto), 'resposta' => '<p>Ok</p>', 'publicada' => true]);

        foreach (array_merge(array_fill(0, $sim, true), array_fill(0, $nao, false)) as $util) {
            $pergunta->avaliacoes()->create(['util' => $util]);
        }

        return $pergunta;
    }
}
