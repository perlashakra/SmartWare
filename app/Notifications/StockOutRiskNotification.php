<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class StockOutRiskNotification extends Notification
{
    use Queueable;

    public function __construct(public int $facility_id, public string $facility_name, public int $product_id, public string $product_name, public float|int $quantity)
    {
        //
    }

    public function via(object $notifiable): array
    {
        return ['database', 'broadcast'];
    }
  
    public function toDatabase(object $notifiable): array
    {
        return [
            'type' => 'stock_out_risk',
            'title' => 'Stock Out Risk',
            'message' => "{$this->product_name} is at risk of stock-out.",
            'facility_id' => $this->facility_id,
            'facility_name' => $this->facility_name,
            'product_id' => $this->product_id,
            'product_name' => $this->product_name,
            'quantity' => $this->quantity,
            'threshold' => 10,
        ];
    }
    
    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return new BroadcastMessage([
            'type' => 'stock_out_risk',
            'title' => 'Stock Out Risk',
            'message' => "{$this->product_name} is at risk of stock-out.",
            'facility_id' => $this->facility_id,
            'facility_name' => $this->facility_name,
            'product_id' => $this->product_id,
            'product_name' => $this->product_name,
            'quantity' => $this->quantity,
            'threshold' => 10,
        ]);
    }
}
