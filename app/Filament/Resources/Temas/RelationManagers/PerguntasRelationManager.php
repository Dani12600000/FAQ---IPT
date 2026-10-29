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
            ->description('Use "Reordenar" para mudar a ordem das perguntas neste tema.')
            ->recordTitleAttribute('pergunta')
            ->reorderable('ordem')
            ->recordUrl(fn (Pergunta $record): string => PerguntaResource::getUrl('edit', ['record' => $record]))
            ->columns([
                TextColumn::make('pergunta')
                    ->label('Pergunta')
                    ->wrap(),
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
