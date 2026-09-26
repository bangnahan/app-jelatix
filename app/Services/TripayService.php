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
            ])->get("{$this->baseUrl}/merchant/payment-channel");

            if ($response->successful()) {
                return $response->json('data') ?? [];
            }

            Log::error('Tripay getPaymentChannels error: ' . $response->body());
            return [];
        } catch (Exception $e) {
            Log::error('Tripay getPaymentChannels exception: ' . $e->getMessage());
            return [];
        }
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

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->apiKey,
        ])->post("{$this->baseUrl}/transaction/create", $payload);

        if (!$response->successful()) {
            Log::error("Tripay createTransaction error [{$order->order_code}]: " . $response->body());
            throw new Exception($response->json('message') ?? 'Gagal membuat transaksi Tripay');
        }

        $data = $response->json('data');

        // Simpan data transaksi ke model Order
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
