<?php

namespace Tests\Feature;

use App\Filament\Resources\Categorias\Pages\EditCategoria;
use App\Filament\Resources\Categorias\RelationManagers\TemasRelationManager;
use App\Filament\Resources\Servicos\Pages\EditServico;
use App\Filament\Resources\Servicos\RelationManagers\CategoriasRelationManager;
use App\Filament\Resources\Temas\Pages\EditTema;
use App\Filament\Resources\Temas\RelationManagers\PerguntasRelationManager;
use App\Models\Categoria;
use App\Models\Pergunta;
use App\Models\Servico;
use App\Models\Tema;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class BackofficeTest extends TestCase
{
    use RefreshDatabase;

    public function test_quem_nao_tem_sessao_e_enviado_para_o_login(): void
    {
        $this->get('/admin/perguntas')->assertRedirect('/admin/login');
    }

    public function test_o_editor_gere_o_conteudo(): void
    {
        $this->actingAs(User::factory()->create());
        [$servico, $categoria, $tema, $pergunta] = $this->conteudoLigado();

        foreach (['servicos', 'categorias', 'temas', 'perguntas'] as $recurso) {
            $this->get("/admin/{$recurso}")->assertOk();
            $this->get("/admin/{$recurso}/create")->assertOk();
        }

        $this->get("/admin/servicos/{$servico->id}/edit")->assertOk();
        $this->get("/admin/categorias/{$categoria->id}/edit")->assertOk();
        $this->get("/admin/temas/{$tema->id}/edit")->assertOk();
        $this->get("/admin/perguntas/{$pergunta->id}/edit")->assertOk();

        // Os relation managers carregam depois da página, por isso são testados à parte.
        Livewire::test(CategoriasRelationManager::class, ['ownerRecord' => $servico, 'pageClass' => EditServico::class])
            ->assertOk()
            ->assertCanSeeTableRecords([$categoria]);
        Livewire::test(TemasRelationManager::class, ['ownerRecord' => $categoria, 'pageClass' => EditCategoria::class])
            ->assertOk()
            ->assertCanSeeTableRecords([$tema]);
        Livewire::test(PerguntasRelationManager::class, ['ownerRecord' => $tema, 'pageClass' => EditTema::class])
            ->assertOk()
            ->assertCanSeeTableRecords([$pergunta]);
    }

    public function test_o_editor_nao_gere_utilizadores(): void
    {
        $this->actingAs(User::factory()->create());

        $this->get('/admin/users')->assertForbidden();
        $this->get('/admin')->assertOk()->assertDontSee('Utilizadores');
    }

    public function test_o_admin_gere_utilizadores(): void
    {
        $admin = User::factory()->admin()->create();
        $this->actingAs($admin);

        $this->get('/admin/users')->assertOk();
        $this->get('/admin/users/create')->assertOk();
        $this->get("/admin/users/{$admin->id}/edit")->assertOk();
    }

    /**
     * @return array{Servico, Categoria, Tema, Pergunta}
     */
    private function conteudoLigado(): array
    {
        $servico = Servico::create(['nome' => 'Biblioteca', 'slug' => 'biblioteca']);
        $categoria = Categoria::create(['nome' => 'Empréstimos', 'slug' => 'emprestimos']);
        $tema = Tema::create(['nome' => 'Renovações', 'slug' => 'renovacoes']);
        $pergunta = Pergunta::create([
            'pergunta' => 'Como renovo um empréstimo?',
            'slug' => 'como-renovo-um-emprestimo',
            'resposta' => '<p>Resposta de exemplo.</p>',
        ]);

        $servico->categorias()->attach($categoria);
        $categoria->temas()->attach($tema);
        $tema->perguntas()->attach($pergunta);

        return [$servico, $categoria, $tema, $pergunta];
    }
}
