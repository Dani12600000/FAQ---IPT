<?php

namespace App\Filament\Resources\Perguntas\Actions;

use Filament\Actions\Action;
use Filament\Forms\Components\RichEditor\RichContentRenderer;
use Filament\Resources\Pages\CreateRecord;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Icons\Heroicon;
use Illuminate\Contracts\View\View;

/**
 * Mostra a pergunta como vai aparecer no site, com o que está escrito no
 * formulário nesse momento (mesmo antes de gravar).
 */
class PreVisualizarAction
{
    public static function make(): Action
    {
        return Action::make('preVisualizar')
            ->label('Pré-visualizar')
            ->icon(Heroicon::OutlinedEye)
            ->color('gray')
            ->modalHeading('Pré-visualização')
            ->modalDescription('É assim que a pergunta aparece no site, com o que está escrito agora (mesmo sem gravar).')
            ->modalWidth('3xl')
            ->modalSubmitAction(false)
            ->modalCancelActionLabel('Fechar')
            ->modalContent(fn (CreateRecord|EditRecord $livewire): View => view('filament.pre-visualizacao-pergunta', [
                'pergunta' => $livewire->data['pergunta'] ?? '',
                // toHtml() converte o conteúdo do editor em HTML e remove o que for perigoso.
                'resposta' => RichContentRenderer::make($livewire->data['resposta'] ?? null)->toHtml(),
            ]));
    }
}
