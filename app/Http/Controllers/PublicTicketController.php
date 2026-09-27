<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Participant;
use App\Services\TicketPdfService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class PublicTicketController extends Controller
{
    public function __construct(protected TicketPdfService $ticketPdfService)
    {
    }

    public function index()
    {
        return view('public.check_ticket');
    }

    public function search(Request $request)
    {
        $request->validate([
            'query' => 'required|string|min:3',
        ]);

        $search = trim($request->input('query'));

        // Cari berdasarkan kode order, NIK, atau email
        $participants = Participant::with(['order', 'category.event', 'jerseySize'])
            ->where(function ($q) use ($search) {
                $q->where('id_number', $search)
                  ->orWhere('email', $search)
                  ->orWhereHas('order', function ($orderQuery) use ($search) {
                      $orderQuery->where('order_code', $search);
                  });
            })
            ->whereHas('order', function ($orderQuery) {
                $orderQuery->where('status', 'paid');
            })
            ->get();

        return view('public.check_ticket', [
            'search' => $search,
            'participants' => $participants,
            'searched' => true,
        ]);
    }

    public function downloadPdf(string $token)
    {
        $participant = Participant::where('qr_token', $token)
            ->with(['order', 'category.event', 'jerseySize'])
            ->first();

        // Fallback: jika token adalah BIB number atau kode invoice pendaftaran
        if (!$participant) {
            $participant = Participant::where('bib_number', $token)
                ->orWhereHas('order', fn ($q) => $q->where('order_code', $token))
                ->with(['order', 'category.event', 'jerseySize'])
                ->first();
        }

        if (!$participant) {
            abort(404, 'Tiket peserta tidak ditemukan. Pastikan pembayaran pendaftaran telah terverifikasi lunas.');
        }

        $pdfBinary = $this->ticketPdfService->generateTicketPdf($participant);

        $filename = 'E-Ticket-' . ($participant->bib_number ?: $participant->id) . '.pdf';

        return response($pdfBinary, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="' . $filename . '"',
            'Cache-Control' => 'public, must-revalidate, max-age=0',
        ]);
    }
}
