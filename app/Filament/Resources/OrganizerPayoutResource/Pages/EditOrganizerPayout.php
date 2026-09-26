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
}
