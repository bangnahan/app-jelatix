<?php

namespace App\Filament\Resources\OrganizerPayoutResource\Pages;

use App\Filament\Resources\OrganizerPayoutResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateOrganizerPayout extends CreateRecord
{
    protected static string $resource = OrganizerPayoutResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        if (in_array($data['status'] ?? '', ['approved', 'transferred'])) {
            $data['approved_by_user_id'] = auth()->id();
        }
        return $data;
    }
}
