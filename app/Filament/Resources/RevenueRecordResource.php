<?php

namespace App\Filament\Resources;

use App\Enums\NavigationGroup;
use App\Filament\Resources\RevenueRecordResource\Pages\CreateRevenueRecord;
use App\Filament\Resources\RevenueRecordResource\Pages\EditRevenueRecord;
use App\Filament\Resources\RevenueRecordResource\Pages\ListRevenueRecords;
use App\Helpers\ArabicSearchHelper;
use App\Helpers\ArabicTafqeetHelper;
use App\Models\FiscalYear;
use App\Models\Month;
use App\Models\RevenueCenter;
use App\Models\RevenueRecord;
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
use Filament\Schemas\Components\Fieldset;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Enums\Width;
use Filament\Support\Icons\Heroicon;
use Filament\Support\RawJs;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\HtmlString;

class RevenueRecordResource extends Resource
{
    protected static ?string $model = RevenueRecord::class;

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedBanknotes;

    protected static ?string $navigationLabel = 'سجلات الإيراد للموانئ';

    protected static string|\UnitEnum|null $navigationGroup = NavigationGroup::Operations;

    protected static ?string $modelLabel = 'سجل إيراد شهري';

    protected static ?string $pluralModelLabel = 'سجلات الإيراد للموانئ';

    protected static ?int $navigationSort = 2;

    public static function canAccess(): bool
    {
        $user = Auth::user();
        if (! $user) {
            return false;
        }

        return $user->hasRole(['المدير العام', 'general_manager', 'مسؤول المتابعة المركزية والعمليات', 'operations_manager', 'مسؤول الإيراد المالي', 'finance_manager', 'مدقق / مراجع', 'reviewer'])
            || in_array($user->user_type, ['general_manager', 'operations_manager', 'finance_manager', 'reviewer']);
    }

    public static function form(Schema $schema): Schema
    {
        $centers = RevenueCenter::where('is_active', true)->orderBy('sort_order')->get();

        // لوحة ألوان باستيل هادئة ومريحة للعين لتمييز كل ميناء/مركز إيراد
        $calmPalettes = [
            // 1. أزرق سماوي هادئ (ميناء أم قصر الشمالي)
            [
                'bg' => '#f0f7ff',
                'border' => '#bfdbfe',
                'accent' => '#3b82f6',
                'badge_bg' => '#dbeafe',
                'text' => '#1e40af',
            ],
            // 2. أخضر نعناعي هادئ (ميناء أم قصر الجنوبي)
            [
                'bg' => '#f0fdf7',
                'border' => '#a7f3d0',
                'accent' => '#10b981',
                'badge_bg' => '#d1fae5',
                'text' => '#065f46',
            ],
            // 3. كهرماني دافئ هادئ (ميناء خور الزبير)
            [
                'bg' => '#fffdf2',
                'border' => '#fde68a',
                'accent' => '#f59e0b',
                'badge_bg' => '#fef3c7',
                'text' => '#92400e',
            ],
            // 4. بنفسجي خزامي هادئ (ميناء المعقل)
            [
                'bg' => '#faf5ff',
                'border' => '#e9d5ff',
                'accent' => '#8b5cf6',
                'badge_bg' => '#ede9fe',
                'text' => '#5b21b6',
            ],
            // 5. فيروزي بحري هادئ (ميناء أبو فلوس)
            [
                'bg' => '#f0fdfa',
                'border' => '#99f6e4',
                'accent' => '#14b8a6',
                'badge_bg' => '#ccfbf1',
                'text' => '#115e59',
            ],
            // 6. نيلي رمادي هادئ (مقر الشركة)
            [
                'bg' => '#f5f7ff',
                'border' => '#c7d2fe',
                'accent' => '#6366f1',
                'badge_bg' => '#e0e7ff',
                'text' => '#3730a3',
            ],
            // 7. وردي خوخي هادئ (ميناء البصرة النفطي)
            [
                'bg' => '#fff5f7',
                'border' => '#fecdd3',
                'accent' => '#f43f5e',
                'badge_bg' => '#ffe4e6',
                'text' => '#9f1239',
            ],
        ];

        $centerFields = [];
        foreach ($centers as $index => $center) {
            $palette = $calmPalettes[$index % count($calmPalettes)];
            $num = $index + 1;

            $legendHtml = new HtmlString(
                "<span style=\"display:inline-flex;align-items:center;gap:8px;padding:3px 12px;border-radius:999px;background-color:{$palette['badge_bg']};border:1px solid {$palette['border']};color:{$palette['text']};font-weight:800;font-size:0.95rem;\">"
                ."<span style=\"display:inline-flex;align-items:center;justify-content:center;width:22px;height:22px;border-radius:50%;background-color:{$palette['accent']};color:#ffffff;font-size:0.78rem;font-weight:800;\">{$num}</span>"
                ."<span>{$center->name_ar}</span>"
                .'</span>'
            );

            $centerFields[] = Fieldset::make($legendHtml)
                ->columns(['default' => 2, 'sm' => 2, 'md' => 2, 'lg' => 2, 'xl' => 2])
                ->extraAttributes([
                    'style' => "background-color: {$palette['bg']}; border: 1.5px solid {$palette['border']}; border-right: 5px solid {$palette['accent']}; border-radius: 12px; padding: 14px 18px; box-shadow: 0 1px 3px rgba(15, 23, 42, 0.03);",
                ])
                ->schema([
                    TextInput::make("center_{$center->id}")
                        ->label('الإيراد الكلي (د.ع)')
                        ->mask(RawJs::make('$money($input, \'.\', \',\', 0)'))
                        ->stripCharacters(',')
                        ->numeric()
                        ->minValue(0)
                        ->default(0)
                        ->live(debounce: 400)
                        ->extraInputAttributes(['style' => 'background-color: #ffffff; font-weight: 700; color: #0f172a; direction: ltr; text-align: right; font-variant-numeric: tabular-nums;'])
                        ->afterStateUpdated(fn ($get, $set) => static::recalculateTotals($get, $set))
                        ->helperText("الإيراد الكلي لـ {$center->name_ar} (د.ع)"),

                    TextInput::make("center_net_{$center->id}")
                        ->label('الإيراد الصافي (د.ع)')
                        ->mask(RawJs::make('$money($input, \'.\', \',\', 0)'))
                        ->stripCharacters(',')
                        ->numeric()
                        ->minValue(0)
                        ->default(0)
                        ->live(debounce: 400)
                        ->extraInputAttributes(['style' => 'background-color: #ffffff; font-weight: 700; color: #0f172a; direction: ltr; text-align: right; font-variant-numeric: tabular-nums;'])
                        ->afterStateUpdated(fn ($get, $set) => static::recalculateTotals($get, $set))
                        ->helperText("الإيراد الصافي لـ {$center->name_ar} (د.ع)"),
                ]);
        }

        return $schema
            ->columns(1)
            ->components([
                // 1. الفترة المالية وحالة السجل
                Section::make('1. الفترة المالية وحالة السجل')
                    ->description('تحديد السنة المالية والشهر لإدخال إيرادات كافة المراكز السبعة معاً')
                    ->columns(['default' => 2, 'sm' => 2, 'md' => 2, 'lg' => 2, 'xl' => 2])
                    ->schema([
                        Select::make('fiscal_year_id')
                            ->label('السنة المالية')
                            ->options(fn () => FiscalYear::pluck('year', 'id'))
                            ->default(fn () => FiscalYear::where('is_current', true)->first()?->id)
                            ->live()
                            ->afterStateUpdated(fn ($get, $set) => static::loadExistingMonthlyData($get, $set))
                            ->required(),

                        Select::make('month_id')
                            ->label('الشهر')
                            ->options(fn () => Month::orderBy('month_number')->get()->mapWithKeys(fn ($m) => [$m->id => "{$m->month_number} - {$m->name_ar}"]))
                            ->required()
                            ->live()
                            ->afterStateUpdated(fn ($get, $set) => static::loadExistingMonthlyData($get, $set)),

                        Hidden::make('status')
                            ->default('approved'),
                    ]),

                // 2. إيرادات مراكز الإيراد السبعة (الكلي والصافي لكل ميناء/مركز)
                Section::make('2. إيرادات مراكز الإيراد السبعة (بالدينار العراقي)')
                    ->description('إدخال الإيراد الكلي والإيراد الصافي لكل ميناء ومركز إيراد للشهر المحدد')
                    ->columns(1)
                    ->schema($centerFields),

                // 3. الإيراد الكلي والصافي للشركة عن هذا الشهر (ناتج الجمع التلقائي مفصولاً بفواصل)
                Section::make('3. ناتج جمع الإيراد الكلي والصافي للشركة عن الشهر')
                    ->description('المجموع الإجمالي المحسوب تلقائياً من جمع الإيراد الكلي والإيراد الصافي لكافة المراكز أعلاه (مفصول بفواصل الآلاف)')
                    ->columns(['default' => 2, 'sm' => 2, 'md' => 2, 'lg' => 2, 'xl' => 2])
                    ->schema([
                        TextInput::make('total_gross_revenue')
                            ->label('مجموع الإيراد الكلي للشهر (د.ع)')
                            ->default('0')
                            ->readOnly()
                            ->dehydrated(false)
                            ->extraInputAttributes(['style' => 'background-color: #f1f5f9; font-weight: 900; color: #1e3a8a; font-size: 1.2rem; cursor: not-allowed; direction: ltr; text-align: right;'])
                            ->helperText('يُحسب تلقائياً بجمع الإيراد الكلي لكافة المراكز السبعة أعلاه'),

                        TextInput::make('monthly_net_revenue')
                            ->label('مجموع الإيراد الصافي للشهر (د.ع)')
                            ->default('0')
                            ->readOnly()
                            ->dehydrated(false)
                            ->extraInputAttributes(['style' => 'background-color: #f0fdf4; font-weight: 900; color: #047857; font-size: 1.2rem; cursor: not-allowed; direction: ltr; text-align: right;'])
                            ->helperText('يُحسب تلقائياً بجمع الإيراد الصافي لكافة المراكز السبعة أعلاه'),

                        Textarea::make('reopen_reason')
                            ->label('ملاحظات / سبب إعادة الفتح (إن وجد)')
                            ->rows(2)
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    // ===================== دوال الحساب والتحميل التلقائي =====================

    public static function cleanNumericValue(mixed $val): float
    {
        if (is_numeric($val)) {
            return (float) $val;
        }
        if (is_string($val)) {
            $clean = str_replace([',', ' '], '', $val);
            return is_numeric($clean) ? (float) $clean : 0.0;
        }
        return 0.0;
    }

    public static function loadExistingMonthlyData($get, $set): void
    {
        $fiscalYearId = $get('fiscal_year_id');
        $monthId = $get('month_id');

        if ($fiscalYearId && $monthId) {
            $centers = RevenueCenter::where('is_active', true)->get();
            foreach ($centers as $center) {
                $set("center_{$center->id}", '0');
                $set("center_net_{$center->id}", '0');
            }

            $records = RevenueRecord::where('fiscal_year_id', $fiscalYearId)
                ->where('month_id', $monthId)
                ->get();

            $totalGross = 0;
            $totalNet = 0;
            $status = 'approved';

            foreach ($records as $record) {
                $gross = (float) $record->gross_revenue;
                $net = (float) $record->net_revenue;
                $set("center_{$record->revenue_center_id}", number_format($gross, 0, '.', ','));
                $set("center_net_{$record->revenue_center_id}", number_format($net, 0, '.', ','));
                $totalGross += $gross;
                $totalNet += $net;
                $status = $record->status;
            }

            $set('total_gross_revenue', number_format($totalGross, 0, '.', ','));
            $set('monthly_net_revenue', number_format($totalNet, 0, '.', ','));
            if ($records->isNotEmpty()) {
                $set('status', $status);
            }
        }
    }

    public static function recalculateTotals($get, $set): void
    {
        $centers = RevenueCenter::where('is_active', true)->get();
        $totalGross = 0;
        $totalNet = 0;

        foreach ($centers as $center) {
            $grossVal = static::cleanNumericValue($get("center_{$center->id}") ?? 0);
            $netVal = static::cleanNumericValue($get("center_net_{$center->id}") ?? 0);
            $totalGross += $grossVal;
            $totalNet += $netVal;
        }

        $set('total_gross_revenue', number_format($totalGross, 0, '.', ','));
        $set('monthly_net_revenue', number_format($totalNet, 0, '.', ','));
    }

    public static function recalculateGrossTotal($get, $set): void
    {
        static::recalculateTotals($get, $set);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query
                ->orderBy(
                    FiscalYear::select('year')
                        ->whereColumn('fiscal_years.id', 'revenue_records.fiscal_year_id')
                        ->limit(1),
                    'desc'
                )
                ->orderBy('revenue_records.month_id', 'desc')
            )
            ->columns([
                TextColumn::make('revenueCenter.name_ar')
                    ->label('مركز الإيراد')
                    ->sortable()
                    ->searchable(
                        query: fn (Builder $query, string $search) => $query->whereHas('revenueCenter', fn ($q) => ArabicSearchHelper::applySearch($q, 'name_ar', $search)),
                        isIndividual: true,
                        isGlobal: true
                    )
                    ->weight('bold')
                    ->toggleable(),

                TextColumn::make('fiscalYear.year')
                    ->label('السنة')
                    ->sortable()
                    ->searchable(isIndividual: true)
                    ->toggleable(),

                TextColumn::make('month_id')
                    ->label('الشهر')
                    ->formatStateUsing(fn ($record) => $record->month ? "{$record->month->month_number} - {$record->month->name_ar}" : $record->month_id)
                    ->sortable()
                    ->searchable(
                        isIndividual: true,
                        query: function (Builder $query, string $search): Builder {
                            $search = trim($search);
                            if ($search === '') {
                                return $query;
                            }

                            if (is_numeric($search)) {
                                return $query->where('revenue_records.month_id', (int) $search);
                            }

                            return $query->whereHas('month', function (Builder $q) use ($search) {
                                if (preg_match('/^(\d+)/', $search, $m)) {
                                    $q->where('month_number', (int) $m[1]);
                                } else {
                                    ArabicSearchHelper::applySearch($q, 'name_ar', $search);
                                }
                            });
                        }
                    )
                    ->alignCenter()
                    ->badge()
                    ->color('info')
                    ->toggleable(),

                TextColumn::make('gross_revenue')
                    ->label('الإيراد الكلي (د.ع)')
                    ->numeric(decimalPlaces: 0)
                    ->sortable()
                    ->searchable(isIndividual: true)
                    ->alignEnd()
                    ->weight('bold')
                    ->color('primary')
                    ->toggleable(),

                TextColumn::make('net_revenue')
                    ->label('الإيراد الصافي (د.ع)')
                    ->numeric(decimalPlaces: 0)
                    ->sortable()
                    ->searchable(isIndividual: true)
                    ->alignEnd()
                    ->weight('bold')
                    ->color('success')
                    ->toggleable(),
            ])
            ->filters([
                SelectFilter::make('revenue_center_id')
                    ->label('مركز الإيراد')
                    ->options(fn () => RevenueCenter::where('is_active', true)->orderBy('sort_order')->pluck('name_ar', 'id')),

                SelectFilter::make('fiscal_year_id')
                    ->label('السنة المالية')
                    ->options(fn () => FiscalYear::orderBy('year', 'desc')->pluck('year', 'id')),

                SelectFilter::make('month_id')
                    ->label('الشهر')
                    ->options(fn () => Month::orderBy('month_number')->get()->mapWithKeys(fn ($m) => [$m->id => "{$m->month_number} - {$m->name_ar}"])),

                SelectFilter::make('status')
                    ->label('الحالة')
                    ->options([
                        'draft' => 'مسودة',
                        'submitted' => 'مُقدَّم للاعتماد',
                        'approved' => 'معتمد',
                        'locked' => 'مقفل',
                    ]),

                TrashedFilter::make()
                    ->label('سلة المحذوفات / سجلات الإيراد المحذوفة مؤقتاً'),
            ])
            ->filtersFormColumns(2)
            ->filtersFormWidth(Width::TwoExtraLarge)
            ->columnToggleFormColumns(2)
            ->columnToggleFormWidth(Width::TwoExtraLarge)
            ->actions([
                ActionGroup::make([
                    ViewAction::make()->label('عرض'),
                    EditAction::make()->label('تعديل'),

                    Action::make('submit')
                        ->label('تقديم للاعتماد')
                        ->icon('heroicon-o-paper-airplane')
                        ->color('warning')
                        ->requiresConfirmation()
                        ->modalHeading('تقديم سجل الإيراد للاعتماد')
                        ->modalDescription('هل أنت متأكد من صحة أرقام الإيراد وتقديمها للاعتماد المالي؟')
                        ->visible(fn (RevenueRecord $record) => $record->status === 'draft' && ! $record->trashed())
                        ->action(function (RevenueRecord $record) {
                            $record->update([
                                'status' => 'submitted',
                                'submitted_by' => Auth::id(),
                                'submitted_at' => now(),
                            ]);

                            Notification::make()
                                ->title('تم تقديم سجل الإيراد للاعتماد بنجاح')
                                ->success()
                                ->send();
                        }),

                    Action::make('approve')
                        ->label('اعتماد')
                        ->icon('heroicon-o-check-badge')
                        ->color('success')
                        ->requiresConfirmation()
                        ->modalHeading('اعتماد سجل الإيراد المالي')
                        ->modalDescription('سيتم تثبيت واعتماد مبالغ الإيراد للشهر.')
                        ->visible(fn (RevenueRecord $record) => $record->status === 'submitted' && ! $record->trashed() && (Auth::user()?->isGeneralManager() || Auth::user()?->hasAnyRole(['admin', 'general_manager', 'المدير العام', 'finance_manager', 'مسؤول الإيراد المالي', 'reviewer', 'مدقق / مراجع']) || in_array(Auth::user()?->user_type, ['general_manager', 'finance_manager', 'reviewer'])))
                        ->action(function (RevenueRecord $record) {
                            $record->update([
                                'status' => 'approved',
                                'approved_by' => Auth::id(),
                                'approved_at' => now(),
                            ]);

                            Notification::make()
                                ->title('تم اعتماد سجل الإيراد بنجاح')
                                ->success()
                                ->send();
                        }),

                    DeleteAction::make()
                        ->label('حذف مؤقت')
                        ->modalHeading('نقل سجل الإيراد إلى سلة المحذوفات')
                        ->modalDescription('سيتم نقل هذا السجل المالي إلى سلة المحذوفات مع إمكانية استرداده.')
                        ->before(function (RevenueRecord $record, DeleteAction $action) {
                            if (in_array($record->status, ['approved', 'locked']) && ! Auth::user()?->hasRole(['super_admin', 'المدير العام', 'general_manager'])) {
                                Notification::make()
                                    ->title('لا يمكن حذف سجل إيراد معتمد')
                                    ->body('هذا السجل المالي معتمد رسمياً. يجب إلغاء اعتماده أولاً قبل حذفه.')
                                    ->danger()
                                    ->send();
                                $action->cancel();
                            }
                        }),

                    RestoreAction::make()
                        ->label('استرداد من الحذف')
                        ->modalHeading('استرداد سجل الإيراد المالي')
                        ->successNotificationTitle('تم استرداد سجل الإيراد المالي بنجاح وإعادته للنظام'),

                    ForceDeleteAction::make()
                        ->label('حذف نهائي')
                        ->visible(fn () => Auth::user()?->hasRole(['super_admin', 'المدير العام'])),
                ])
                    ->tooltip('قائمة الإجراءات')
                    ->icon('heroicon-m-ellipsis-vertical'),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->label('حذف مؤقت للمحدد')
                        ->before(function ($records, DeleteBulkAction $action) {
                            $hasApproved = $records->contains(fn ($r) => in_array($r->status, ['approved', 'locked']));
                            if ($hasApproved && ! Auth::user()?->hasRole(['super_admin', 'المدير العام', 'general_manager'])) {
                                Notification::make()
                                    ->title('تحذير: توجد سجلات مالية معتمدة')
                                    ->body('لا يمكن حذف سجلات الإيراد المعتمدة جماعياً. يرجى إلغاء اعتمادها أولاً.')
                                    ->danger()
                                    ->send();
                                $action->cancel();
                            }
                        }),
                    RestoreBulkAction::make()->label('استرداد المحدد'),
                    ForceDeleteBulkAction::make()
                        ->label('حذف نهائي للمحدد')
                        ->visible(fn () => Auth::user()?->hasRole(['super_admin', 'المدير العام'])),
                ]),
            ]);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with(['revenueCenter', 'fiscalYear', 'month']);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListRevenueRecords::route('/'),
            'create' => CreateRevenueRecord::route('/create'),
            'edit' => EditRevenueRecord::route('/{record}/edit'),
        ];
    }
}
