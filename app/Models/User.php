<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Enums\Papel;
use Database\Factories\UserFactory;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'password', 'papel'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable implements FilamentUser
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'papel' => Papel::class,
        ];
    }

    public function isAdmin(): bool
    {
        return $this->papel === Papel::Admin;
    }

    /**
     * Serviços cujo conteúdo esta pessoa gere (por exemplo, a Biblioteca).
     *
     * @return BelongsToMany<Servico, $this>
     */
    public function servicos(): BelongsToMany
    {
        return $this->belongsToMany(Servico::class);
    }

    /**
     * Se pode alterar o conteúdo de um serviço. O administrador pode alterar tudo,
     * incluindo o conteúdo sem serviço responsável.
     */
    public function podeGerirServico(?int $servicoId): bool
    {
        return $this->isAdmin() || ($servicoId !== null && $this->servicos->contains($servicoId));
    }

    /**
     * Só há contas para a equipa do FAQ (não há registo público), por isso
     * qualquer admin ou editor entra no backoffice.
     */
    public function canAccessPanel(Panel $panel): bool
    {
        return in_array($this->papel, [Papel::Admin, Papel::Editor], true);
    }
}
