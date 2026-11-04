<?php

namespace App\Filament\Resources\Categorias\Tables;

use App\Models\Categoria;
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
                TextColumn::make('servico.nome')
                    ->label('Responsável')
                    ->placeholder('Sem responsável'),
                TextColumn::make('servicos.nome')
                    ->label('Serviços')
                    ->badge(),
                TextColumn::make('temas_count')
                    ->label('Temas')
                    ->counts('temas'),
                ToggleColumn::make('ativo')
                    ->label('Ativa')
                    // Uma categoria de outro serviço aparece na lista, mas só o responsável a altera.
                    ->disabled(fn (Categoria $record): bool => auth()->user()->cannot('update', $record)),
                TextColumn::make('updated_at')
                    ->label('Atualizada em')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('servico')
                    ->label('Responsável')
                    ->relationship('servico', 'nome')
                    ->preload(),
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
                    DeleteBulkAction::make()
                        ->authorizeIndividualRecords('delete'),
                ]),
            ]);
    }
}
