<?php

namespace App\Filament\Resources\Perguntas\Pages;

use App\Filament\Resources\Perguntas\Actions\PreVisualizarAction;
use App\Filament\Resources\Perguntas\PerguntaResource;
use App\Models\Pergunta;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Icons\Heroicon;
use Illuminate\Contracts\View\View;

/**
 * @property Pergunta $record
 */
class EditPergunta extends EditRecord
{
    protected static string $resource = PerguntaResource::class;

    /**
     * Numa versão própria, diz de onde veio e se a original mudou entretanto.
     */
    public function getSubheading(): ?string
    {
        $origem = $this->record->origem;

        if (! $origem) {
            return null;
        }

        $servico = $origem->servico?->nome ?? 'sem serviço responsável';
        $texto = "Versão própria de «{$origem->pergunta}» ({$servico}), a partir da versão {$this->record->origem_versao}.";

        return $this->record->origemMudou()
            ? "{$texto} A original mudou depois disso e vai na versão {$origem->versaoAtual()}."
            : $texto;
    }

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
            $this->acoesDaVersaoPropria(),
            DeleteAction::make(),
        ];
    }

    /**
     * Ações de uma versão própria, para a acertar com a original de onde veio.
     */
    private function acoesDaVersaoPropria(): ActionGroup
    {
        return ActionGroup::make([
            Action::make('compararComOriginal')
                ->label('Comparar com a original')
                ->icon(Heroicon::OutlinedArrowsRightLeft)
                ->modalHeading('Comparar com a original')
                ->modalDescription('O texto desta pergunta está como foi gravado (sem as alterações ainda por gravar).')
                ->modalWidth('7xl')
                ->modalSubmitAction(false)
                ->modalCancelActionLabel('Fechar')
                ->modalContent(fn (Pergunta $record): View => view('filament.comparar-com-original', [
                    'colunas' => $this->colunasDaComparacao($record),
                ])),
            Action::make('trazerDaOriginal')
                ->label('Trazer o texto da original')
                ->icon(Heroicon::OutlinedArrowDownTray)
                ->requiresConfirmation()
                ->modalHeading('Trazer o texto da original?')
                ->modalDescription('A pergunta e a resposta passam a ser iguais às da original. O texto atual continua guardado no histórico de versões.')
                ->modalSubmitActionLabel('Trazer')
                ->visible(fn (Pergunta $record): bool => $record->origemMudou())
                ->action(function (Pergunta $record): void {
                    $record->trazerAlteracoesDaOrigem();
                    $this->refreshFormData(['pergunta', 'resposta']);

                    Notification::make()->success()->title('Texto trazido da original')->send();
                }),
            Action::make('ignorarOriginal')
                ->label('Manter o meu texto')
                ->icon(Heroicon::OutlinedCheckCircle)
                ->requiresConfirmation()
                ->modalHeading('Manter o texto desta pergunta?')
                ->modalDescription('As alterações da original ficam dadas por vistas. Se a original voltar a mudar, aparece outra vez o aviso.')
                ->modalSubmitActionLabel('Manter')
                ->visible(fn (Pergunta $record): bool => $record->origemMudou())
                ->action(function (Pergunta $record): void {
                    $record->ignorarAlteracoesDaOrigem();

                    Notification::make()->success()->title('Alterações da original dadas por vistas')->send();
                }),
            Action::make('voltarAOriginal')
                ->label('Voltar a usar a original')
                ->icon(Heroicon::OutlinedArrowUturnLeft)
                ->color('danger')
                ->requiresConfirmation()
                ->modalHeading('Voltar a usar a original?')
                ->modalDescription('Nos temas onde esta pergunta aparece passa a aparecer a original. Esta fica por publicar e sem temas, com o seu histórico (pode apagá-la depois).')
                ->modalSubmitActionLabel('Voltar à original')
                ->action(function (Pergunta $record): void {
                    $record->voltarAOrigem();

                    Notification::make()->success()->title('Os seus temas voltaram a usar a original')->send();

                    $this->redirect(PerguntaResource::getUrl());
                }),
        ])
            ->label('Versão própria')
            ->icon(Heroicon::OutlinedDocumentDuplicate)
            ->color('gray')
            ->button()
            ->visible(fn (): bool => $this->record->origem !== null);
    }

    /**
     * @return array<string, array{pergunta: string, resposta: string}|null>
     */
    private function colunasDaComparacao(Pergunta $record): array
    {
        $origem = $record->origem;
        $base = $origem->versoes()->firstWhere('numero', $record->origem_versao);

        return [
            "Base comum (versão {$record->origem_versao} da original)" => $base
                ? ['pergunta' => $base->pergunta, 'resposta' => $base->respostaSegura()]
                : null,
            "Original agora (versão {$origem->versaoAtual()})" => ['pergunta' => $origem->pergunta, 'resposta' => $origem->respostaSegura()],
            'Esta pergunta' => ['pergunta' => $record->pergunta, 'resposta' => $record->respostaSegura()],
        ];
    }
}
