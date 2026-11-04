<?php

namespace App\Filament\Resources\Temas\Tables;

use App\Models\Tema;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class TemasTable
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
                TextColumn::make('servico.nome')
                    ->label('Responsável')
                    ->placeholder('Sem responsável'),
                TextColumn::make('categorias.nome')
                    ->label('Categorias')
                    ->badge(),
                TextColumn::make('perguntas_count')
                    ->label('Perguntas')
                    ->counts('perguntas'),
                ToggleColumn::make('ativo')
                    ->label('Ativo')
                    // Um tema de outro serviço aparece na lista, mas só o responsável o altera.
                    ->disabled(fn (Tema $record): bool => auth()->user()->cannot('update', $record)),
                TextColumn::make('updated_at')
                    ->label('Atualizado em')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('servico')
                    ->label('Responsável')
                    ->relationship('servico', 'nome')
                    ->preload(),
                SelectFilter::make('categorias')
                    ->label('Categoria')
                    ->relationship('categorias', 'nome')
                    ->preload(),
                TernaryFilter::make('ativo')
                    ->label('Ativo'),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->authorizeIndividualRecords('delete'),
                ]),
            ]);
    }
}
