<?php

namespace App\Filament\Resources\Perguntas\RelationManagers;

use App\Filament\Resources\Perguntas\PerguntaResource;
use App\Models\Pergunta;
use App\Models\PerguntaVersao;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Contracts\View\View;

/**
 * Histórico do texto da pergunta e da resposta (as versões são criadas pelo modelo Pergunta).
 */
class VersoesRelationManager extends RelationManager
{
    protected static string $relationship = 'versoes';

    protected static ?string $title = 'Histórico de versões';

    protected static ?string $modelLabel = 'versão';

    protected static ?string $pluralModelLabel = 'versões';

    public function table(Table $table): Table
    {
        return $table
            ->description('Sempre que a pergunta ou a resposta muda, fica aqui uma cópia. Repor uma versão antiga cria uma versão nova, por isso nada se perde.')
            ->recordTitleAttribute('numero')
            ->defaultSort('numero', 'desc')
            ->columns([
                TextColumn::make('numero')
                    ->label('Versão')
                    ->prefix('#'),
                TextColumn::make('pergunta')
                    ->label('Pergunta')
                    ->wrap(),
                TextColumn::make('autor.name')
                    ->label('Autor')
                    // Versões criadas fora do backoffice (seeders, migrações).
                    ->placeholder('Sistema'),
                TextColumn::make('nota')
                    ->label('Nota')
                    ->placeholder('—'),
                TextColumn::make('created_at')
                    ->label('Data')
                    ->dateTime(),
            ])
            ->recordActions([
                Action::make('ver')
                    ->label('Ver')
                    ->icon(Heroicon::OutlinedEye)
                    ->color('gray')
                    ->modalHeading(fn (PerguntaVersao $record): string => "Versão {$record->numero}")
                    ->modalWidth('3xl')
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Fechar')
                    ->modalContent(fn (PerguntaVersao $record): View => view('filament.pre-visualizacao-pergunta', [
                        'pergunta' => $record->pergunta,
                        'resposta' => $record->respostaSegura(),
                    ])),
                Action::make('repor')
                    ->label('Repor')
                    ->icon(Heroicon::OutlinedArrowUturnLeft)
                    ->requiresConfirmation()
                    ->modalHeading(fn (PerguntaVersao $record): string => "Repor a versão {$record->numero}?")
                    ->modalDescription('A pergunta e a resposta voltam a este texto. O texto atual continua guardado no histórico.')
                    ->modalSubmitActionLabel('Repor')
                    // A versão mais recente é o texto atual, não há nada a repor.
                    ->hidden(fn (PerguntaVersao $record): bool => $record->numero === (int) $this->getOwnerRecord()->versoes()->max('numero'))
                    ->action(function (PerguntaVersao $record): void {
                        /** @var Pergunta $pergunta */
                        $pergunta = $this->getOwnerRecord();
                        $pergunta->reporVersao($record);

                        Notification::make()
                            ->success()
                            ->title("Versão {$record->numero} reposta")
                            ->send();

                        // Abre a página de novo para o formulário mostrar o texto reposto.
                        $this->redirect(PerguntaResource::getUrl('edit', ['record' => $pergunta]));
                    }),
            ]);
    }
}
