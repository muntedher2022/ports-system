<?php

namespace App\Filament\Resources;

use App\Enums\NavigationGroup;
use App\Filament\Resources\ContainerStatusRecordResource\Pages;
use App\Models\ContainerEntity;
use App\Models\ContainerStatusRecord;
use App\Models\FiscalYear;
use App\Models\Month;
use App\Models\Port;
use App\Models\ContainerStatusDetail;
use App\Services\ActivityLogger;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ContainerStatusRecordResource extends Resource
{
    protected static ?string $model = ContainerStatusRecord::class;
    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedInboxStack;
    protected static ?string $navigationLabel = 'سجلات الحاويات';
    protected static ?string $modelLabel = 'سجل حاويات';
    protected static ?string $pluralModelLabel = 'سجلات الحاويات';
    protected static string|\UnitEnum|null $navigationGroup = NavigationGroup::Containers;
    protected static ?int $navigationSort = 2;

    public static function canAccess(): bool
    {
        $user = Auth::user();
        if (! $user) return false;

        return true;
    }

    /** السنوات من 2004 حتى السنة الحالية */
    public static function availableYears(): array
    {
        $years = [];
        for ($y = 2004; $y <= (int) date('Y'); $y++) {
            $years[$y] = (string) $y;
        }
        return $years;
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                // -------- 1. قسم المعلومات الرئيسية (كامل العرض) --------
                Section::make('1. معلومات التقرير والميناء')
                    ->description('تحديد الميناء، السنة المالية، الشهر، نوع الحاوية، وتاريخ التقرير')
                    ->icon(Heroicon::OutlinedDocumentText)
                    ->columnSpanFull()
                    ->columns(5)
                    ->schema([
                        Select::make('port_id')
                            ->label('الميناء')
                            ->options(function (?ContainerStatusRecord $record) {
                                return Port::where('is_active', true)
                                    ->where(function ($q) use ($record) {
                                        $q->where('has_container_status', true);
                                        if ($record?->port_id) {
                                            $q->orWhere('id', $record->port_id);
                                        }
                                    })
                                    ->orderBy('sort_order')
                                    ->pluck('name_ar', 'id');
                            })
                            ->required()
                            ->searchable()
                            ->preload(),

                        Select::make('container_type')
                            ->label('نوع الحاوية')
                            ->required()
                            ->options([
                                'abandoned' => '📦 حاويات متخلفة',
                                'dangerous' => '⚠️ حاويات خطرة',
                            ])
                            ->default('abandoned'),

                        Select::make('fiscal_year_id')
                            ->label('السنة المالية')
                            ->options(fn () => FiscalYear::pluck('year', 'id'))
                            ->default(fn () => FiscalYear::where('is_current', true)->first()?->id ?? FiscalYear::orderBy('year', 'desc')->first()?->id)
                            ->required(),

                        Select::make('month_id')
                            ->label('الشهر')
                            ->options(fn () => Month::orderBy('month_number')->get()->mapWithKeys(fn ($m) => [$m->id => "{$m->month_number} - {$m->name_ar}"]))
                            ->default(fn () => Month::where('month_number', now()->month)->first()?->id ?? Month::first()?->id)
                            ->required()
                            ->rules([
                                fn ($get, $record) => function (string $attribute, $value, \Closure $fail) use ($get, $record) {
                                    $portId = $get('port_id');
                                    $fiscalYearId = $get('fiscal_year_id');
                                    $type = $get('container_type');

                                    if ($portId && $fiscalYearId && $value && $type) {
                                        $query = ContainerStatusRecord::where('port_id', $portId)
                                            ->where('fiscal_year_id', $fiscalYearId)
                                            ->where('month_id', $value)
                                            ->where('container_type', $type);

                                        if ($record) {
                                            $query->where('id', '!=', $record->id);
                                        }

                                        if ($query->exists()) {
                                            $fail('يوجد سجل موقف حاويات مُسجل مسبقاً لهذا الميناء بنفس النوع والشهر والسنة.');
                                        }
                                    }
                                },
                            ]),

                        DatePicker::make('report_date')
                            ->label('تاريخ إصدار التقرير')
                            ->native(false)
                            ->displayFormat('d/m/Y')
                            ->format('Y-m-d')
                            ->closeOnDateSelection()
                            ->required()
                            ->default(now()),

                        Textarea::make('notes')
                            ->label('ملاحظات إضافية')
                            ->rows(2)
                            ->columnSpanFull(),
                    ]),

                // -------- 2. قسم بيانات الحاويات (اختيار الجهة مرة واحدة وإدخال سنواتها بداخلها) --------
                Section::make('2. بيانات الحاويات حسب الجهة والسنوات')
                    ->description('اختر الجهة / الوزارة مرة واحدة، ثم أضف السنوات وأعداد الحاويات التابعة لها')
                    ->icon(Heroicon::OutlinedTableCells)
                    ->columnSpanFull()
                    ->schema([
                        Repeater::make('entities_data')
                            ->hiddenLabel()
                            ->addActionLabel('➕ إضافة جهة / وزارة جديدة')
                            ->reorderable(true)
                            ->reorderableWithButtons()
                            ->collapsible()
                            ->cloneable()
                            ->itemLabel(function (array $state): ?string {
                                if (!empty($state['container_entity_id'])) {
                                    $entity = ContainerEntity::find($state['container_entity_id']);
                                    $total = 0;
                                    if (!empty($state['years']) && is_array($state['years'])) {
                                        foreach ($state['years'] as $y) {
                                            $total += (int) ($y['count'] ?? 0);
                                        }
                                    }
                                    return $entity ? "🏛️ [{$entity->entity_type_label}] {$entity->name_ar} — (الإجمالي: " . number_format($total) . " حاوية)" : null;
                                }
                                return '➕ جهة جديدة';
                            })
                            ->schema([
                                Select::make('container_entity_id')
                                    ->label('الجهة / الوزارة / القطاع')
                                    ->required()
                                    ->options(
                                        ContainerEntity::active()
                                            ->orderBy('entity_type')
                                            ->orderBy('sort_order')
                                            ->get()
                                            ->mapWithKeys(fn($e) => [
                                                $e->id => "[{$e->entity_type_label}] {$e->name_ar}"
                                            ])
                                    )
                                    ->searchable()
                                    ->preload()
                                    ->live()
                                    ->columnSpanFull(),

                                Repeater::make('years')
                                    ->label('أعداد الحاويات حسب السنوات لهذه الجهة')
                                    ->addActionLabel('➕ إضافة سنة')
                                    ->extraAttributes(['class' => 'years-repeater-grid-4'])
                                    ->compact()
                                    ->schema([
                                        Select::make('year_label')
                                            ->label('السنة الميلادية')
                                            ->required()
                                            ->options(static::availableYears())
                                            ->default(date('Y'))
                                            ->searchable(),

                                        TextInput::make('count')
                                            ->label('عدد الحاويات')
                                            ->required()
                                            ->numeric()
                                            ->default(0)
                                            ->minValue(0)
                                            ->live(onBlur: true),
                                    ])
                                    ->columns(1)
                                    ->defaultItems(1)
                                    ->reorderable(false)
                                    ->columnSpanFull(),
                            ])
                            ->columnSpanFull()
                            ->defaultItems(0),
                    ]),

                // -------- 3. قسم ملف الإكسل المرفق لموقف الحاويات --------
                Section::make('3. ملف الإكسل التفصيلي المرفق (لحفظ بيانات الحاويات الأصلية)')
                    ->description('إرفاق ملف Excel الشهري الذي يحتوي على تفاصيل وسجلات الحاويات الأصلية لكل باخرة ورصيف')
                    ->icon(Heroicon::OutlinedDocumentArrowUp)
                    ->columnSpanFull()
                    ->schema([
                        FileUpload::make('excel_file_path')
                            ->label('ملف Excel لموقف الحاويات (xlsx / xls / csv)')
                            ->helperText('ارفع ملف الإكسل المعتمد للميناء لحفظه وتنزيله في أي وقت')
                            ->acceptedFileTypes([
                                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                                'application/vnd.ms-excel',
                                'text/csv',
                            ])
                            ->disk('public')
                            ->directory('container-records/excel')
                            ->storeFileNamesIn('excel_file_name')
                            ->downloadable()
                            ->openable()
                            ->previewable()
                            ->maxSize(51200)
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(function (Builder $query) {
                return $query
                    ->orderBy(
                        FiscalYear::select('year')
                            ->whereColumn('fiscal_years.id', 'container_status_records.fiscal_year_id')
                            ->limit(1),
                        'desc'
                    )
                    ->orderBy(
                        Month::select('month_number')
                            ->whereColumn('months.id', 'container_status_records.month_id')
                            ->limit(1),
                        'desc'
                    )
                    ->orderBy('container_status_records.port_id', 'asc')
                    ->orderBy('container_status_records.container_type', 'asc');
            })
            ->columns([
                TextColumn::make('port.name_ar')
                    ->label('الميناء')
                    ->sortable()
                    ->searchable()
                    ->badge()
                    ->color('info'),

                TextColumn::make('container_type')
                    ->label('نوع الحاوية')
                    ->badge()
                    ->formatStateUsing(fn($state) => match($state) {
                        'abandoned' => 'متخلفة',
                        'dangerous' => 'خطرة',
                        default     => $state,
                    })
                    ->color(fn($state) => match($state) {
                        'abandoned' => 'warning',
                        'dangerous' => 'danger',
                        default     => 'gray',
                    }),

                TextColumn::make('fiscalYear.year')
                    ->label('السنة المالية')
                    ->sortable(),

                TextColumn::make('month.month_number')
                    ->label('الشهر')
                    ->formatStateUsing(fn ($record) => $record->month ? "{$record->month->month_number} - {$record->month->name_ar}" : '—')
                    ->sortable(),

                TextColumn::make('report_date')
                    ->label('تاريخ التقرير')
                    ->date('d/m/Y')
                    ->sortable(),

                TextColumn::make('details_count')
                    ->label('عدد القيود')
                    ->counts('details')
                    ->badge()
                    ->color('gray'),

                TextColumn::make('total_count')
                    ->label('إجمالي الحاويات')
                    ->getStateUsing(fn($record) => number_format($record->total_count))
                    ->badge()
                    ->color('success'),

                TextColumn::make('excel_file_name')
                    ->label('ملف Excel المرفق')
                    ->icon('heroicon-o-document-arrow-down')
                    ->formatStateUsing(fn ($record) => $record->excel_file_name ?: ($record->excel_file_path ? 'تحميل الملف 📥' : '—'))
                    ->badge()
                    ->color(fn ($record) => $record->excel_file_path ? 'success' : 'gray')
                    ->url(fn ($record) => $record->excel_file_path ? asset('storage/' . $record->excel_file_path) : null, shouldOpenInNewTab: true)
                    ->tooltip(fn ($record) => $record->excel_file_path ? 'انقر لتحميل ملف الإكسل المرفق' : 'لا يوجد ملف مرفق'),

                TextColumn::make('deleted_at')
                    ->label('محذوف في')
                    ->dateTime('d/m/Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('port_id')
                    ->label('الميناء')
                    ->options(fn () => Port::where('is_active', true)->where(fn($q) => $q->where('has_container_status', true)->orWhereHas('containerStatusRecords'))->orderBy('sort_order')->pluck('name_ar', 'id'))
                    ->searchable(),

                SelectFilter::make('container_type')
                    ->label('نوع الحاوية')
                    ->options([
                        'abandoned' => 'حاويات متخلفة',
                        'dangerous' => 'حاويات خطرة',
                    ]),

                SelectFilter::make('fiscal_year_id')
                    ->label('السنة المالية')
                    ->options(FiscalYear::pluck('year', 'id')),

                SelectFilter::make('month_id')
                    ->label('الشهر')
                    ->options(Month::orderBy('month_number')->pluck('name_ar', 'id')),

                TrashedFilter::make(),
            ])
            ->recordActions([
                ViewAction::make()->color('info'),
                EditAction::make()->color('warning'),
                Action::make('download_excel')
                    ->label('تحميل Excel')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('success')
                    ->visible(fn (ContainerStatusRecord $record) => !empty($record->excel_file_path))
                    ->url(fn (ContainerStatusRecord $record) => asset('storage/' . $record->excel_file_path), shouldOpenInNewTab: true),
                Action::make('clone_record')
                    ->label('نسخ إلى شهر آخر')
                    ->icon('heroicon-o-document-duplicate')
                    ->color('success')
                    ->modalHeading(fn (ContainerStatusRecord $record) => "نسخ بيانات موقف الحاويات ({$record->port?->name_ar} - " . ($record->container_type === 'abandoned' ? 'متخلفة' : 'خطرة') . ")")
                    ->modalDescription('اختر السنة المالية والشهر المستهدف لنسخ وتكرار كافة بيانات القيود والجهات والأعداد إليه مباشرة.')
                    ->modalSubmitActionLabel('بدء النسخ والإنشاء')
                    ->modalIcon('heroicon-o-document-duplicate')
                    ->form([
                        Select::make('target_fiscal_year_id')
                            ->label('السنة المالية المستهدفة')
                            ->options(FiscalYear::orderBy('year', 'desc')->pluck('year', 'id'))
                            ->default(fn (ContainerStatusRecord $record) => $record->fiscal_year_id)
                            ->required(),

                        Select::make('target_month_id')
                            ->label('الشهر المستهدف')
                            ->options(Month::orderBy('month_number')->pluck('name_ar', 'id'))
                            ->default(function (ContainerStatusRecord $record) {
                                $nextMonthNum = ($record->month?->month_number % 12) + 1;
                                return Month::where('month_number', $nextMonthNum)->first()?->id ?? $record->month_id;
                            })
                            ->required(),

                        DatePicker::make('target_report_date')
                            ->label('تاريخ التقرير الجديد')
                            ->default(now()->toDateString())
                            ->required(),
                    ])
                    ->action(function (ContainerStatusRecord $record, array $data) {
                        $targetYearId = (int) $data['target_fiscal_year_id'];
                        $targetMonthId = (int) $data['target_month_id'];
                        $targetReportDate = $data['target_report_date'];

                        // التحقق من وجود السجل مسبقاً
                        $existing = ContainerStatusRecord::withTrashed()
                            ->where('port_id', $record->port_id)
                            ->where('container_type', $record->container_type)
                            ->where('fiscal_year_id', $targetYearId)
                            ->where('month_id', $targetMonthId)
                            ->first();

                        if ($existing) {
                            Notification::make()
                                ->title('تنبيه: السجل موجود مسبقاً')
                                ->body("يوجد قيد مسجل مسبقاً لميناء ({$record->port?->name_ar}) في الشهر والسنة المحددين!")
                                ->danger()
                                ->send();
                            return;
                        }

                        DB::transaction(function () use ($record, $targetYearId, $targetMonthId, $targetReportDate) {
                            $newRecord = ContainerStatusRecord::create([
                                'port_id'         => $record->port_id,
                                'container_type'  => $record->container_type,
                                'fiscal_year_id'  => $targetYearId,
                                'month_id'        => $targetMonthId,
                                'report_date'     => $targetReportDate,
                                'notes'           => $record->notes,
                                'created_by'      => Auth::id() ?? 1,
                            ]);

                            foreach ($record->details as $detail) {
                                ContainerStatusDetail::create([
                                    'container_status_record_id' => $newRecord->id,
                                    'container_entity_id'        => $detail->container_entity_id,
                                    'year_label'                 => $detail->year_label,
                                    'count'                      => $detail->count,
                                    'sort_order'                 => $detail->sort_order,
                                ]);
                            }

                            ActivityLogger::log(
                                'created',
                                "نسخ سجل موقف الحاويات لميناء ({$record->port?->name_ar}) من شهر ({$record->month?->name_ar}) إلى شهر جديد",
                                ContainerStatusRecord::class,
                                $newRecord->id,
                                ['source_record_id' => $record->id, 'target_year_id' => $targetYearId, 'target_month_id' => $targetMonthId]
                            );
                        });

                        $targetMonthName = Month::find($targetMonthId)?->name_ar;
                        $targetYearVal = FiscalYear::find($targetYearId)?->year;

                        Notification::make()
                            ->title('تم نسخ السجل بنجاح')
                            ->body("تم نسخ كافة بيانات موقف الحاويات لميناء ({$record->port?->name_ar}) إلى شهر {$targetMonthName} {$targetYearVal} بنجاح.")
                            ->success()
                            ->send();
                    }),
                DeleteAction::make()
                    ->label('حذف مؤقت')
                    ->modalHeading('هل تريد حذف هذا السجل مؤقتاً؟')
                    ->modalDescription('سيتم نقل السجل إلى سلة المحذوفات ويمكن استرداده لاحقاً.')
                    ->successNotificationTitle('تم نقل السجل إلى سلة المحذوفات'),
                RestoreAction::make()
                    ->color('success')
                    ->successNotificationTitle('تم استرداد السجل بنجاح'),
                ForceDeleteAction::make()
                    ->label('حذف نهائي')
                    ->modalHeading('⚠️ حذف نهائي لا رجعة فيه!')
                    ->successNotificationTitle('تم الحذف النهائي'),
            ])
            ->toolbarActions([
                DeleteBulkAction::make()->label('حذف مؤقت للمحدد'),
                RestoreBulkAction::make()->label('استرداد المحدد'),
                ForceDeleteBulkAction::make()->label('حذف نهائي للمحدد'),
            ]);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([SoftDeletingScope::class]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListContainerStatusRecords::route('/'),
            'create' => Pages\CreateContainerStatusRecord::route('/create'),
            'edit'   => Pages\EditContainerStatusRecord::route('/{record}/edit'),
        ];
    }
}
