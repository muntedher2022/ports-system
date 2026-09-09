<?php

namespace App\Filament\Resources\PortResource\Pages;

use App\Filament\Resources\PortResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewPort extends ViewRecord
{
    protected static string $resource = PortResource::class;
    protected ?string $heading = 'بيانات الميناء';

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make()->label('تعديل'),
        ];
    }
}
