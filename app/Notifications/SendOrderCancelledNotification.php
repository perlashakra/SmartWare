<?php

namespace App\Listeners;

use App\Events\OrderCancelled;
use App\Notifications\AppNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Log;
use PushNotification;
use Throwable;

class SendOrderCancelledNotification extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct()
    {
        //
    }

    public function handle(OrderCancelled $event): void
    {
        $order = $event->order;

        $user = $order->warehouseOfTheOrder?->user;
        
        if(!$user){
            return;
        }

        $title = 'Order Cancelled';
        $message ='Order #' . $order->id . ' has been cancelled.';

        $data = ['order_id' => $order->id];

        //database + reverb
        $user->notify(new AppNotification($title, $message, 'order_cancelled', $data));

        //fcm
        if($user->notificationTokens()->exists()){
            try{
                $user->notify(new PushNotification($title, $message, 'order_cancelled', $data));
            } catch (Throwable $e){
                Log::error('FCM order-cancelled notification failed', [
                    'user_id' => $user->id,
                    'order_id' => $order->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }
}
