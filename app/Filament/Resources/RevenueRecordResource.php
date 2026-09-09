<?php

namespace App\Filament\Resources;

use App\Enums\NavigationGroup;
use App\Filament\Resources\RevenueRecordResource\Pages\CreateRevenueRecord;
use App\Filament\Resources\RevenueRecordResource\Pages\EditRevenueRecord;
use App\Filament\Resources\RevenueRecordResource\Pages\ListRevenueRecords;
use App\Helpers\ArabicSearchHelper;
use App\Models\FiscalYear;
use App\Models\Month;
use App\Models\RevenueCenter;
use App\Models\RevenueRecord;
use Illuminate\Database\Eloquent\Builder;
use Filament\Actions\Action;
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
        if (! $user) return false;

        return $user->hasRole(['المدير العام', 'general_manager', 'مسؤول المتابعة المركزية والعمليات', 'operations_manager', 'مسؤول الإيراد المالي', 'finance_manager', 'مدقق / مراجع', 'reviewer'])
            || in_array($user->user_type, ['general_manager', 'operations_manager', 'finance_manager', 'reviewer']);
    }

    public static function form(Schema $schema): Schema
    {
        $centers = RevenueCenter::where('is_active', true)->orderBy('sort_order')->get();

        $centerFields = [];
        foreach ($centers as $center) {
            $centerFields[] = TextInput::make("center_{$center->id}")
                ->label($center->name_ar)
                ->numeric()
                ->minValue(0)
                ->default(0)
                ->live(debounce: 500)
                ->afterStateUpdated(fn ($get, $set) => static::recalculateGrossTotal($get, $set))
                ->helperText("الإيراد الكلي لـ {$center->name_ar} (د.ع)");
        }

        return $schema
            ->columns(1)
            ->components([
                // 1. الفترة المالية وحالة السجل
                Section::make('1. الفترة المالية وحالة السجل')
                    ->description('تحديد السنة المالية والشهر لإدخال إيرادات كافة المراكز السبعة معاً')
                    ->columns(2)
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

                // 2. إيرادات مراكز الإيراد السبعة
                Section::make('2. إيرادات مراكز الإيراد السبعة (بالدينار العراقي)')
                    ->description('إدخال الإيراد الكلي لكل ميناء ومركز إيراد للشهر المحدد')
                    ->columns(4)
                    ->schema($centerFields),

                // 3. الإيراد الكلي والصافي للشركة عن هذا الشهر
                Section::make('3. الإيراد الكلي والصافي للشركة عن الشهر')
                    ->description('المجموع الإجمالي المحسوب لكافة المراكز مع الإيراد الصافي للشركة')
                    ->columns(2)
                    ->schema([
                        TextInput::make('total_gross_revenue')
                            ->label('مجموع الإيراد الكلي للشهر (د.ع)')
                            ->numeric()
                            ->default(0)
                            ->readOnly()
                            ->extraInputAttributes(['style' => 'background-color: #f1f5f9; font-weight: bold; color: #1e3a8a; font-size: 1.1rem; cursor: not-allowed;'])
                            ->helperText('يُحسب تلقائياً بجمع إيرادات المراكز السبعة أعلاه'),

                        TextInput::make('monthly_net_revenue')
                            ->label('الإيراد الصافي للشركة عن هذا الشهر (د.ع)')
                            ->numeric()
                            ->minValue(0)
                            ->default(0)
                            ->required()
                            ->helperText('المبلغ الصافي الكلي للشركة عن هذا الشهر (يُسجل مرة واحدة)'),

                        Textarea::make('reopen_reason')
                            ->label('ملاحظات / سبب إعادة الفتح (إن وجد)')
                            ->rows(2)
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    // ===================== دوال الحساب والتحميل التلقائي =====================

    public static function loadExistingMonthlyData($get, $set): void
    {
        $fiscalYearId = $get('fiscal_year_id');
        $monthId = $get('month_id');

        if ($fiscalYearId && $monthId) {
            $records = RevenueRecord::where('fiscal_year_id', $fiscalYearId)
                ->where('month_id', $monthId)
                ->get();

            $totalGross = 0;
            $totalNet = 0;
            $status = 'draft';

            foreach ($records as $record) {
                $set("center_{$record->revenue_center_id}", (float) $record->gross_revenue);
                $totalGross += (float) $record->gross_revenue;
                $totalNet += (float) $record->net_revenue;
                $status = $record->status;
            }

            $set('total_gross_revenue', $totalGross);
            if ($totalNet > 0) {
                $set('monthly_net_revenue', $totalNet);
            }
            if ($records->isNotEmpty()) {
                $set('status', $status);
            }
        }
    }

    public static function recalculateGrossTotal($get, $set): void
    {
        $centers = RevenueCenter::where('is_active', true)->get();
        $totalGross = 0;

        foreach ($centers as $center) {
            $val = (float) ($get("center_{$center->id}") ?? 0);
            $totalGross += $val;
        }

        $set('total_gross_revenue', $totalGross);
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

                TextColumn::make('gross_revenue')
                    ->label('الإيراد الكلي (د.ع)')
                    ->numeric(decimalPlaces: 0)
                    ->sortable()
                    ->searchable(isIndividual: true)
                    ->alignEnd()
                    ->weight('bold'),
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
                        'draft'     => 'مسودة',
                        'submitted' => 'مُقدَّم للاعتماد',
                        'approved'  => 'معتمد',
                        'locked'    => 'مقفل',
                    ]),

                TrashedFilter::make()
                    ->label('سلة المحذوفات / سجلات الإيراد المحذوفة مؤقتاً'),
            ])
            ->actions([
                ViewAction::make()->label('عرض'),
                EditAction::make()->label('تعديل'),

                Action::make('submit')
                    ->label('تقديم للاعتماد')
                    ->icon('heroicon-o-paper-airplane')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->modalHeading('تقديم سجل الإيراد للاعتماد')
                    ->modalDescription('هل أنت متأكد من صحة أرقام الإيراد وتقديمها للاعتماد المالي؟')
                    ->visible(fn (RevenueRecord $record) => $record->status === 'draft' && !$record->trashed())
                    ->action(function (RevenueRecord $record) {
                        $record->update([
                            'status'       => 'submitted',
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
                    ->visible(fn (RevenueRecord $record) => $record->status === 'submitted' && !$record->trashed() && Auth::user()?->hasRole(['admin', 'general_manager', 'finance_manager', 'reviewer']))
                    ->action(function (RevenueRecord $record) {
                        $record->update([
                            'status'      => 'approved',
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
                        if (in_array($record->status, ['approved', 'locked']) && !Auth::user()?->hasRole(['super_admin', 'المدير العام', 'general_manager'])) {
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
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->label('حذف مؤقت للمحدد')
                        ->before(function ($records, DeleteBulkAction $action) {
                            $hasApproved = $records->contains(fn ($r) => in_array($r->status, ['approved', 'locked']));
                            if ($hasApproved && !Auth::user()?->hasRole(['super_admin', 'المدير العام', 'general_manager'])) {
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

    public static function getPages(): array
    {
        return [
            'index'  => ListRevenueRecords::route('/'),
            'create' => CreateRevenueRecord::route('/create'),
            'edit'   => EditRevenueRecord::route('/{record}/edit'),
        ];
    }
}
