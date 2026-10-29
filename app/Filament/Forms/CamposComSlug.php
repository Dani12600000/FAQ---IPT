<?php

namespace App\Filament\Forms;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Illuminate\Support\Str;

/**
 * Campo de título (nome ou pergunta) seguido do slug gerado a partir dele.
 */
class CamposComSlug
{
    /**
     * @param  list<string>  $reservados  Slugs que não podem ser usados.
     * @return array<TextInput>
     */
    public static function make(string $campo, string $label, array $reservados = []): array
    {
        return [
            TextInput::make($campo)
                ->label($label)
                ->required()
                ->maxLength(255)
                ->live(onBlur: true)
                ->afterStateUpdated(function (Get $get, Set $set, string $operation, ?string $old, ?string $state): void {
                    // Só ao criar, para não mudar o endereço de uma página que já existe.
                    // Se a pessoa já mexeu no slug à mão, não lhe tocamos.
                    if ($operation !== 'create' || ($get('slug') ?? '') !== Str::slug($old ?? '')) {
                        return;
                    }

                    $set('slug', Str::slug($state ?? ''));
                }),

            TextInput::make('slug')
                ->label('Slug (endereço)')
                ->helperText('Parte do endereço da página. É gerado automaticamente e pode ser editado.')
                ->required()
                ->maxLength(255)
                ->alphaDash()
                ->unique(ignoreRecord: true)
                ->notIn($reservados)
                ->validationMessages([
                    'not_in' => 'Este endereço está reservado pelo site. Escolha outro.',
                ]),
        ];
    }
}
