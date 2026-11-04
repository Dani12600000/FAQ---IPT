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
            ->description('Para mudar a ordem das categorias neste serviço, carregue no botão "Reordenar registos" (as setas) e arraste as linhas.')
            ->recordTitleAttribute('nome')
            ->reorderable('ordem')
            // Uma categoria de outro serviço pode ser usada aqui, mas só o responsável a abre para editar.
            ->recordUrl(fn (Categoria $record): ?string => auth()->user()->can('update', $record) ? CategoriaResource::getUrl('edit', ['record' => $record]) : null)
            ->columns([
                TextColumn::make('nome')
                    ->label('Nome'),
                TextColumn::make('servico.nome')
                    ->label('Responsável')
                    ->placeholder('Sem responsável'),
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
