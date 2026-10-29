<?php

namespace App\Filament\Resources\Servicos\RelationManagers;

use App\Filament\Resources\Categorias\CategoriaResource;
use App\Models\Categoria;
use Filament\Actions\AttachAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DetachAction;
use Filament\Actions\DetachBulkAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class CategoriasRelationManager extends RelationManager
{
    protected static string $relationship = 'categorias';

    protected static ?string $title = 'Categorias deste serviço';

    protected static ?string $modelLabel = 'categoria';

    protected static ?string $pluralModelLabel = 'categorias';

    public function table(Table $table): Table
    {
        return $table
            ->description('Use "Reordenar" para mudar a ordem das categorias neste serviço.')
            ->recordTitleAttribute('nome')
            ->reorderable('ordem')
            ->recordUrl(fn (Categoria $record): string => CategoriaResource::getUrl('edit', ['record' => $record]))
            ->columns([
                TextColumn::make('nome')
                    ->label('Nome'),
                IconColumn::make('ativo')
                    ->label('Ativa')
                    ->boolean(),
            ])
            ->headerActions([
                AttachAction::make()
                    ->label('Adicionar categoria')
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
