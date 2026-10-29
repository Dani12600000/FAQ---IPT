<?php

namespace App\Filament\Resources\Perguntas\Pages;

use App\Filament\Resources\Perguntas\PerguntaResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListPerguntas extends ListRecords
{
    protected static string $resource = PerguntaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
