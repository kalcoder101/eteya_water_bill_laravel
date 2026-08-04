<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\AuditService;
use App\Services\BillCalculatorService;
use App\Services\I18nService;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(I18nService::class);
        $this->app->singleton(BillCalculatorService::class);
        $this->app->singleton(AuditService::class);
    }

    public function boot(): void
    {
        // Auto-detect the subfolder the app lives in (mirrors original BASE_URL logic)
        // and expose it to all Blade views as $baseUrl.
        $scriptName = request()?->server('SCRIPT_NAME') ?? '';
        $dir = str_replace('\\', '/', dirname($scriptName));
        if ($dir === '.' || $dir === '/') {
            $dir = '';
        }

        view()->share('baseUrl', $dir);
        view()->share('appVersion', config('app.version', '1.0.0 Laravel'));
    }
}
