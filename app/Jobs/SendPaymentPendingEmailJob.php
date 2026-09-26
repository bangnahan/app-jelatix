<?php

namespace App\Jobs;

use App\Models\Order;
use App\Services\MailketingService;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendPaymentPendingEmailJob
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public Order $order)
    {
    }

    public function handle(MailketingService $mailketing): void
    {
        $order = $this->order->fresh(['event', 'participants.category', 'participants.jerseySize', 'items.category']);

        if (!$order || $order->status !== 'pending') {
            Log::info("SendPaymentPendingEmailJob skipped: Order #{$this->order->id} is not pending (status: {$order?->status})");
            return;
        }

        try {
            $event = $order->event;
            $subject = "[PENTING] Panduan Bayar: " . ($event?->title ?? 'Tiket Lari') . " - {$order->order_code} (Batas Waktu 30 Menit)";

            $htmlContent = view('emails.payment_pending', [
                'order' => $order,
                'event' => $event,
            ])->render();

            $sent = $mailketing->sendEmail(
                recipientEmail: $order->customer_email,
                recipientName: $order->customer_name,
                subject: $subject,
                htmlContent: $htmlContent
            );

            if ($sent) {
                Log::info("Payment pending email sent successfully to {$order->customer_email} for order {$order->order_code}");
            } else {
                Log::warning("Payment pending email failed for {$order->customer_email} (order: {$order->order_code})");
            }
        } catch (Exception $e) {
            Log::error("Failed to send payment pending email for order #{$order->id}: " . $e->getMessage());
        }
    }
}
