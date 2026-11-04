<?php

namespace Tests\Feature;

use App\Filament\Resources\Perguntas\Pages\CreatePergunta;
use App\Filament\Resources\Perguntas\Pages\EditPergunta;
use App\Filament\Resources\Perguntas\Pages\ListPerguntas;
use App\Filament\Resources\Perguntas\Schemas\PerguntaForm;
use App\Models\Pergunta;
use App\Models\Servico;
use App\Models\Tema;
use App\Models\User;
use App\Services\PerguntasSemelhantes;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class PerguntasSemelhantesTest extends TestCase
{
    use RefreshDatabase;

    private Servico $biblioteca;

    private Servico $acaoSocial;

    private Pergunta $renovar;

    protected function setUp(): void
    {
        parent::setUp();

        $this->biblioteca = Servico::create(['nome' => 'Biblioteca', 'slug' => 'biblioteca']);
        $this->acaoSocial = Servico::create(['nome' => 'Ação Social', 'slug' => 'acao-social']);

        $this->renovar = $this->pergunta('Como renovo um empréstimo?', $this->biblioteca, '<p>Resposta de exemplo: no portal da biblioteca.</p>');
        $this->pergunta('Quem pode usar as cantinas?', $this->acaoSocial);
        $this->pergunta('Como me candidato a uma bolsa de estudo?', $this->acaoSocial);
    }

    public function test_compara_palavras_sem_acentos_nem_palavras_vazias(): void
    {
        $semelhantes = new PerguntasSemelhantes;

        $this->assertSame(['renov', 'empre'], $semelhantes->palavras('Como posso renovar os empréstimos?'));
        $this->assertSame(1.0, $semelhantes->semelhanca(['renov', 'empre'], ['renov', 'empre']));
        $this->assertSame(0.0, $semelhantes->semelhanca(['renov', 'empre'], ['canti']));
    }

    public function test_encontra_perguntas_escritas_de_outra_forma(): void
    {
        $sugestoes = (new PerguntasSemelhantes)->para('Posso renovar os meus empréstimos online?');

        $this->assertCount(1, $sugestoes);
        $this->assertTrue($sugestoes[0]['pergunta']->is($this->renovar));
    }

    public function test_nao_sugere_perguntas_sobre_outro_assunto(): void
    {
        $this->assertCount(0, (new PerguntasSemelhantes)->para('Onde fica a reitoria?'));
        $this->assertCount(0, (new PerguntasSemelhantes)->para('Como?'));
    }

    public function test_ao_criar_mostra_as_perguntas_parecidas_com_a_resposta(): void
    {
        $this->actingAs($this->editorDe($this->acaoSocial));

        Livewire::test(CreatePergunta::class)
            ->fillForm(['pergunta' => 'Como posso renovar os empréstimos?'])
            ->assertSee('Já existe uma pergunta parecida?')
            ->assertSee('Como renovo um empréstimo?')
            ->assertSee('no portal da biblioteca')
            ->assertSee('Biblioteca')
            ->assertDontSee('Quem pode usar as cantinas?');
    }

    public function test_usa_a_pergunta_existente_nos_seus_temas_sem_a_duplicar(): void
    {
        $this->actingAs($this->editorDe($this->acaoSocial));
        $temaProprio = Tema::create(['servico_id' => $this->acaoSocial->id, 'nome' => 'Apoios', 'slug' => 'apoios']);

        Livewire::test(CreatePergunta::class)
            ->fillForm(['pergunta' => 'Como posso renovar os empréstimos?'])
            ->fillForm(['pergunta_existente' => $this->renovar->id, 'temas_destino' => [$temaProprio->id]])
            // A resposta e o slug não são pedidos: a pergunta já existe.
            ->assertFormFieldHidden('resposta')
            ->call('create')
            ->assertHasNoFormErrors()
            // Não é do seu serviço, por isso não a pode editar: volta à lista.
            ->assertRedirect(ListPerguntas::getUrl());

        $this->assertSame(3, Pergunta::count());
        $this->assertTrue($this->renovar->temas()->whereKey($temaProprio)->exists());
        // Continua a ser da Biblioteca, que é quem mantém a resposta.
        $this->assertSame($this->biblioteca->id, $this->renovar->fresh()->servico_id);
    }

    public function test_uma_pergunta_sem_responsavel_passa_a_ser_de_quem_a_usa(): void
    {
        $this->actingAs($this->editorDe($this->acaoSocial));
        $temaProprio = Tema::create(['servico_id' => $this->acaoSocial->id, 'nome' => 'Exames', 'slug' => 'exames']);
        $rascunho = Pergunta::create([
            'pergunta' => 'Existe época especial de exames?',
            'slug' => 'existe-epoca-especial-de-exames',
            'resposta' => '<p>Resposta de exemplo.</p>',
        ]);

        Livewire::test(CreatePergunta::class)
            ->fillForm(['pergunta' => 'Há época especial de exames?'])
            ->assertSee('passa a ser do seu serviço')
            ->fillForm(['pergunta_existente' => $rascunho->id, 'temas_destino' => [$temaProprio->id]])
            ->call('create')
            ->assertHasNoFormErrors()
            // Agora é sua, por isso abre a página de edição (para a rever e publicar).
            ->assertRedirect(EditPergunta::getUrl(['record' => $rascunho]));

        $this->assertSame($this->acaoSocial->id, $rascunho->fresh()->servico_id);
        // Mudar o responsável não é uma versão nova do texto.
        $this->assertSame(1, $rascunho->versoes()->count());
    }

    public function test_se_nao_for_a_mesma_cria_uma_pergunta_nova_do_seu_servico(): void
    {
        $this->actingAs($this->editorDe($this->acaoSocial));

        Livewire::test(CreatePergunta::class)
            ->fillForm(['pergunta' => 'Como posso renovar a bolsa de estudo?'])
            ->assertFormSet(['pergunta_existente' => PerguntaForm::NOVA])
            ->fillForm(['slug' => 'como-posso-renovar-a-bolsa-de-estudo', 'resposta' => '<p>Resposta de exemplo.</p>'])
            ->call('create')
            ->assertHasNoFormErrors();

        $nova = Pergunta::firstWhere('slug', 'como-posso-renovar-a-bolsa-de-estudo');
        $this->assertSame($this->acaoSocial->id, $nova->servico_id);
        $this->assertSame(4, Pergunta::count());
    }

    public function test_mudar_o_texto_volta_a_ser_uma_pergunta_nova(): void
    {
        $this->actingAs($this->editorDe($this->acaoSocial));

        Livewire::test(CreatePergunta::class)
            ->fillForm(['pergunta' => 'Como posso renovar os empréstimos?'])
            ->fillForm(['pergunta_existente' => $this->renovar->id])
            ->fillForm(['pergunta' => 'Onde fica a reitoria?'])
            ->assertFormSet(['pergunta_existente' => PerguntaForm::NOVA])
            ->assertFormFieldVisible('resposta');
    }

    private function editorDe(Servico $servico): User
    {
        $editor = User::factory()->create();
        $editor->servicos()->attach($servico);

        return $editor;
    }

    private function pergunta(string $texto, Servico $servico, string $resposta = '<p>Resposta de exemplo.</p>'): Pergunta
    {
        return Pergunta::create([
            'servico_id' => $servico->id,
            'pergunta' => $texto,
            'slug' => str()->slug($texto),
            'resposta' => $resposta,
            'publicada' => true,
        ]);
    }
}
