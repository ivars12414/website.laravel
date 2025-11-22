<?php

namespace App\Providers;

use App\Listeners\Stripe\HandleCreditsTopUp;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        'stripe-webhooks::checkout.session.completed' => [
            HandleCreditsTopUp::class,
        ],
    ];

    public function boot(): void
    {
        //
    }
}
