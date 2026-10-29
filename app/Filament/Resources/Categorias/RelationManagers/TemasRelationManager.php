<?php

namespace App\Filament\Resources\Categorias\RelationManagers;

use App\Filament\Resources\Temas\TemaResource;
use App\Models\Tema;
use Filament\Actions\AttachAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DetachAction;
use Filament\Actions\DetachBulkAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class TemasRelationManager extends RelationManager
{
    protected static string $relationship = 'temas';

    protected static ?string $title = 'Temas desta categoria';

    protected static ?string $modelLabel = 'tema';

    protected static ?string $pluralModelLabel = 'temas';

    public function table(Table $table): Table
    {
        return $table
            ->description('Para mudar a ordem dos temas nesta categoria, carregue no botão "Reordenar registos" (as setas) e arraste as linhas.')
            ->recordTitleAttribute('nome')
            ->reorderable('ordem')
            ->recordUrl(fn (Tema $record): string => TemaResource::getUrl('edit', ['record' => $record]))
            ->columns([
                TextColumn::make('nome')
                    ->label('Nome'),
                IconColumn::make('ativo')
                    ->label('Ativo')
                    ->boolean(),
            ])
            ->headerActions([
                AttachAction::make()
                    ->label('Adicionar tema')
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
                        ->label('Retirar selecionados'),
                ]),
            ]);
    }
}
