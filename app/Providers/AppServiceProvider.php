<?php

namespace App\Providers;

use Dedoc\Scramble\Scramble;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Symfony\Component\HttpFoundation\IpUtils;

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
        // Limites de pedidos com nome, usados nas rotas com o middleware throttle:api e
        // throttle:avaliacoes. Os valores estão em config/faq.php.
        RateLimiter::for('api', fn (Request $request): Limit => IpUtils::checkIp((string) $request->ip(), config('faq.limites.api_ips_sem_limite'))
            ? Limit::none()
            : Limit::perMinute(config('faq.limites.api_por_minuto'))->by($request->ip()));

        RateLimiter::for('avaliacoes', fn (Request $request): Limit => Limit::perMinute(config('faq.limites.avaliacoes_por_minuto'))->by($request->ip()));

        // Datas no backoffice no formato português (29/10/2026 18:24) em vez do americano.
        Table::configureUsing(fn (Table $table) => $table
            ->defaultDateDisplayFormat('d/m/Y')
            ->defaultDateTimeDisplayFormat('d/m/Y H:i'));

        Schema::configureUsing(fn (Schema $schema) => $schema
            ->defaultDateDisplayFormat('d/m/Y')
            ->defaultDateTimeDisplayFormat('d/m/Y H:i'));
    }
}
