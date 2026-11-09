<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\Perguntas\PerguntaResource;
use App\Models\Pergunta;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;

class RespostasARever extends TableWidget
{
    protected static ?int $sort = 5;

    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->heading('Respostas a rever')
            ->description('Respostas cuja data de revisão já passou ou passa nos próximos 30 dias.')
            // Um editor só vê as do seu serviço (as que pode alterar).
            ->query(fn (): Builder => Pergunta::query()->geridosPor(auth()->user())->aRever())
            ->defaultSort('rever_ate')
            ->paginated([5, 10, 25])
            ->defaultPaginationPageOption(5)
            ->emptyStateHeading('Não há respostas a rever')
            ->recordUrl(fn (Pergunta $record): string => PerguntaResource::getUrl('edit', ['record' => $record]))
            ->columns([
                TextColumn::make('pergunta')
                    ->label('Pergunta')
                    ->wrap(),
                TextColumn::make('servico.nome')
                    ->label('Responsável')
                    ->placeholder('Sem responsável'),
                TextColumn::make('rever_ate')
                    ->label('Rever até')
                    ->date()
                    ->color(fn (Pergunta $record): string => $record->rever_ate->lt(today()) ? 'danger' : 'warning'),
            ]);
    }
}
