<?php

namespace App\Filament\Resources\ContainerEntityResource\Pages;

use App\Filament\Resources\ContainerEntityResource;
use Filament\Resources\Pages\CreateRecord;

class CreateContainerEntity extends CreateRecord
{
    protected static string $resource = ContainerEntityResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
