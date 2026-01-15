<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use App\Models\InventoryAnalytics;

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
        View::composer('layouts.navigation', function ($view) {
            try {
                $alerts = InventoryAnalytics::with('product', 'location.warehouse')
                    ->whereIn('status', ['slow_moving', 'dead_stock'])
                    ->orderBy('status', 'desc')
                    ->orderBy('updated_at', 'desc')
                    ->limit(5)
                    ->get();

                $view->with('inventoryAlerts', $alerts);
                $view->with('inventoryAlertCount', $alerts->count());
            } catch (\Exception $e) {
                $view->with('inventoryAlerts', collect());
                $view->with('inventoryAlertCount', 0);
            }
        });
    }
}
