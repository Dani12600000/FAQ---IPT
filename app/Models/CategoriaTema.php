<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Relations\Pivot;

/**
 * Ligação entre uma categoria e um tema. A "ordem" é a posição do tema
 * dentro da categoria.
 */
#[Table('categoria_tema')]
class CategoriaTema extends Pivot
{
    protected static function booted(): void
    {
        // Um tema ligado sem ordem indicada vai para o fim da lista da categoria.
        static::creating(function (self $ligacao): void {
            $ligacao->ordem ??= static::where('categoria_id', $ligacao->categoria_id)->max('ordem') + 1;
        });
    }
}
