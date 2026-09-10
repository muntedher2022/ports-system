<?php

namespace App\Filament\Resources;

use App\Enums\NavigationGroup;
use App\Filament\Resources\MonthlyPortRecordResource\Pages\CreateMonthlyPortRecord;
use App\Filament\Resources\MonthlyPortRecordResource\Pages\EditMonthlyPortRecord;
use App\Filament\Resources\MonthlyPortRecordResource\Pages\ListMonthlyPortRecords;
use App\Filament\Resources\MonthlyPortRecordResource\Pages\ViewMonthlyPortRecord;
use App\Helpers\ArabicSearchHelper;
use App\Models\FiscalYear;
use App\Models\Month;
use App\Models\MonthlyPortRecord;
use App\Models\Port;
use App\Models\RevenueCenter;
use App\Models\RevenueRecord;
use Illuminate\Database\Eloquent\Builder;

use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Hidden;
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
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\HtmlString;

class MonthlyPortRecordResource extends Resource
{
    protected static ?string $model = MonthlyPortRecord::class;
    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedClipboardDocumentList;
    protected static ?string $navigationLabel = 'السجلات التشغيلية للموانئ';
    protected static string|\UnitEnum|null $navigationGroup = NavigationGroup::Operations;
    protected static ?string $modelLabel = 'سجل تشغيلي شهري';
    protected static ?string $pluralModelLabel = 'السجلات التشغيلية للموانئ';
    protected static ?int $navigationSort = 1;

    public static function canAccess(): bool
    {
        $user = Auth::user();
        if (!$user)
            return false;

        return $user->hasRole(['المدير العام', 'general_manager', 'مسؤول المتابعة المركزية والعمليات', 'operations_manager', 'مدخل بيانات الميناء', 'port_data_entry', 'مدقق / مراجع', 'reviewer'])
            || in_array($user->user_type, ['general_manager', 'operations_manager', 'port_data_entry', 'reviewer']);
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                // 1. معلومات السجل والميناء
                Section::make('1. معلومات السجل والميناء')
                    ->description('تحديد الميناء، السنة المالية والشهر')
                    ->columns(3)
                    ->schema([
                        Select::make('port_id')
                            ->label('الميناء')
                            ->options(function (?MonthlyPortRecord $record) {
                                $user = Auth::user();
                                if ($user?->isPortRestricted() && $user?->port_id) {
                                    return Port::where('id', $user->port_id)->pluck('name_ar', 'id');
                                }
                                return Port::where('is_active', true)
                                    ->where(function ($q) use ($record) {
                                        $q->where('has_monthly_records', true);
                                        if ($record?->port_id) {
                                            $q->orWhere('id', $record->port_id);
                                        }
                                    })
                                    ->orderBy('sort_order')
                                    ->pluck('name_ar', 'id');
                            })
                            ->required()
                            ->searchable()
                            ->live()
                            ->afterStateUpdated(fn($get, $set) => static::autoFillRevenue($get, $set))
                            ->default(function () {
                                $user = Auth::user();
                                return $user?->port_id;
                            })
                            ->disabled(function () {
                                $user = Auth::user();
                                return (bool) ($user?->isPortRestricted() && $user?->port_id);
                            }),

                        Select::make('fiscal_year_id')
                            ->label('السنة المالية')
                            ->options(fn() => FiscalYear::pluck('year', 'id'))
                            ->default(fn() => FiscalYear::where('is_current', true)->first()?->id)
                            ->live()
                            ->afterStateUpdated(fn($get, $set) => static::autoFillRevenue($get, $set))
                            ->required(),

                        Select::make('month_id')
                            ->label('الشهر')
                            ->options(fn() => Month::orderBy('month_number')->get()->mapWithKeys(fn($m) => [$m->id => "{$m->month_number} - {$m->name_ar}"]))
                            ->required()
                            ->live()
                            ->afterStateUpdated(fn($get, $set) => static::autoFillRevenue($get, $set))
                            ->rules([
                                fn($get, $record) => function (string $attribute, $value, \Closure $fail) use ($get, $record) {
                                    $portId = $get('port_id');
                                    $fiscalYearId = $get('fiscal_year_id');

                                    if ($portId && $fiscalYearId && $value) {
                                        $query = MonthlyPortRecord::where('port_id', $portId)
                                            ->where('fiscal_year_id', $fiscalYearId)
                                            ->where('month_id', $value);

                                        if ($record) {
                                            $query->where('id', '!=', $record->id);
                                        }

                                        if ($query->exists()) {
                                            $fail('يوجد سجل تشغيلي مُسجل مسبقاً لهذا الميناء في نفس الشهر والسنة المالية.');
                                        }
                                    }
                                },
                            ]),

                        Hidden::make('status')
                            ->default('approved'),
                    ]),

                // 2. حركة بواخر الحاويات والحاويات المستوردة
                Section::make('2. حركة بواخر الحاويات والحاويات المستوردة')
                    ->columns(4)
                    ->schema([
                        // السطر الأول: ثلاث حقول (الحقل الثالث يمتد لعمودين ليكتمل السطر)
                        TextInput::make('total_container_ships')
                            ->label('عدد بواخر الحاويات الكلي')
                            ->numeric()
                            ->minValue(0)
                            ->default(0)
                            ->required(),

                        TextInput::make('imported_containers_weight_tons')
                            ->label('الوزن بالطن (حاويات مستوردة)')
                            ->numeric()
                            ->minValue(0)
                            ->default(0)
                            ->required(),

                        TextInput::make('imported_containers_count')
                            ->label('عدد الحاويات المستوردة الكلي')
                            ->numeric()
                            ->default(0)
                            ->readOnly()
                            ->columnSpan(2)
                            ->extraInputAttributes(['style' => 'background-color: #f1f5f9; cursor: not-allowed; font-weight: bold; color: #1e293b;'])
                            ->helperText('يُحسب تلقائياً: 20 + 40 + 45'),

                        // السطر الثاني: أربع حقول
                        TextInput::make('imported_20ft')
                            ->label('20 قدم (مستورد)')
                            ->numeric()
                            ->minValue(0)
                            ->default(0)
                            ->live(debounce: 500)
                            ->afterStateUpdated(fn($get, $set) => static::recalculateImported($get, $set)),

                        TextInput::make('imported_40ft')
                            ->label('40 قدم (مستورد)')
                            ->numeric()
                            ->minValue(0)
                            ->default(0)
                            ->live(debounce: 500)
                            ->afterStateUpdated(fn($get, $set) => static::recalculateImported($get, $set)),

                        TextInput::make('imported_45ft')
                            ->label('45 قدم (مستورد)')
                            ->numeric()
                            ->minValue(0)
                            ->default(0)
                            ->live(debounce: 500)
                            ->afterStateUpdated(fn($get, $set) => static::recalculateImported($get, $set)),

                        TextInput::make('imported_teu')
                            ->label('TEU المستورد')
                            ->numeric()
                            ->default(0)
                            ->readOnly()
                            ->extraInputAttributes(['style' => 'background-color: #f0fdf4; border-color: #86efac; cursor: not-allowed; font-weight: bold; color: #15803d; font-size: 1rem;'])
                            ->helperText('يُحسب تلقائياً: 20 + (40×2) + (45×2)'),
                    ]),

                // 3. الحاويات المصدرة
                Section::make('3. الحاويات المصدرة')
                    ->columns(4)
                    ->schema([
                        TextInput::make('exported_empty_count')
                            ->label('عدد الحاويات المصدرة فارغة')
                            ->numeric()
                            ->minValue(0)
                            ->default(0)
                            ->live(debounce: 500)
                            ->afterStateUpdated(fn($get, $set) => static::recalculateExported($get, $set)),

                        TextInput::make('exported_full_count')
                            ->label('عدد الحاويات المصدرة مملوءة')
                            ->numeric()
                            ->minValue(0)
                            ->default(0)
                            ->live(debounce: 500)
                            ->afterStateUpdated(fn($get, $set) => static::recalculateExported($get, $set)),

                        TextInput::make('exported_full_weight_tons')
                            ->label('الوزن بالطن للمصدر المليان')
                            ->numeric()
                            ->minValue(0)
                            ->default(0)
                            ->required(),

                        TextInput::make('exported_containers_count')
                            ->label('عدد الحاويات المصدرة الكلي')
                            ->numeric()
                            ->default(0)
                            ->readOnly()
                            ->extraInputAttributes(function ($get) {
                                $empty = (int) ($get('exported_empty_count') ?? 0);
                                $full = (int) ($get('exported_full_count') ?? 0);
                                $c20 = (int) ($get('exported_20ft') ?? 0);
                                $c40 = (int) ($get('exported_40ft') ?? 0);
                                $c45 = (int) ($get('exported_45ft') ?? 0);

                                $sumSizes = $c20 + $c40 + $c45;
                                $sumStatus = $empty + $full;

                                if (($sumSizes > 0 || $sumStatus > 0) && $sumSizes !== $sumStatus) {
                                    return ['style' => 'background-color: #fff1f2; border: 2px solid #e11d48; color: #be123c; font-weight: bold; cursor: not-allowed; font-size: 1.05rem;'];
                                }

                                if ($sumSizes > 0 && $sumSizes === $sumStatus) {
                                    return ['style' => 'background-color: #f0fdf4; border: 2px solid #16a34a; color: #15803d; font-weight: bold; cursor: not-allowed; font-size: 1.05rem;'];
                                }

                                return ['style' => 'background-color: #f1f5f9; cursor: not-allowed; font-weight: bold; color: #1e293b;'];
                            })
                            ->helperText(function ($get) {
                                $empty = (int) ($get('exported_empty_count') ?? 0);
                                $full = (int) ($get('exported_full_count') ?? 0);
                                $c20 = (int) ($get('exported_20ft') ?? 0);
                                $c40 = (int) ($get('exported_40ft') ?? 0);
                                $c45 = (int) ($get('exported_45ft') ?? 0);

                                $sumSizes = $c20 + $c40 + $c45;
                                $sumStatus = $empty + $full;

                                if ($sumSizes === 0 && $sumStatus === 0) {
                                    return new HtmlString('<span class="text-gray-500 font-medium">يُحسب من المقاسات: 20 + 40 + 45 (ويجب أن يطابق: فارغ + مملوء)</span>');
                                }

                                if ($sumSizes === $sumStatus) {
                                    return new HtmlString("<span class='text-emerald-700 font-bold flex items-center gap-1'>✅ متطابق وصحيح: (مجموع المقاسات {$sumSizes} = مجموع فارغ ومملوء {$sumStatus})</span>");
                                }

                                return new HtmlString("<span class='text-rose-700 font-bold flex items-center gap-1'>⚠️ غير متطابق: مجموع المقاسات ({$sumSizes}) لا يساوي مجموع (فارغ + مملوء = {$sumStatus})</span>");
                            })
                            ->rules([
                                fn($get) => function (string $attribute, $value, \Closure $fail) use ($get) {
                                    $empty = (int) ($get('exported_empty_count') ?? 0);
                                    $full = (int) ($get('exported_full_count') ?? 0);
                                    $c20 = (int) ($get('exported_20ft') ?? 0);
                                    $c40 = (int) ($get('exported_40ft') ?? 0);
                                    $c45 = (int) ($get('exported_45ft') ?? 0);

                                    $sumSizes = $c20 + $c40 + $c45;
                                    $sumStatus = $empty + $full;

                                    if ($sumSizes !== $sumStatus) {
                                        $fail("مجموع الحاويات المصدرة حسب المقاسات (20 + 40 + 45 = {$sumSizes}) لا يطابق مجموع الحاويات (فارغ + مملوء = {$sumStatus}). يرجى تدقيق الأعداد المدخلة.");
                                    }
                                },
                            ]),

                        TextInput::make('exported_20ft')
                            ->label('20 قدم (مصدر)')
                            ->numeric()
                            ->minValue(0)
                            ->default(0)
                            ->live(debounce: 500)
                            ->afterStateUpdated(fn($get, $set) => static::recalculateExported($get, $set)),

                        TextInput::make('exported_40ft')
                            ->label('40 قدم (مصدر)')
                            ->numeric()
                            ->minValue(0)
                            ->default(0)
                            ->live(debounce: 500)
                            ->afterStateUpdated(fn($get, $set) => static::recalculateExported($get, $set)),

                        TextInput::make('exported_45ft')
                            ->label('45 قدم (مصدر)')
                            ->numeric()
                            ->minValue(0)
                            ->default(0)
                            ->live(debounce: 500)
                            ->afterStateUpdated(fn($get, $set) => static::recalculateExported($get, $set)),

                        TextInput::make('exported_teu')
                            ->label('TEU المصدر')
                            ->numeric()
                            ->default(0)
                            ->readOnly()
                            ->extraInputAttributes(['style' => 'background-color: #f0fdf4; border-color: #86efac; cursor: not-allowed; font-weight: bold; color: #15803d; font-size: 1rem;'])
                            ->helperText('يُحسب تلقائياً: 20 + (40×2) + (45×2)'),
                    ]),

                // 4. البضائع المتنوعة والنفط والسيارات والإيراد
                Section::make('4. البضائع المتنوعة والنفط والسيارات والإيراد')
                    ->columns(3)
                    ->schema([
                        // السطر الأول (3 حقول): البضائع المتنوعة والناقلات
                        TextInput::make('general_cargo_ships')
                            ->label('عدد البواخر المتنوعة')
                            ->numeric()
                            ->minValue(0)
                            ->default(0)
                            ->required(),

                        TextInput::make('general_cargo_weight_tons')
                            ->label('الوزن بالطن (بضائع متنوعة)')
                            ->numeric()
                            ->minValue(0)
                            ->default(0)
                            ->required(),

                        TextInput::make('oil_tankers_count')
                            ->label('عدد الناقلات النفطية الكلي')
                            ->numeric()
                            ->minValue(0)
                            ->default(0)
                            ->required(),

                        // السطر الثاني (3 حقول): النفط والمشتقات
                        TextInput::make('oil_exported_tons')
                            ->label('نفط ومشتقات مصدر بالطن')
                            ->numeric()
                            ->minValue(0)
                            ->default(0)
                            ->live(debounce: 500)
                            ->afterStateUpdated(fn($get, $set) => static::recalculateOilTotal($get, $set)),

                        TextInput::make('oil_imported_tons')
                            ->label('نفط ومشتقاته مستورد بالطن')
                            ->numeric()
                            ->minValue(0)
                            ->default(0)
                            ->live(debounce: 500)
                            ->afterStateUpdated(fn($get, $set) => static::recalculateOilTotal($get, $set)),

                        TextInput::make('oil_total_tons')
                            ->label('نفط ومشتقاته الكلي بالطن')
                            ->numeric()
                            ->default(0)
                            ->readOnly()
                            ->extraInputAttributes(['style' => 'background-color: #f1f5f9; cursor: not-allowed; font-weight: bold; color: #1e293b;'])
                            ->helperText('يُحسب تلقائياً: مصدر + مستورد'),

                        // السطر الثالث (3 حقول): السيارات والإيراد
                        TextInput::make('imported_cars_count')
                            ->label('عدد السيارات المستوردة')
                            ->numeric()
                            ->minValue(0)
                            ->default(0)
                            ->required(),

                        TextInput::make('imported_cars_weight_tons')
                            ->label('الوزن بالطن (سيارات مستوردة)')
                            ->numeric()
                            ->minValue(0)
                            ->default(0)
                            ->required(),

                        TextInput::make('total_revenue')
                            ->label('الإيراد الكلي للميناء (د.ع)')
                            ->numeric()
                            ->default(0)
                            ->readOnly()
                            ->extraInputAttributes(['style' => 'background-color: #f0fdf4; border-color: #86efac; cursor: not-allowed; font-weight: bold; color: #15803d;'])
                            ->hintAction(
                                Action::make('syncRevenue')
                                    ->label('🔄 جلب الإيراد')
                                    ->tooltip('الضغط هنا لجلب أحدث إيراد مسجل لهذا الميناء والشهر من جدول الإيرادات')
                                    ->action(function ($get, $set) {
                                        static::autoFillRevenue($get, $set, true);
                                    })
                            )
                            ->helperText('يُجلب تلقائياً من جدول الإيرادات للشهر والسنة المحددين (للقراءة فقط)'),

                        Textarea::make('reopen_reason')
                            ->label('ملاحظات / سبب إعادة الفتح (إن وجد)')
                            ->rows(1)
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    // ===================== دوال الحساب التفاعلي الفوري =====================

    public static function autoFillRevenue($get, $set, bool $notify = false): void
    {
        $portId = $get('port_id');
        $fiscalYearId = $get('fiscal_year_id');
        $monthId = $get('month_id');

        if ($portId && $fiscalYearId && $monthId) {
            $center = RevenueCenter::where('port_id', $portId)->first();
            if ($center) {
                $revenueRecord = RevenueRecord::where('revenue_center_id', $center->id)
                    ->where('fiscal_year_id', $fiscalYearId)
                    ->where('month_id', $monthId)
                    ->first();

                if ($revenueRecord && (float) $revenueRecord->gross_revenue > 0) {
                    $set('total_revenue', (float) $revenueRecord->gross_revenue);
                    if ($notify) {
                        Notification::make()
                            ->title('تم جلب الإيراد بنجاح')
                            ->body('تم وضع المبلغ: ' . number_format((float) $revenueRecord->gross_revenue, 0) . ' د.ع')
                            ->success()
                            ->send();
                    }
                    return;
                }
            }
        }

        if ($notify) {
            Notification::make()
                ->title('لم يتم العثور على إيراد مسجل')
                ->body('لا يوجد قيد إيراد مسجل لهذا الميناء في هذا الشهر والسنة.')
                ->warning()
                ->send();
        }
    }

    public static function recalculateImported($get, $set): void
    {
        $c20 = (int) ($get('imported_20ft') ?? 0);
        $c40 = (int) ($get('imported_40ft') ?? 0);
        $c45 = (int) ($get('imported_45ft') ?? 0);

        $totalCount = $c20 + $c40 + $c45;
        $teu = $c20 + ($c40 * 2) + ($c45 * 2);

        $set('imported_containers_count', $totalCount);
        $set('imported_teu', $teu);
    }

    public static function recalculateExported($get, $set): void
    {
        $empty = (int) ($get('exported_empty_count') ?? 0);
        $full = (int) ($get('exported_full_count') ?? 0);
        $c20 = (int) ($get('exported_20ft') ?? 0);
        $c40 = (int) ($get('exported_40ft') ?? 0);
        $c45 = (int) ($get('exported_45ft') ?? 0);

        $sumSizes = $c20 + $c40 + $c45;
        $sumStatus = $empty + $full;
        $teu = $c20 + ($c40 * 2) + ($c45 * 2);

        $set('exported_teu', $teu);

        // يعتمد المقاسات (20 + 40 + 45) كأساس للعدد الكلي، أو مجموع (فارغ + مملوء)
        $totalCount = $sumSizes > 0 ? $sumSizes : $sumStatus;
        $set('exported_containers_count', $totalCount);
    }

    public static function recalculateOilTotal($get, $set): void
    {
        $exp = (float) ($get('oil_exported_tons') ?? 0);
        $imp = (float) ($get('oil_imported_tons') ?? 0);

        $set('oil_total_tons', round($exp + $imp, 3));
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(function (Builder $query) {
                $user = Auth::user();
                if ($user?->isPortRestricted() && $user?->port_id) {
                    $query->where('monthly_port_records.port_id', $user->port_id);
                }

                return $query
                    ->orderBy(
                        FiscalYear::select('year')
                            ->whereColumn('fiscal_years.id', 'monthly_port_records.fiscal_year_id')
                            ->limit(1),
                        'desc'
                    )
                    ->orderBy('monthly_port_records.month_id', 'desc');
            })
            ->columns([
                TextColumn::make('port.name_ar')
                    ->label('الميناء')
                    ->sortable()
                    ->searchable(
                        query: fn(Builder $query, string $search) => $query->whereHas('port', fn($q) => ArabicSearchHelper::applySearch($q, 'name_ar', $search)),
                        isIndividual: true,
                        isGlobal: true
                    )
                    ->weight('bold'),

                TextColumn::make('fiscalYear.year')
                    ->label('السنة')
                    ->sortable()
                    ->searchable(isIndividual: true),

                TextColumn::make('month_id')
                    ->label('الشهر')
                    ->sortable()
                    ->searchable(isIndividual: true)
                    ->alignCenter()
                    ->badge()
                    ->color('info'),

                TextColumn::make('total_ships')
                    ->label('إجمالي البواخر')
                    ->state(fn(MonthlyPortRecord $record) => $record->total_ships)
                    ->numeric()
                    ->badge()
                    ->color('primary')
                    ->alignCenter(),

                TextColumn::make('total_teu')
                    ->label('إجمالي TEU')
                    ->state(fn(MonthlyPortRecord $record) => $record->total_teu)
                    ->numeric()
                    ->badge()
                    ->color('info')
                    ->alignCenter(),

                TextColumn::make('total_tonnage')
                    ->label('الطاقة بالطن')
                    ->state(fn(MonthlyPortRecord $record) => number_format($record->total_tonnage, 0))
                    ->alignEnd()
                    ->weight('bold'),

                TextColumn::make('total_revenue')
                    ->label('الإيراد (د.ع)')
                    ->numeric(decimalPlaces: 0)
                    ->sortable()
                    ->searchable(isIndividual: true)
                    ->alignEnd(),

                TextColumn::make('status')
                    ->label('الحالة')
                    ->formatStateUsing(fn($state) => match ($state) {
                        'draft' => 'مسودة',
                        'submitted' => 'مُقدَّم للاعتماد',
                        'approved' => 'معتمد',
                        'locked' => 'مقفل',
                        default => $state,
                    })
                    ->badge()
                    ->searchable(isIndividual: true)
                    ->color(fn($state) => match ($state) {
                        'approved' => 'success',
                        'submitted' => 'warning',
                        'locked' => 'danger',
                        default => 'gray',
                    }),
            ])
            ->filters([
                SelectFilter::make('port_id')
                    ->label('الميناء')
                    ->options(fn() => Port::where('is_active', true)->where(fn($q) => $q->where('has_monthly_records', true)->orWhereHas('monthlyPortRecords'))->orderBy('sort_order')->pluck('name_ar', 'id')),

                SelectFilter::make('fiscal_year_id')
                    ->label('السنة المالية')
                    ->options(fn() => FiscalYear::orderBy('year', 'desc')->pluck('year', 'id')),

                SelectFilter::make('month_id')
                    ->label('الشهر')
                    ->options(fn() => Month::orderBy('month_number')->get()->mapWithKeys(fn($m) => [$m->id => "{$m->month_number} - {$m->name_ar}"])),

                SelectFilter::make('status')
                    ->label('الحالة')
                    ->options([
                        'draft' => 'مسودة',
                        'submitted' => 'مُقدَّم للاعتماد',
                        'approved' => 'معتمد',
                        'locked' => 'مقفل',
                    ]),

                TrashedFilter::make()
                    ->label('سلة المحذوفات / السجلات المحذوفة مؤقتاً'),
            ])
            ->actions([
                ActionGroup::make([
                    ViewAction::make()->label('عرض'),
                    EditAction::make()->label('تعديل'),

                    Action::make('submit')
                        ->label('تقديم للاعتماد')
                        ->icon('heroicon-o-paper-airplane')
                        ->color('warning')
                        ->requiresConfirmation()
                        ->modalHeading('تقديم السجل للاعتماد والتدقيق')
                        ->modalDescription('هل أنت متأكد من اكتمال إدخال بيانات هذا الشهر وتقديمها للتدقيق؟')
                        ->visible(fn(MonthlyPortRecord $record) => $record->status === 'draft' && !$record->trashed())
                        ->action(function (MonthlyPortRecord $record) {
                            $record->update([
                                'status' => 'submitted',
                                'submitted_by' => Auth::id(),
                                'submitted_at' => now(),
                            ]);

                            Notification::make()
                                ->title('تم تقديم السجل للاعتماد بنجاح')
                                ->success()
                                ->send();
                        }),

                    Action::make('approve')
                        ->label('اعتماد')
                        ->icon('heroicon-o-check-badge')
                        ->color('success')
                        ->requiresConfirmation()
                        ->modalHeading('اعتماد السجل التشغيلي')
                        ->modalDescription('سيتم تثبيت واعتماد أرقام هذا الشهر بعد مراجعتها.')
                        ->visible(fn(MonthlyPortRecord $record) => $record->status === 'submitted' && !$record->trashed() && Auth::user()?->hasRole(['admin', 'general_manager', 'operations_manager', 'reviewer']))
                        ->action(function (MonthlyPortRecord $record) {
                            $record->update([
                                'status' => 'approved',
                                'approved_by' => Auth::id(),
                                'approved_at' => now(),
                            ]);

                            Notification::make()
                                ->title('تم اعتماد السجل بنجاح')
                                ->success()
                                ->send();
                        }),

                    DeleteAction::make()
                        ->label('حذف مؤقت')
                        ->modalHeading('نقل السجل إلى سلة المحذوفات')
                        ->modalDescription('سيتم نقل السجل مؤقتاً إلى سلة المحذوفات مع إمكانية استرداده لاحقاً.')
                        ->before(function (MonthlyPortRecord $record, DeleteAction $action) {
                            if (in_array($record->status, ['approved', 'locked']) && !Auth::user()?->hasRole(['super_admin', 'المدير العام', 'general_manager'])) {
                                Notification::make()
                                    ->title('لا يمكن حذف سجل معتمد أو مقفل')
                                    ->body('هذا السجل تم اعتماده رسمياً. يجب إلغاء اعتماده أولاً من قبل الإدارة العامة قبل الحذف.')
                                    ->danger()
                                    ->send();
                                $action->cancel();
                            }
                        }),

                    RestoreAction::make()
                        ->label('استرداد من الحذف')
                        ->modalHeading('استرداد السجل التشغيلي')
                        ->successNotificationTitle('تم استرداد السجل التشغيلي بنجاح وإعادته للنظام'),

                    ForceDeleteAction::make()
                        ->label('حذف نهائي')
                        ->visible(fn() => Auth::user()?->hasRole(['super_admin', 'المدير العام'])),
                ])
                ->tooltip('قائمة الإجراءات')
                ->icon('heroicon-m-ellipsis-vertical'),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->label('حذف مؤقت للمحدد')
                        ->before(function ($records, DeleteBulkAction $action) {
                            $hasApproved = $records->contains(fn($r) => in_array($r->status, ['approved', 'locked']));
                            if ($hasApproved && !Auth::user()?->hasRole(['super_admin', 'المدير العام', 'general_manager'])) {
                                Notification::make()
                                    ->title('تحذير: توجد سجلات معتمدة')
                                    ->body('لا يمكن حذف السجلات المعتمدة جماعياً. يرجى إلغاء اعتمادها أولاً.')
                                    ->danger()
                                    ->send();
                                $action->cancel();
                            }
                        }),
                    RestoreBulkAction::make()->label('استرداد المحدد'),
                    ForceDeleteBulkAction::make()
                        ->label('حذف نهائي للمحدد')
                        ->visible(fn() => Auth::user()?->hasRole(['super_admin', 'المدير العام'])),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListMonthlyPortRecords::route('/'),
            'create' => CreateMonthlyPortRecord::route('/create'),
            'view' => ViewMonthlyPortRecord::route('/{record}'),
            'edit' => EditMonthlyPortRecord::route('/{record}/edit'),
        ];
    }
}
