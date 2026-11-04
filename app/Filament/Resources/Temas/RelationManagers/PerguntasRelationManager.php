<?php

namespace App\Filament\Resources\Temas\RelationManagers;

use App\Filament\Resources\Perguntas\PerguntaResource;
use App\Models\Pergunta;
use Filament\Actions\AttachAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DetachAction;
use Filament\Actions\DetachBulkAction;
use Filament\Resources\RelationManagers\RelationManager;
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
