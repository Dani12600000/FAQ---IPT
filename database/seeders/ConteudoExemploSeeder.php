<?php

namespace Database\Seeders;

use App\Models\Categoria;
use App\Models\Pergunta;
use App\Models\Servico;
use App\Models\Tema;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;

/**
 * Conteúdo de exemplo para demonstrar o FAQ.
 *
 * Não é informação oficial do IPT: todas as respostas começam por
 * "Resposta de exemplo:" e não indicam prazos, valores nem regras reais.
 *
 * Pode correr-se várias vezes: os registos são encontrados pelo slug e
 * atualizados, e as ligações são acertadas (incluindo a ordem).
 */
class ConteudoExemploSeeder extends Seeder
{
    /**
     * Serviços, pela ordem da página inicial, com as suas categorias por ordem.
     *
     * @var array<string, array{nome: string, descricao: string, categorias: list<string>}>
     */
    private const SERVICOS = [
        'servicos-academicos' => [
            'nome' => 'Serviços Académicos',
            'descricao' => 'Matrículas, inscrições, propinas e documentos.',
            'categorias' => ['candidaturas-e-matriculas', 'propinas-e-pagamentos', 'certidoes-e-documentos'],
        ],
        'acao-social' => [
            'nome' => 'Ação Social',
            'descricao' => 'Bolsas, alojamento e alimentação.',
            'categorias' => ['bolsas-de-estudo', 'alojamento', 'alimentacao', 'propinas-e-pagamentos'],
        ],
        'biblioteca' => [
            'nome' => 'Biblioteca',
            'descricao' => 'Empréstimos e recursos digitais.',
            'categorias' => ['emprestimos', 'recursos-digitais'],
        ],
        'internacional' => [
            'nome' => 'Internacional',
            'descricao' => 'Mobilidade e estudantes internacionais.',
            'categorias' => ['mobilidade-erasmus', 'candidaturas-e-matriculas', 'alojamento', 'bolsas-de-estudo', 'certidoes-e-documentos'],
        ],
    ];

    /**
     * Categorias com os seus temas por ordem.
     *
     * @var array<string, array{nome: string, temas: list<string>}>
     */
    private const CATEGORIAS = [
        'candidaturas-e-matriculas' => [
            'nome' => 'Candidaturas e matrículas',
            'temas' => ['primeira-matricula', 'inscricao-em-unidades-curriculares', 'estudantes-internacionais'],
        ],
        'propinas-e-pagamentos' => [
            'nome' => 'Propinas e pagamentos',
            'temas' => ['pagamento-de-propinas', 'isencoes-e-reducoes'],
        ],
        'certidoes-e-documentos' => [
            'nome' => 'Certidões e documentos',
            'temas' => ['pedido-de-certidoes'],
        ],
        'bolsas-de-estudo' => [
            'nome' => 'Bolsas de estudo',
            'temas' => ['candidatura-a-bolsa', 'isencoes-e-reducoes'],
        ],
        'alojamento' => [
            'nome' => 'Alojamento',
            'temas' => ['residencias'],
        ],
        'alimentacao' => [
            'nome' => 'Alimentação',
            'temas' => ['cantinas-e-bares'],
        ],
        'emprestimos' => [
            'nome' => 'Empréstimos',
            'temas' => ['requisicao-e-renovacao'],
        ],
        'recursos-digitais' => [
            'nome' => 'Recursos digitais',
            'temas' => ['acesso-remoto'],
        ],
        'mobilidade-erasmus' => [
            'nome' => 'Mobilidade Erasmus+',
            'temas' => ['preparar-a-partida', 'estudantes-internacionais'],
        ],
    ];

    /**
     * Temas com as suas perguntas por ordem.
     *
     * @var array<string, array{nome: string, perguntas: list<string>}>
     */
    private const TEMAS = [
        'primeira-matricula' => [
            'nome' => 'Primeira matrícula',
            'perguntas' => ['como-faco-a-primeira-matricula', 'que-documentos-preciso-para-me-matricular'],
        ],
        'inscricao-em-unidades-curriculares' => [
            'nome' => 'Inscrição em unidades curriculares',
            'perguntas' => ['como-me-inscrevo-nas-unidades-curriculares', 'posso-alterar-a-inscricao-depois-de-a-submeter', 'como-sao-reconhecidas-as-unidades-curriculares-feitas-em-erasmus'],
        ],
        'estudantes-internacionais' => [
            'nome' => 'Estudantes internacionais',
            'perguntas' => ['como-peco-o-reconhecimento-de-estudos-feitos-no-estrangeiro', 'que-documentos-preciso-para-me-matricular', 'como-peco-uma-certidao-de-matricula'],
        ],
        'pagamento-de-propinas' => [
            'nome' => 'Pagamento de propinas',
            'perguntas' => ['quais-sao-as-formas-de-pagamento-das-propinas', 'posso-pagar-as-propinas-em-prestacoes', 'o-que-acontece-se-pagar-uma-propina-fora-do-prazo', 'a-bolsa-de-estudo-cobre-a-propina'],
        ],
        'isencoes-e-reducoes' => [
            'nome' => 'Isenções e reduções',
            'perguntas' => ['quem-pode-pedir-isencao-ou-reducao-de-propinas', 'como-me-candidato-a-uma-bolsa-de-estudo', 'posso-pagar-as-propinas-em-prestacoes'],
        ],
        'pedido-de-certidoes' => [
            'nome' => 'Pedido de certidões',
            'perguntas' => ['como-peco-uma-certidao-de-matricula', 'quanto-tempo-demora-a-emissao-de-uma-certidao'],
        ],
        'candidatura-a-bolsa' => [
            'nome' => 'Candidatura a bolsa',
            'perguntas' => ['como-me-candidato-a-uma-bolsa-de-estudo', 'a-bolsa-de-estudo-cobre-a-propina'],
        ],
        'residencias' => [
            'nome' => 'Residências',
            'perguntas' => ['como-me-candidato-a-uma-residencia', 'posso-ficar-na-residencia-durante-as-ferias'],
        ],
        'cantinas-e-bares' => [
            'nome' => 'Cantinas e bares',
            'perguntas' => ['quem-pode-usar-as-cantinas'],
        ],
        'requisicao-e-renovacao' => [
            'nome' => 'Requisição e renovação',
            'perguntas' => ['quantos-livros-posso-requisitar', 'como-renovo-um-emprestimo'],
        ],
        'acesso-remoto' => [
            'nome' => 'Acesso remoto',
            'perguntas' => ['como-acedo-as-bases-de-dados-fora-do-campus'],
        ],
        'preparar-a-partida' => [
            'nome' => 'Preparar a partida',
            'perguntas' => ['que-documentos-preciso-antes-de-partir-em-erasmus', 'como-sao-reconhecidas-as-unidades-curriculares-feitas-em-erasmus', 'como-me-candidato-a-uma-residencia'],
        ],
    ];

    /**
     * Perguntas e respostas (HTML, como sai do editor do backoffice).
     *
     * @var array<string, array{pergunta: string, resposta: string, publicada?: bool}>
     */
    private const PERGUNTAS = [
        'como-faco-a-primeira-matricula' => [
            'pergunta' => 'Como faço a primeira matrícula?',
            'resposta' => '<p><strong>Resposta de exemplo:</strong> aqui ficará a explicação oficial do processo de matrícula. Uma resposta deste tipo costuma indicar os passos por ordem:</p><ol><li>Confirmar a colocação.</li><li>Preparar os documentos pedidos.</li><li>Submeter a matrícula no portal indicado pelos Serviços Académicos.</li></ol>',
        ],
        'que-documentos-preciso-para-me-matricular' => [
            'pergunta' => 'Que documentos preciso para me matricular?',
            'resposta' => '<p><strong>Resposta de exemplo:</strong> aqui ficará a lista oficial de documentos. Esta pergunta aparece em dois temas (Primeira matrícula e Estudantes internacionais), mas a resposta é escrita uma só vez.</p>',
        ],
        'como-me-inscrevo-nas-unidades-curriculares' => [
            'pergunta' => 'Como me inscrevo nas unidades curriculares?',
            'resposta' => '<p><strong>Resposta de exemplo:</strong> aqui ficará a explicação de como e onde se faz a inscrição nas unidades curriculares de cada ano.</p>',
        ],
        'posso-alterar-a-inscricao-depois-de-a-submeter' => [
            'pergunta' => 'Posso alterar a inscrição depois de a submeter?',
            'resposta' => '<p><strong>Resposta de exemplo:</strong> aqui ficará a indicação de se, quando e como é possível alterar uma inscrição já submetida.</p>',
        ],
        'como-peco-o-reconhecimento-de-estudos-feitos-no-estrangeiro' => [
            'pergunta' => 'Como peço o reconhecimento de estudos feitos no estrangeiro?',
            'resposta' => '<p><strong>Resposta de exemplo:</strong> aqui ficará o procedimento oficial de reconhecimento, com a indicação dos documentos e do serviço responsável.</p>',
        ],
        'quais-sao-as-formas-de-pagamento-das-propinas' => [
            'pergunta' => 'Quais são as formas de pagamento das propinas?',
            'resposta' => '<p><strong>Resposta de exemplo:</strong> aqui ficarão os meios de pagamento aceites. Por exemplo:</p><ul><li>Referência multibanco.</li><li>Outro meio indicado pelos serviços.</li></ul>',
        ],
        'posso-pagar-as-propinas-em-prestacoes' => [
            'pergunta' => 'Posso pagar as propinas em prestações?',
            'resposta' => '<p><strong>Resposta de exemplo:</strong> aqui ficará a informação oficial sobre o pagamento faseado. Esta pergunta aparece nos temas Pagamento de propinas e Isenções e reduções.</p>',
        ],
        'o-que-acontece-se-pagar-uma-propina-fora-do-prazo' => [
            'pergunta' => 'O que acontece se pagar uma propina fora do prazo?',
            'resposta' => '<p><strong>Resposta de exemplo:</strong> aqui ficarão as consequências de um pagamento em atraso, tal como definidas no regulamento em vigor.</p>',
        ],
        'quem-pode-pedir-isencao-ou-reducao-de-propinas' => [
            'pergunta' => 'Quem pode pedir isenção ou redução de propinas?',
            'resposta' => '<p><strong>Resposta de exemplo:</strong> aqui ficarão as situações previstas no regulamento e a forma de fazer o pedido.</p>',
        ],
        'como-peco-uma-certidao-de-matricula' => [
            'pergunta' => 'Como peço uma certidão de matrícula?',
            'resposta' => '<p><strong>Resposta de exemplo:</strong> aqui ficará a explicação de onde e como se pede uma certidão de matrícula.</p>',
        ],
        'quanto-tempo-demora-a-emissao-de-uma-certidao' => [
            'pergunta' => 'Quanto tempo demora a emissão de uma certidão?',
            'resposta' => '<p><strong>Resposta de exemplo:</strong> aqui ficará o tempo de emissão indicado pelos Serviços Académicos.</p>',
        ],
        'como-me-candidato-a-uma-bolsa-de-estudo' => [
            'pergunta' => 'Como me candidato a uma bolsa de estudo?',
            'resposta' => '<p><strong>Resposta de exemplo:</strong> aqui ficarão os passos da candidatura e a ligação para a plataforma onde é submetida.</p>',
        ],
        'a-bolsa-de-estudo-cobre-a-propina' => [
            'pergunta' => 'A bolsa de estudo cobre a propina?',
            'resposta' => '<p><strong>Resposta de exemplo:</strong> aqui ficará a relação entre a bolsa e o pagamento da propina. Esta pergunta aparece nos Serviços Académicos e na Ação Social.</p>',
        ],
        'como-me-candidato-a-uma-residencia' => [
            'pergunta' => 'Como me candidato a uma residência?',
            'resposta' => '<p><strong>Resposta de exemplo:</strong> aqui ficará o processo de candidatura às residências. Esta pergunta aparece na Ação Social e no Internacional.</p>',
        ],
        'posso-ficar-na-residencia-durante-as-ferias' => [
            'pergunta' => 'Posso ficar na residência durante as férias?',
            'resposta' => '<p><strong>Resposta de exemplo:</strong> aqui ficarão as regras de utilização das residências fora do período letivo.</p>',
        ],
        'quem-pode-usar-as-cantinas' => [
            'pergunta' => 'Quem pode usar as cantinas?',
            'resposta' => '<p><strong>Resposta de exemplo:</strong> aqui ficará quem pode usar as cantinas e os bares e como funciona o acesso.</p>',
        ],
        'quantos-livros-posso-requisitar' => [
            'pergunta' => 'Quantos livros posso requisitar?',
            'resposta' => '<p><strong>Resposta de exemplo:</strong> aqui ficará o número de obras e o período de empréstimo para cada tipo de utilizador.</p>',
        ],
        'como-renovo-um-emprestimo' => [
            'pergunta' => 'Como renovo um empréstimo?',
            'resposta' => '<p><strong>Resposta de exemplo:</strong> aqui ficará a explicação de como renovar um empréstimo, presencialmente ou online.</p>',
        ],
        'como-acedo-as-bases-de-dados-fora-do-campus' => [
            'pergunta' => 'Como acedo às bases de dados fora do campus?',
            'resposta' => '<p><strong>Resposta de exemplo:</strong> aqui ficarão as instruções de acesso remoto aos recursos digitais da biblioteca.</p>',
        ],
        'que-documentos-preciso-antes-de-partir-em-erasmus' => [
            'pergunta' => 'Que documentos preciso antes de partir em Erasmus+?',
            'resposta' => '<p><strong>Resposta de exemplo:</strong> aqui ficará a lista de documentos a tratar antes da mobilidade, como o acordo de estudos.</p>',
        ],
        'como-sao-reconhecidas-as-unidades-curriculares-feitas-em-erasmus' => [
            'pergunta' => 'Como são reconhecidas as unidades curriculares feitas em Erasmus+?',
            'resposta' => '<p><strong>Resposta de exemplo:</strong> aqui ficará a explicação do reconhecimento das unidades curriculares feitas durante a mobilidade.</p>',
        ],
        // Rascunho sem tema, para demonstrar o filtro "Sem tema" e o estado "por publicar".
        'existe-epoca-especial-de-exames' => [
            'pergunta' => 'Existe época especial de exames?',
            'resposta' => '<p><strong>Resposta de exemplo:</strong> rascunho por rever e ainda sem tema.</p>',
            'publicada' => false,
        ],
    ];

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $ordem = 0;
        $servicos = collect(self::SERVICOS)->map(fn (array $dados, string $slug) => Servico::updateOrCreate(
            ['slug' => $slug],
            ['nome' => $dados['nome'], 'descricao' => $dados['descricao'], 'ordem' => ++$ordem, 'ativo' => true],
        ));

        $responsaveis = $this->responsaveis();
        $servicoDe = fn (string $tipo, string $slug): ?int => $servicos->get($responsaveis[$tipo][$slug] ?? '')?->id;

        $perguntas = collect(self::PERGUNTAS)->map(fn (array $dados, string $slug) => Pergunta::updateOrCreate(
            ['slug' => $slug],
            [
                'servico_id' => $servicoDe('perguntas', $slug),
                'pergunta' => $dados['pergunta'],
                'resposta' => $dados['resposta'],
                'publicada' => $dados['publicada'] ?? true,
            ],
        ));

        $temas = collect(self::TEMAS)->map(function (array $dados, string $slug) use ($perguntas, $servicoDe) {
            $tema = Tema::updateOrCreate(['slug' => $slug], ['servico_id' => $servicoDe('temas', $slug), 'nome' => $dados['nome'], 'ativo' => true]);
            $tema->perguntas()->sync($this->comOrdem($perguntas, $dados['perguntas']));

            return $tema;
        });

        $categorias = collect(self::CATEGORIAS)->map(function (array $dados, string $slug) use ($temas, $servicoDe) {
            $categoria = Categoria::updateOrCreate(['slug' => $slug], ['servico_id' => $servicoDe('categorias', $slug), 'nome' => $dados['nome'], 'ativo' => true]);
            $categoria->temas()->sync($this->comOrdem($temas, $dados['temas']));

            return $categoria;
        });

        foreach ($servicos as $slug => $servico) {
            $servico->categorias()->sync($this->comOrdem($categorias, self::SERVICOS[$slug]['categorias']));
        }
    }

    /**
     * Serviço responsável por cada categoria, tema e pergunta: o primeiro serviço (pela ordem
     * da página inicial) onde aparece. O que aparece também noutros serviços demonstra a
     * reutilização: por exemplo, "A bolsa de estudo cobre a propina?" é dos Serviços Académicos
     * e é usada pela Ação Social.
     *
     * @return array<string, array<string, string>> [tipo => [slug => slug do serviço]]
     */
    private function responsaveis(): array
    {
        $responsaveis = [
            'categorias' => [],
            'temas' => [],
            // O rascunho sem tema não está em nenhum serviço, por isso é indicado aqui.
            'perguntas' => ['existe-epoca-especial-de-exames' => 'servicos-academicos'],
        ];

        foreach (self::SERVICOS as $servico => $dados) {
            foreach ($dados['categorias'] as $categoria) {
                $responsaveis['categorias'][$categoria] ??= $servico;

                foreach (self::CATEGORIAS[$categoria]['temas'] as $tema) {
                    $responsaveis['temas'][$tema] ??= $servico;

                    foreach (self::TEMAS[$tema]['perguntas'] as $pergunta) {
                        $responsaveis['perguntas'][$pergunta] ??= $servico;
                    }
                }
            }
        }

        return $responsaveis;
    }

    /**
     * Transforma uma lista de slugs no formato do sync(): [id => ['ordem' => posição]].
     *
     * @param  Collection<string, Model>  $registos
     * @param  list<string>  $slugs
     * @return array<int, array{ordem: int}>
     */
    private function comOrdem(Collection $registos, array $slugs): array
    {
        return collect($slugs)
            ->mapWithKeys(fn (string $slug, int $i) => [$registos[$slug]->id => ['ordem' => $i + 1]])
            ->all();
    }
}
