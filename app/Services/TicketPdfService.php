<?php

namespace App\Services;

use App\Models\Participant;
use Barryvdh\DomPDF\Facade\Pdf;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class TicketPdfService
{
    /**
     * Render E-Ticket PDF binary untuk peserta
     */
    public function generateTicketPdf(Participant $participant): string
    {
        $qrCodeSvg = QrCode::format('svg')
            ->size(200)
            ->errorCorrection('H')
            ->generate($participant->qr_token);

        $qrCodeBase64 = base64_encode($qrCodeSvg);

        $pdf = Pdf::loadView('pdf.ticket', [
            'participant' => $participant,
            'event' => $participant->event,
            'category' => $participant->category,
            'jerseySize' => $participant->jerseySize,
            'order' => $participant->order,
            'qrCodeBase64' => $qrCodeBase64,
        ])->setPaper('a4', 'portrait');

        return $pdf->output();
    }
}
