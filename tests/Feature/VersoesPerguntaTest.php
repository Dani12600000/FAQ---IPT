<?php

namespace Tests\Feature;

use App\Filament\Resources\Perguntas\Pages\EditPergunta;
use App\Filament\Resources\Perguntas\RelationManagers\VersoesRelationManager;
use App\Models\Pergunta;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class VersoesPerguntaTest extends TestCase
{
    use RefreshDatabase;

    public function test_guarda_a_versao_1_ao_criar_com_o_autor(): void
    {
        $autor = User::factory()->admin()->create();
        $this->actingAs($autor);

        $pergunta = $this->pergunta();

        $this->assertSame(1, $pergunta->versoes()->count());
        $this->assertSame(1, $pergunta->versoes()->first()->numero);
        $this->assertTrue($pergunta->versoes()->first()->autor->is($autor));
    }

    public function test_so_cria_versao_quando_o_texto_muda(): void
    {
        $pergunta = $this->pergunta();

        $pergunta->update(['publicada' => true, 'slug' => 'outro-slug']);
        $this->assertSame(1, $pergunta->versoes()->count());

        $pergunta->update(['resposta' => '<p>Resposta nova.</p>']);
        $this->assertSame(2, $pergunta->versoes()->count());
        $this->assertSame('<p>Resposta nova.</p>', $pergunta->versoes()->first()->resposta);
    }

    public function test_repor_uma_versao_cria_uma_versao_nova_sem_apagar_nada(): void
    {
        $pergunta = $this->pergunta();
        $pergunta->update(['pergunta' => 'Texto mudado?', 'resposta' => '<p>Resposta nova.</p>']);

        $pergunta->reporVersao($pergunta->versoes()->firstWhere('numero', 1));

        $pergunta->refresh();
        $this->assertSame('Como renovo um empréstimo?', $pergunta->pergunta);
        $this->assertSame('<p>Resposta antiga.</p>', $pergunta->resposta);
        // A cópia em texto simples (usada na pesquisa) também volta atrás.
        $this->assertSame('Resposta antiga.', $pergunta->resposta_texto);
        $this->assertSame([3, 2, 1], $pergunta->versoes()->pluck('numero')->all());
        $this->assertSame('Reposição da versão 1', $pergunta->versoes()->first()->nota);
    }

    public function test_o_historico_aparece_ao_editar_e_permite_repor(): void
    {
        $this->actingAs(User::factory()->admin()->create());
        $pergunta = $this->pergunta();
        $pergunta->update(['resposta' => '<p>Resposta nova.</p>']);
        [$atual, $antiga] = $pergunta->versoes()->get()->all();

        Livewire::test(VersoesRelationManager::class, ['ownerRecord' => $pergunta, 'pageClass' => EditPergunta::class])
            ->assertCanSeeTableRecords([$atual, $antiga])
            ->mountTableAction('ver', $antiga)
            ->assertMountedActionModalSee('Resposta antiga.');

        Livewire::test(VersoesRelationManager::class, ['ownerRecord' => $pergunta, 'pageClass' => EditPergunta::class])
            // A versão mais recente é o texto atual.
            ->assertTableActionHidden('repor', $atual)
            ->callTableAction('repor', $antiga);

        $this->assertSame('<p>Resposta antiga.</p>', $pergunta->fresh()->resposta);
    }

    private function pergunta(): Pergunta
    {
        return Pergunta::create([
            'pergunta' => 'Como renovo um empréstimo?',
            'slug' => 'como-renovo-um-emprestimo',
            'resposta' => '<p>Resposta antiga.</p>',
        ]);
    }
}
