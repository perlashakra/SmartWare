<?php

namespace App\Providers;

use App\Events\OrderCancelled;
use App\Events\OrderCreated;
use App\Events\OrderDecision;
use App\Listeners\SendOrderCreatedNotification;
use App\Notifications\SendOrderCancelledNotification;
use App\Notifications\SendOrderDecisionNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        OrderCreated::class => [
            SendOrderCreatedNotification::class,
        ],

        OrderCancelled::class => [
            SendOrderCancelledNotification::class,
        ],

        OrderDecision::class => [
            SendOrderDecisionNotification::class,
        ],
    ];
}