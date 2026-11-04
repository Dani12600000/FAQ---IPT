<?php

namespace App\Policies;

use App\Models\Servico;
use App\Models\User;

/**
 * Só os administradores criam, apagam e ordenam serviços. Um editor altera apenas
 * os serviços a que está atribuído (descrição, categorias e a ordem delas).
 */
class ServicoPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Servico $servico): bool
    {
        return $user->podeGerirServico($servico->id);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Servico $servico): bool
    {
        return $user->podeGerirServico($servico->id);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Servico $servico): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can delete models in bulk.
     */
    public function deleteAny(User $user): bool
    {
        return $user->isAdmin();
    }

    /**
     * A ordem dos serviços é a da página inicial, comum a todos.
     */
    public function reorder(User $user): bool
    {
        return $user->isAdmin();
    }
}
