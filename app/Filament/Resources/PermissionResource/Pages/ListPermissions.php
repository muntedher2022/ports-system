<?php

namespace App\Filament\Resources\PermissionResource\Pages;

use App\Filament\Resources\PermissionResource;
use Filament\Resources\Pages\ListRecords;

class ListPermissions extends ListRecords
{
    protected static string $resource = PermissionResource::class;
    protected ?string $heading = 'دليل صلاحيات النظام';
    protected ?string $subheading = 'استعراض وتتبع كافة الصلاحيات البرمجية والأدوار المرتبطة بها في النظام';

    protected function getHeaderActions(): array
    {
        return [];
    }
}
