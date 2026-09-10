<?php

namespace App\Filament\Resources;

use App\Enums\NavigationGroup;
use App\Filament\Resources\PermissionResource\Pages\ListPermissions;
use App\Filament\Resources\PermissionResource\Pages\ViewPermission;
use App\Models\Permission;
use Filament\Actions\ActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Models\Role;

class PermissionResource extends Resource
{
    protected static ?string $model = Permission::class;
    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedKey;
    protected static ?string $navigationLabel = 'دليل الصلاحيات';
    protected static string|\UnitEnum|null $navigationGroup = NavigationGroup::SystemAdmin;
    protected static ?string $modelLabel = 'صلاحية';
    protected static ?string $pluralModelLabel = 'دليل الصلاحيات';
    protected static ?int $navigationSort = 3;

    public static function canAccess(): bool
    {
        $user = Auth::user();
        if (!$user) {
            return false;
        }

        return $user->hasRole(['super_admin', 'المدير العام', 'general_manager'])
            || $user->user_type === 'general_manager';
    }

    public static function canCreate(): bool
    {
        $user = Auth::user();
        if (!$user) {
            return false;
        }

        return $user->hasRole(['super_admin', 'المدير العام', 'general_manager'])
            || $user->user_type === 'general_manager';
    }

    public static function canEdit(Model $record): bool
    {
        $user = Auth::user();
        if (!$user) {
            return false;
        }

        return $user->hasRole(['super_admin', 'المدير العام', 'general_manager'])
            || $user->user_type === 'general_manager';
    }

    public static function canDelete(Model $record): bool
    {
        $user = Auth::user();
        if (!$user) {
            return false;
        }

        return $user->hasRole(['super_admin', 'المدير العام', 'general_manager'])
            || $user->user_type === 'general_manager';
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('معلومات الصلاحية')
                ->schema([
                    Grid::make(2)
                        ->schema([
                            TextInput::make('name')
                                ->label('المفتاح البرمجي للصلاحية (Permission Key)')
                                ->required()
                                ->unique(ignoreRecord: true)
                                ->placeholder('مثال: Create:MonthlyPortRecord أو page_CustomReport')
                                ->helperText('المفتاح الفريد للصلاحية في النظام باللغة الإنجليزية.')
                                ->disabled(fn (string $operation): bool => $operation === 'view'),

                            TextInput::make('guard_name')
                                ->label('الحارس (Guard)')
                                ->default('web')
                                ->required()
                                ->disabled(fn (string $operation): bool => $operation === 'view'),
                        ]),

                    Grid::make(3)
                        ->visible(fn (string $operation): bool => $operation !== 'create')
                        ->schema([
                            Placeholder::make('action_label')
                                ->label('نوع الإجراء')
                                ->content(fn (?Permission $record) => $record?->action_arabic ?? '-'),

                            Placeholder::make('entity_label')
                                ->label('المورد / الكيان التابع له')
                                ->content(fn (?Permission $record) => $record?->entity_arabic ?? '-'),

                            Placeholder::make('system_label')
                                ->label('المجموعة / النظام')
                                ->content(fn (?Permission $record) => $record?->system_group ?? '-'),
                        ]),
                ]),

            Section::make('الأدوار المرتبطة بهذه الصلاحية')
                ->description('تحديد وإسناد الأدوار الممنوحة لها هذه الصلاحية')
                ->schema([
                    Select::make('roles')
                        ->label('الأدوار الممنوحة')
                        ->relationship('roles', 'name')
                        ->multiple()
                        ->preload()
                        ->searchable()
                        ->helperText('اختر الأدوار التي تمتلك هذه الصلاحية في النظام.')
                        ->disabled(fn (string $operation): bool => $operation === 'view'),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('name', 'asc')
            ->columns([
                TextColumn::make('name')
                    ->label('المفتاح البرمجي (Permission Key)')
                    ->searchable(isIndividual: true)
                    ->sortable()
                    ->copyable()
                    ->copyMessage('تم نسخ المفتاح البرمجي')
                    ->fontFamily('mono')
                    ->weight('medium')
                    ->color('primary')
                    ->description(fn (Permission $record): string => $record->entity_arabic),

                TextColumn::make('action_arabic')
                    ->label('الإجراء')
                    ->badge()
                    ->color(fn (Permission $record): string => match(true) {
                        str_starts_with($record->name, 'Create') => 'success',
                        str_starts_with($record->name, 'Update') => 'warning',
                        str_starts_with($record->name, 'Delete') || str_starts_with($record->name, 'Force') => 'danger',
                        str_starts_with($record->name, 'Restore') => 'info',
                        str_starts_with($record->name, 'View') => 'primary',
                        default => 'gray',
                    })
                    ->sortable(query: fn (Builder $query, string $direction): Builder => $query->orderBy('name', $direction))
                    ->searchable(isIndividual: true, query: function (Builder $query, string $search) {
                        $query->where('name', 'like', "%{$search}%");
                    }),

                TextColumn::make('system_group')
                    ->label('النظام / المجموعة')
                    ->badge()
                    ->color(fn (Permission $record): string => $record->system_group_color)
                    ->searchable(isIndividual: true, query: function (Builder $query, string $search) {
                        $query->where('name', 'like', "%{$search}%");
                    }),

                TextColumn::make('type_label')
                    ->label('النوع')
                    ->color('gray')
                    ->toggleable(),

                TextColumn::make('roles.name')
                    ->label('الأدوار الممنوحة')
                    ->badge()
                    ->color('info')
                    ->separator(', ')
                    ->limitList(3)
                    ->expandableLimitedList()
                    ->searchable(isIndividual: true),

                TextColumn::make('roles_count')
                    ->label('عدد الأدوار')
                    ->counts('roles')
                    ->badge()
                    ->color(fn (int $state): string => $state > 0 ? 'success' : 'gray')
                    ->sortable(),

                TextColumn::make('guard_name')
                    ->label('الحارس')
                    ->badge()
                    ->color('gray')
                    ->searchable(isIndividual: true)
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('created_at')
                    ->label('تاريخ الإنشاء')
                    ->dateTime('Y-m-d H:i')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('role')
                    ->label('تصفية حسب الدور')
                    ->options(fn () => Role::pluck('name', 'id')->toArray())
                    ->query(function (Builder $query, array $data): Builder {
                        if (!empty($data['value'])) {
                            return $query->whereHas('roles', fn ($q) => $q->where('id', $data['value']));
                        }
                        return $query;
                    }),

                SelectFilter::make('system_filter')
                    ->label('تصفية حسب النظام')
                    ->options([
                        'monthly'   => 'السجلات التشغيلية للموانئ',
                        'revenue'   => 'نظام الإيرادات',
                        'container' => 'نظام الحاويات المتخلفة والخطرة',
                        'cargo'     => 'نظام المواد والبضائع المتخلفة والخطرة',
                        'settings'  => 'البيانات الرئيسية والإعدادات',
                        'security'  => 'إدارة النظام والأمان',
                        'analytics' => 'التحليلات والمقارنات الإحصائية',
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return match ($data['value'] ?? null) {
                            'monthly'   => $query->where(fn ($q) => $q->where('name', 'like', '%MonthlyPortRecord%')->orWhere('name', 'like', '%LatestPortRecords%')->orWhere('name', 'like', '%MonthlyPerformance%')),
                            'revenue'   => $query->where(fn ($q) => $q->where('name', 'like', '%RevenueRecord%')->orWhere('name', 'like', '%RevenueCenter%')->orWhere('name', 'like', '%RevenueTrends%')->orWhere('name', 'like', '%TotalRevenueMatrix%')->orWhere('name', 'like', '%RevenueComparison%')),
                            'container' => $query->where('name', 'like', '%Container%'),
                            'cargo'     => $query->where('name', 'like', '%Cargo%'),
                            'settings'  => $query->where(fn ($q) => $q->where('name', 'like', '%:Port%')->orWhere('name', 'like', '%:FiscalYear%')->orWhere('name', 'like', '%:Month%')),
                            'security'  => $query->where(fn ($q) => $q->where('name', 'like', '%:User%')->orWhere('name', 'like', '%:Role%')->orWhere('name', 'like', '%:Audit%')),
                            'analytics' => $query->where(fn ($q) => $q->where('name', 'like', '%Comparison%')->orWhere('name', 'like', '%Analytics%')->orWhere('name', 'like', '%Capacity%')->orWhere('name', 'like', '%PortStats%')->orWhere('name', 'like', '%PortShare%')->orWhere('name', 'like', '%StandardDeviation%')),
                            default     => $query,
                        };
                    }),

                Filter::make('has_roles')
                    ->label('صلاحيات ممنوحة لأدوار فقط')
                    ->query(fn (Builder $query): Builder => $query->has('roles')),

                Filter::make('no_roles')
                    ->label('صلاحيات غير ممنوحة لأي دور')
                    ->query(fn (Builder $query): Builder => $query->doesntHave('roles')),
            ])
            ->recordActions([
                ActionGroup::make([
                    ViewAction::make()
                        ->label('عرض')
                        ->modalHeading('تفاصيل الصلاحية'),
                    EditAction::make()
                        ->label('تعديل')
                        ->modalHeading('تعديل الصلاحية والأدوار'),
                    DeleteAction::make()
                        ->label('حذف')
                        ->modalHeading('هل أنت متأكد من حذف هذه الصلاحية؟')
                        ->modalDescription('تحذير: حذف الصلاحيات الأساسية قد يؤثر على وصول المستخدمين للشاشات المرتبطة بها.')
                        ->successNotificationTitle('تم حذف الصلاحية بنجاح'),
                ])
                ->tooltip('قائمة الإجراءات')
                ->icon('heroicon-m-ellipsis-vertical'),
            ])
            ->toolbarActions([]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPermissions::route('/'),
        ];
    }
}
