<?php

namespace App\Providers;

use App\Models\Setting;
use App\Models\User;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;

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
        Schema::defaultStringLength(191);

        if (! $this->app->runningInConsole()) {
            $settings = Setting::query()
                ->pluck('value', 'key')
                ->toArray();
            config([
               'settings' => $settings
            ]);

            config(['app.name' => config('settings.app_name')]);
        }

        Gate::before(function (?User $user, string $ability): ?bool {
            if (! $user) {
                return null;
            }

            if ($user->isSuperAdmin()) {
                return true;
            }

            if (str_starts_with($ability, 'banks.')) {
                $action = substr($ability, 6);
                if ($user->hasPermission('banks.' . $action) || $user->hasPermission('payment_methods.' . $action)) {
                    return true;
                }
            } elseif (str_starts_with($ability, 'payment_methods.')) {
                $action = substr($ability, 16);
                if ($user->hasPermission('payment_methods.' . $action) || $user->hasPermission('banks.' . $action)) {
                    return true;
                }
            }

            return $user->hasPermission($ability) ? true : null;
        });

        Paginator::useBootstrap();
    }
}
