<?php

namespace App\Filament\Resources\Perguntas\Actions;

use App\Filament\Resources\Perguntas\PerguntaResource;
use App\Models\Pergunta;
use App\Models\Servico;
use Closure;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Collection;
use Livewire\Component;

/**
 * Cria a versão própria de um serviço para uma pergunta de outro serviço que ele usa
 * (ver Pergunta::derivarPara()). Aparece na lista de perguntas e nas perguntas de um tema.
 */
class CriarVersaoPropriaAction
{
    /**
     * @param  Closure(): ?Servico|null  $servicoDoTema  Na tabela de um tema, só o serviço desse tema.
     */
    public static function make(?Closure $servicoDoTema = null): Action
    {
        $servicos = fn (Pergunta $record): Collection => static::servicos($record)
            ->when($servicoDoTema, fn (Collection $servicos) => $servicos->where('id', $servicoDoTema()?->id)->values());

        return Action::make('criarVersaoPropria')
            ->label('Criar versão própria')
            ->icon(Heroicon::OutlinedDocumentDuplicate)
            ->color('gray')
            ->requiresConfirmation()
            ->modalHeading('Criar uma versão própria desta pergunta?')
            ->modalDescription('Fica uma cópia da pergunta e da resposta, do seu serviço, no lugar da original nos temas desse serviço. '
                .'A original continua igual para os outros serviços. Depois pode alterar a cópia, compará-la com a original e trazer as alterações dela.')
            ->modalSubmitActionLabel('Criar versão própria')
            // Só se pede o serviço quando há mais do que um possível.
            ->schema(fn (Pergunta $record): array => $servicos($record)->count() > 1 ? [
                Select::make('servico_id')
                    ->label('Para o serviço')
                    ->options($servicos($record)->pluck('nome', 'id'))
                    ->required(),
            ] : [])
            ->visible(fn (Pergunta $record): bool => $servicos($record)->isNotEmpty())
            ->action(function (Pergunta $record, array $data, Component $livewire) use ($servicos): void {
                $servico = $servicos($record)->firstWhere('id', $data['servico_id'] ?? null) ?? $servicos($record)->first();
                $copia = $record->derivarPara($servico);

                Notification::make()
                    ->success()
                    ->title('Versão própria criada')
                    ->body('Já pode alterar a pergunta e a resposta. A original não mudou.')
                    ->send();

                $livewire->redirect(PerguntaResource::getUrl('edit', ['record' => $copia]));
            });
    }

    /**
     * Serviços que a pessoa gere e que usam a pergunta (têm temas onde ela aparece),
     * sem contar o responsável pela pergunta, que a edita diretamente.
     *
     * @return Collection<int, Servico>
     */
    public static function servicos(Pergunta $pergunta): Collection
    {
        return Servico::query()
            ->geridosPor(auth()->user())
            ->when($pergunta->servico_id, fn ($query, int $responsavel) => $query->whereKeyNot($responsavel))
            ->whereIn('id', $pergunta->temas()->pluck('temas.servico_id'))
            ->orderBy('nome')
            ->get();
    }
}
