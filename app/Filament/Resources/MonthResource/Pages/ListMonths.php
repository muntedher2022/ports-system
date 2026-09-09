<?php

namespace App\Filament\Resources\MonthResource\Pages;

use App\Filament\Resources\MonthResource;
use Filament\Resources\Pages\ListRecords;

class ListMonths extends ListRecords
{
    protected static string $resource = MonthResource::class;
    protected ?string $heading = 'الأشهر';

    protected function getHeaderActions(): array
    {
        return [];
    }
}
