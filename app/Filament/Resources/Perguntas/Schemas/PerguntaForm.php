<?php

namespace App\Filament\Resources\Perguntas\Schemas;

use App\Filament\Forms\CamposComSlug;
use App\Filament\Forms\CampoServicoResponsavel;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class PerguntaForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                ...CamposComSlug::make('pergunta', 'Pergunta'),
                RichEditor::make('resposta')
                    ->label('Resposta')
                    ->required()
                    ->toolbarButtons([
                        ['bold', 'italic', 'link'],
                        ['h2', 'h3'],
                        ['bulletList', 'orderedList'],
                        ['undo', 'redo'],
                    ])
                    ->columnSpanFull(),
                CampoServicoResponsavel::make(),
                CampoServicoResponsavel::ligacoes('temas', 'nome', 'Aparece nos temas', 'os temas'),
                Toggle::make('publicada')
                    ->label('Publicada')
                    ->helperText('Uma pergunta por publicar fica guardada como rascunho e não aparece no site nem na API.'),
            ]);
    }
}
