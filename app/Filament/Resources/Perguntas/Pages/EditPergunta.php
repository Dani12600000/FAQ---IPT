<?php

namespace App\Filament\Resources\Perguntas\Pages;

use App\Filament\Resources\Perguntas\Actions\PreVisualizarAction;
use App\Filament\Resources\Perguntas\PerguntaResource;
use App\Models\Pergunta;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Icons\Heroicon;

class EditPergunta extends EditRecord
{
    protected static string $resource = PerguntaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            PreVisualizarAction::make(),
            Action::make('verNoSite')
                ->label('Ver no site')
                ->icon(Heroicon::OutlinedArrowTopRightOnSquare)
                ->color('gray')
                ->url(fn (Pergunta $record): string => route('faq.canonica', $record->slug))
                ->openUrlInNewTab()
                // Só existe no site depois de publicada (e gravada).
                ->visible(fn (Pergunta $record): bool => $record->publicada),
            DeleteAction::make(),
        ];
    }
}
