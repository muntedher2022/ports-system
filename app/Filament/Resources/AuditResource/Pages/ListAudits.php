<?php

namespace App\Filament\Resources\AuditResource\Pages;

use App\Filament\Resources\AuditResource;
use Filament\Resources\Pages\ListRecords;

class ListAudits extends ListRecords
{
    protected static string $resource = AuditResource::class;
    protected ?string $heading = 'سجل تتبع العمليات والأنشطة (Audit Trail)';

    protected function getHeaderActions(): array
    {
        return [];
    }
}
