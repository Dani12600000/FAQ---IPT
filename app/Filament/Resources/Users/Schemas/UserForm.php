<?php

namespace App\Filament\Resources\Users\Schemas;

use App\Enums\Papel;
use App\Models\User;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Nome')
                    ->required()
                    ->maxLength(255),
                TextInput::make('email')
                    ->label('Email')
                    ->email()
                    ->required()
                    ->maxLength(255)
                    ->unique(ignoreRecord: true),
                Select::make('papel')
                    ->label('Papel')
                    ->options(Papel::class)
                    ->default(Papel::Editor)
                    ->required()
                    ->helperText('O administrador gere tudo, incluindo utilizadores. O editor gere apenas o conteúdo.')
                    // Um administrador não pode tirar a si próprio o papel de administrador.
                    ->disabled(fn (?User $record): bool => $record?->is(auth()->user()) ?? false)
                    ->live(),
                Select::make('servicos')
                    ->label('Serviços')
                    ->relationship('servicos', 'nome')
                    ->multiple()
                    ->preload()
                    ->helperText('O editor só altera o conteúdo destes serviços. Pode usar (mas não alterar) perguntas, temas e categorias de outros serviços.')
                    // O administrador gere todos os serviços, por isso não precisa desta lista.
                    ->visible(fn (Get $get): bool => $get('papel') !== Papel::Admin && $get('papel') !== Papel::Admin->value),
                TextInput::make('password')
                    ->label('Palavra-passe')
                    ->password()
                    ->revealable()
                    ->minLength(8)
                    ->required(fn (string $operation): bool => $operation === 'create')
                    // Ao editar, deixar em branco mantém a palavra-passe atual.
                    ->dehydrated(fn (?string $state): bool => filled($state))
                    ->helperText(fn (string $operation): ?string => $operation === 'edit' ? 'Deixe em branco para manter a atual.' : null),
            ]);
    }
}
