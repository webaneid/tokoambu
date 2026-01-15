<?php

namespace App\Mail;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class InvoiceMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Order $order,
        public string $pdfContent
    ) {}

    public function build()
    {
        return $this->subject('Invoice #' . $this->order->order_number)
            ->view('emails.invoice', [
                'order' => $this->order,
            ])
            ->attachData($this->pdfContent, 'Invoice-' . $this->order->order_number . '.pdf', [
                'mime' => 'application/pdf',
            ]);
    }
}
