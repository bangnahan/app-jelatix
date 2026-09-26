<?php

namespace App\Http\Controllers;

use App\Jobs\SendTicketEmailJob;
use App\Models\Event;
use App\Models\EventCategory;
use App\Models\JerseySize;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Participant;
use App\Services\BibGeneratorService;
use App\Services\TripayService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PublicRegistrationController extends Controller
{
    public function __construct(
        protected TripayService $tripayService,
        protected BibGeneratorService $bibService
    ) {}

    /**
     * Tampilkan formulir pendaftaran lomba lari publik
     */
    public function show(string $slug)
    {
        $event = Event::where('slug', $slug)
            ->with(['categories' => fn($q) => $q->where('is_active', true), 'jerseySizes', 'customFields'])
            ->firstOrFail();

        $paymentChannels = $this->tripayService->getPaymentChannels();

        return view('public.register', compact('event', 'paymentChannels'));
    }

    /**
     * Proses checkout dan request pembayaran ke Tripay
     */
    public function checkout(Request $request, string $slug): RedirectResponse
    {
        $event = Event::where('slug', $slug)->firstOrFail();

        $request->validate([
            'category_id' => 'required|exists:event_categories,id',
            'jersey_size_id' => 'required|exists:jersey_sizes,id',
            'full_name' => 'required|string|max:100',
            'bib_name' => 'nullable|string|max:14',
            'id_type' => 'required|in:KTP,SIM,Passport,KIA',
            'id_number' => 'required|string|max:30',
            'gender' => 'required|in:male,female',
            'birth_date' => 'required|date',
            'blood_type' => 'required|string',
            'phone' => 'required|string|max:20',
            'email' => 'required|email|max:100',
            'emergency_contact_name' => 'required|string|max:100',
            'emergency_contact_phone' => 'required|string|max:20',
            'emergency_contact_relation' => 'required|string|max:50',
            'payment_method' => 'required|string',
            'waiver_accepted' => 'accepted',
            'estimated_finish_time' => 'nullable|string',
            'custom_fields' => 'nullable|array',
        ]);

        try {
            $order = DB::transaction(function () use ($request, $event) {
                // Kunci kuota kategori lari (Pessimistic Locking)
                $category = EventCategory::where('id', $request->input('category_id'))
                    ->where('event_id', $event->id)
                    ->lockForUpdate()
                    ->firstOrFail();

                if ($category->slots_taken >= $category->quota) {
                    throw new Exception("Mohon maaf, kuota untuk kategori {$category->name} telah habis!");
                }

                // Kunci stok ukuran jersey
                $jersey = JerseySize::where('id', $request->input('jersey_size_id'))
                    ->where('event_id', $event->id)
                    ->lockForUpdate()
                    ->firstOrFail();

                if ($jersey->allocated_stock >= $jersey->stock) {
                    throw new Exception("Mohon maaf, stok untuk ukuran jersey {$jersey->size_name} telah habis!");
                }

                // Alokasikan slot & stok jersey
                $category->increment('slots_taken', 1);
                $jersey->increment('allocated_stock', 1);

                $unitPrice = $category->getCurrentPrice();
                $platformFee = 5000; // Platform fee Jelatix
                $grandTotal = $unitPrice + $platformFee;

                $orderCode = 'JLTX-' . date('Ymd') . '-' . strtoupper(Str::random(5));

                $order = Order::create([
                    'event_id' => $event->id,
                    'order_code' => $orderCode,
                    'customer_name' => $request->input('full_name'),
                    'customer_email' => $request->input('email'),
                    'customer_phone' => $request->input('phone'),
                    'subtotal' => $unitPrice,
                    'platform_fee' => $platformFee,
                    'grand_total' => $grandTotal,
                    'status' => 'pending',
                ]);

                OrderItem::create([
                    'order_id' => $order->id,
                    'event_category_id' => $category->id,
                    'quantity' => 1,
                    'unit_price' => $unitPrice,
                    'total_price' => $unitPrice,
                ]);

                Participant::create([
                    'order_id' => $order->id,
                    'event_category_id' => $category->id,
                    'jersey_size_id' => $jersey->id,
                    'full_name' => $request->input('full_name'),
                    'bib_name' => strtoupper($request->input('bib_name') ?: $request->input('full_name')),
                    'id_type' => $request->input('id_type'),
                    'id_number' => $request->input('id_number'),
                    'gender' => $request->input('gender'),
                    'birth_date' => $request->input('birth_date'),
                    'blood_type' => $request->input('blood_type'),
                    'phone' => $request->input('phone'),
                    'email' => $request->input('email'),
                    'emergency_contact_name' => $request->input('emergency_contact_name'),
                    'emergency_contact_phone' => $request->input('emergency_contact_phone'),
                    'emergency_contact_relation' => $request->input('emergency_contact_relation'),
                    'medical_conditions' => $request->input('medical_conditions'),
                    'estimated_finish_time' => $request->input('estimated_finish_time'),
                    'custom_fields_data' => $request->input('custom_fields'),
                    'waiver_accepted' => true,
                    'waiver_accepted_at' => now(),
                    'waiver_ip_address' => $request->ip(),
                ]);

                return $order;
            });

            // Request transaksi ke Tripay
            $this->tripayService->createTransaction($order, $request->input('payment_method'));

            return redirect()->route('public.order.show', $order->order_code);
        } catch (Exception $e) {
            return back()->withInput()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Tampilkan halaman invoice pembayaran & instruksi bayar Tripay
     */
    public function showInvoice(string $orderCode)
    {
        $order = Order::where('order_code', $orderCode)
            ->with(['event', 'items.category', 'participants.category', 'participants.jerseySize'])
            ->firstOrFail();

        return view('public.invoice', compact('order'));
    }

    /**
     * Polling real-time cek status pembayaran
     */
    public function checkStatus(string $orderCode): JsonResponse
    {
        $order = Order::where('order_code', $orderCode)->firstOrFail();

        return response()->json([
            'status' => $order->status,
            'is_paid' => $order->isPaid(),
            'paid_at' => $order->paid_at?->format('d M Y, H:i'),
        ]);
    }

    /**
     * Simulasi pelunasan untuk Sandbox Testing Tripay
     */
    public function simulatePay(string $orderCode): RedirectResponse
    {
        $order = Order::where('order_code', $orderCode)->firstOrFail();

        if ($order->status !== 'paid') {
            DB::transaction(function () use ($order) {
                $order->update([
                    'status' => 'paid',
                    'paid_at' => now(),
                ]);

                // Auto-assign BIB
                if ($order->event?->auto_assign_bib) {
                    foreach ($order->participants as $participant) {
                        $this->bibService->autoAssignBib($participant);
                    }
                }

                // Kirim email tiket
                SendTicketEmailJob::dispatch($order);
            });
        }

        return redirect()->route('public.order.show', $orderCode)->with('success', 'Pembayaran Sandbox Berhasil Dikonfirmasi!');
    }
}
