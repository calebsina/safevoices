<?php

namespace App\Providers;

use App\Models\Report\Report;
use App\Policies\ReportPolicy;
use Dedoc\Scramble\Scramble;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {

        Scramble::ignoreDefaultRoutes();

        // Fail fast on any unguarded mass assignment during development.
        Model::preventSilentlyDiscardingAttributes(! app()->isProduction());

        Gate::policy(Report::class, ReportPolicy::class);
    }
}
