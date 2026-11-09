<?php

namespace App\Filament\Resources\Servicos\Schemas;

use App\Filament\Forms\CamposComSlug;
use App\Models\Servico;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ServicoForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                ...CamposComSlug::make('nome', 'Nome', Servico::SLUGS_RESERVADOS),
                Textarea::make('descricao')
                    ->label('Descrição')
                    ->rows(3)
                    ->columnSpanFull(),
                Section::make('Contactos')
                    ->description('Opcionais. Aparecem na página do serviço e a quem responde "Não" a "Esta resposta foi útil?".')
                    ->columns(2)
                    ->columnSpanFull()
                    ->schema([
                        TextInput::make('email')
                            ->label('Email')
                            ->email()
                            ->maxLength(255),
                        TextInput::make('telefone')
                            ->label('Telefone')
                            ->tel()
                            ->maxLength(50),
                        TextInput::make('horario')
                            ->label('Horário de atendimento')
                            ->maxLength(255),
                        TextInput::make('pagina_url')
                            ->label('Endereço da página do serviço')
                            ->url()
                            // Só http e https: o valor é usado numa ligação do site público.
                            ->rule('url:http,https')
                            ->maxLength(255),
                    ]),
                Toggle::make('ativo')
                    ->label('Ativo')
                    ->helperText('Um serviço inativo não aparece no site nem na API.')
                    ->default(true),
            ]);
    }
}
