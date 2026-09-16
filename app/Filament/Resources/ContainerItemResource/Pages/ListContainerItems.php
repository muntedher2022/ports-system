<?php

namespace App\Filament\Resources\ContainerItemResource\Pages;

use App\Filament\Resources\ContainerItemResource;
use App\Models\FiscalYear;
use App\Models\Month;
use App\Models\Port;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Forms\Components\Select;
use Filament\Resources\Pages\ListRecords;

class ListContainerItems extends ListRecords
{
    protected static string $resource = ContainerItemResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('export_detailed_excel')
                ->label('تصدير كشف الحاويات (Excel مقسم بحسب الجهات)')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('success')
                ->modalHeading('تصدير كشف الحاويات الفردية إلى ملف Excel مقسم بحسب الجهات')
                ->modalDescription('سيتم تصدير ملف إكسل يحتوي على أوراق عمل متعددة (لكل وزارة وجهة ورقة عمل مستقلة) بنفس التنسيق الرسمي وبكافة تفاصيل الحاويات الموجودة في الميناء حالياً مع استثناء الحاويات المخرجة.')
                ->modalSubmitActionLabel('بدء تحميل ملف Excel')
                ->modalIcon('heroicon-o-arrow-down-tray')
                ->form([
                    Select::make('container_type')
                        ->label('نوع الموقف المراد تصديره')
                        ->options([
                            'abandoned' => '📦 موقف الحاويات المتخلفة',
                            'dangerous' => '⚠️ موقف الحاويات الخطرة',
                        ])
                        ->default('abandoned')
                        ->required(),

                    Select::make('fiscal_year_id')
                        ->label('السنة المالية')
                        ->options(FiscalYear::orderBy('year', 'desc')->pluck('year', 'id'))
                        ->default(fn () => FiscalYear::where('is_current', true)->first()?->id ?? FiscalYear::orderBy('year', 'desc')->first()?->id)
                        ->required(),

                    Select::make('month_id')
                        ->label('الشهر')
                        ->options(Month::orderBy('month_number')->pluck('name_ar', 'id'))
                        ->default(fn () => Month::where('month_number', (int) date('n'))->first()?->id)
                        ->required(),

                    Select::make('port_id')
                        ->label('الميناء')
                        ->options(Port::where('is_active', true)->where('has_container_status', true)->orderBy('sort_order')->pluck('name_ar', 'id'))
                        ->placeholder('كافة الموانئ (مجمّع)')
                        ->searchable(),
                ])
                ->action(function (array $data) {
                    $queryParams = http_build_query([
                        'type'  => $data['container_type'],
                        'year'  => $data['fiscal_year_id'],
                        'month' => $data['month_id'],
                        'port'  => $data['port_id'] ?? null,
                    ]);

                    return redirect()->away(route('admin.containers.export-detailed-excel') . '?' . $queryParams);
                }),

            CreateAction::make()
                ->label('إضافة حاوية جديدة يدوياً')
                ->icon('heroicon-o-plus-circle')
                ->color('primary'),
        ];
    }
}
