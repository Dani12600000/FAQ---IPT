<?php

namespace App\Filament\Resources\Perguntas\Pages;

use App\Filament\Resources\Perguntas\PerguntaResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditPergunta extends EditRecord
{
    protected static string $resource = PerguntaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
