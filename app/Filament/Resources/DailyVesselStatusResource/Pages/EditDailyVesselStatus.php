<?php

namespace App\Filament\Resources\DailyVesselStatusResource\Pages;

use App\Filament\Resources\DailyVesselStatusResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditDailyVesselStatus extends EditRecord
{
    protected static string $resource = DailyVesselStatusResource::class;

    protected static ?string $title = 'تعديل الموقف اليومي للسفن';

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()
                ->label('حذف الموقف'),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
