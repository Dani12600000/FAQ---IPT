<?php

namespace Tests\Feature;

use App\Filament\Resources\Perguntas\Pages\CreatePergunta;
use App\Filament\Resources\Perguntas\Pages\EditPergunta;
use App\Filament\Widgets\PerguntasMaisUteis;
use App\Filament\Widgets\PerguntasMenosUteis;
use App\Filament\Widgets\PerguntasSemTema;
use App\Filament\Widgets\PesquisasSemResultados;
use App\Models\Pergunta;
use App\Models\Pesquisa;
use App\Models\Tema;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class PainelBackofficeTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->actingAs(User::factory()->create());
    }

    public function test_o_painel_abre_com_os_widgets(): void
    {
        $this->get('/admin')
            ->assertOk()
            ->assertSeeLivewire(PesquisasSemResultados::class)
            ->assertSeeLivewire(PerguntasMaisUteis::class)
            ->assertSeeLivewire(PerguntasMenosUteis::class)
            ->assertSeeLivewire(PerguntasSemTema::class);
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

    private function perguntaAvaliada(string $texto, int $sim, int $nao): Pergunta
    {
        $pergunta = Pergunta::create(['pergunta' => $texto, 'slug' => str()->slug($texto), 'resposta' => '<p>Ok</p>', 'publicada' => true]);

        foreach (array_merge(array_fill(0, $sim, true), array_fill(0, $nao, false)) as $util) {
            $pergunta->avaliacoes()->create(['util' => $util]);
        }

        return $pergunta;
    }
}
