<?php
namespace App\Filament\Resources\RevenueCenterResource\Pages;
use App\Filament\Resources\RevenueCenterResource;
use Filament\Resources\Pages\CreateRecord;

class CreateRevenueCenter extends CreateRecord
{
    protected static string $resource = RevenueCenterResource::class;
    protected ?string $heading = 'إضافة مركز إيراد';
    protected function getRedirectUrl(): string { return $this->getResource()::getUrl('index'); }
}
