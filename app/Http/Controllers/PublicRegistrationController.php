<?php

namespace App\Http\Controllers;

use App\Jobs\SendPaymentPendingEmailJob;
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

        // Cek apakah payload bertipe multi-peserta (array `participants`) atau single-peserta
        if ($request->has('participants') && is_array($request->input('participants'))) {
            $request->validate([
                'buyer_name' => 'required|string|max:100',
                'buyer_email' => 'required|email|max:100',
                'buyer_phone' => 'required|string|max:20',
                'payment_method' => 'required|string',
                'waiver_accepted' => 'accepted',
                'participants' => 'required|array|min:1|max:10',
                'participants.*.category_id' => 'required|exists:event_categories,id',
                'participants.*.jersey_size_id' => 'required|exists:jersey_sizes,id',
                'participants.*.full_name' => 'required|string|max:100',
                'participants.*.bib_name' => 'nullable|string|max:30',
                'participants.*.id_type' => 'required|in:KTP,SIM,Passport,KIA',
                'participants.*.id_number' => 'required|string|max:30',
                'participants.*.gender' => 'required|in:male,female',
                'participants.*.birth_date' => 'required|date',
                'participants.*.blood_type' => 'required|string',
                'participants.*.phone' => 'required|string|max:20',
                'participants.*.email' => 'required|email|max:100',
                'participants.*.emergency_contact_name' => 'required|string|max:100',
                'participants.*.emergency_contact_phone' => 'required|string|max:20',
                'participants.*.emergency_contact_relation' => 'required|string|max:50',
                'participants.*.medical_conditions' => 'nullable|string',
                'participants.*.estimated_finish_time' => 'nullable|string|max:50',
                'participants.*.custom_fields' => 'nullable|array',
            ]);

            $buyerName = $request->input('buyer_name');
            $buyerEmail = $request->input('buyer_email');
            $buyerPhone = $request->input('buyer_phone');
            $participantsData = $request->input('participants');
        } else {
            // Fallback untuk single-participant (backward compatibility)
            $request->validate([
                'category_id' => 'required|exists:event_categories,id',
                'jersey_size_id' => 'required|exists:jersey_sizes,id',
                'full_name' => 'required|string|max:100',
                'bib_name' => 'nullable|string|max:30',
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
                'medical_conditions' => 'nullable|string',
                'estimated_finish_time' => 'nullable|string|max:50',
                'custom_fields' => 'nullable|array',
            ]);

            $buyerName = $request->input('full_name');
            $buyerEmail = $request->input('email');
            $buyerPhone = $request->input('phone');
            $participantsData = [
                [
                    'category_id' => $request->input('category_id'),
                    'jersey_size_id' => $request->input('jersey_size_id'),
                    'full_name' => $request->input('full_name'),
                    'bib_name' => $request->input('bib_name'),
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
                    'custom_fields' => $request->input('custom_fields'),
                ]
            ];
        }

        try {
            $order = DB::transaction(function () use ($request, $event, $buyerName, $buyerEmail, $buyerPhone, $participantsData) {
                // 1. Kunci dan validasi kuota per kategori lari
                $categoryCounts = [];
                foreach ($participantsData as $p) {
                    $catId = (int) $p['category_id'];
                    $categoryCounts[$catId] = ($categoryCounts[$catId] ?? 0) + 1;
                }

                $categoryModels = [];
                $totalTicketsPrice = 0;

                foreach ($categoryCounts as $catId => $qtyNeeded) {
                    $category = EventCategory::where('id', $catId)
                        ->where('event_id', $event->id)
                        ->lockForUpdate()
                        ->firstOrFail();

                    if (($category->slots_taken + $qtyNeeded) > $category->quota) {
                        $available = max(0, $category->quota - $category->slots_taken);
                        throw new Exception("Mohon maaf, sisa kuota kategori {$category->name} tersisa {$available} slot (Anda memesan {$qtyNeeded} slot).");
                    }

                    $category->increment('slots_taken', $qtyNeeded);
                    $categoryModels[$catId] = $category;
                    $totalTicketsPrice += ($category->getCurrentPrice() * $qtyNeeded);
                }

                // 2. Kunci dan validasi stok jersey
                $jerseyCounts = [];
                foreach ($participantsData as $p) {
                    $jerseyId = (int) $p['jersey_size_id'];
                    $jerseyCounts[$jerseyId] = ($jerseyCounts[$jerseyId] ?? 0) + 1;
                }

                foreach ($jerseyCounts as $jerseyId => $qtyNeeded) {
                    $jersey = JerseySize::where('id', $jerseyId)
                        ->where('event_id', $event->id)
                        ->lockForUpdate()
                        ->firstOrFail();

                    if (($jersey->allocated_stock + $qtyNeeded) > $jersey->stock) {
                        $available = max(0, $jersey->stock - $jersey->allocated_stock);
                        throw new Exception("Mohon maaf, sisa stok ukuran kaos {$jersey->size_name} tersisa {$available} pcs (Anda memilih {$qtyNeeded} pcs).");
                    }

                    $jersey->increment('allocated_stock', $qtyNeeded);
                }

                $platformFee = 5000;
                $grandTotal = $totalTicketsPrice + $platformFee;
                $orderCode = 'JLTX-' . date('Ymd') . '-' . strtoupper(Str::random(5));

                $order = Order::create([
                    'event_id' => $event->id,
                    'order_code' => $orderCode,
                    'customer_name' => $buyerName,
                    'customer_email' => $buyerEmail,
                    'customer_phone' => $buyerPhone,
                    'subtotal' => $totalTicketsPrice,
                    'platform_fee' => $platformFee,
                    'grand_total' => $grandTotal,
                    'status' => 'pending',
                ]);

                // 3. Buat OrderItem per kategori
                foreach ($categoryCounts as $catId => $qty) {
                    $cat = $categoryModels[$catId];
                    $price = $cat->getCurrentPrice();
                    OrderItem::create([
                        'order_id' => $order->id,
                        'event_category_id' => $cat->id,
                        'quantity' => $qty,
                        'unit_price' => $price,
                        'total_price' => $price * $qty,
                    ]);
                }

                // 4. Buat data Participant per pelari
                foreach ($participantsData as $pData) {
                    $rawBib = !empty($pData['bib_name']) ? trim($pData['bib_name']) : trim($pData['full_name']);
                    $safeBibName = mb_substr(strtoupper($rawBib), 0, 40);
                    $safeFinishTime = !empty($pData['estimated_finish_time']) ? mb_substr(trim($pData['estimated_finish_time']), 0, 45) : null;

                    Participant::create([
                        'order_id' => $order->id,
                        'event_category_id' => (int) $pData['category_id'],
                        'jersey_size_id' => (int) $pData['jersey_size_id'],
                        'full_name' => $pData['full_name'],
                        'bib_name' => $safeBibName,
                        'id_type' => $pData['id_type'],
                        'id_number' => $pData['id_number'],
                        'gender' => $pData['gender'],
                        'birth_date' => $pData['birth_date'],
                        'blood_type' => $pData['blood_type'],
                        'phone' => $pData['phone'],
                        'email' => $pData['email'],
                        'emergency_contact_name' => $pData['emergency_contact_name'],
                        'emergency_contact_phone' => $pData['emergency_contact_phone'],
                        'emergency_contact_relation' => $pData['emergency_contact_relation'],
                        'medical_conditions' => $pData['medical_conditions'] ?? null,
                        'estimated_finish_time' => $safeFinishTime,
                        'custom_fields_data' => $pData['custom_fields'] ?? null,
                        'waiver_accepted' => true,
                        'waiver_accepted_at' => now(),
                        'waiver_ip_address' => $request->ip(),
                    ]);
                }

                return $order;
            });

            // Request transaksi ke Tripay
            $tripayData = $this->tripayService->createTransaction($order, $request->input('payment_method'));

            // Kirim email panduan pembayaran & pemberitahuan batas bayar 30 menit ke pendaftar
            SendPaymentPendingEmailJob::dispatchAfterResponse($order);

            // Jika Tripay mengembalikan checkout_url resmi, langsung alihkan calon peserta ke halaman pembayaran Tripay
            if (!empty($tripayData['checkout_url']) && filter_var($tripayData['checkout_url'], FILTER_VALIDATE_URL) && !str_contains($tripayData['checkout_url'], url('/orders/'))) {
                return redirect()->away($tripayData['checkout_url']);
            }

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
