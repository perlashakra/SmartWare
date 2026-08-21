<?php

namespace App\Listeners;

use App\Events\OrderCreated;
use App\Notifications\AppNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SendOrderCreatedNotification
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(OrderCreated $event): void
    {
        $order = $event->order;

        // TODO:
        // We will determine exactly which user(s)
        // should receive the notification based on your WMS rules.

        $user = $order->user;
        
        if(!$user){
            return;
        }

        $user->notify(new AppNotification('New Order', 'Order #'.$order->id.' has been created', 'order_created', ['order_id' => $order->id]));
    }
}
