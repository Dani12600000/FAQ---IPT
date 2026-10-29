<?php

namespace App\Filament\Resources\Servicos\Schemas;

use App\Filament\Forms\CamposComSlug;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class ServicoForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                ...CamposComSlug::make('nome', 'Nome'),
                Textarea::make('descricao')
                    ->label('Descrição')
                    ->rows(3)
                    ->columnSpanFull(),
                Toggle::make('ativo')
                    ->label('Ativo')
                    ->helperText('Um serviço inativo não aparece no site nem na API.')
                    ->default(true),
            ]);
    }
}
