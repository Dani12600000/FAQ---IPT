<?php

namespace App\Filament\Resources\Categorias\Schemas;

use App\Filament\Forms\CamposComSlug;
use App\Filament\Forms\CampoServicoResponsavel;
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
                CampoServicoResponsavel::make(),
                CampoServicoResponsavel::ligacoes('servicos', 'nome', 'Aparece nos serviços', 'os serviços'),
                Toggle::make('ativo')
                    ->label('Ativa')
                    ->helperText('Uma categoria inativa não aparece no site nem na API.')
                    ->default(true),
            ]);
    }
}
