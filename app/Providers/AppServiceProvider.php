<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use App\Models\Order;
use App\Models\Party;
use App\Models\Invoice;

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
        MorphTo::morphMap([
            'order' => Order::class,
            'party' => Party::class,
            'invoice' => Invoice::class,
        ]);
    }
}
