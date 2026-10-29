<?php

namespace App\Filament\Widgets;

use App\Filament\Widgets\Concerns\MostraUtilidadeDasPerguntas;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;

class PerguntasMenosUteis extends TableWidget
{
    use MostraUtilidadeDasPerguntas;

    protected static ?int $sort = 3;

    public function table(Table $table): Table
    {
        return $this->tabelaDeUtilidade($table, 'asc')
            ->heading('Perguntas menos úteis')
            ->description('Respostas a rever primeiro.');
    }
}
