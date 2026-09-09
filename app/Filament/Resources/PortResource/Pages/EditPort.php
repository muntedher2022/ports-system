<?php

namespace App\Filament\Resources\PortResource\Pages;

use App\Filament\Resources\PortResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditPort extends EditRecord
{
    protected static string $resource = PortResource::class;
    protected ?string $heading = 'تعديل بيانات الميناء';

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()->label('حذف'),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
