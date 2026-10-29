<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\Perguntas\PerguntaResource;
use App\Models\Pergunta;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;

class PerguntasSemTema extends TableWidget
{
    protected static ?int $sort = 4;

    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->heading('Perguntas sem tema')
            ->description('Não aparecem na navegação do site. Se estiverem publicadas, só se chega a elas pela pesquisa ou pelo endereço /p/.')
            ->query(fn (): Builder => Pergunta::query()->doesntHave('temas'))
            ->defaultSort('updated_at', 'desc')
            ->paginated([5, 10, 25])
            ->defaultPaginationPageOption(5)
            ->emptyStateHeading('Todas as perguntas têm tema')
            ->recordUrl(fn (Pergunta $record): string => PerguntaResource::getUrl('edit', ['record' => $record]))
            ->columns([
                TextColumn::make('pergunta')
                    ->label('Pergunta')
                    ->wrap(),
                IconColumn::make('publicada')
                    ->label('Publicada')
                    ->boolean(),
                TextColumn::make('updated_at')
                    ->label('Atualizada em')
                    ->dateTime(),
            ]);
    }
}
