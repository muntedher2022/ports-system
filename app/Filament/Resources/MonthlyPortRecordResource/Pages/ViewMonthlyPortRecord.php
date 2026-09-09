<?php

namespace App\Filament\Resources\MonthlyPortRecordResource\Pages;

use App\Filament\Resources\MonthlyPortRecordResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewMonthlyPortRecord extends ViewRecord
{
    protected static string $resource = MonthlyPortRecordResource::class;
    protected ?string $heading = 'عرض السجل التشغيلي الشهري';

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make()->label('تعديل'),
        ];
    }
}
