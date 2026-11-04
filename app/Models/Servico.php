<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

#[Fillable(['nome', 'slug', 'descricao', 'ordem', 'ativo'])]
class Servico extends Model
{
    /**
     * Endereços de primeiro nível usados pelo próprio site. Um serviço não pode ter
     * um destes slugs, porque o URL /{servico} ficaria tapado por eles.
     */
    public const SLUGS_RESERVADOS = ['admin', 'api', 'p', 'pesquisa', 'up', 'livewire', 'storage', 'css', 'js', 'fonts', 'filament', 'docs'];

    protected static function booted(): void
    {
        // Um serviço novo sem ordem indicada vai para o fim da lista da página inicial.
        static::creating(function (self $servico): void {
            $servico->ordem ??= static::max('ordem') + 1;
        });
    }

    /**
     * Categorias do serviço, pela ordem definida neste serviço.
     *
     * @return BelongsToMany<Categoria, $this>
     */
    public function categorias(): BelongsToMany
    {
        return $this->belongsToMany(Categoria::class, 'servico_categoria')
            ->using(ServicoCategoria::class)
            ->withPivot('ordem')
            ->orderByPivot('ordem');
    }

    /**
     * Só os serviços visíveis no site e na API.
     */
    #[Scope]
    protected function ativos(Builder $query): void
    {
        $query->where($query->qualifyColumn('ativo'), true);
    }

    /**
     * Pessoas (editores) que gerem o conteúdo deste serviço.
     *
     * @return BelongsToMany<User, $this>
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class);
    }

    /**
     * Só os serviços que a pessoa pode gerir (todos, no caso do administrador).
     */
    #[Scope]
    protected function geridosPor(Builder $query, User $user): void
    {
        if ($user->isAdmin()) {
            return;
        }

        $query->whereIn($query->qualifyColumn('id'), $user->servicos->modelKeys());
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'ordem' => 'integer',
            'ativo' => 'boolean',
        ];
    }
}
