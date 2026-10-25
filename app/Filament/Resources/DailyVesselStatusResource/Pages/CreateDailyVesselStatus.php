<?php

namespace App\Filament\Resources\DailyVesselStatusResource\Pages;

use App\Filament\Resources\DailyVesselStatusResource;
use Filament\Resources\Pages\CreateRecord;

class CreateDailyVesselStatus extends CreateRecord
{
    protected static string $resource = DailyVesselStatusResource::class;

    protected static ?string $title = 'تسجيل موقف سفن يومي جديد';

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
