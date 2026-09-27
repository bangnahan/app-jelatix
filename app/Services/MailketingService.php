<?php

namespace App\Services;

use Exception;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MailketingService
{
    protected string $apiToken;

    protected string $apiUrl;

    protected string $fromEmail;

    protected string $fromName;

    public function __construct()
    {
        $this->apiToken = config('mailketing.api_token', '');
        $this->apiUrl = config('mailketing.api_url', 'https://api.mailketing.co.id/api/v1');
        $this->fromEmail = config('mailketing.from_email', 'noreply@jelatix.id');
        $this->fromName = config('mailketing.from_name', 'Jelatix Running Tickets');
    }

    /**
     * Kirim email transaksional melalui Mailketing API
     */
    public function sendEmail(
        string $recipientEmail,
        string $recipientName,
        string $subject,
        string $htmlContent,
        ?string $attachmentBase64 = null,
        ?string $attachmentName = null,
        ?string $attachmentUrl = null
    ): bool {
        // Jika token belum diset di .env, log konten dan return true (agar tidak crash saat dev lokal)
        if (empty($this->apiToken)) {
            Log::info("Mailketing [MOCK]: Sending to {$recipientEmail} - {$subject}");

            return true;
        }

        try {
            $payload = [
                'api_token' => $this->apiToken,
                'from_email' => $this->fromEmail,
                'from_name' => $this->fromName,
                'recipient' => $recipientEmail,
                'recipient_name' => $recipientName,
                'subject' => $subject,
                'content' => $htmlContent,
            ];

            if ($attachmentUrl) {
                $payload['attach1'] = $attachmentUrl;
            } elseif ($attachmentBase64 && $attachmentName) {
                $payload['attachment'] = $attachmentBase64;
                $payload['attachment_name'] = $attachmentName;
            }

            // Mailketing API v1 menerima x-www-form-urlencoded
            $response = Http::asForm()->timeout(15)->post("{$this->apiUrl}/send", $payload);

            if ($response->successful()) {
                Log::info("Mailketing sent successfully to {$recipientEmail}: ".$response->body());

                return true;
            }

            Log::error("Mailketing API failed to {$recipientEmail}: ".$response->body());

            return false;
        } catch (Exception $e) {
            Log::error("Mailketing exception for {$recipientEmail}: ".$e->getMessage());

            return false;
        }
    }
}
