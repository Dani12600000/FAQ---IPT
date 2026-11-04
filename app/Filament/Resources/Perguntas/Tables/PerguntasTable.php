<?php

namespace App\Filament\Resources\Perguntas\Tables;

use App\Models\Pergunta;
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
                    ->description(fn (Pergunta $record): ?string => $record->origem ? "Versão própria de «{$record->origem->pergunta}»" : null)
                    ->searchable()
                    ->wrap(),
                TextColumn::make('servico.nome')
                    ->label('Responsável')
                    ->placeholder('Sem responsável'),
                TextColumn::make('temas.nome')
                    ->label('Temas')
                    ->badge()
                    ->placeholder('Sem tema'),
                ToggleColumn::make('publicada')
                    ->label('Publicada')
                    // Uma pergunta de outro serviço aparece na lista, mas só o responsável a altera.
                    ->disabled(fn (Pergunta $record): bool => auth()->user()->cannot('update', $record)),
                TextColumn::make('updated_at')
                    ->label('Atualizada em')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('servico')
                    ->label('Responsável')
                    ->relationship('servico', 'nome')
                    ->preload(),
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
                    DeleteBulkAction::make()
                        ->authorizeIndividualRecords('delete'),
                ]),
            ]);
    }
}
