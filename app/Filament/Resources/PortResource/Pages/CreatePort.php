<?php

namespace App\Filament\Resources\PortResource\Pages;

use App\Filament\Resources\PortResource;
use Filament\Resources\Pages\CreateRecord;

class CreatePort extends CreateRecord
{
    protected static string $resource = PortResource::class;
    protected ?string $heading = 'إضافة ميناء';

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
