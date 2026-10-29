<?php

namespace App\Filament\Resources\Perguntas\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class PerguntasTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('updated_at', 'desc')
            ->columns([
                TextColumn::make('pergunta')
                    ->label('Pergunta')
                    ->searchable()
                    ->wrap(),
                TextColumn::make('temas.nome')
                    ->label('Temas')
                    ->badge()
                    ->placeholder('Sem tema'),
                ToggleColumn::make('publicada')
                    ->label('Publicada'),
                TextColumn::make('updated_at')
                    ->label('Atualizada em')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('temas')
                    ->label('Tema')
                    ->relationship('temas', 'nome')
                    ->preload(),
                TernaryFilter::make('publicada')
                    ->label('Publicada'),
                Filter::make('sem_tema')
                    ->label('Sem tema')
                    ->query(fn (Builder $query): Builder => $query->doesntHave('temas')),
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
