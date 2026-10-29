<?php

namespace App\Filament\Resources\Perguntas;

use App\Filament\Resources\Perguntas\Pages\CreatePergunta;
use App\Filament\Resources\Perguntas\Pages\EditPergunta;
use App\Filament\Resources\Perguntas\Pages\ListPerguntas;
use App\Filament\Resources\Perguntas\Schemas\PerguntaForm;
use App\Filament\Resources\Perguntas\Tables\PerguntasTable;
use App\Models\Pergunta;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class PerguntaResource extends Resource
{
    protected static ?string $model = Pergunta::class;

    protected static ?string $modelLabel = 'pergunta';

    protected static ?string $pluralModelLabel = 'perguntas';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedQuestionMarkCircle;

    protected static string|UnitEnum|null $navigationGroup = 'Conteúdo';

    protected static ?int $navigationSort = 4;

    protected static ?string $recordTitleAttribute = 'pergunta';

    public static function form(Schema $schema): Schema
    {
        return PerguntaForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PerguntasTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPerguntas::route('/'),
            'create' => CreatePergunta::route('/create'),
            'edit' => EditPergunta::route('/{record}/edit'),
        ];
    }
}
