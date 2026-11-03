<?php

namespace App\Providers;

use Dedoc\Scramble\Scramble;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // As rotas da documentação da API (/docs/api) são registadas em routes/web.php,
        // antes das rotas com caminho, que de outra forma as tapavam.
        Scramble::ignoreDefaultRoutes();
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Datas no backoffice no formato português (29/10/2026 18:24) em vez do americano.
        Table::configureUsing(fn (Table $table) => $table
            ->defaultDateDisplayFormat('d/m/Y')
            ->defaultDateTimeDisplayFormat('d/m/Y H:i'));

        Schema::configureUsing(fn (Schema $schema) => $schema
            ->defaultDateDisplayFormat('d/m/Y')
            ->defaultDateTimeDisplayFormat('d/m/Y H:i'));
    }
}
