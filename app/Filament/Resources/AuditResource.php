<?php

namespace App\Filament\Resources;

use App\Enums\NavigationGroup;
use App\Filament\Resources\AuditResource\Pages\ListAudits;
use App\Models\Audit;
use App\Models\User;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\HtmlString;

class AuditResource extends Resource
{
    protected static ?string $model = Audit::class;
    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedFingerPrint;
    protected static ?string $navigationLabel = 'سجل تتبع العمليات (Audit Trail)';
    protected static string|\UnitEnum|null $navigationGroup = NavigationGroup::SystemAdmin;
    protected static ?string $modelLabel = 'سجل نشاط';
    protected static ?string $pluralModelLabel = 'سجل تتبع العمليات والأنشطة';
    protected static ?int $navigationSort = 4;

    public static function canAccess(): bool
    {
        $user = Auth::user();
        if (!$user)
            return false;

        return $user->hasRole(['super_admin', 'المدير العام', 'general_manager', 'مدقق / مراجع', 'reviewer'])
            || in_array($user->user_type, ['general_manager', 'reviewer']);
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('تفاصيل العملية والنشاط')
                ->columns(3)
                ->schema([
                    TextInput::make('user.name')
                        ->label('المستخدم المسؤول')
                        ->default(fn($record) => $record?->user?->name ?? 'مستخدم النظام')
                        ->disabled(),

                    TextInput::make('event_label')
                        ->label('نوع العملية')
                        ->default(fn($record) => $record?->event_label ?? $record?->event)
                        ->disabled(),

                    TextInput::make('auditable_type_label')
                        ->label('القسم / الكيان المتأثر')
                        ->default(fn($record) => $record?->auditable_type_label)
                        ->disabled(),

                    TextInput::make('ip_address')
                        ->label('عنوان IP')
                        ->disabled(),

                    TextInput::make('created_at')
                        ->label('تاريخ ووقت التنفيذ')
                        ->formatStateUsing(fn($state) => $state ? date('Y-m-d H:i:s', strtotime($state)) : '—')
                        ->disabled(),

                    TextInput::make('tags')
                        ->label('بيان العملية')
                        ->disabled(),
                ]),

            Section::make('مقارنة القيم قبل وبعد التعديل')
                ->columns(2)
                ->schema([
                    Placeholder::make('old_values_view')
                        ->label('القيم السابقة (قبل العملية)')
                        ->content(function ($record) {
                            if (empty($record?->old_values)) {
                                return new HtmlString('<div class="p-3 bg-gray-50 dark:bg-gray-800 text-gray-400 rounded-lg text-sm">لا توجد قيم سابقة (عملية إضافة جديدة أو تصدير)</div>');
                            }
                            $html = '<div class="p-3 bg-red-50 dark:bg-red-950/30 border border-red-200 dark:border-red-900 rounded-lg text-xs font-mono overflow-auto max-h-60" dir="ltr"><pre>' . htmlspecialchars(json_encode($record->old_values, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) . '</pre></div>';
                            return new HtmlString($html);
                        }),

                    Placeholder::make('new_values_view')
                        ->label('القيم الجديدة (بعد العملية)')
                        ->content(function ($record) {
                            if (empty($record?->new_values)) {
                                return new HtmlString('<div class="p-3 bg-gray-50 dark:bg-gray-800 text-gray-400 rounded-lg text-sm">لا توجد قيم جديدة (عملية حذف)</div>');
                            }
                            $html = '<div class="p-3 bg-emerald-50 dark:bg-emerald-950/30 border border-emerald-200 dark:border-emerald-900 rounded-lg text-xs font-mono overflow-auto max-h-60" dir="ltr"><pre>' . htmlspecialchars(json_encode($record->new_values, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) . '</pre></div>';
                            return new HtmlString($html);
                        }),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('created_at')
                    ->label('التاريخ والوقت')
                    ->dateTime('Y-m-d H:i:s')
                    ->description(fn(Audit $record) => $record->created_at?->diffForHumans())
                    ->sortable()
                    ->weight('bold'),

                TextColumn::make('user.name')
                    ->label('المستخدم')
                    ->default('النظام / زائر')
                    ->description(fn(Audit $record) => $record->user?->user_type ? match ($record->user->user_type) {
                        'general_manager' => 'المدير العام',
                        'operations_manager' => 'مسؤول العمليات',
                        'finance_manager' => 'مسؤول الإيرادات',
                        'port_data_entry' => 'مدخل بيانات',
                        'reviewer' => 'مدقق / مراجع',
                        default => $record->user->user_type,
                    } : null)
                    ->searchable()
                    ->sortable(),

                TextColumn::make('event_label')
                    ->label('نوع العملية')
                    ->badge()
                    ->color(fn(Audit $record) => $record->event_color)
                    ->icon(fn(Audit $record) => $record->event_icon)
                    ->alignCenter(),

                TextColumn::make('auditable_type_label')
                    ->label('القسم / الكيان')
                    ->badge()
                    ->color('gray'),

                TextColumn::make('target_record_description')
                    ->label('السجل المستهدف / البيان')
                    ->wrap()
                    ->searchable(query: function (Builder $query, string $search) {
                        $query->where('tags', 'like', "%{$search}%");
                    }),

                TextColumn::make('ip_address')
                    ->label('عنوان IP')
                    ->badge()
                    ->color('info')
                    ->toggleable(isToggledHiddenByDefault: false),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('event')
                    ->label('نوع العملية')
                    ->options([
                        'created' => 'إضافة جديدة (Created)',
                        'updated' => 'تعديل بيانات (Updated)',
                        'deleted' => 'حذف مؤقت (Deleted)',
                        'restored' => 'استرداد من الحذف (Restored)',
                        'forceDeleted' => 'حذف نهائي (Force Deleted)',
                        'exported_excel' => 'تصدير ملف Excel',
                        'exported_pdf' => 'تصدير تقرير PDF',
                        'printed' => 'طباعة تقرير',
                        'approved' => 'اعتماد رسمي',
                        'submitted' => 'تقديم للاعتماد',
                    ]),

                SelectFilter::make('user_id')
                    ->label('المستخدم')
                    ->options(fn() => User::orderBy('name')->pluck('name', 'id')),

                SelectFilter::make('auditable_type')
                    ->label('القسم المستهدف')
                    ->options([
                        'App\\Models\\MonthlyPortRecord'     => 'السجلات التشغيلية للموانئ',
                        'App\\Models\\RevenueRecord'         => 'سجلات الإيراد للموانئ والمراكز',
                        'App\\Models\\ContainerStatusRecord' => 'سجلات الحاويات المتخلفة والخطرة',
                        'App\\Models\\ContainerEntity'       => 'جهات ووزارات الحاويات',
                        'App\\Models\\Port'                  => 'الموانئ',
                        'App\\Models\\RevenueCenter'         => 'مراكز الإيراد',
                        'App\\Models\\FiscalYear'            => 'السنوات المالية',
                        'App\\Models\\User'                  => 'المستخدمون',
                        'Report'                             => 'التقارير والمقارنات والتصدير',
                    ]),

                Filter::make('created_at')
                    ->form([
                        DatePicker::make('from_date')
                            ->label('من تاريخ')
                            ->native(false)
                            ->displayFormat('d/m/Y')
                            ->format('Y-m-d')
                            ->closeOnDateSelection(),
                        DatePicker::make('to_date')
                            ->label('إلى تاريخ')
                            ->native(false)
                            ->displayFormat('d/m/Y')
                            ->format('Y-m-d')
                            ->closeOnDateSelection(),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when($data['from_date'] ?? null, fn(Builder $q, $date) => $q->whereDate('created_at', '>=', $date))
                            ->when($data['to_date'] ?? null, fn(Builder $q, $date) => $q->whereDate('created_at', '<=', $date));
                    }),
            ])
            ->actions([
                ViewAction::make()
                    ->label('عرض التفاصيل')
                    ->modalHeading('تفاصيل العملية وسجل التعديل')
                    ->modalWidth('4xl'),
            ])
            ->bulkActions([]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListAudits::route('/'),
        ];
    }
}
