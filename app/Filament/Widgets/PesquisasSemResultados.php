<?php

namespace App\Filament\Widgets;

use App\Models\Pesquisa;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;

/**
 * O que as pessoas procuram e o FAQ ainda não responde.
 */
class PesquisasSemResultados extends TableWidget
{
    protected static ?int $sort = 1;

    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->heading('Pesquisas sem resultados')
            ->description('O que as pessoas procuram e o FAQ ainda não responde. Bom ponto de partida para novas perguntas.')
            ->query(fn (): Builder => static::termosSemResultados())
            ->defaultSort('vezes', 'desc')
            ->paginated([5, 10, 25])
            ->defaultPaginationPageOption(5)
            ->emptyStateHeading('Ainda não há pesquisas sem resultados')
            ->columns([
                TextColumn::make('termo')
                    ->label('Termo pesquisado'),
                TextColumn::make('vezes')
                    ->label('Vezes')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('ultima')
                    ->label('Última vez')
                    ->dateTime()
                    ->sortable(),
            ]);
    }

    /**
     * Um registo por termo, com quantas vezes foi pesquisado e quando foi a última.
     *
     * O agrupamento fica numa subconsulta porque o Filament ordena também pela chave
     * (id), e o MySQL (ONLY_FULL_GROUP_BY) não aceita isso numa consulta agrupada.
     * MIN(id) dá a cada termo uma chave para a tabela do Filament.
     */
    public static function termosSemResultados(): Builder
    {
        $porTermo = Pesquisa::query()
            ->selectRaw('MIN(id) as id, termo, COUNT(*) as vezes, MAX(created_at) as ultima')
            ->where('resultados', 0)
            ->groupBy('termo');

        return Pesquisa::query()->fromSub($porTermo, 'pesquisas');
    }
}
