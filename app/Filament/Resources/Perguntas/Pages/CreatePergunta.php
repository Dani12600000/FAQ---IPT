<?php

namespace App\Filament\Resources\Perguntas\Pages;

use App\Filament\Resources\Perguntas\Actions\PreVisualizarAction;
use App\Filament\Resources\Perguntas\PerguntaResource;
use App\Models\Pergunta;
use App\Models\Tema;
use Filament\Actions\Action;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;

class CreatePergunta extends CreateRecord
{
    protected static string $resource = PerguntaResource::class;

    /**
     * Se, em vez de criar, se ligou uma pergunta que já existia aos temas da pessoa.
     */
    protected bool $usouPerguntaExistente = false;

    protected function getHeaderActions(): array
    {
        return [
            PreVisualizarAction::make(),
        ];
    }

    /**
     * Cria a pergunta ou, se a pessoa escolheu uma pergunta parecida que já existe,
     * liga essa aos temas escolhidos. Assim a mesma resposta serve vários serviços.
     */
    protected function handleRecordCreation(array $data): Model
    {
        $existente = Arr::pull($data, 'pergunta_existente');
        $temas = Arr::pull($data, 'temas_destino', []);

        if (! is_numeric($existente)) {
            return parent::handleRecordCreation($data);
        }

        $pergunta = Pergunta::findOrFail($existente);

        // Só nos temas que a pessoa gere, pela ordem em que os escolheu. As ligações que já existiam mantêm-se.
        $temasProprios = Tema::query()->geridosPor(auth()->user())->whereKey($temas)->get()
            ->sortBy(fn (Tema $tema): int|false => array_search($tema->id, $temas));
        $pergunta->temas()->syncWithoutDetaching($temasProprios->modelKeys());

        // Uma pergunta sem responsável passa a ser do serviço do primeiro tema escolhido,
        // para quem a usa a poder publicar e manter (senão só um administrador o podia fazer).
        if ($pergunta->servico_id === null) {
            $pergunta->update(['servico_id' => $temasProprios->first()?->servico_id]);
        }

        $this->usouPerguntaExistente = true;

        return $pergunta;
    }

    protected function getCreatedNotificationTitle(): ?string
    {
        return $this->usouPerguntaExistente
            ? 'Pergunta adicionada aos seus temas'
            : parent::getCreatedNotificationTitle();
    }

    protected function getCreateFormAction(): Action
    {
        return parent::getCreateFormAction()
            ->label(fn (): string => is_numeric($this->data['pergunta_existente'] ?? null)
                ? 'Adicionar aos meus temas'
                : __('filament-panels::resources/pages/create-record.form.actions.create.label'));
    }
}
