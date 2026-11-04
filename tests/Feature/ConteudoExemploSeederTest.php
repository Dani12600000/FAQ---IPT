<?php

namespace Tests\Feature;

use App\Models\Categoria;
use App\Models\Pergunta;
use App\Models\Servico;
use App\Models\Tema;
use Database\Seeders\ConteudoExemploSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ConteudoExemploSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_pode_correr_varias_vezes_sem_duplicar(): void
    {
        $this->seed(ConteudoExemploSeeder::class);
        $this->seed(ConteudoExemploSeeder::class);

        $this->assertSame(4, Servico::count());
        $this->assertSame(9, Categoria::count());
        $this->assertSame(12, Tema::count());
        $this->assertSame(22, Pergunta::count());
    }

    public function test_todas_as_respostas_dizem_que_sao_exemplo(): void
    {
        $this->seed(ConteudoExemploSeeder::class);

        Pergunta::all()->each(fn (Pergunta $pergunta) => $this->assertStringStartsWith(
            '<p><strong>Resposta de exemplo:</strong>',
            $pergunta->resposta,
            "A pergunta \"{$pergunta->pergunta}\" não começa por \"Resposta de exemplo:\".",
        ));
    }

    public function test_demonstra_ligacoes_partilhadas_e_ordem(): void
    {
        $this->seed(ConteudoExemploSeeder::class);

        // A mesma categoria em vários serviços, o mesmo tema em várias categorias, a mesma pergunta em vários temas.
        $this->assertGreaterThan(1, Categoria::firstWhere('slug', 'propinas-e-pagamentos')->servicos()->count());
        $this->assertGreaterThan(1, Tema::firstWhere('slug', 'isencoes-e-reducoes')->categorias()->count());
        $this->assertGreaterThan(1, Pergunta::firstWhere('slug', 'posso-pagar-as-propinas-em-prestacoes')->temas()->count());

        // A pergunta é a 2.ª num tema e a 3.ª noutro.
        $this->assertSame('posso-pagar-as-propinas-em-prestacoes', Tema::firstWhere('slug', 'pagamento-de-propinas')->perguntas[1]->slug);
        $this->assertSame('posso-pagar-as-propinas-em-prestacoes', Tema::firstWhere('slug', 'isencoes-e-reducoes')->perguntas[2]->slug);
    }

    public function test_inclui_um_rascunho_sem_tema(): void
    {
        $this->seed(ConteudoExemploSeeder::class);

        $rascunhos = Pergunta::where('publicada', false)->doesntHave('temas')->pluck('slug');

        $this->assertSame(['existe-epoca-especial-de-exames'], $rascunhos->all());
    }

    public function test_todo_o_conteudo_tem_servico_responsavel_e_ha_reutilizacao(): void
    {
        $this->seed(ConteudoExemploSeeder::class);

        $this->assertSame(0, Categoria::whereNull('servico_id')->count());
        $this->assertSame(0, Tema::whereNull('servico_id')->count());
        $this->assertSame(0, Pergunta::whereNull('servico_id')->count());

        // Uma pergunta dos Serviços Académicos usada num tema da Ação Social.
        $pergunta = Pergunta::firstWhere('slug', 'a-bolsa-de-estudo-cobre-a-propina');
        $this->assertSame('servicos-academicos', $pergunta->servico->slug);
        $this->assertTrue($pergunta->temas->contains(fn (Tema $tema) => $tema->servico->slug === 'acao-social'));
    }

    public function test_cada_pergunta_comeca_com_a_versao_1(): void
    {
        $this->seed(ConteudoExemploSeeder::class);
        $this->seed(ConteudoExemploSeeder::class);

        // Correr outra vez sem mudar o texto não cria versões novas.
        Pergunta::all()->each(fn (Pergunta $pergunta) => $this->assertSame([1], $pergunta->versoes()->pluck('numero')->all()));
    }
}
