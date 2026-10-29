<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

// O Laravel pluraliza em inglês ("avaliacaos"), por isso o nome da tabela é indicado aqui.
#[Table('avaliacoes')]
#[Fillable(['pergunta_id', 'util'])]
class Avaliacao extends Model
{
    /**
     * A tabela só tem created_at (um registo nunca é alterado).
     */
    public const UPDATED_AT = null;

    /**
     * @return BelongsTo<Pergunta, $this>
     */
    public function pergunta(): BelongsTo
    {
        return $this->belongsTo(Pergunta::class);
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'util' => 'boolean',
        ];
    }
}
