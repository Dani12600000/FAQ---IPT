<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Relations\Pivot;

/**
 * Ligação entre um serviço e uma categoria. A "ordem" é a posição da
 * categoria dentro do serviço.
 */
#[Table('servico_categoria')]
class ServicoCategoria extends Pivot
{
    protected static function booted(): void
    {
        // Uma categoria ligada sem ordem indicada vai para o fim da lista do serviço.
        static::creating(function (self $ligacao): void {
            $ligacao->ordem ??= static::where('servico_id', $ligacao->servico_id)->max('ordem') + 1;
        });
    }
}
