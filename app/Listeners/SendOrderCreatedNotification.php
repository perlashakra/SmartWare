<?php

namespace App\Listeners;

use App\Events\OrderCreated;
use App\Notifications\AppNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;
use PushNotification;
use Throwable;

class SendOrderCreatedNotification
{
    public function __construct()
    {
        //
    }

    public function handle(OrderCreated $event): void
    {
        $order = $event->order;

        $user = $order->src_facility_id?->user;
        
        if(!$user){
            return;
        }

        $title = 'New Order';
        $message ='Order #' . $order->id . ' has been created.';

        $data = ['order_id' => $order->id];

        //database + reverb
        $user->notify(new AppNotification($title, $message, 'order_created', $data));

        if($user->notificationTokens()->exists()){
            try{
                $user->notify(new PushNotification($title, $message, 'order_created', $data));
            } catch (Throwable $e){
                Log::error('FCM order-created notification failed', [
                    'user_id' => $user->id,
                    'order_id' => $order->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }
}
