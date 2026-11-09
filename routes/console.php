<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Apaga todos os dias as pesquisas antigas (ver App\Models\Pesquisa::prunable()).
// Só corre se o cron do servidor chamar o agendador: ver "Pôr em produção" no README.
Schedule::command('model:prune')->daily();
