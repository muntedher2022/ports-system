<?php

namespace App\Filament\Pages;

use App\Enums\NavigationGroup;
use App\Models\DailyVesselStatus;
use App\Models\FiscalYear;
use App\Models\Month;
use Carbon\Carbon;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;

class DailyVesselStatusMatrix extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedTableCells;

    protected static ?string $navigationLabel = 'مصفوفة وتقارير الموقف الدوري';

    protected static ?string $title = 'إحصائية حركة السفن اليومية (في الانتظار والراسية)';

    protected static string|\UnitEnum|null $navigationGroup = NavigationGroup::VesselTraffic;

    protected static ?int $navigationSort = 2;

    public static function canAccess(): bool
    {
        $user = Auth::user();
        if (! $user) {
            return false;
        }

        return true;
    }

    protected string $view = 'filament.pages.daily-vessel-status-matrix';

    // ─── خصائص الفلترة الذكية ───
    public ?int $selectedFiscalYearId = null;

    public ?int $selectedMonthId = null; // null تعني كافة الأشهر

    public ?string $fromDate = null;

    public ?string $toDate = null;

    public function mount(): void
    {
        // تهيئة السنة والشهر بناءً على أحدث سجل أو الشهر الحالي
        $latestRecord = DailyVesselStatus::orderBy('record_date', 'desc')->first();

        if ($latestRecord && $latestRecord->fiscal_year_id) {
            $this->selectedFiscalYearId = $latestRecord->fiscal_year_id;
            $this->selectedMonthId = $latestRecord->month_id;
        } else {
            $this->selectedFiscalYearId = FiscalYear::where('is_current', true)->first()?->id
                ?? FiscalYear::orderBy('year', 'desc')->first()?->id;

            $this->selectedMonthId = Month::where('month_number', now()->month)->first()?->id;
        }

        $this->syncDatesFromYearAndMonth();
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Grid::make([
                    'default' => 1,
                    'sm' => 2,
                    'md' => 4,
                    'lg' => 4,
                ])->schema([
                    Select::make('selectedFiscalYearId')
                        ->label('السنة المالية')
                        ->options(fn () => FiscalYear::orderBy('year', 'desc')->pluck('year', 'id'))
                        ->live()
                        ->afterStateUpdated(function ($state, $set) {
                            $this->selectedFiscalYearId = $state;
                            $this->syncDatesFromYearAndMonth();
                            $set('fromDate', $this->fromDate);
                            $set('toDate', $this->toDate);
                        }),

                    Select::make('selectedMonthId')
                        ->label('الشهر')
                        ->options(fn () => Month::orderBy('month_number')->pluck('name_ar', 'id'))
                        ->placeholder('-- كافة الأشهر (السنة كاملة) --')
                        ->live()
                        ->afterStateUpdated(function ($state, $set) {
                            $this->selectedMonthId = $state;
                            $this->syncDatesFromYearAndMonth();
                            $set('fromDate', $this->fromDate);
                            $set('toDate', $this->toDate);
                        }),

                    DatePicker::make('fromDate')
                        ->label('من تاريخ')
                        ->native(false)
                        ->displayFormat('d/m/Y')
                        ->format('Y-m-d')
                        ->closeOnDateSelection()
                        ->live(),

                    DatePicker::make('toDate')
                        ->label('إلى تاريخ')
                        ->native(false)
                        ->displayFormat('d/m/Y')
                        ->format('Y-m-d')
                        ->closeOnDateSelection()
                        ->live(),
                ]),
            ]);
    }

    public function updatedSelectedFiscalYearId(): void
    {
        $this->syncDatesFromYearAndMonth();
    }

    public function updatedSelectedMonthId(): void
    {
        $this->syncDatesFromYearAndMonth();
    }

    protected function syncDatesFromYearAndMonth(): void
    {
        $year = FiscalYear::find($this->selectedFiscalYearId)?->year ?? (int) date('Y');

        if ($this->selectedMonthId) {
            $monthNum = Month::find($this->selectedMonthId)?->month_number ?? 10;
            $start = Carbon::create($year, $monthNum, 1)->startOfMonth();
            $this->fromDate = $start->format('Y-m-d');
            $this->toDate = $start->copy()->endOfMonth()->format('Y-m-d');
        } else {
            // كافة الأشهر: السنة كاملة
            $this->fromDate = "{$year}-01-01";
            $this->toDate = "{$year}-12-31";
        }

        if (isset($this->form)) {
            $this->form->fill([
                'selectedFiscalYearId' => $this->selectedFiscalYearId,
                'selectedMonthId' => $this->selectedMonthId,
                'fromDate' => $this->fromDate,
                'toDate' => $this->toDate,
            ]);
        }
    }

    public function getCleanFromDateProperty(): string
    {
        return $this->fromDate ? Carbon::parse($this->fromDate)->format('Y-m-d') : '';
    }

    public function getCleanToDateProperty(): string
    {
        return $this->toDate ? Carbon::parse($this->toDate)->format('Y-m-d') : '';
    }

    public function getFiscalYearsProperty(): Collection
    {
        return FiscalYear::orderBy('year', 'desc')->get();
    }

    public function getMonthsProperty(): Collection
    {
        return Month::orderBy('month_number', 'asc')->get();
    }

    public function getRecordsProperty(): Collection
    {
        $from = $this->fromDate ? Carbon::parse($this->fromDate)->format('Y-m-d') : null;
        $to = $this->toDate ? Carbon::parse($this->toDate)->format('Y-m-d') : null;

        return DailyVesselStatus::query()
            ->when($from, fn ($q) => $q->whereDate('record_date', '>=', $from))
            ->when($to, fn ($q) => $q->whereDate('record_date', '<=', $to))
            ->orderBy('record_date', 'asc')
            ->get();
    }

    public function getSummaryProperty(): array
    {
        $records = $this->records;
        $count = $records->count();

        $sums = [
            'oil_port_waiting' => $records->sum('oil_port_waiting'),
            'oil_port_berth' => $records->sum('oil_port_berth'),
            'anchorage_waiting' => $records->sum('anchorage_waiting'),
            'um_qasr_north' => $records->sum('um_qasr_north'),
            'um_qasr_south' => $records->sum('um_qasr_south'),
            'khor_al_zubair' => $records->sum('khor_al_zubair'),
            'abu_flus' => $records->sum('abu_flus'),
            'lpg_berth' => $records->sum('lpg_berth'),
            'control_vessels_in' => $records->sum('control_vessels_in'),
            'control_vessels_out' => $records->sum('control_vessels_out'),
            'total_berthed' => $records->sum('total_berthed'),
            'total_overall' => $records->sum('total_overall'),
        ];

        $averages = [];
        foreach ($sums as $key => $val) {
            $averages[$key] = $count > 0 ? round($val / $count, 1) : 0;
        }

        $peakRecord = $records->sortByDesc('total_overall')->first();

        return [
            'days_count' => $count,
            'sums' => $sums,
            'averages' => $averages,
            'peak_record' => $peakRecord,
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('create_record')
                ->label('تسجيل موقف اليوم')
                ->icon('heroicon-o-plus')
                ->color('primary')
                ->url(fn (): string => \App\Filament\Resources\DailyVesselStatusResource::getUrl('create')),

            Action::make('import_marine_control')
                ->label('استيراد موقف السيطرة البحرية')
                ->icon('heroicon-o-arrow-up-tray')
                ->color('warning')
                ->form([
                    FileUpload::make('marine_control_file')
                        ->label('اختر ملف ملخص موقف السيطرة البحرية (.xlsx)')
                        ->acceptedFileTypes(['application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'application/vnd.ms-excel'])
                        ->disk('local')
                        ->directory('temp_imports')
                        ->required(),
                ])
                ->action(function (array $data): void {
                    $filePath = storage_path('app/'.$data['marine_control_file']);
                    if (! file_exists($filePath)) {
                        Notification::make()->title('الملف غير موجود')->danger()->send();

                        return;
                    }

                    $importedCount = $this->importMarineControlFile($filePath);
                    @unlink($filePath);

                    Notification::make()
                        ->title("تم تحديث موقف السيطرة البحرية لـ {$importedCount} يوم بنجاح!")
                        ->success()
                        ->send();
                }),

            Action::make('import_excel')
                ->label('استيراد من ملف Excel')
                ->icon('heroicon-o-document-arrow-up')
                ->color('gray')
                ->form([
                    FileUpload::make('excel_file')
                        ->label('اختر ملف موقف السفن (.xlsx)')
                        ->acceptedFileTypes(['application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'application/vnd.ms-excel'])
                        ->disk('local')
                        ->directory('temp_imports')
                        ->required(),
                ])
                ->action(function (array $data): void {
                    $filePath = storage_path('app/'.$data['excel_file']);
                    if (! file_exists($filePath)) {
                        Notification::make()->title('الملف غير موجود')->danger()->send();

                        return;
                    }

                    $importedCount = $this->importExcelFile($filePath);
                    @unlink($filePath);

                    Notification::make()
                        ->title("تم استيراد وحفظ {$importedCount} سجل بنجاح!")
                        ->success()
                        ->send();
                }),
        ];
    }

    protected function importExcelFile(string $filePath): int
    {
        $rows = Excel::toArray([], $filePath)[0] ?? [];
        $imported = 0;

        foreach ($rows as $index => $row) {
            if ($index < 6) {
                continue;
            }

            $dateVal = $row[0] ?? null;
            if (! $dateVal) {
                continue;
            }

            if (is_string($dateVal) && str_contains($dateVal, 'مجموع')) {
                break;
            }

            $date = null;
            if ($dateVal instanceof \DateTimeInterface) {
                $date = Carbon::instance($dateVal)->format('Y-m-d');
            } elseif (is_numeric($dateVal)) {
                $date = Carbon::instance(\PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($dateVal))->format('Y-m-d');
            } else {
                try {
                    $date = Carbon::parse(trim($dateVal))->format('Y-m-d');
                } catch (\Exception $e) {
                    continue;
                }
            }

            if (! $date) {
                continue;
            }

            DailyVesselStatus::updateOrCreate(
                ['record_date' => $date],
                [
                    'oil_port_waiting' => (int) ($row[1] ?? 0),
                    'oil_port_berth' => (int) ($row[2] ?? 0),
                    'anchorage_waiting' => (int) ($row[3] ?? 0),
                    'um_qasr_north' => (int) ($row[4] ?? 0),
                    'um_qasr_south' => (int) ($row[5] ?? 0),
                    'khor_al_zubair' => (int) ($row[6] ?? 0),
                    'abu_flus' => (int) ($row[7] ?? 0),
                    'lpg_berth' => (int) ($row[8] ?? 0),
                ]
            );

            $imported++;
        }

        return $imported;
    }

    protected function importMarineControlFile(string $filePath): int
    {
        $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($filePath);
        $sheet = $spreadsheet->getSheet(0);
        $highestRow = $sheet->getHighestRow();
        $imported = 0;

        for ($row = 3; $row <= $highestRow; $row++) {
            $dateCell = trim((string) $sheet->getCell("A$row")->getFormattedValue());
            if (! $dateCell || str_contains($dateCell, 'المجموع')) {
                continue;
            }

            try {
                $date = Carbon::parse($dateCell)->format('Y-m-d');
            } catch (\Exception $e) {
                continue;
            }

            $in = (int) $sheet->getCell("B$row")->getValue();
            $out = (int) $sheet->getCell("C$row")->getValue();
            $waiting = (int) $sheet->getCell("E$row")->getValue();

            $record = DailyVesselStatus::where('record_date', $date)->first();
            if ($record) {
                $record->update([
                    'control_vessels_in' => $in,
                    'control_vessels_out' => $out,
                    'anchorage_waiting' => $waiting,
                ]);
                $imported++;
            } else {
                DailyVesselStatus::create([
                    'record_date' => $date,
                    'control_vessels_in' => $in,
                    'control_vessels_out' => $out,
                    'anchorage_waiting' => $waiting,
                ]);
                $imported++;
            }
        }

        return $imported;
    }
}
