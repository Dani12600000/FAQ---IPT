<?php

namespace App\Filament\Resources\Servicos\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class ServicosTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->description('A ordem desta lista é a ordem dos serviços na página inicial. Para a mudar, carregue no botão "Reordenar registos" (as setas) e arraste as linhas.')
            ->reorderable('ordem')
            ->defaultSort('ordem')
            ->columns([
                TextColumn::make('nome')
                    ->label('Nome')
                    ->searchable(),
                TextColumn::make('slug')
                    ->label('Slug')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('categorias_count')
                    ->label('Categorias')
                    ->counts('categorias'),
                ToggleColumn::make('ativo')
                    ->label('Ativo'),
                TextColumn::make('updated_at')
                    ->label('Atualizado em')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TernaryFilter::make('ativo')
                    ->label('Ativo'),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
