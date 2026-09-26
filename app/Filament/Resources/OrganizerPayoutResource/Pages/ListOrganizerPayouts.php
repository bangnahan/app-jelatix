<?php

namespace App\Filament\Resources\OrganizerPayoutResource\Pages;

use App\Filament\Resources\OrganizerPayoutResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListOrganizerPayouts extends ListRecords
{
    protected static string $resource = OrganizerPayoutResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
