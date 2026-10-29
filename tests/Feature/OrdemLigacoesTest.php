<?php

namespace Tests\Feature;

use App\Models\Categoria;
use App\Models\Pergunta;
use App\Models\Servico;
use App\Models\Tema;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrdemLigacoesTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_mesma_pergunta_pode_ter_ordem_diferente_em_cada_tema(): void
    {
        $propinas = Tema::create(['nome' => 'Propinas', 'slug' => 'propinas']);
        $bolsas = Tema::create(['nome' => 'Bolsas', 'slug' => 'bolsas']);

        $prazo = $this->pergunta('Qual é o prazo?', 'qual-e-o-prazo');
        $pagar = $this->pergunta('Como pago?', 'como-pago');

        $propinas->perguntas()->attach([
            $pagar->id => ['ordem' => 2],
            $prazo->id => ['ordem' => 1],
        ]);
        $bolsas->perguntas()->attach([
            $prazo->id => ['ordem' => 2],
            $pagar->id => ['ordem' => 1],
        ]);

        $this->assertSame(['qual-e-o-prazo', 'como-pago'], $propinas->perguntas->pluck('slug')->all());
        $this->assertSame(['como-pago', 'qual-e-o-prazo'], $bolsas->perguntas->pluck('slug')->all());
    }

    public function test_categorias_e_temas_seguem_a_ordem_da_ligacao(): void
    {
        $servico = Servico::create(['nome' => 'Serviços Académicos', 'slug' => 'servicos-academicos']);
        $matriculas = Categoria::create(['nome' => 'Matrículas', 'slug' => 'matriculas']);
        $certidoes = Categoria::create(['nome' => 'Certidões', 'slug' => 'certidoes']);
        $servico->categorias()->attach([
            $matriculas->id => ['ordem' => 2],
            $certidoes->id => ['ordem' => 1],
        ]);

        $prazos = Tema::create(['nome' => 'Prazos', 'slug' => 'prazos']);
        $documentos = Tema::create(['nome' => 'Documentos', 'slug' => 'documentos']);
        $matriculas->temas()->attach([
            $prazos->id => ['ordem' => 2],
            $documentos->id => ['ordem' => 1],
        ]);

        $this->assertSame(['certidoes', 'matriculas'], $servico->categorias->pluck('slug')->all());
        $this->assertSame(['documentos', 'prazos'], $matriculas->temas->pluck('slug')->all());
        $this->assertSame(2, $matriculas->temas->last()->pivot->ordem);
    }

    public function test_as_relacoes_inversas_mostram_onde_cada_item_aparece(): void
    {
        $pergunta = $this->pergunta('Como peço uma certidão?', 'como-peco-uma-certidao');
        $temaA = Tema::create(['nome' => 'Certidões', 'slug' => 'certidoes']);
        $temaB = Tema::create(['nome' => 'Diplomados', 'slug' => 'diplomados']);
        $temaA->perguntas()->attach($pergunta, ['ordem' => 1]);
        $temaB->perguntas()->attach($pergunta, ['ordem' => 3]);

        $this->assertEqualsCanonicalizing(['certidoes', 'diplomados'], $pergunta->temas->pluck('slug')->all());
    }

    private function pergunta(string $texto, string $slug): Pergunta
    {
        return Pergunta::create([
            'pergunta' => $texto,
            'slug' => $slug,
            'resposta' => '<p>Resposta de exemplo.</p>',
            'publicada' => true,
        ]);
    }
}
