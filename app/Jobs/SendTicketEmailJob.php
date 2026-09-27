<?php

namespace App\Jobs;

use App\Models\Order;
use App\Services\MailketingService;
use App\Services\TicketPdfService;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendTicketEmailJob
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public Order $order)
    {
    }

    public function handle(MailketingService $mailketing, TicketPdfService $ticketPdfService): void
    {
        $order = $this->order->fresh(['event', 'participants.category', 'participants.jerseySize']);

        if (!$order || !$order->isPaid()) {
            Log::warning("SendTicketEmailJob skipped: Order #{$this->order->id} is not paid (status: {$order?->status})");
            return;
        }

        // Tentukan base URL kanonikal publik (selalu gunakan https://app.jelatix.com jika dijalankan di CLI/local)
        $baseUrl = config('app.url', 'https://app.jelatix.com');
        if (str_contains($baseUrl, 'localhost') || str_contains($baseUrl, '127.0.0.1')) {
            $baseUrl = 'https://app.jelatix.com';
        }
        $baseUrl = rtrim($baseUrl, '/');

        $storageDir = storage_path('app/public/tickets');
        if (!file_exists($storageDir)) {
            @mkdir($storageDir, 0755, true);
        }

        foreach ($order->participants as $participant) {
            try {
                $pdfBinary = $ticketPdfService->generateTicketPdf($participant);
                $pdfBase64 = base64_encode($pdfBinary);

                // Simpan salinan PDF ke storage publik agar dapat diunduh langsung & dilampirkan via Mailketing
                $pdfFileName = "E-Ticket-{$participant->bib_number}-{$participant->qr_token}.pdf";
                @file_put_contents("{$storageDir}/{$pdfFileName}", $pdfBinary);

                $directPdfUrl = "{$baseUrl}/storage/tickets/{$pdfFileName}";
                $downloadRouteUrl = "{$baseUrl}/ticket/{$participant->qr_token}/download";

                $subject = "E-Ticket & Nomor BIB: {$participant->event?->title} - {$participant->bib_number}";
                $htmlContent = view('emails.ticket_confirmed', [
                    'participant' => $participant,
                    'event' => $participant->event,
                    'order' => $order,
                    'downloadUrl' => $downloadRouteUrl,
                    'directPdfUrl' => $directPdfUrl,
                ])->render();

                $targetEmail = !empty($participant->email) ? $participant->email : $order->customer_email;

                $sent = $mailketing->sendEmail(
                    recipientEmail: $targetEmail,
                    recipientName: $participant->full_name,
                    subject: $subject,
                    htmlContent: $htmlContent,
                    attachmentBase64: $pdfBase64,
                    attachmentName: "E-Ticket-{$participant->bib_number}.pdf",
                    attachmentUrl: $directPdfUrl
                );

                if ($sent) {
                    Log::info("E-Ticket email sent successfully to {$targetEmail} for participant #{$participant->id} (BIB: {$participant->bib_number}) - URL: {$downloadRouteUrl}");
                } else {
                    Log::warning("E-Ticket email sending returned false for participant #{$participant->id} ({$targetEmail})");
                }
            } catch (Exception $e) {
                Log::error("Failed to send ticket email to participant #{$participant->id}: " . $e->getMessage());
            }
        }
    }
}
