<?php

namespace App\Filament\Resources\CargoEntityResource\Pages;

use App\Filament\Resources\CargoEntityResource;
use Filament\Resources\Pages\CreateRecord;

class CreateCargoEntity extends CreateRecord
{
    protected static string $resource = CargoEntityResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
