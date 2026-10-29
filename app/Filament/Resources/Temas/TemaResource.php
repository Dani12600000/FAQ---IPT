<?php

namespace App\Filament\Resources\Temas;

use App\Filament\Resources\Temas\Pages\CreateTema;
use App\Filament\Resources\Temas\Pages\EditTema;
use App\Filament\Resources\Temas\Pages\ListTemas;
use App\Filament\Resources\Temas\RelationManagers\PerguntasRelationManager;
use App\Filament\Resources\Temas\Schemas\TemaForm;
use App\Filament\Resources\Temas\Tables\TemasTable;
use App\Models\Tema;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class TemaResource extends Resource
{
    protected static ?string $model = Tema::class;

    protected static ?string $modelLabel = 'tema';

    protected static ?string $pluralModelLabel = 'temas';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedTag;

    protected static string|UnitEnum|null $navigationGroup = 'Conteúdo';

    protected static ?int $navigationSort = 3;

    protected static ?string $recordTitleAttribute = 'nome';

    public static function form(Schema $schema): Schema
    {
        return TemaForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return TemasTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            PerguntasRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListTemas::route('/'),
            'create' => CreateTema::route('/create'),
            'edit' => EditTema::route('/{record}/edit'),
        ];
    }
}
