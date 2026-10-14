<?php

declare(strict_types=1);

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Usado pelo entrypoint do container: diferente de "migrate --isolated", as réplicas
// que não pegam o lock esperam a migração terminar em vez de seguir adiante.
// O seeder roda dentro do mesmo lock para duas réplicas não semearem ao mesmo tempo.
Artisan::command('migrate:wait
    {--timeout=600 : Segundos de espera pelo lock}
    {--seeder= : Seeder a rodar depois das migrations}', function () {
    $timeout = (int) $this->option('timeout');
    $seeder = $this->option('seeder');

    return Cache::lock('deploy:migrate', $timeout)->block($timeout, fn () => $this->call('migrate', [
        '--force' => true,
        '--no-interaction' => true,
        '--seed' => filled($seeder),
        '--seeder' => $seeder,
    ]));
})->purpose('Run central migrations (and optionally a seeder), waiting for any other replica that is already migrating');
