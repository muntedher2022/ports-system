<?php

namespace App\Filament\Resources\MonthlyPortRecordResource\Pages;

use App\Filament\Resources\MonthlyPortRecordResource;
use App\Models\FiscalYear;
use App\Models\Month;
use App\Models\MonthlyPortRecord;
use App\Models\Port;
use App\Services\SinglePortMonthlyImportService;
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Filament\Schemas\Components\Grid;
use Filament\Support\Enums\Width;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class CreateMonthlyPortRecord extends CreateRecord
{
    protected static string $resource = MonthlyPortRecordResource::class;

    protected ?string $heading = 'إدخال سجل تشغيلي شهري مباشر';

    protected function getHeaderActions(): array
    {
        return [
            Action::make('import_single_port_month')
                ->label('استيراد بيانات هذا الشهر من Excel')
                ->icon('heroicon-o-arrow-up-tray')
                ->color('success')
                ->modalHeading('استيراد بيانات تشغيلية من ملف Excel')
                ->modalDescription('بدلاً من الإدخال اليدوي للحقول، يمكنك رفع ملف الإكسل المعتمد للميناء والشهر ليتم سحب كافة البيانات وحفظها فورياً.')
                ->modalSubmitActionLabel('بدء الاستيراد والحفظ')
                ->modalIcon('heroicon-o-table-cells')
                ->modalWidth(Width::Large)
                ->form([
                    Grid::make(3)->schema([
                        Select::make('port_id')
                            ->label('الميناء')
                            ->options(function () {
                                $user = Auth::user();
                                if ($user?->isPortRestricted() && $user?->port_id) {
                                    return Port::where('id', $user->port_id)->pluck('name_ar', 'id');
                                }

                                return Port::where('is_active', true)
                                    ->where(fn ($q) => $q->where('has_monthly_records', true)->orWhereHas('monthlyPortRecords'))
                                    ->orderBy('sort_order')
                                    ->pluck('name_ar', 'id');
                            })
                            ->default(function () {
                                $user = Auth::user();
                                if ($user?->isPortRestricted() && $user?->port_id) {
                                    return $user->port_id;
                                }

                                return Port::where('is_active', true)->where('has_monthly_records', true)->orderBy('sort_order')->first()?->id;
                            })
                            ->required()
                            ->searchable(),

                        Select::make('fiscal_year_id')
                            ->label('السنة المالية')
                            ->options(FiscalYear::orderBy('year', 'desc')->pluck('year', 'id'))
                            ->default(FiscalYear::where('is_current', true)->first()?->id ?? FiscalYear::latest('year')->first()?->id)
                            ->required(),

                        Select::make('month_id')
                            ->label('الشهر')
                            ->options(Month::orderBy('month_number')->pluck('name_ar', 'id'))
                            ->default(Month::where('month_number', (int) date('n'))->first()?->id ?? Month::first()?->id)
                            ->required(),
                    ]),

                    FileUpload::make('excel_file')
                        ->label('ملف Excel الخاص ببيانات الميناء والشهر')
                        ->helperText('يقبل ملفات Excel (.xlsx, .xls, .csv). يتم قراءة مؤشرات البواخر، الحاويات، البضائع والنفط وحساب المجاميع تلقائياً.')
                        ->acceptedFileTypes([
                            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                            'application/vnd.ms-excel',
                            'text/csv',
                        ])
                        ->required()
                        ->maxSize(51200)
                        ->disk('local')
                        ->directory('imports/single-port-monthly')
                        ->preserveFilenames(false),
                ])
                ->action(function (array $data) {
                    $path = storage_path('app/private/'.$data['excel_file']);
                    if (! file_exists($path)) {
                        $path = storage_path('app/'.$data['excel_file']);
                    }

                    $service = app(SinglePortMonthlyImportService::class);

                    try {
                        $res = $service->import(
                            (int) $data['port_id'],
                            (int) $data['fiscal_year_id'],
                            (int) $data['month_id'],
                            $path
                        );

                        Notification::make()
                            ->success()
                            ->title('تم استيراد وحفظ السجل بنجاح ✓')
                            ->body("ميناء: {$res['port_name']} | شهر: {$res['month_name']} {$res['year']}\nإجمالي البواخر: {$res['total_ships']} | الحاويات TEU: {$res['total_teu']} | البضائع: ".number_format($res['total_cargo']).' طن')
                            ->persistent()
                            ->send();

                        return redirect()->to(MonthlyPortRecordResource::getUrl('edit', ['record' => $res['record']->id]));
                    } catch (\Throwable $e) {
                        Notification::make()
                            ->danger()
                            ->title('خطأ أثناء استيراد الملف')
                            ->body($e->getMessage())
                            ->persistent()
                            ->send();
                    }
                }),
        ];
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['created_by'] = Auth::id();

        return $data;
    }

    protected function handleRecordCreation(array $data): Model
    {
        $existing = MonthlyPortRecord::withTrashed()->where([
            'port_id' => $data['port_id'],
            'fiscal_year_id' => $data['fiscal_year_id'],
            'month_id' => $data['month_id'],
        ])->first();

        if ($existing) {
            if ($existing->trashed()) {
                $existing->restore();
            }
            $existing->update($data);

            return $existing;
        }

        return MonthlyPortRecord::create($data);
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('edit', ['record' => $this->getRecord()]);
    }
}
