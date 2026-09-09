<?php
namespace App\Filament\Resources\FiscalYearResource\Pages;
use App\Filament\Resources\FiscalYearResource;
use Filament\Resources\Pages\CreateRecord;

class CreateFiscalYear extends CreateRecord
{
    protected static string $resource = FiscalYearResource::class;
    protected ?string $heading = 'إضافة سنة مالية';
    protected function getRedirectUrl(): string { return $this->getResource()::getUrl('index'); }
}
