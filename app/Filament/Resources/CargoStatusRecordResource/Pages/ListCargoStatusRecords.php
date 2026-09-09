<?php

namespace App\Filament\Resources\CargoStatusRecordResource\Pages;

use App\Filament\Resources\CargoStatusRecordResource;
use App\Models\CargoEntity;
use App\Models\CargoStatusDetail;
use App\Models\CargoStatusRecord;
use App\Models\FiscalYear;
use App\Models\Month;
use App\Models\Port;
use App\Services\ActivityLogger;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ListCargoStatusRecords extends ListRecords
{
    protected static string $resource = CargoStatusRecordResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // ─── زر نسخ بيانات ميناء إلى شهر آخر ───
            Action::make('copy_port_month')
                ->label('نسخ ميناء إلى شهر آخر')
                ->icon('heroicon-o-document-duplicate')
                ->color('success')
                ->modalHeading('نسخ وتكرار بيانات موقف المواد والبضائع لميناء إلى شهر آخر')
                ->modalDescription('يتيح هذا الإجراء نسخ جميع قيود وجهات المواد وأعدادها من شهر وسنة سابقة إلى شهر جديد عند عدم تغير البيانات.')
                ->modalSubmitActionLabel('تنفيذ النسخ')
                ->modalIcon('heroicon-o-document-duplicate')
                ->form([
                    Select::make('port_id')
                        ->label('الميناء')
                        ->options(Port::where('is_active', true)->where('has_cargo_status', true)->orderBy('sort_order')->pluck('name_ar', 'id'))
                        ->required()
                        ->searchable(),

                    Select::make('cargo_type')
                        ->label('نوع المواد')
                        ->options([
                            'abandoned' => '📦 مواد وبضائع متخلفة',
                            'dangerous' => '⚠️ مواد وبضائع خطرة',
                        ])
                        ->default('abandoned')
                        ->required(),

                    Select::make('source_fiscal_year_id')
                        ->label('السنة المالية (المصدر)')
                        ->options(FiscalYear::orderBy('year', 'desc')->pluck('year', 'id'))
                        ->default(fn () => FiscalYear::where('is_current', true)->first()?->id ?? FiscalYear::orderBy('year', 'desc')->first()?->id)
                        ->required(),

                    Select::make('source_month_id')
                        ->label('الشهر (المصدر)')
                        ->options(Month::orderBy('month_number')->pluck('name_ar', 'id'))
                        ->required(),

                    Select::make('target_fiscal_year_id')
                        ->label('السنة المالية (المستهدفة للنسخ إليها)')
                        ->options(FiscalYear::orderBy('year', 'desc')->pluck('year', 'id'))
                        ->default(fn () => FiscalYear::where('is_current', true)->first()?->id ?? FiscalYear::orderBy('year', 'desc')->first()?->id)
                        ->required(),

                    Select::make('target_month_id')
                        ->label('الشهر (المستهدف للنسخ إليه)')
                        ->options(Month::orderBy('month_number')->pluck('name_ar', 'id'))
                        ->required(),

                    DatePicker::make('target_report_date')
                        ->label('تاريخ التقرير الجديد')
                        ->default(now()->toDateString())
                        ->required(),
                ])
                ->action(function (array $data) {
                    $portId = (int) $data['port_id'];
                    $cargoType = $data['cargo_type'];
                    $srcYearId = (int) $data['source_fiscal_year_id'];
                    $srcMonthId = (int) $data['source_month_id'];
                    $targetYearId = (int) $data['target_fiscal_year_id'];
                    $targetMonthId = (int) $data['target_month_id'];
                    $targetReportDate = $data['target_report_date'];

                    // البحث عن سجل المصدر
                    $sourceRecord = CargoStatusRecord::where('port_id', $portId)
                        ->where('cargo_type', $cargoType)
                        ->where('fiscal_year_id', $srcYearId)
                        ->where('month_id', $srcMonthId)
                        ->with('details')
                        ->first();

                    if (! $sourceRecord) {
                        Notification::make()
                            ->title('تعذر النسخ: سجل المصدر غير موجود')
                            ->body('لا توجد بيانات مسجلة للميناء المحدد في سنة وشهر المصدر لنقلها.')
                            ->danger()
                            ->send();
                        return;
                    }

                    // التحقق من أن السجل المستهدف غير موجود
                    $existing = CargoStatusRecord::withTrashed()
                        ->where('port_id', $portId)
                        ->where('cargo_type', $cargoType)
                        ->where('fiscal_year_id', $targetYearId)
                        ->where('month_id', $targetMonthId)
                        ->first();

                    if ($existing) {
                        Notification::make()
                            ->title('تنبيه: السجل المستهدف موجود مسبقاً')
                            ->body('يوجد بالفعل سجل محفوظ لهذا الميناء في الشهر والسنة المستهدفين.')
                            ->warning()
                            ->send();
                        return;
                    }

                    DB::transaction(function () use ($sourceRecord, $portId, $cargoType, $targetYearId, $targetMonthId, $targetReportDate, $srcYearId, $srcMonthId) {
                        $newRecord = CargoStatusRecord::create([
                            'port_id'         => $portId,
                            'cargo_type'      => $cargoType,
                            'fiscal_year_id'  => $targetYearId,
                            'month_id'        => $targetMonthId,
                            'report_date'     => $targetReportDate,
                            'notes'           => $sourceRecord->notes,
                            'created_by'      => Auth::id() ?? 1,
                        ]);

                        foreach ($sourceRecord->details as $detail) {
                            CargoStatusDetail::create([
                                'cargo_status_record_id' => $newRecord->id,
                                'cargo_entity_id'        => $detail->cargo_entity_id,
                                'year_label'             => $detail->year_label,
                                'count'                  => $detail->count,
                                'sort_order'             => $detail->sort_order,
                            ]);
                        }

                        ActivityLogger::log(
                            'created',
                            "نسخ سجل موقف المواد لميناء ({$sourceRecord->port?->name_ar}) من شهر ({$sourceRecord->month?->name_ar}) إلى شهر جديد",
                            CargoStatusRecord::class,
                            $newRecord->id,
                            ['source_record_id' => $sourceRecord->id, 'target_year_id' => $targetYearId, 'target_month_id' => $targetMonthId]
                        );
                    });

                    $portName = Port::find($portId)?->name_ar;
                    $targetMonthName = Month::find($targetMonthId)?->name_ar;
                    $targetYearVal = FiscalYear::find($targetYearId)?->year;

                    Notification::make()
                        ->title('تم نسخ السجل بنجاح')
                        ->body("تم نسخ كافة بيانات موقف المواد لميناء ({$portName}) إلى شهر {$targetMonthName} {$targetYearVal} بنجاح.")
                        ->success()
                        ->send();
                }),

            CreateAction::make()
                ->label('إضافة سجل مواد وبضائع')
                ->color('primary'),
        ];
    }
}
