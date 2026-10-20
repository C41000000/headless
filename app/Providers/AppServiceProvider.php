<?php

declare(strict_types=1);

namespace App\Providers;

use Dedoc\Scramble\Scramble;
use Dedoc\Scramble\Support\Generator\OpenApi;
use Dedoc\Scramble\Support\Generator\SecurityScheme;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->configureScramble();
        $this->configureModels();
        $this->configureDatabase();
        $this->configureApiResponses();
        $this->configureSecurity();
        $this->configureRateLimiting();
        $this->configureUrls();
    }

    private function configureUrls(): void
    {
        URL::defaults([
            'tenant_domain' => request()->getHost(),
        ]);
    }

    private function configureModels(): void
    {
        // Sem unguard global: cada model declara o que aceita via #[Fillable].
        Model::shouldBeStrict(! $this->app->isProduction());
    }

    private function configureDatabase(): void
    {
        DB::prohibitDestructiveCommands($this->app->isProduction());
        DB::whenQueryingForLongerThan(500, function ($connection) {
            Log::warning("High query execution time detected on connection: {$connection->getName()}");
        });
    }

    private function configureApiResponses(): void
    {
        JsonResource::withoutWrapping();
    }

    private function configureSecurity(): void
    {
        if (! $this->app->isLocal()) {
            URL::forceScheme('https');
        }
    }

    private function configureRateLimiting(): void
    {
        // Por e-mail + IP contra força bruta numa conta; só por IP contra varredura de contas.
        RateLimiter::for('login', fn (Request $request) => [
            Limit::perMinute(5)->by(mb_strtolower((string) $request->input('email')).'|'.$request->ip()),
            Limit::perMinute(20)->by($request->ip()),
        ]);
    }

    private function configureScramble(): void
    {
        Scramble::afterOpenApiGenerated(function (OpenApi $openApi) {
            // Título fixo: o APP_NAME não muda aqui porque é o prefixo das chaves do cache
            $openApi->info->title = 'Headless API';

            $openApi->secure(
                SecurityScheme::http('bearer', 'JWT')
            );
        });
    }
}
