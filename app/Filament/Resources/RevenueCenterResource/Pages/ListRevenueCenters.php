<?php
namespace App\Filament\Resources\RevenueCenterResource\Pages;
use App\Filament\Resources\RevenueCenterResource;
use Filament\Actions;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListRevenueCenters extends ListRecords
{
    protected static string $resource = RevenueCenterResource::class;
    protected ?string $heading = 'مراكز الإيراد';
    protected function getHeaderActions(): array
    {
        return [CreateAction::make()->label('إضافة مركز إيراد')];
    }
}
