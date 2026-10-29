<?php

namespace App\Filament\Resources\Temas\Schemas;

use App\Filament\Forms\CamposComSlug;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class TemaForm
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
                Select::make('categorias')
                    ->label('Aparece nas categorias')
                    ->relationship('categorias', 'nome')
                    ->multiple()
                    ->preload()
                    ->searchable()
                    ->columnSpanFull(),
                Toggle::make('ativo')
                    ->label('Ativo')
                    ->helperText('Um tema inativo não aparece no site nem na API.')
                    ->default(true),
            ]);
    }
}
