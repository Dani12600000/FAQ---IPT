<?php

namespace App\Policies;

use App\Models\Categoria;
use App\Models\Pergunta;
use App\Models\Tema;
use App\Models\User;

/**
 * Regras comuns às categorias, aos temas e às perguntas: cada registo tem um serviço
 * responsável e só os editores desse serviço (e os administradores) o alteram.
 *
 * O Laravel encontra as políticas pelo nome (CategoriaPolicy para Categoria, etc.).
 * Cada uma estende esta classe e o Filament usa-as para mostrar ou esconder botões.
 */
abstract class ConteudoPolicy
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
    public function view(User $user, Categoria|Tema|Pergunta $model): bool
    {
        return $user->podeGerirServico($model->servico_id);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        // Um editor sem serviço não tem onde pôr o que criar.
        return $user->isAdmin() || $user->servicos->isNotEmpty();
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Categoria|Tema|Pergunta $model): bool
    {
        return $user->podeGerirServico($model->servico_id);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Categoria|Tema|Pergunta $model): bool
    {
        return $user->podeGerirServico($model->servico_id);
    }

    /**
     * Determine whether the user can delete models in bulk.
     *
     * As tabelas mostram também conteúdo de outros serviços, por isso cada registo
     * é verificado com delete() (ver authorizeIndividualRecords() nas tabelas).
     */
    public function deleteAny(User $user): bool
    {
        return true;
    }
}
