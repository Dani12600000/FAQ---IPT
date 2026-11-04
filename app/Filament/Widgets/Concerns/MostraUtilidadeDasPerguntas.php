<?php

namespace App\Filament\Widgets\Concerns;

use App\Filament\Resources\Perguntas\PerguntaResource;
use App\Models\Pergunta;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

/**
 * Tabela de perguntas avaliadas com "Esta resposta foi útil?", partilhada pelos
 * widgets "mais úteis" e "menos úteis" (só muda a ordem).
 */
trait MostraUtilidadeDasPerguntas
{
    protected function tabelaDeUtilidade(Table $table, string $direcao): Table
    {
        return $table
            ->query(fn (): Builder => static::perguntasAvaliadas())
            ->defaultSort(fn (Builder $query) => $query
                ->orderBy('percentagem', $direcao)
                ->orderBy('total', 'desc'))
            ->paginated([5, 10])
            ->defaultPaginationPageOption(5)
            ->emptyStateHeading('Ainda não há avaliações')
            ->recordUrl(fn (Pergunta $record): ?string => auth()->user()->can('update', $record) ? PerguntaResource::getUrl('edit', ['record' => $record]) : null)
            ->columns([
                TextColumn::make('pergunta')
                    ->label('Pergunta')
                    ->wrap(),
                TextColumn::make('sim')
                    ->label('Sim')
                    ->numeric(),
                TextColumn::make('nao')
                    ->label('Não')
                    ->numeric(),
                TextColumn::make('percentagem')
                    ->label('Útil')
                    ->suffix('%')
                    ->sortable(),
            ]);
    }

    /**
     * Perguntas com pelo menos uma avaliação, com as contagens de "sim" e "não", o total e
     * a percentagem de "sim". As subconsultas fazem destes valores colunas de "perguntas",
     * para se poder ordenar por elas. Um editor só vê as perguntas que usa nos seus serviços.
     */
    public static function perguntasAvaliadas(): Builder
    {
        $contagens = Pergunta::query()
            ->select('perguntas.*')
            ->visiveisPara(auth()->user())
            ->has('avaliacoes')
            ->withCount([
                'avaliacoes as sim' => fn (Builder $q) => $q->where('util', true),
                'avaliacoes as nao' => fn (Builder $q) => $q->where('util', false),
            ]);

        $comPercentagem = DB::query()
            ->fromSub($contagens, 'contagens')
            ->select('*')
            ->selectRaw('sim + nao as total')
            ->selectRaw('ROUND(100.0 * sim / (sim + nao)) as percentagem');

        return Pergunta::query()->fromSub($comPercentagem, 'perguntas');
    }
}
