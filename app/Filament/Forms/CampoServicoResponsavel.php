<?php

namespace App\Filament\Forms;

use App\Models\User;
use Filament\Forms\Components\Select;
use Illuminate\Database\Eloquent\Builder;

/**
 * Campos ligados aos serviços de quem está a editar: o serviço responsável de um
 * registo e as ligações que um editor pode fazer.
 */
class CampoServicoResponsavel
{
    /**
     * Serviço responsável pelo registo. Um editor só pode escolher os seus serviços.
     */
    public static function make(): Select
    {
        return Select::make('servico_id')
            ->label('Serviço responsável')
            ->relationship('servico', 'nome', fn (Builder $query) => $query->geridosPor(static::utilizador()))
            ->required()
            ->preload()
            // Um editor de um só serviço não tem de o escolher.
            ->default(fn (): ?int => static::utilizador()->servicos->count() === 1 ? static::utilizador()->servicos->first()->id : null)
            ->helperText('Só os editores deste serviço podem alterar este registo. Os outros serviços podem usá-lo, mas não alterá-lo.');
    }

    /**
     * Lista de ligações (por exemplo, os temas onde uma pergunta aparece) limitada ao que a
     * pessoa gere. Ao gravar, o Filament só mexe nessas ligações: as que outros serviços
     * fizeram mantêm-se.
     */
    public static function ligacoes(string $relacao, string $titulo, string $label, string $plural): Select
    {
        return Select::make($relacao)
            ->label($label)
            ->relationship($relacao, $titulo, fn (Builder $query) => $query->geridosPor(static::utilizador()))
            ->multiple()
            ->preload()
            ->searchable()
            ->helperText(fn (): ?string => static::utilizador()->isAdmin() ? null
                : "Só aparecem {$plural} dos seus serviços. As ligações feitas por outros serviços não são alteradas.")
            ->columnSpanFull();
    }

    private static function utilizador(): User
    {
        return auth()->user();
    }
}
