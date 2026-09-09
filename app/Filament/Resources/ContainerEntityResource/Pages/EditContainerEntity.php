<?php

namespace App\Filament\Resources\ContainerEntityResource\Pages;

use App\Filament\Resources\ContainerEntityResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditContainerEntity extends EditRecord
{
    protected static string $resource = ContainerEntityResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->label('حذف')
                ->successNotificationTitle('تم إرسال الجهة إلى سلة المحذوفات'),
            Actions\RestoreAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
