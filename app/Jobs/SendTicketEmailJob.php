<?php

namespace App\Jobs;

use App\Models\Order;
use App\Services\MailketingService;
use App\Services\TicketPdfService;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendTicketEmailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public function __construct(public Order $order)
    {
    }

    public function handle(MailketingService $mailketing, TicketPdfService $ticketPdfService): void
    {
        $order = $this->order->fresh(['event', 'participants.category', 'participants.jerseySize']);

        if (!$order || !$order->isPaid()) {
            return;
        }

        foreach ($order->participants as $participant) {
            try {
                $pdfBinary = $ticketPdfService->generateTicketPdf($participant);
                $pdfBase64 = base64_encode($pdfBinary);

                $subject = "E-Ticket & Nomor BIB: {$participant->event?->title} - {$participant->bib_number}";
                $htmlContent = view('emails.ticket_confirmed', [
                    'participant' => $participant,
                    'event' => $participant->event,
                    'order' => $order,
                ])->render();

                $mailketing->sendEmail(
                    recipientEmail: $participant->email,
                    recipientName: $participant->full_name,
                    subject: $subject,
                    htmlContent: $htmlContent,
                    attachmentBase64: $pdfBase64,
                    attachmentName: "E-Ticket-{$participant->bib_number}.pdf"
                );
            } catch (Exception $e) {
                Log::error("Failed to send ticket email to participant #{$participant->id}: " . $e->getMessage());
            }
        }
    }
}
