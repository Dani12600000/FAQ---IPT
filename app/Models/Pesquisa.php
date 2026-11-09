<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\MassPrunable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['termo', 'resultados'])]
class Pesquisa extends Model
{
    use MassPrunable;

    /**
     * A tabela só tem created_at (um registo nunca é alterado).
     */
    public const UPDATED_AT = null;

    /**
     * Pesquisas a apagar pelo comando model:prune (ver routes/console.php). Os termos
     * pesquisados podem ter dados pessoais, por isso não ficam guardados para sempre.
     *
     * @return Builder<static>
     */
    public function prunable(): Builder
    {
        return static::where('created_at', '<', now()->subMonths(config('faq.pesquisas_meses_a_guardar')));
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'resultados' => 'integer',
        ];
    }
}
