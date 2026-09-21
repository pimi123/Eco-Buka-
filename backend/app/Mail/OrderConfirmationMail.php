<?php

namespace App\Mail;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class OrderConfirmationMail extends Mailable
{
    use Queueable;
    use SerializesModels;

    public function __construct(
        public Order $order,
    ) {
    }

    public function build(): self
    {
        $this->order->loadMissing('items.product', 'latestPayment');

        return $this
            ->subject('Konfirmimi i porosisë '.$this->order->order_number)
            ->view('emails.orders.confirmation', [
                'order' => $this->order,
            ]);
    }
}
