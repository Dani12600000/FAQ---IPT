<?php

namespace App\Models\Concerns;

use App\Models\Servico;
use App\Models\User;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Conteúdo (categoria, tema ou pergunta) com um serviço responsável. Só os editores
 * desse serviço o podem alterar. Os outros serviços podem usá-lo, mas não alterá-lo.
 */
trait TemServicoResponsavel
{
    /**
     * @return BelongsTo<Servico, $this>
     */
    public function servico(): BelongsTo
    {
        return $this->belongsTo(Servico::class);
    }

    /**
     * Só os registos que a pessoa pode alterar (todos, no caso do administrador).
     */
    #[Scope]
    protected function geridosPor(Builder $query, User $user): void
    {
        if ($user->isAdmin()) {
            return;
        }

        $query->whereIn($query->qualifyColumn('servico_id'), $user->servicos->modelKeys());
    }
}
