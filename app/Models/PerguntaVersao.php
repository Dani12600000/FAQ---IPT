<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

/**
 * Uma versão guardada do texto de uma pergunta e da sua resposta.
 */
// O Laravel pluraliza em inglês ("pergunta_versaos"), por isso o nome da tabela é indicado aqui.
#[Table('pergunta_versoes')]
#[Fillable(['numero', 'pergunta', 'resposta', 'nota', 'user_id'])]
class PerguntaVersao extends Model
{
    /**
     * A tabela só tem created_at (uma versão nunca é alterada).
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
     * Quem fez a alteração.
     *
     * @return BelongsTo<User, $this>
     */
    public function autor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Resposta pronta a mostrar, sem HTML perigoso.
     */
    public function respostaSegura(): string
    {
        return Str::sanitizeHtml($this->resposta);
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'numero' => 'integer',
        ];
    }
}
