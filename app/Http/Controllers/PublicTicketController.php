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
            ->firstOrFail();

        $pdfBinary = $this->ticketPdfService->generateTicketPdf($participant);

        $filename = 'E-Ticket-' . ($participant->bib_number ?: $participant->id) . '.pdf';

        return response($pdfBinary, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }
}
