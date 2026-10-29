<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['termo', 'resultados'])]
class Pesquisa extends Model
{
    /**
     * A tabela só tem created_at (um registo nunca é alterado).
     */
    public const UPDATED_AT = null;

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
