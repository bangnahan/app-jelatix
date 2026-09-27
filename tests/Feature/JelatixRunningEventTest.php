<?php

namespace Tests\Feature;

use App\Console\Commands\ReleaseExpiredOrdersCommand;
use App\Models\Event;
use App\Models\EventCategory;
use App\Models\Order;
use App\Models\Organizer;
use App\Models\Participant;
use App\Services\BibGeneratorService;
use App\Services\TicketPdfService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class JelatixRunningEventTest extends TestCase
{
    use RefreshDatabase;

    protected Organizer $organizer;
    protected Event $event;
    protected EventCategory $category;

    protected function setUp(): void
    {
        parent::setUp();

        $this->organizer = Organizer::create([
            'name' => 'Test EO',
            'slug' => 'test-eo',
            'commission_rate' => 5.0,
            'is_verified' => true,
        ]);

        $this->event = Event::create([
            'organizer_id' => $this->organizer->id,
            'title' => 'Test Marathon 2026',
            'slug' => 'test-marathon-2026',
            'race_location_name' => 'Stadion Utama',
            'event_start_date' => now()->addDays(30),
            'registration_open_date' => now()->subDay(),
            'registration_close_date' => now()->addDays(20),
            'auto_assign_bib' => true,
            'status' => 'published',
        ]);

        $this->category = EventCategory::create([
            'event_id' => $this->event->id,
            'name' => '10K Race',
            'distance_km' => 10.0,
            'normal_price' => 250000,
            'quota' => 100,
            'bib_prefix' => '10K',
            'bib_start_number' => 1001,
            'reserved_bib_numbers' => [1001, 1002, 9999], // 1001 & 1002 diblokir untuk VVIP
        ]);
    }

    public function test_auto_assign_bib_skips_reserved_vvip_numbers(): void
    {
        $bibService = new BibGeneratorService();

        $participant = Participant::create([
            'event_category_id' => $this->category->id,
            'full_name' => 'Pelari Biasa',
            'id_number' => '317100001',
            'gender' => 'male',
            'birth_date' => '1990-01-01',
            'phone' => '0812345678',
            'email' => 'runner@test.com',
            'emergency_contact_name' => 'Kontak',
            'emergency_contact_phone' => '0812345679',
            'emergency_contact_relation' => 'Saudara',
        ]);

        $bib = $bibService->autoAssignBib($participant);

        // Harus melompati 1001 dan 1002, menghasilkan 10K1003
        $this->assertEquals('10K1003', $bib);
    }

    public function test_custom_vvip_bib_is_protected_from_bulk_remapping(): void
    {
        $bibService = new BibGeneratorService();

        $order = Order::create([
            'event_id' => $this->event->id,
            'order_code' => 'JLTX-TEST-001',
            'customer_name' => 'Order Lunas',
            'customer_email' => 'order@test.com',
            'customer_phone' => '0812345678',
            'grand_total' => 250000,
            'status' => 'paid',
        ]);

        // 1. Peserta VVIP dengan nomor khusus
        $vvip = Participant::create([
            'order_id' => $order->id,
            'event_category_id' => $this->category->id,
            'full_name' => 'Pejabat VVIP',
            'id_number' => '317100099',
            'gender' => 'male',
            'birth_date' => '1975-01-01',
            'phone' => '0811999999',
            'email' => 'vvip@gov.id',
            'emergency_contact_name' => 'Ajudan',
            'emergency_contact_phone' => '0811888888',
            'emergency_contact_relation' => 'Staf',
            'bib_number' => 'VIP-01',
            'is_vip' => true,
            'is_custom_bib' => true,
            'custom_bib_reason' => 'Tamu Kehormatan',
        ]);

        // 2. Peserta Reguler
        $regular = Participant::create([
            'order_id' => $order->id,
            'event_category_id' => $this->category->id,
            'full_name' => 'Pelari Biasa',
            'id_number' => '317100001',
            'gender' => 'male',
            'birth_date' => '1990-01-01',
            'phone' => '0812345678',
            'email' => 'runner@test.com',
            'emergency_contact_name' => 'Kontak',
            'emergency_contact_phone' => '0812345679',
            'emergency_contact_relation' => 'Saudara',
            'estimated_finish_time' => '00:45:00',
        ]);

        // Jalankan Bulk Remap
        $bibService->bulkRemapBibs($this->category);

        // VVIP BIB harus tetap 'VIP-01'
        $this->assertEquals('VIP-01', $vvip->fresh()->bib_number);
        // Peserta reguler mendapatkan nomor terurut (melewati reserved 1001, 1002 -> 10K1003)
        $this->assertEquals('10K1003', $regular->fresh()->bib_number);
    }

    public function test_public_check_ticket_and_pdf_download(): void
    {
        $order = Order::create([
            'event_id' => $this->event->id,
            'order_code' => 'JLTX-VERIFY-001',
            'customer_name' => 'Budi Santoso',
            'customer_email' => 'budi@test.com',
            'customer_phone' => '0812345678',
            'grand_total' => 250000,
            'status' => 'paid',
        ]);

        $participant = Participant::create([
            'order_id' => $order->id,
            'event_category_id' => $this->category->id,
            'full_name' => 'Budi Santoso',
            'id_number' => '3578012345670001',
            'gender' => 'male',
            'birth_date' => '1992-03-15',
            'phone' => '0812345678',
            'email' => 'budi@test.com',
            'emergency_contact_name' => 'Siti',
            'emergency_contact_phone' => '0812345679',
            'emergency_contact_relation' => 'Istri',
            'bib_number' => '10K1005',
        ]);

        // Cari via NIK di form pencarian publik
        $searchResponse = $this->post('/cek-tiket', [
            'query' => '3578012345670001',
        ]);

        $searchResponse->assertStatus(200);
        $searchResponse->assertSee('10K1005');
        $searchResponse->assertSee('Budi Santoso');

        // Unduh PDF E-Ticket
        $downloadResponse = $this->get("/ticket/{$participant->qr_token}/download");
        $downloadResponse->assertStatus(200);
        $this->assertEquals('application/pdf', $downloadResponse->headers->get('content-type'));
    }

    public function test_release_expired_orders_command_restores_quota(): void
    {
        $order = Order::create([
            'event_id' => $this->event->id,
            'order_code' => 'JLTX-EXPIRED-99',
            'customer_name' => 'Calon Peserta Batal',
            'customer_email' => 'batal@test.com',
            'customer_phone' => '0812345678',
            'grand_total' => 250000,
            'status' => 'pending',
            'expired_at' => now()->subMinutes(10), // Melebihi 30 menit
        ]);

        $this->category->update(['slots_taken' => 5]);

        Participant::create([
            'order_id' => $order->id,
            'event_category_id' => $this->category->id,
            'full_name' => 'Calon Peserta Batal',
            'id_number' => '3578000000000001',
            'gender' => 'female',
            'birth_date' => '1995-01-01',
            'phone' => '0812345678',
            'email' => 'batal@test.com',
            'emergency_contact_name' => 'Kontak',
            'emergency_contact_phone' => '0812345679',
            'emergency_contact_relation' => 'Keluarga',
        ]);

        $this->artisan('jelatix:release-expired-orders')->assertSuccessful();

        $this->assertEquals('expired', $order->fresh()->status);
        $this->assertEquals(4, $this->category->fresh()->slots_taken);
    }

    public function test_crew_can_verify_qr_and_claim_rpc(): void
    {
        $order = Order::create([
            'event_id' => $this->event->id,
            'order_code' => 'JLTX-RPC-001',
            'customer_name' => 'Peserta RPC',
            'customer_email' => 'rpc@test.com',
            'customer_phone' => '0812345678',
            'grand_total' => 250000,
            'status' => 'paid',
        ]);

        $participant = Participant::create([
            'order_id' => $order->id,
            'event_category_id' => $this->category->id,
            'full_name' => 'Peserta RPC',
            'id_number' => '3578099999990001',
            'gender' => 'male',
            'birth_date' => '1990-01-01',
            'phone' => '0812345678',
            'email' => 'rpc@test.com',
            'emergency_contact_name' => 'Kontak',
            'emergency_contact_phone' => '0812345679',
            'emergency_contact_relation' => 'Keluarga',
            'bib_number' => '10K1099',
            'is_rpc_claimed' => false,
        ]);

        // 1. Verifikasi QR
        $verifyRes = $this->postJson('/crew/verify', ['token' => $participant->qr_token]);
        $verifyRes->assertStatus(200);
        $verifyRes->assertJsonPath('participant.bib_number', '10K1099');
        $verifyRes->assertJsonPath('participant.is_rpc_claimed', false);

        // 2. Serahkan Race Pack (Claim)
        $claimRes = $this->postJson('/crew/claim', ['participant_id' => $participant->id]);
        $claimRes->assertStatus(200);
        $this->assertTrue($participant->fresh()->is_rpc_claimed);

        // 3. Coba klaim kedua kali (harus ditolak)
        $doubleClaimRes = $this->postJson('/crew/claim', ['participant_id' => $participant->id]);
        $doubleClaimRes->assertStatus(422);
    }

    public function test_proxy_collection_records_proxy_collector_nik(): void
    {
        $order = Order::create([
            'event_id' => $this->event->id,
            'order_code' => 'JLTX-PROXY-001',
            'customer_name' => 'Peserta Diwakilkan',
            'customer_email' => 'proxy@test.com',
            'customer_phone' => '0812345678',
            'grand_total' => 250000,
            'status' => 'paid',
        ]);

        $participant = Participant::create([
            'order_id' => $order->id,
            'event_category_id' => $this->category->id,
            'full_name' => 'Peserta Diwakilkan',
            'id_number' => '3578011111110001',
            'gender' => 'female',
            'birth_date' => '1992-01-01',
            'phone' => '0812345678',
            'email' => 'proxy@test.com',
            'emergency_contact_name' => 'Kontak',
            'emergency_contact_phone' => '0812345679',
            'emergency_contact_relation' => 'Keluarga',
            'bib_number' => '10K1088',
        ]);

        // Klaim dengan Surat Kuasa
        $claimRes = $this->postJson('/crew/claim', [
            'participant_id' => $participant->id,
            'is_proxy' => true,
            'proxy_name' => 'Kawan Satu Komunitas',
            'proxy_nik' => '3578022222220002',
        ]);

        $claimRes->assertStatus(200);

        $fresh = $participant->fresh();
        $this->assertTrue($fresh->is_rpc_claimed);
        $this->assertTrue($fresh->is_proxy_claimed);
        $this->assertEquals('Kawan Satu Komunitas', $fresh->proxy_collector_name);
        $this->assertEquals('3578022222220002', $fresh->proxy_collector_id_number);
    }

    public function test_public_runner_can_register_and_checkout_to_tripay(): void
    {
        $jersey = \App\Models\JerseySize::create([
            'event_id' => $this->event->id,
            'size_name' => 'L',
            'gender_type' => 'unisex',
            'stock' => 50,
            'allocated_stock' => 0,
        ]);

        $payload = [
            'category_id' => $this->category->id,
            'jersey_size_id' => $jersey->id,
            'full_name' => 'Fajar Pratama',
            'bib_name' => 'FAJAR P',
            'id_type' => 'KTP',
            'id_number' => '3271012345670001',
            'gender' => 'male',
            'birth_date' => '1994-08-17',
            'blood_type' => 'B+',
            'phone' => '081288887777',
            'email' => 'fajar@running.id',
            'emergency_contact_name' => 'Ratna',
            'emergency_contact_phone' => '081288887778',
            'emergency_contact_relation' => 'Istri',
            'payment_method' => 'QRIS',
            'waiver_accepted' => '1',
        ];

        $checkoutRes = $this->post("/events/{$this->event->slug}/checkout", $payload);

        $order = Order::where('customer_email', 'fajar@running.id')->first();
        $this->assertNotNull($order);
        $checkoutRes->assertRedirect("https://tripay.co.id/checkout/TP-TEST-{$order->order_code}");

        // Test Invoice View
        $invoiceRes = $this->get("/orders/{$order->order_code}");
        $invoiceRes->assertStatus(200);
        $invoiceRes->assertSee('Fajar Pratama');
        $invoiceRes->assertSee('Menunggu Pembayaran');

        // Test Sandbox Simulate Payment
        $simulateRes = $this->post("/orders/{$order->order_code}/simulate");
        $simulateRes->assertRedirect(route('public.order.show', $order->order_code));

        $paidOrder = $order->fresh();
        $this->assertEquals('paid', $paidOrder->status);
        $this->assertNotNull($paidOrder->participants->first()->bib_number);

        // Test resend ticket
        $resendRes = $this->post("/orders/{$order->order_code}/resend-ticket");
        $resendRes->assertRedirect(route('public.order.show', $order->order_code));
        $resendRes->assertSessionHas('success');
    }

    public function test_buyer_can_register_multiple_runners_in_single_order(): void
    {
        $category5k = \App\Models\EventCategory::create([
            'event_id' => $this->event->id,
            'name' => '5K Fun Run',
            'distance_km' => 5.0,
            'normal_price' => 150000,
            'quota' => 50,
            'bib_prefix' => '5K',
            'bib_start_number' => 5001,
            'is_active' => true,
        ]);

        $jerseyM = \App\Models\JerseySize::create([
            'event_id' => $this->event->id,
            'size_name' => 'M',
            'gender_type' => 'unisex',
            'stock' => 50,
            'allocated_stock' => 0,
        ]);

        $jerseyL = \App\Models\JerseySize::create([
            'event_id' => $this->event->id,
            'size_name' => 'L',
            'gender_type' => 'unisex',
            'stock' => 50,
            'allocated_stock' => 0,
        ]);

        $payload = [
            'buyer_name' => 'Budi Santoso',
            'buyer_email' => 'budi@runnerclub.id',
            'buyer_phone' => '081299990000',
            'payment_method' => 'QRIS',
            'waiver_accepted' => '1',
            'participants' => [
                [
                    'category_id' => $this->category->id, // 250.000
                    'jersey_size_id' => $jerseyL->id,
                    'full_name' => 'Budi Santoso',
                    'bib_name' => 'BUDI S',
                    'id_type' => 'KTP',
                    'id_number' => '3271011111110001',
                    'gender' => 'male',
                    'birth_date' => '1992-05-10',
                    'blood_type' => 'O+',
                    'phone' => '081299990000',
                    'email' => 'budi@runnerclub.id',
                    'emergency_contact_name' => 'Siti',
                    'emergency_contact_phone' => '081299990001',
                    'emergency_contact_relation' => 'Istri',
                ],
                [
                    'category_id' => $category5k->id, // 150.000
                    'jersey_size_id' => $jerseyM->id,
                    'full_name' => 'Andi Wijaya',
                    'bib_name' => 'ANDI W',
                    'id_type' => 'KTP',
                    'id_number' => '3271012222220002',
                    'gender' => 'male',
                    'birth_date' => '1995-09-20',
                    'blood_type' => 'A+',
                    'phone' => '081299990002',
                    'email' => 'andi@runnerclub.id',
                    'emergency_contact_name' => 'Siti',
                    'emergency_contact_phone' => '081299990001',
                    'emergency_contact_relation' => 'Teman Club',
                ],
            ],
        ];

        $checkoutRes = $this->post("/events/{$this->event->slug}/checkout", $payload);

        $order = Order::where('customer_email', 'budi@runnerclub.id')->first();
        $this->assertNotNull($order);
        $this->assertEquals('pending', $order->status);
        $this->assertEquals(2, $order->participants()->count());

        // Price: 250.000 + 150.000 = 400.000; total = 405.000 (with flat platform fee 5.000)
        $this->assertEquals(400000, $order->subtotal);
        $this->assertEquals(405000, $order->grand_total);

        // Check quota increments
        $this->assertEquals(1, $this->category->fresh()->slots_taken);
        $this->assertEquals(1, $category5k->fresh()->slots_taken);

        // Check invoice page shows both participants
        $invoiceRes = $this->get("/orders/{$order->order_code}");
        $invoiceRes->assertStatus(200);
        $invoiceRes->assertSee('Budi Santoso');
        $invoiceRes->assertSee('Andi Wijaya');

        // Simulate Tripay Payment
        $simulateRes = $this->post("/orders/{$order->order_code}/simulate");
        $simulateRes->assertRedirect(route('public.order.show', $order->order_code));

        $paidOrder = $order->fresh();
        $this->assertEquals('paid', $paidOrder->status);

        $participants = $paidOrder->participants()->get();
        $this->assertCount(2, $participants);
        foreach ($participants as $p) {
            $this->assertNotNull($p->bib_number);
            $this->assertNotEmpty($p->bib_number);
        }
        $this->assertNotEquals($participants[0]->bib_number, $participants[1]->bib_number);
    }

    public function test_mailketing_service_sends_email_with_configured_credentials(): void
    {
        \Illuminate\Support\Facades\Http::fake([
            'api.mailketing.co.id/*' => \Illuminate\Support\Facades\Http::response(['status' => 'success'], 200),
        ]);

        $mailketing = new \App\Services\MailketingService();
        $result = $mailketing->sendEmail(
            recipientEmail: 'runner@jelatix.com',
            recipientName: 'Budi Runner',
            subject: 'Tiket Lari Anda',
            htmlContent: '<h1>Halo Budi</h1>',
            attachmentUrl: 'https://jelatix.com/ticket/download'
        );

        $this->assertTrue($result);

        \Illuminate\Support\Facades\Http::assertSent(function ($request) {
            return $request->url() === 'https://api.mailketing.co.id/api/v1/send'
                && $request['from_email'] === 'hi@jelatix.com'
                && $request['recipient'] === 'runner@jelatix.com'
                && $request['subject'] === 'Tiket Lari Anda'
                && $request['api_token'] === '308b31d3313311776744479fa8fd7eb3'
                && $request['attach1'] === 'https://jelatix.com/ticket/download';
        });
    }

    public function test_auto_deploy_webhook_rejects_unauthorized_requests(): void
    {
        $response = $this->postJson('/api/deploy', [
            'ref' => 'refs/heads/main',
        ]);

        $response->assertStatus(403);
    }

    public function test_auto_deploy_webhook_accepts_valid_secret(): void
    {
        $response = $this->postJson('/api/deploy?secret=jelatix_auto_deploy_secret_2026', [
            'ref' => 'refs/heads/feature-branch',
        ]);

        // Branch non-main is ignored gracefully
        $response->assertStatus(200);
        $response->assertJson([
            'status' => 'ignored',
        ]);
    }

    public function test_auto_deploy_webhook_handles_github_ping_event(): void
    {
        $response = $this->withHeaders([
            'X-GitHub-Event' => 'ping',
            'X-Deploy-Secret' => 'jelatix_auto_deploy_secret_2026',
        ])->postJson('/api/deploy');

        $response->assertStatus(200);
        $response->assertJson([
            'status' => 'success',
        ]);
    }

    public function test_tripay_webhook_valid_signature_marks_order_as_paid_and_assigns_bib(): void
    {
        $order = Order::create([
            'event_id' => $this->event->id,
            'order_code' => 'JLTX-TP-TEST-001',
            'customer_name' => 'Tripay Tester',
            'customer_email' => 'tripay@test.com',
            'customer_phone' => '0812345678',
            'grand_total' => 255000,
            'status' => 'pending',
        ]);

        $participant = Participant::create([
            'order_id' => $order->id,
            'event_category_id' => $this->category->id,
            'full_name' => 'Tripay Tester',
            'id_number' => '3271019999990001',
            'gender' => 'male',
            'birth_date' => '1990-01-01',
            'phone' => '0812345678',
            'email' => 'tripay@test.com',
            'emergency_contact_name' => 'Kontak',
            'emergency_contact_phone' => '0812345679',
            'emergency_contact_relation' => 'Kerabat',
        ]);

        $privateKey = config('tripay.private_key');
        $payload = [
            'reference' => 'DEV-T3943012345678',
            'merchant_ref' => $order->order_code,
            'payment_method' => 'BRIVA',
            'payment_method_code' => 'BRIVA',
            'total_amount' => 255000,
            'fee_merchant' => 0,
            'fee_customer' => 3000,
            'total_fee' => 3000,
            'amount_received' => 255000,
            'is_closed_payment' => 1,
            'status' => 'PAID',
            'paid_at' => time(),
            'note' => 'Payment testing callback',
        ];

        $rawJson = json_encode($payload);
        $signature = hash_hmac('sha256', $rawJson, $privateKey);

        $response = $this->call(
            'POST',
            '/api/webhooks/tripay',
            [],
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_X_CALLBACK_SIGNATURE' => $signature,
                'HTTP_X_CALLBACK_EVENT' => 'payment_status',
            ],
            $rawJson
        );

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $order->refresh();
        $this->assertEquals('paid', $order->status);
        $this->assertNotNull($order->paid_at);

        $participant->refresh();
        $this->assertNotNull($participant->bib_number);
    }

    public function test_tripay_webhook_invalid_signature_is_rejected(): void
    {
        $response = $this->call(
            'POST',
            '/api/webhooks/tripay',
            [],
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_X_CALLBACK_SIGNATURE' => 'invalid_fake_signature',
                'HTTP_X_CALLBACK_EVENT' => 'payment_status',
            ],
            json_encode(['merchant_ref' => 'TEST'])
        );

        $response->assertStatus(403);
        $response->assertJson(['success' => false, 'message' => 'Invalid signature']);
    }

    public function test_tripay_webhook_handles_test_ping(): void
    {
        $privateKey = config('tripay.private_key');
        $payload = ['merchant_ref' => 'TEST', 'status' => 'PAID'];
        $rawJson = json_encode($payload);
        $signature = hash_hmac('sha256', $rawJson, $privateKey);

        $response = $this->call(
            'POST',
            '/api/webhooks/tripay',
            [],
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_X_CALLBACK_SIGNATURE' => $signature,
                'HTTP_X_CALLBACK_EVENT' => 'payment_status',
            ],
            $rawJson
        );

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
    }

    public function test_registration_sends_payment_pending_email_with_30_minute_warning_and_guide(): void
    {
        $order = Order::create([
            'event_id' => $this->event->id,
            'order_code' => 'JLTX-TEST-PAYPENDING',
            'customer_name' => 'Budi Santoso',
            'customer_email' => 'budi@santoso.id',
            'customer_phone' => '081234567890',
            'subtotal' => 250000,
            'platform_fee' => 5000,
            'grand_total' => 255000,
            'status' => 'pending',
            'tripay_reference' => 'DEV-T39430TEST123',
            'tripay_payment_method' => 'BRIVA',
            'tripay_pay_code' => '442711669584357',
            'tripay_checkout_url' => 'https://tripay.co.id/checkout/DEV-T39430TEST123',
            'expired_at' => now()->addMinutes(30),
        ]);

        $jersey = \App\Models\JerseySize::create([
            'event_id' => $this->event->id,
            'size_name' => 'XL',
            'gender_type' => 'unisex',
            'stock' => 20,
            'allocated_stock' => 1,
        ]);

        $participant = Participant::create([
            'order_id' => $order->id,
            'event_category_id' => $this->category->id,
            'jersey_size_id' => $jersey->id,
            'full_name' => 'Budi Santoso',
            'bib_name' => 'BUDI S',
            'id_type' => 'KTP',
            'id_number' => '3201010101010001',
            'gender' => 'male',
            'birth_date' => '1995-05-05',
            'blood_type' => 'O+',
            'phone' => '081234567890',
            'email' => 'budi@santoso.id',
            'emergency_contact_name' => 'Siti',
            'emergency_contact_phone' => '081234567899',
            'emergency_contact_relation' => 'Istri',
        ]);

        // Render blade view check
        $viewContent = view('emails.payment_pending', [
            'order' => $order->fresh(['event', 'participants.category', 'participants.jerseySize', 'items.category']),
            'event' => $this->event,
        ])->render();

        $this->assertStringContainsString('Pendaftaran Berhasil!', $viewContent);
        $this->assertStringContainsString('30 MENIT', $viewContent);
        $this->assertStringContainsString('442711669584357', $viewContent);
        $this->assertStringContainsString('BRIVA', $viewContent);
        $this->assertStringContainsString('https://tripay.co.id/checkout/DEV-T39430TEST123', $viewContent);
        $this->assertStringContainsString('BUDI S', $viewContent);

        // Run the Job directly
        $mockMailketing = \Mockery::mock(\App\Services\MailketingService::class);
        $mockMailketing->shouldReceive('sendEmail')
            ->once()
            ->withArgs(function ($recipientEmail, $recipientName, $subject, $htmlContent) {
                return $recipientEmail === 'budi@santoso.id'
                    && $recipientName === 'Budi Santoso'
                    && str_contains($subject, 'Batas Waktu 30 Menit')
                    && str_contains($htmlContent, '30 MENIT')
                    && str_contains($htmlContent, '442711669584357');
            })
            ->andReturn(true);

        $job = new \App\Jobs\SendPaymentPendingEmailJob($order);
        $job->handle($mockMailketing);
    }

    public function test_superadmin_can_manage_organizers_and_payouts(): void
    {
        $superadmin = \App\Models\User::create([
            'name' => 'Super Admin',
            'email' => 'superadmin@jelatix.com',
            'password' => bcrypt('password'),
            'role' => 'superadmin',
            'is_active' => true,
        ]);

        $this->actingAs($superadmin);

        // 1. Admin can access Organizers list and create page
        $resOrganizers = $this->get('/admin/organizers');
        $resOrganizers->assertStatus(200);

        $resCreateOrganizer = $this->get('/admin/organizers/create');
        $resCreateOrganizer->assertStatus(200);

        // 2. Admin can create Organizer model
        $newOrg = Organizer::create([
            'name' => 'Jakarta Running Community',
            'slug' => 'jakarta-running-community',
            'email' => 'info@jakartarunning.id',
            'phone' => '081299990000',
            'bank_name' => 'Bank Central Asia (BCA)',
            'bank_account_number' => '8820123456',
            'bank_account_holder' => 'PT Jakarta Lari Sejahtera',
            'commission_rate' => 5.0,
            'is_verified' => true,
            'is_active' => true,
        ]);
        $this->assertDatabaseHas('organizers', ['slug' => 'jakarta-running-community']);

        // 3. Admin can access Organizer Payouts list and create page
        $resPayouts = $this->get('/admin/organizer-payouts');
        $resPayouts->assertStatus(200);

        $resCreatePayout = $this->get('/admin/organizer-payouts/create');
        $resCreatePayout->assertStatus(200);

        // 4. Admin can create OrganizerPayout
        $payout = \App\Models\OrganizerPayout::create([
            'organizer_id' => $newOrg->id,
            'event_id' => $this->event->id,
            'milestone_phase' => 'phase_1_closed_reg',
            'requested_amount' => 10000000,
            'platform_fee_deducted' => 500000,
            'net_payout_amount' => 9500000,
            'bank_name' => 'BCA',
            'bank_account_number' => '8820123456',
            'bank_account_holder' => 'PT Jakarta Lari Sejahtera',
            'status' => 'requested',
        ]);
        $this->assertDatabaseHas('organizer_payouts', ['id' => $payout->id, 'net_payout_amount' => 9500000]);

        // 5. Test payout status update to transferred
        $payout->update([
            'status' => 'transferred',
            'transferred_at' => now(),
            'approved_by_user_id' => $superadmin->id,
        ]);
        $this->assertEquals('transferred', $payout->fresh()->status);
        $this->assertEquals($superadmin->id, $payout->fresh()->approved_by_user_id);
    }
}



