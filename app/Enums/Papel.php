<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum Papel: string implements HasLabel
{
    case Admin = 'admin';
    case Editor = 'editor';

    public function getLabel(): string
    {
        return match ($this) {
            self::Admin => 'Administrador',
            self::Editor => 'Editor',
        };
    }
}
