<?php

namespace App\Filament\Organizer\Resources\ParticipantResource\Pages;

use App\Filament\Organizer\Resources\ParticipantResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditParticipant extends EditRecord
{
    protected static string $resource = ParticipantResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
