<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Relations\Pivot;

/**
 * Ligação entre um tema e uma pergunta. A "ordem" é a posição da pergunta
 * dentro do tema.
 */
#[Table('tema_pergunta')]
class TemaPergunta extends Pivot
{
    protected static function booted(): void
    {
        // Uma pergunta ligada sem ordem indicada vai para o fim da lista do tema.
        static::creating(function (self $ligacao): void {
            $ligacao->ordem ??= static::where('tema_id', $ligacao->tema_id)->max('ordem') + 1;
        });
    }
}
