<?php

use App\Providers\AppServiceProvider;
use App\Providers\Filament\AdminPanelProvider;
use App\Providers\Filament\OrganizerPanelProvider;

return [
    AppServiceProvider::class,
    AdminPanelProvider::class,
    OrganizerPanelProvider::class,
];
