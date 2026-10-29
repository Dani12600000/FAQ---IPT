<?php

namespace App\Filament\Resources\Categorias\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class CategoriasTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('nome')
            ->columns([
                TextColumn::make('nome')
                    ->label('Nome')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('servicos.nome')
                    ->label('Serviços')
                    ->badge(),
                TextColumn::make('temas_count')
                    ->label('Temas')
                    ->counts('temas'),
                ToggleColumn::make('ativo')
                    ->label('Ativa'),
                TextColumn::make('updated_at')
                    ->label('Atualizada em')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('servicos')
                    ->label('Serviço')
                    ->relationship('servicos', 'nome')
                    ->preload(),
                TernaryFilter::make('ativo')
                    ->label('Ativa'),
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
