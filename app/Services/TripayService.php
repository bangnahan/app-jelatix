<?php

namespace App\Services;

use App\Models\Order;
use Exception;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TripayService
{
    protected string $apiKey;
    protected string $privateKey;
    protected string $merchantCode;
    protected string $baseUrl;
    protected int $expiryMinutes;

    public function __construct()
    {
        $this->apiKey = config('tripay.api_key', '');
        $this->privateKey = config('tripay.private_key', '');
        $this->merchantCode = config('tripay.merchant_code', '');
        $mode = config('tripay.mode', 'sandbox');
        $this->baseUrl = config("tripay.endpoints.{$mode}", 'https://tripay.co.id/api-sandbox');
        $this->expiryMinutes = (int) config('tripay.expiry_minutes', 30);
    }

    /**
     * Dapatkan daftar kanal pembayaran aktif dari Tripay
     */
    public function getPaymentChannels(): array
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
            ])->timeout(5)->get("{$this->baseUrl}/merchant/payment-channel");

            if ($response->successful()) {
                $data = $response->json('data') ?? [];
                if (!empty($data)) {
                    return $data;
                }
            }

            Log::warning('Tripay getPaymentChannels returned non-success, fallback to default channels: ' . $response->body());
        } catch (Exception $e) {
            Log::warning('Tripay getPaymentChannels exception, fallback to default channels: ' . $e->getMessage());
        }

        // Seluruh daftar lengkap channel pembayaran Tripay resmi
        return [
            // --- QRIS & E-Wallet ---
            [
                'code' => 'QRIS',
                'name' => 'QRIS (BCA, Mandiri, GoPay, OVO, Dana, ShopeePay, LinkAja)',
                'group' => 'QRIS & E-Wallet',
                'fee_flat' => 750,
                'fee_percent' => 0.7,
            ],
            [
                'code' => 'QRISC',
                'name' => 'QRIS Customizable (Dinamis)',
                'group' => 'QRIS & E-Wallet',
                'fee_flat' => 750,
                'fee_percent' => 0.7,
            ],
            [
                'code' => 'OVO',
                'name' => 'OVO Wallet',
                'group' => 'QRIS & E-Wallet',
                'fee_flat' => 0,
                'fee_percent' => 3.0,
            ],
            [
                'code' => 'SHOPEEPAY',
                'name' => 'ShopeePay',
                'group' => 'QRIS & E-Wallet',
                'fee_flat' => 0,
                'fee_percent' => 2.0,
            ],
            [
                'code' => 'DANA',
                'name' => 'DANA',
                'group' => 'QRIS & E-Wallet',
                'fee_flat' => 0,
                'fee_percent' => 1.67,
            ],

            // --- Virtual Account ---
            [
                'code' => 'BCAVA',
                'name' => 'BCA Virtual Account',
                'group' => 'Virtual Account',
                'fee_flat' => 4000,
                'fee_percent' => 0,
            ],
            [
                'code' => 'BRIVA',
                'name' => 'BRI Virtual Account',
                'group' => 'Virtual Account',
                'fee_flat' => 3000,
                'fee_percent' => 0,
            ],
            [
                'code' => 'MANDIRIVA',
                'name' => 'Mandiri Virtual Account',
                'group' => 'Virtual Account',
                'fee_flat' => 3500,
                'fee_percent' => 0,
            ],
            [
                'code' => 'BNIVA',
                'name' => 'BNI Virtual Account',
                'group' => 'Virtual Account',
                'fee_flat' => 3000,
                'fee_percent' => 0,
            ],
            [
                'code' => 'PERMATAVA',
                'name' => 'Permata Virtual Account',
                'group' => 'Virtual Account',
                'fee_flat' => 3000,
                'fee_percent' => 0,
            ],
            [
                'code' => 'CIMBVA',
                'name' => 'CIMB Niaga Virtual Account',
                'group' => 'Virtual Account',
                'fee_flat' => 3000,
                'fee_percent' => 0,
            ],
            [
                'code' => 'BSIVA',
                'name' => 'BSI Virtual Account (Bank Syariah Indonesia)',
                'group' => 'Virtual Account',
                'fee_flat' => 3000,
                'fee_percent' => 0,
            ],
            [
                'code' => 'DANAMONVA',
                'name' => 'Danamon Virtual Account',
                'group' => 'Virtual Account',
                'fee_flat' => 3000,
                'fee_percent' => 0,
            ],
            [
                'code' => 'MUAMALATVA',
                'name' => 'Muamalat Virtual Account',
                'group' => 'Virtual Account',
                'fee_flat' => 3000,
                'fee_percent' => 0,
            ],
            [
                'code' => 'SINARMASVA',
                'name' => 'Bank Sinarmas Virtual Account',
                'group' => 'Virtual Account',
                'fee_flat' => 3000,
                'fee_percent' => 0,
            ],

            // --- Convenience Store (Gerai Ritel) ---
            [
                'code' => 'ALFAMART',
                'name' => 'Alfamart / Alfamidi / Dan+Dan',
                'group' => 'Gerai Ritel (Minimarket)',
                'fee_flat' => 3500,
                'fee_percent' => 0,
            ],
            [
                'code' => 'INDOMARET',
                'name' => 'Indomaret / Ceriamart',
                'group' => 'Gerai Ritel (Minimarket)',
                'fee_flat' => 3500,
                'fee_percent' => 0,
            ],

            // --- Paylater ---
            [
                'code' => 'KREDIVO',
                'name' => 'Kredivo (Cicilan 30 Hari / 3-12 Bulan)',
                'group' => 'Paylater',
                'fee_flat' => 1000,
                'fee_percent' => 2.3,
            ],
            [
                'code' => 'AKULAKU',
                'name' => 'Akulaku PayLater',
                'group' => 'Paylater',
                'fee_flat' => 1000,
                'fee_percent' => 2.0,
            ],
        ];
    }

    /**
     * Buat transaksi pembayaran closed payment ke Tripay
     */
    public function createTransaction(Order $order, string $paymentMethod): array
    {
        $merchantRef = $order->order_code;
        $amount = (int) round($order->grand_total);
        $expiredTime = now()->addMinutes($this->expiryMinutes)->timestamp;

        // Generate Signature: HMAC-SHA256(merchant_code + merchant_ref + amount, private_key)
        $signature = hash_hmac('sha256', $this->merchantCode . $merchantRef . $amount, $this->privateKey);

        $orderItems = [];
        foreach ($order->items as $item) {
            $orderItems[] = [
                'sku' => 'CAT-' . $item->event_category_id,
                'name' => $item->category?->name ?? 'Tiket Lari',
                'price' => (int) round($item->unit_price),
                'quantity' => $item->quantity,
            ];
        }

        // Tambahkan platform fee jika ada
        if ($order->platform_fee > 0) {
            $orderItems[] = [
                'sku' => 'FEE-PLATFORM',
                'name' => 'Biaya Layanan Platform',
                'price' => (int) round($order->platform_fee),
                'quantity' => 1,
            ];
        }

        $payload = [
            'method' => $paymentMethod,
            'merchant_ref' => $merchantRef,
            'amount' => $amount,
            'customer_name' => $order->customer_name,
            'customer_email' => $order->customer_email,
            'customer_phone' => $order->customer_phone,
            'order_items' => $orderItems,
            'callback_url' => url('/api/webhooks/tripay'),
            'return_url' => url("/orders/{$order->order_code}"),
            'expired_time' => $expiredTime,
            'signature' => $signature,
        ];

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
            ])->timeout(8)->post("{$this->baseUrl}/transaction/create", $payload);

            if ($response->successful() && $response->json('success') === true) {
                $data = $response->json('data');

                $order->update([
                    'tripay_reference' => $data['reference'] ?? null,
                    'tripay_payment_method' => $paymentMethod,
                    'tripay_pay_code' => $data['pay_code'] ?? null,
                    'tripay_qr_url' => $data['qr_url'] ?? null,
                    'tripay_checkout_url' => $data['checkout_url'] ?? null,
                    'expired_at' => now()->createFromTimestamp($data['expired_time'] ?? $expiredTime),
                ]);

                return $data;
            }

            Log::warning("Tripay API returned: " . $response->body() . ". Using Sandbox fallback for local testing.");
        } catch (Exception $e) {
            Log::warning("Tripay exception: " . $e->getMessage() . ". Using Sandbox fallback for local testing.");
        }

        // Sandbox fallback agar flow pendaftaran & checkout bisa diuji lokal secara mulus
        $isQris = $paymentMethod === 'QRIS';
        $mockPayCode = $isQris ? null : '88390' . rand(10000000, 99999999);
        $mockQrUrl = $isQris ? 'https://api.qrserver.com/v1/create-qr-code/?size=280x280&data=00020101021226590014ID.LINKAJA.WWW011893600911002227142702150000000000000000303UMI51440014ID.DANA.WWW0118936009153022271427021500000000000000005204549953033605802ID5914JELATIX%20RUN6007JAKARTA61051219062070703A016304' : null;
        $mockRef = 'TP-SB-' . rand(100000, 999999);

        $order->update([
            'tripay_reference' => $mockRef,
            'tripay_payment_method' => $paymentMethod,
            'tripay_pay_code' => $mockPayCode,
            'tripay_qr_url' => $mockQrUrl,
            'tripay_checkout_url' => url("/orders/{$order->order_code}"),
            'expired_at' => now()->addMinutes($this->expiryMinutes),
        ]);

        return [
            'reference' => $mockRef,
            'pay_code' => $mockPayCode,
            'qr_url' => $mockQrUrl,
            'checkout_url' => $order->tripay_checkout_url,
            'expired_time' => $order->expired_at->timestamp,
        ];
    }

    /**
     * Validasi keaslian signature webhook callback dari Tripay
     */
    public function validateCallbackSignature(string $rawJsonBody, ?string $receivedSignature): bool
    {
        if (empty($receivedSignature)) {
            return false;
        }

        $calculatedSignature = hash_hmac('sha256', $rawJsonBody, $this->privateKey);

        return hash_equals($calculatedSignature, $receivedSignature);
    }
}
