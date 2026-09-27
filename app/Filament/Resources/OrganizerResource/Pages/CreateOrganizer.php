<?php

namespace App\Filament\Resources\OrganizerResource\Pages;

use App\Filament\Resources\OrganizerResource;
use App\Models\User;
use App\Services\MailketingService;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Log;

class CreateOrganizer extends CreateRecord
{
    protected static string $resource = OrganizerResource::class;

    protected ?array $userData = null;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $this->userData = [
            'create_user_account' => ! empty($data['create_user_account']),
            'pic_name' => $data['pic_name'] ?? null,
            'pic_email' => $data['pic_email'] ?? null,
            'pic_password' => $data['pic_password'] ?? null,
            'send_credentials_email' => ! empty($data['send_credentials_email']),
        ];

        unset(
            $data['create_user_account'],
            $data['pic_name'],
            $data['pic_email'],
            $data['pic_password'],
            $data['send_credentials_email']
        );

        return $data;
    }

    protected function afterCreate(): void
    {
        if ($this->userData && $this->userData['create_user_account'] && ! empty($this->userData['pic_email'])) {
            $user = User::create([
                'organizer_id' => $this->record->id,
                'name' => $this->userData['pic_name'] ?: $this->record->name,
                'email' => $this->userData['pic_email'],
                'password' => bcrypt($this->userData['pic_password']),
                'role' => 'organizer_owner',
                'is_active' => true,
            ]);

            if ($this->userData['send_credentials_email']) {
                $loginUrl = rtrim(config('app.url', 'https://app.jelatix.com'), '/').'/organizer';
                if (str_contains($loginUrl, 'localhost') || str_contains($loginUrl, '127.0.0.1')) {
                    $loginUrl = 'https://app.jelatix.com/organizer';
                }

                $html = view('emails.organizer_credentials', [
                    'organizer' => $this->record,
                    'userName' => $user->name,
                    'userEmail' => $user->email,
                    'password' => $this->userData['pic_password'],
                    'loginUrl' => $loginUrl,
                ])->render();

                try {
                    app(MailketingService::class)->sendEmail(
                        recipientEmail: $user->email,
                        recipientName: $user->name,
                        subject: "[Akses Portal EO] Kredensial Login Penyelenggara: {$this->record->name}",
                        htmlContent: $html
                    );
                } catch (\Exception $e) {
                    Log::error('Gagal mengirim email kredensial EO: '.$e->getMessage());
                }
            }
        }
    }
}
