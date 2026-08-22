<?php

namespace App\Listeners;

use App\Events\OrderDecision;
use App\Notifications\AppNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Log;
use PushNotification;
use Throwable;

class SendOrderDecisionNotification extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct()
    {
        //
    }

    public function handle(OrderDecision $event): void
    {
        $order = $event->order;
        $status = $event->status;

        $user = $order->user;
        
        if(!$user){
            return;
        }

        $title = $status === 'approved' ? 'Order Approved' : 'Order Rejected';
        $message = $status === 'approved' ? 'Order #'. $order->id. ' has been approved' : 'Order #'. $order->id. ' has been rejected';

        $data = ['order_id' => $order->id, 'status' => $status];

        //database + reverb
        $user->notify(new AppNotification($title, $message, 'order_decision', $data));

        if($user->notificationTokens()->exists()){
            try{
                $user->notify(new PushNotification($title, $message, 'order_decision', $data));
            } catch (Throwable $e){
                Log::error('FCM order-decision notification failed', [
                    'user_id' => $user->id,
                    'order_id' => $order->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }
}
