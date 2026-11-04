<?php

namespace App\Filament\Resources\Temas\RelationManagers;

use App\Filament\Resources\Perguntas\PerguntaResource;
use App\Models\Pergunta;
use Filament\Actions\Action;
use Filament\Actions\AttachAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DetachAction;
use Filament\Actions\DetachBulkAction;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class PerguntasRelationManager extends RelationManager
{
    protected static string $relationship = 'perguntas';

    protected static ?string $title = 'Perguntas deste tema';

    protected static ?string $modelLabel = 'pergunta';

    protected static ?string $pluralModelLabel = 'perguntas';

    public function table(Table $table): Table
    {
        return $table
            ->description('Para mudar a ordem das perguntas neste tema, carregue no botão "Reordenar registos" (as setas) e arraste as linhas.')
            ->recordTitleAttribute('pergunta')
            // Nas opções de "Adicionar pergunta" vê-se de que serviço é cada uma.
            ->recordTitle(fn (Pergunta $record): string => $record->servico ? "{$record->pergunta} ({$record->servico->nome})" : $record->pergunta)
            ->reorderable('ordem')
            // Uma pergunta de outro serviço pode ser usada aqui, mas só o responsável a abre para editar.
            ->recordUrl(fn (Pergunta $record): ?string => auth()->user()->can('update', $record) ? PerguntaResource::getUrl('edit', ['record' => $record]) : null)
            ->columns([
                TextColumn::make('pergunta')
                    ->label('Pergunta')
                    ->description(fn (Pergunta $record): ?string => $record->origem ? "Versão própria de «{$record->origem->pergunta}»" : null)
                    ->wrap(),
                TextColumn::make('servico.nome')
                    ->label('Responsável')
                    ->placeholder('Sem responsável'),
                IconColumn::make('publicada')
                    ->label('Publicada')
                    ->boolean(),
            ])
            ->headerActions([
                AttachAction::make()
                    ->label('Adicionar pergunta')
                    ->multiple()
                    ->preloadRecordSelect(),
            ])
            ->recordActions([
                Action::make('criarVersaoPropria')
                    ->label('Criar versão própria')
                    ->icon(Heroicon::OutlinedDocumentDuplicate)
                    ->color('gray')
                    ->requiresConfirmation()
                    ->modalHeading('Criar uma versão própria desta pergunta?')
                    ->modalDescription(fn (): string => 'Fica uma cópia da pergunta e da resposta, de '.$this->getOwnerRecord()->servico->nome.', '
                        .'no lugar da original nos temas deste serviço. A original continua igual para os outros serviços. '
                        .'Depois pode alterar a cópia, compará-la com a original e trazer as alterações dela.')
                    ->modalSubmitActionLabel('Criar versão própria')
                    // Só para perguntas de outro serviço (as do serviço do tema editam-se diretamente).
                    ->visible(fn (Pergunta $record): bool => $this->getOwnerRecord()->servico_id !== null
                        && $record->servico_id !== $this->getOwnerRecord()->servico_id)
                    ->action(function (Pergunta $record): void {
                        $copia = $record->derivarPara($this->getOwnerRecord()->servico);

                        Notification::make()
                            ->success()
                            ->title('Versão própria criada')
                            ->body('Já pode alterar a pergunta e a resposta. A original não mudou.')
                            ->send();

                        $this->redirect(PerguntaResource::getUrl('edit', ['record' => $copia]));
                    }),
                DetachAction::make()
                    ->label('Retirar'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DetachBulkAction::make()
                        ->label('Retirar selecionadas'),
                ]),
            ]);
    }
}
