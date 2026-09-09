<?php

namespace App\Filament\Resources\CargoEntityResource\Pages;

use App\Filament\Resources\CargoEntityResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;

class EditCargoEntity extends EditRecord
{
    protected static string $resource = CargoEntityResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()
                ->label('حذف مؤقت')
                ->modalDescription('سيتم نقل الجهة إلى سلة المحذوفات.')
                ->successNotificationTitle('تم نقل الجهة إلى سلة المحذوفات'),
            RestoreAction::make()->color('success'),
            ForceDeleteAction::make()
                ->label('حذف نهائي')
                ->modalHeading('⚠️ تحذير: حذف نهائي لا يمكن التراجع عنه'),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
