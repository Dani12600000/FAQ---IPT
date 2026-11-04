<?php

namespace App\Filament\Resources\Perguntas\Schemas;

use App\Filament\Forms\CamposComSlug;
use App\Filament\Forms\CampoServicoResponsavel;
use App\Models\Pergunta;
use App\Models\Tema;
use App\Services\PerguntasSemelhantes;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Support\Collection;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;

class PerguntaForm
{
    /**
     * Valor da opção "é uma pergunta nova" na lista de perguntas parecidas.
     */
    public const NOVA = 'nova';

    public static function configure(Schema $schema): Schema
    {
        [$pergunta, $slug] = CamposComSlug::make('pergunta', 'Pergunta');

        // Com outro texto, as sugestões são outras: volta a ser "uma pergunta nova".
        $pergunta->afterStateUpdated(fn (Set $set) => $set('pergunta_existente', self::NOVA));

        $usaExistente = fn (Get $get): bool => static::usaPerguntaExistente($get);

        return $schema
            ->components([
                $pergunta,
                static::perguntasParecidas(),
                $slug->hidden($usaExistente),
                RichEditor::make('resposta')
                    ->label('Resposta')
                    ->required()
                    ->toolbarButtons([
                        ['bold', 'italic', 'link'],
                        ['h2', 'h3'],
                        ['bulletList', 'orderedList'],
                        ['undo', 'redo'],
                    ])
                    ->hidden($usaExistente)
                    ->columnSpanFull(),
                CampoServicoResponsavel::make()
                    ->hidden($usaExistente),
                CampoServicoResponsavel::ligacoes('temas', 'nome', 'Aparece nos temas', 'os temas')
                    ->hidden($usaExistente),
                // Só ao usar uma pergunta que já existe (ver CreatePergunta).
                Select::make('temas_destino')
                    ->label('Adicionar aos temas')
                    ->helperText('Temas dos seus serviços onde a pergunta escolhida vai passar a aparecer. A resposta continua a ser mantida pelo serviço responsável.')
                    ->options(fn (): Collection => Tema::query()->geridosPor(auth()->user())->orderBy('nome')->pluck('nome', 'id'))
                    ->multiple()
                    ->searchable()
                    ->required()
                    ->visible($usaExistente)
                    ->columnSpanFull(),
                Toggle::make('publicada')
                    ->label('Publicada')
                    ->helperText('Uma pergunta por publicar fica guardada como rascunho e não aparece no site nem na API.')
                    ->hidden($usaExistente),
            ]);
    }

    /**
     * Se a pessoa escolheu usar uma pergunta que já existe em vez de criar uma nova.
     */
    public static function usaPerguntaExistente(Get $get): bool
    {
        return is_numeric($get('pergunta_existente'));
    }

    /**
     * Ao criar, mostra as perguntas já existentes (de qualquer serviço) parecidas com a
     * que se está a escrever, com a resposta, para se poder usar uma delas.
     */
    private static function perguntasParecidas(): Section
    {
        return Section::make('Já existe uma pergunta parecida?')
            ->description('Estas perguntas já existem no FAQ. Se uma delas responde ao mesmo, use-a: a resposta fica escrita uma só vez e passa a aparecer também nos seus temas.')
            ->visible(fn (string $operation, Get $get): bool => $operation === 'create' && static::sugestoes($get('pergunta'))->isNotEmpty())
            ->schema([
                Radio::make('pergunta_existente')
                    ->hiddenLabel()
                    ->options(fn (Get $get): array => static::sugestoes($get('pergunta'))
                        ->mapWithKeys(fn (array $sugestao): array => [$sugestao['pergunta']->id => $sugestao['pergunta']->pergunta])
                        ->put(self::NOVA, 'Não, é uma pergunta nova')
                        ->all())
                    ->descriptions(fn (Get $get): array => static::sugestoes($get('pergunta'))
                        ->mapWithKeys(fn (array $sugestao): array => [$sugestao['pergunta']->id => static::descricao($sugestao)])
                        ->put(self::NOVA, 'Escreve a sua resposta e a pergunta fica a pertencer ao seu serviço.')
                        ->all())
                    ->default(self::NOVA)
                    ->required()
                    ->live(),
            ])
            ->columnSpanFull();
    }

    /**
     * @return Collection<int, array{pergunta: Pergunta, semelhanca: float}>
     */
    private static function sugestoes(?string $texto): Collection
    {
        return app(PerguntasSemelhantes::class)->para($texto);
    }

    /**
     * Serviço responsável, semelhança e início da resposta de uma sugestão.
     *
     * @param  array{pergunta: Pergunta, semelhanca: float}  $sugestao
     */
    private static function descricao(array $sugestao): HtmlString
    {
        $pergunta = $sugestao['pergunta'];

        $detalhes = collect([
            // Ver CreatePergunta::handleRecordCreation().
            $pergunta->servico?->nome ?? 'Sem serviço responsável (se a usar, passa a ser do seu serviço)',
            round($sugestao['semelhanca'] * 100).'% parecida',
            $pergunta->publicada ? null : 'rascunho',
        ])->filter()->implode(' · ');

        // e() escapa o texto, para o HTML só vir daqui.
        return new HtmlString('<strong>'.e($detalhes).'</strong><br>'.e(Str::limit($pergunta->resposta_texto, 400)));
    }
}
