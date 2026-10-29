<?php

namespace App\Filament\Resources\Categorias\Schemas;

use App\Filament\Forms\CamposComSlug;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class CategoriaForm
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
                Select::make('servicos')
                    ->label('Aparece nos serviços')
                    ->relationship('servicos', 'nome')
                    ->multiple()
                    ->preload()
                    ->searchable()
                    ->columnSpanFull(),
                Toggle::make('ativo')
                    ->label('Ativa')
                    ->helperText('Uma categoria inativa não aparece no site nem na API.')
                    ->default(true),
            ]);
    }
}
