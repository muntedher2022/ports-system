<?php
namespace App\Filament\Resources\FiscalYearResource\Pages;
use App\Filament\Resources\FiscalYearResource;
use Filament\Actions;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListFiscalYears extends ListRecords
{
    protected static string $resource = FiscalYearResource::class;
    protected ?string $heading = 'السنوات المالية';
    protected function getHeaderActions(): array { return [CreateAction::make()->label('إضافة سنة')]; }
}
