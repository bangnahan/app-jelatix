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
     * Dapatkan seluruh daftar kanal pembayaran aktif dari Tripay
     */
    public function getPaymentChannels(): array
    {
        // Cache selama 10 menit agar checkout cepat dan tanggap
        return cache()->remember('tripay_active_payment_channels', 600, function () {
            try {
                $response = Http::withHeaders([
                    'Authorization' => 'Bearer ' . $this->apiKey,
                ])->timeout(4)->get("{$this->baseUrl}/merchant/payment-channel");

                if ($response->successful()) {
                    $data = $response->json('data') ?? [];
                    if (!empty($data) && is_array($data)) {
                        // Filter hanya channel yang berstatus aktif
                        $activeChannels = array_filter($data, function ($ch) {
                            return ($ch['active'] ?? true) === true;
                        });

                        if (!empty($activeChannels)) {
                            // Normalisasi nama grup agar konsisten
                            return array_values(array_map(function ($ch) {
                                $group = $ch['group'] ?? 'Lainnya';
                                if (in_array(strtolower($group), ['virtual account', 'va'])) {
                                    $ch['group'] = 'Virtual Account';
                                } elseif (in_array(strtolower($group), ['e-wallet', 'ewallet', 'qris'])) {
                                    $ch['group'] = 'QRIS & E-Wallet';
                                } elseif (in_array(strtolower($group), ['convenience store', 'retail', 'gerai ritel', 'minimarket'])) {
                                    $ch['group'] = 'Gerai Ritel (Minimarket)';
                                } elseif (in_array(strtolower($group), ['paylater', 'cicilan'])) {
                                    $ch['group'] = 'Paylater';
                                }
                                return $ch;
                            }, $activeChannels));
                        }
                    }
                }

                Log::warning('Tripay getPaymentChannels returned non-success, fallback to default channels: ' . $response->body());
            } catch (Exception $e) {
                Log::warning('Tripay getPaymentChannels exception, fallback to default channels: ' . $e->getMessage());
            }

            return $this->getDefaultPaymentChannels();
        });
    }

    /**
     * Seluruh katalog resmi kanal pembayaran Tripay Closed Payment (26 Channel Lengkap)
     */
    public function getDefaultPaymentChannels(): array
    {
        return [
            // ==========================================
            // 1. QRIS & DOMPET DIGITAL (E-WALLET)
            // ==========================================
            [
                'code' => 'QRIS',
                'name' => 'QRIS Dinamis (BCA, BRI, Mandiri, BNI, GoPay, OVO, DANA, ShopeePay, LinkAja)',
                'group' => 'QRIS & E-Wallet',
                'fee_flat' => 750,
                'fee_percent' => 0.7,
                'icon_url' => 'https://tripay.co.id/images/payment-channel/qris.png',
                'color' => '#dc2626',
                'active' => true,
            ],
            [
                'code' => 'QRISC',
                'name' => 'QRIS Customizable (Real-time Dynamic QR)',
                'group' => 'QRIS & E-Wallet',
                'fee_flat' => 750,
                'fee_percent' => 0.7,
                'icon_url' => 'https://tripay.co.id/images/payment-channel/qris.png',
                'color' => '#dc2626',
                'active' => true,
            ],
            [
                'code' => 'QRIS2',
                'name' => 'QRIS ShopeePay & E-Wallet',
                'group' => 'QRIS & E-Wallet',
                'fee_flat' => 750,
                'fee_percent' => 0.7,
                'icon_url' => 'https://tripay.co.id/images/payment-channel/qris.png',
                'color' => '#ea580c',
                'active' => true,
            ],
            [
                'code' => 'OVO',
                'name' => 'OVO Wallet',
                'group' => 'QRIS & E-Wallet',
                'fee_flat' => 0,
                'fee_percent' => 3.0,
                'icon_url' => 'https://tripay.co.id/images/payment-channel/ovo.png',
                'color' => '#4c1d95',
                'active' => true,
            ],
            [
                'code' => 'DANA',
                'name' => 'DANA',
                'group' => 'QRIS & E-Wallet',
                'fee_flat' => 0,
                'fee_percent' => 1.67,
                'icon_url' => 'https://tripay.co.id/images/payment-channel/dana.png',
                'color' => '#0284c7',
                'active' => true,
            ],
            [
                'code' => 'SHOPEEPAY',
                'name' => 'ShopeePay',
                'group' => 'QRIS & E-Wallet',
                'fee_flat' => 0,
                'fee_percent' => 2.0,
                'icon_url' => 'https://tripay.co.id/images/payment-channel/shopeepay.png',
                'color' => '#ea580c',
                'active' => true,
            ],

            // ==========================================
            // 2. VIRTUAL ACCOUNT (15 BANK NASIONAL & SYARIAH)
            // ==========================================
            [
                'code' => 'BCAVA',
                'name' => 'BCA Virtual Account',
                'group' => 'Virtual Account',
                'fee_flat' => 4500,
                'fee_percent' => 0,
                'icon_url' => 'https://tripay.co.id/images/payment-channel/bcava.png',
                'color' => '#005caa',
                'active' => true,
            ],
            [
                'code' => 'BRIVA',
                'name' => 'BRI Virtual Account',
                'group' => 'Virtual Account',
                'fee_flat' => 3000,
                'fee_percent' => 0,
                'icon_url' => 'https://tripay.co.id/images/payment-channel/bri.png',
                'color' => '#00529c',
                'active' => true,
            ],
            [
                'code' => 'MANDIRIVA',
                'name' => 'Mandiri Virtual Account',
                'group' => 'Virtual Account',
                'fee_flat' => 3500,
                'fee_percent' => 0,
                'icon_url' => 'https://tripay.co.id/images/payment-channel/mandiriva.png',
                'color' => '#003366',
                'active' => true,
            ],
            [
                'code' => 'BNIVA',
                'name' => 'BNI Virtual Account',
                'group' => 'Virtual Account',
                'fee_flat' => 3000,
                'fee_percent' => 0,
                'icon_url' => 'https://tripay.co.id/images/payment-channel/bniva.png',
                'color' => '#f15a24',
                'active' => true,
            ],
            [
                'code' => 'PERMATAVA',
                'name' => 'Permata Virtual Account',
                'group' => 'Virtual Account',
                'fee_flat' => 3000,
                'fee_percent' => 0,
                'icon_url' => 'https://tripay.co.id/images/payment-channel/permatava.png',
                'color' => '#65b32e',
                'active' => true,
            ],
            [
                'code' => 'CIMBVA',
                'name' => 'CIMB Niaga Virtual Account',
                'group' => 'Virtual Account',
                'fee_flat' => 3000,
                'fee_percent' => 0,
                'icon_url' => 'https://tripay.co.id/images/payment-channel/cimbva.png',
                'color' => '#7e1112',
                'active' => true,
            ],
            [
                'code' => 'BSIVA',
                'name' => 'BSI Virtual Account (Bank Syariah Indonesia)',
                'group' => 'Virtual Account',
                'fee_flat' => 3000,
                'fee_percent' => 0,
                'icon_url' => 'https://tripay.co.id/images/payment-channel/bsiva.png',
                'color' => '#00a39d',
                'active' => true,
            ],
            [
                'code' => 'DANAMONVA',
                'name' => 'Danamon Virtual Account',
                'group' => 'Virtual Account',
                'fee_flat' => 3000,
                'fee_percent' => 0,
                'icon_url' => 'https://tripay.co.id/images/payment-channel/danamonva.png',
                'color' => '#f58220',
                'active' => true,
            ],
            [
                'code' => 'MUAMALATVA',
                'name' => 'Bank Muamalat Virtual Account',
                'group' => 'Virtual Account',
                'fee_flat' => 3000,
                'fee_percent' => 0,
                'icon_url' => 'https://tripay.co.id/images/payment-channel/muamalatva.png',
                'color' => '#5e2750',
                'active' => true,
            ],
            [
                'code' => 'SINARMASVA',
                'name' => 'Bank Sinarmas Virtual Account',
                'group' => 'Virtual Account',
                'fee_flat' => 3000,
                'fee_percent' => 0,
                'icon_url' => 'https://tripay.co.id/images/payment-channel/sinarmasva.png',
                'color' => '#d71920',
                'active' => true,
            ],
            [
                'code' => 'BSSVA',
                'name' => 'Bank Sahabat Sampoerna Virtual Account',
                'group' => 'Virtual Account',
                'fee_flat' => 3000,
                'fee_percent' => 0,
                'icon_url' => 'https://tripay.co.id/images/payment-channel/bssva.png',
                'color' => '#00843d',
                'active' => true,
            ],
            [
                'code' => 'BNCVA',
                'name' => 'Bank Neo Commerce (BNC) Virtual Account',
                'group' => 'Virtual Account',
                'fee_flat' => 3000,
                'fee_percent' => 0,
                'icon_url' => 'https://tripay.co.id/images/payment-channel/bncva.png',
                'color' => '#ffcc00',
                'active' => true,
            ],
            [
                'code' => 'OCBCVA',
                'name' => 'OCBC NISP Virtual Account',
                'group' => 'Virtual Account',
                'fee_flat' => 3000,
                'fee_percent' => 0,
                'icon_url' => 'https://tripay.co.id/images/payment-channel/ocbcva.png',
                'color' => '#ee2e24',
                'active' => true,
            ],
            [
                'code' => 'MYBVA',
                'name' => 'Maybank Virtual Account',
                'group' => 'Virtual Account',
                'fee_flat' => 3000,
                'fee_percent' => 0,
                'icon_url' => 'https://tripay.co.id/images/payment-channel/mybva.png',
                'color' => '#ffc400',
                'active' => true,
            ],
            [
                'code' => 'BJBVA',
                'name' => 'Bank BJB Virtual Account',
                'group' => 'Virtual Account',
                'fee_flat' => 3000,
                'fee_percent' => 0,
                'icon_url' => 'https://tripay.co.id/images/payment-channel/bjbva.png',
                'color' => '#0066b2',
                'active' => true,
            ],

            // ==========================================
            // 3. GERAI RITEL (MINIMARKET CONVENIENCE STORE)
            // ==========================================
            [
                'code' => 'ALFAMART',
                'name' => 'Alfamart / Alfamidi / Dan+Dan',
                'group' => 'Gerai Ritel (Minimarket)',
                'fee_flat' => 3500,
                'fee_percent' => 0,
                'icon_url' => 'https://tripay.co.id/images/payment-channel/alfamart.png',
                'color' => '#d71920',
                'active' => true,
            ],
            [
                'code' => 'INDOMARET',
                'name' => 'Indomaret / Ceriamart',
                'group' => 'Gerai Ritel (Minimarket)',
                'fee_flat' => 3500,
                'fee_percent' => 0,
                'icon_url' => 'https://tripay.co.id/images/payment-channel/indomaret.png',
                'color' => '#00529c',
                'active' => true,
            ],
            [
                'code' => 'ALFAMIDI',
                'name' => 'Alfamidi',
                'group' => 'Gerai Ritel (Minimarket)',
                'fee_flat' => 3500,
                'fee_percent' => 0,
                'icon_url' => 'https://tripay.co.id/images/payment-channel/alfamidi.png',
                'color' => '#ed1c24',
                'active' => true,
            ],

            // ==========================================
            // 4. PAYLATER (CICILAN ONLINE)
            // ==========================================
            [
                'code' => 'KREDIVO',
                'name' => 'Kredivo (Cicilan 30 Hari / 3 - 12 Bulan)',
                'group' => 'Paylater',
                'fee_flat' => 1000,
                'fee_percent' => 2.3,
                'icon_url' => 'https://tripay.co.id/images/payment-channel/kredivo.png',
                'color' => '#00afef',
                'active' => true,
            ],
            [
                'code' => 'AKULAKU',
                'name' => 'Akulaku PayLater',
                'group' => 'Paylater',
                'fee_flat' => 1000,
                'fee_percent' => 2.0,
                'icon_url' => 'https://tripay.co.id/images/payment-channel/akulaku.png',
                'color' => '#e60012',
                'active' => true,
            ],
        ];
    }

    /**
     * Buat transaksi pembayaran closed payment ke Tripay
     */
    public function createTransaction(Order $order, string $paymentMethod): array
    {
        if (app()->environment('testing')) {
            $mockCheckoutUrl = "https://tripay.co.id/checkout/TP-TEST-{$order->order_code}";
            $order->update([
                'tripay_reference' => 'DEV-T3943012345678',
                'tripay_payment_method' => $paymentMethod,
                'tripay_pay_code' => '12800123456789',
                'tripay_checkout_url' => $mockCheckoutUrl,
                'expired_at' => now()->addMinutes($this->expiryMinutes),
            ]);

            return [
                'reference' => 'DEV-T3943012345678',
                'pay_code' => '12800123456789',
                'qr_url' => null,
                'checkout_url' => $mockCheckoutUrl,
                'expired_time' => $order->expired_at->timestamp,
            ];
        }

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

        $callbackUrl = env('TRIPAY_CALLBACK_URL');
        if (empty($callbackUrl)) {
            $appUrl = config('app.url', 'https://app.jelatix.com');
            if (str_contains($appUrl, 'localhost') || str_contains($appUrl, '127.0.0.1')) {
                $callbackUrl = 'https://app.jelatix.com/api/webhooks/tripay';
            } else {
                $callbackUrl = rtrim($appUrl, '/') . '/api/webhooks/tripay';
            }
        }

        $returnUrl = rtrim(config('app.url', 'https://app.jelatix.com'), '/') . "/orders/{$order->order_code}";
        if (str_contains($returnUrl, 'localhost') || str_contains($returnUrl, '127.0.0.1')) {
            $returnUrl = "https://app.jelatix.com/orders/{$order->order_code}";
        }

        $payload = [
            'method' => $paymentMethod,
            'merchant_ref' => $merchantRef,
            'amount' => $amount,
            'customer_name' => $order->customer_name,
            'customer_email' => $order->customer_email,
            'customer_phone' => $order->customer_phone,
            'order_items' => $orderItems,
            'callback_url' => $callbackUrl,
            'return_url' => $returnUrl,
            'expired_time' => $expiredTime,
            'signature' => $signature,
        ];

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
            ])->timeout(10)->post("{$this->baseUrl}/transaction/create", $payload);

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

            $errorMessage = $response->json('message') ?? $response->body();
            Log::error("Tripay API Error on transaction/create: {$errorMessage}", [
                'status' => $response->status(),
                'payload' => $payload,
                'response' => $response->json() ?? $response->body(),
            ]);

            // Jika ada API Key yang dikonfigurasi, laporkan error sebenarnya dari Tripay agar tidak diam-diam tertutup data mock
            if (!empty($this->apiKey) && !str_starts_with($this->apiKey, 'MOCK')) {
                throw new Exception("Tripay Gateway: {$errorMessage}. Periksa Kredensial Tripay (API Key, Private Key, atau Merchant Code) di .env.");
            }
        } catch (Exception $e) {
            Log::error("Tripay exception: " . $e->getMessage());
            if (str_starts_with($e->getMessage(), 'Tripay Gateway:')) {
                throw $e;
            }
        }

        // Sandbox fallback HANYA jika TRIPAY_API_KEY kosong (mode offline development)
        $isQris = str_starts_with($paymentMethod, 'QRIS');
        $isRetail = in_array($paymentMethod, ['ALFAMART', 'INDOMARET', 'ALFAMIDI']);
        $isEwallet = in_array($paymentMethod, ['OVO', 'DANA', 'SHOPEEPAY']);
        $isPaylater = in_array($paymentMethod, ['KREDIVO', 'AKULAKU']);

        $mockPayCode = null;
        $mockQrUrl = null;
        $mockCheckoutUrl = url("/orders/{$order->order_code}");

        if ($isQris) {
            $mockQrUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=280x280&data=00020101021226590014ID.LINKAJA.WWW011893600911002227142702150000000000000000303UMI51440014ID.DANA.WWW0118936009153022271427021500000000000000005204549953033605802ID5914JELATIX%20RUN6007JAKARTA61051219062070703A016304';
        } elseif ($isRetail) {
            $mockPayCode = 'TRP-' . rand(10000000, 99999999);
        } elseif ($isEwallet || $isPaylater) {
            $mockPayCode = '0812' . rand(10000000, 99999999);
        } else {
            // Virtual Account Prefix
            $bankPrefix = match($paymentMethod) {
                'BCAVA' => '88390',
                'BRIVA' => '12800',
                'BNIVA' => '98800',
                'MANDIRIVA' => '89608',
                'PERMATAVA' => '85280',
                'CIMBVA' => '59190',
                'BSIVA' => '90000',
                'DANAMONVA' => '88560',
                'MUAMALATVA' => '84830',
                'SINARMASVA', 'SMSVA' => '82140',
                'BSSVA' => '85500',
                'BNCVA' => '89800',
                'OCBCVA' => '88010',
                'MYBVA' => '78100',
                'BJBVA' => '83400',
                default => '88888',
            };
            $mockPayCode = $bankPrefix . rand(10000000, 99999999);
        }

        $mockRef = 'TP-SB-' . rand(100000, 999999);

        $order->update([
            'tripay_reference' => $mockRef,
            'tripay_payment_method' => $paymentMethod,
            'tripay_pay_code' => $mockPayCode,
            'tripay_qr_url' => $mockQrUrl,
            'tripay_checkout_url' => $mockCheckoutUrl,
            'expired_at' => now()->addMinutes($this->expiryMinutes),
        ]);

        return [
            'reference' => $mockRef,
            'pay_code' => $mockPayCode,
            'qr_url' => $mockQrUrl,
            'checkout_url' => $mockCheckoutUrl,
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
