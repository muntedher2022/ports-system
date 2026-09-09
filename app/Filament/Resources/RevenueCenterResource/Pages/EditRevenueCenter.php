<?php
namespace App\Filament\Resources\RevenueCenterResource\Pages;
use App\Filament\Resources\RevenueCenterResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditRevenueCenter extends EditRecord
{
    protected static string $resource = RevenueCenterResource::class;
    protected ?string $heading = 'تعديل مركز الإيراد';
    protected function getHeaderActions(): array { return [DeleteAction::make()->label('حذف')]; }
    protected function getRedirectUrl(): string { return $this->getResource()::getUrl('index'); }
}
