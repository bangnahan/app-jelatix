<?php

namespace App\Filament\Resources\OrganizerPayoutResource\Pages;

use App\Filament\Resources\OrganizerPayoutResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditOrganizerPayout extends EditRecord
{
    protected static string $resource = OrganizerPayoutResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        if (in_array($data['status'] ?? '', ['approved', 'transferred']) && empty($this->record->approved_by_user_id)) {
            $data['approved_by_user_id'] = auth()->id();
        }
        return $data;
    }
}
