<?php

namespace App\Filament\Widgets;

use App\Filament\Widgets\Concerns\MostraUtilidadeDasPerguntas;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;

class PerguntasMaisUteis extends TableWidget
{
    use MostraUtilidadeDasPerguntas;

    protected static ?int $sort = 2;

    public function table(Table $table): Table
    {
        return $this->tabelaDeUtilidade($table, 'desc')
            ->heading('Perguntas mais úteis')
            ->description('Respostas que as pessoas acharam úteis.');
    }
}
