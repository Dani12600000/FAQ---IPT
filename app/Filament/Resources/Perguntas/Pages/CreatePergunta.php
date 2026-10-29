<?php

namespace App\Filament\Resources\Perguntas\Pages;

use App\Filament\Resources\Perguntas\Actions\PreVisualizarAction;
use App\Filament\Resources\Perguntas\PerguntaResource;
use Filament\Resources\Pages\CreateRecord;

class CreatePergunta extends CreateRecord
{
    protected static string $resource = PerguntaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            PreVisualizarAction::make(),
        ];
    }
}
