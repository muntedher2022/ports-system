<?php

namespace App\Filament\Resources;

use App\Enums\NavigationGroup;
use App\Filament\Resources\UserResource\Pages;
use App\Filament\Resources\UserResource\Pages\CreateUser;
use App\Filament\Resources\UserResource\Pages\EditUser;
use App\Filament\Resources\UserResource\Pages\ListUsers;
use App\Models\Port;
use App\Models\User;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Forms\Components\Select;
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
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class UserResource extends Resource
{
    protected static ?string $model = User::class;
    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedUsers;
    protected static ?string $navigationLabel = 'المستخدمون';
    protected static string|\UnitEnum|null $navigationGroup = NavigationGroup::SystemAdmin;
    protected static ?string $modelLabel = 'مستخدم';
    protected static ?string $pluralModelLabel = 'المستخدمون';
    protected static ?int $navigationSort = 1;

    public static function canAccess(): bool
    {
        $user = Auth::user();
        return (bool) ($user && ($user->hasRole(['المدير العام', 'general_manager']) || $user->user_type === 'general_manager'));
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('بيانات المستخدم')
                ->columns(2)
                ->schema([
                    TextInput::make('name')
                        ->label('الاسم الكامل')
                        ->required()
                        ->maxLength(255),

                    TextInput::make('email')
                        ->label('البريد الإلكتروني')
                        ->email()
                        ->required()
                        ->unique(ignoreRecord: true)
                        ->maxLength(255),

                    TextInput::make('password')
                        ->label('كلمة المرور')
                        ->password()
                        ->required(fn($livewire) => $livewire instanceof CreateUser)
                        ->dehydrated(fn($state) => filled($state))
                        ->dehydrateStateUsing(fn($state) => Hash::make($state))
                        ->maxLength(255),

                    Select::make('user_type')
                        ->label('نوع المستخدم')
                        ->options([
                            'general_manager'    => 'المدير العام',
                            'operations_manager' => 'مسؤول المتابعة المركزية والعمليات',
                            'port_data_entry'    => 'مدخل بيانات الميناء',
                            'finance_manager'    => 'مسؤول الإيراد المالي',
                            'reviewer'           => 'مدقق / مراجع',
                        ])
                        ->required()
                        ->live(),
                ]),

            Section::make('الصلاحيات')
                ->columns(2)
                ->schema([
                    Select::make('roles')
                        ->label('الدور في النظام')
                        ->options(fn() => Role::all()->pluck('name', 'id'))
                        ->multiple()
                        ->preload()
                        ->searchable()
                        ->relationship('roles', 'name'),

                    Select::make('port_id')
                        ->label('الميناء المخصص')
                        ->options(fn() => Port::where('is_active', true)->pluck('name_ar', 'id'))
                        ->nullable()
                        ->searchable()
                        ->helperText('مطلوب لمدخلي بيانات الموانئ فقط')
                        ->visible(fn($get) => $get('user_type') === 'port_data_entry'),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('الاسم الكامل')
                    ->searchable(isIndividual: true)
                    ->sortable()
                    ->weight('bold'),

                TextColumn::make('email')
                    ->label('البريد الإلكتروني')
                    ->searchable(isIndividual: true),

                TextColumn::make('user_type')
                    ->label('النوع والوظيفة')
                    ->formatStateUsing(fn($state) => match($state) {
                        'general_manager'    => 'المدير العام',
                        'operations_manager' => 'المتابعة المركزية والعمليات',
                        'port_data_entry'    => 'مدخل بيانات ميناء',
                        'finance_manager'    => 'مسؤول الإيراد المالي',
                        'reviewer'           => 'مدقق / مراجع',
                        default              => $state ?? '—',
                    })
                    ->badge()
                    ->color(fn($state) => match($state) {
                        'general_manager'    => 'danger',
                        'operations_manager' => 'primary',
                        'finance_manager'    => 'warning',
                        'port_data_entry'    => 'info',
                        'reviewer'           => 'success',
                        default              => 'gray',
                    }),

                TextColumn::make('port.name_ar')
                    ->label('الميناء التابع له')
                    ->searchable(isIndividual: true)
                    ->default('كافة الموانئ / الإدارة العامة')
                    ->badge()
                    ->color(fn($record) => $record->port_id ? 'info' : 'gray'),

                TextColumn::make('roles.name')
                    ->label('الأدوار الممنوحة')
                    ->badge()
                    ->color('success')
                    ->separator(','),
            ])
            ->filters([
                SelectFilter::make('user_type')
                    ->label('نوع المستخدم')
                    ->options([
                        'general_manager'    => 'المدير العام',
                        'operations_manager' => 'مسؤول المتابعة المركزية والعمليات',
                        'port_data_entry'    => 'مدخل بيانات الميناء',
                        'finance_manager'    => 'مسؤول الإيراد المالي',
                        'reviewer'           => 'مدقق / مراجع',
                    ]),

                TrashedFilter::make()->label('سلة المحذوفات / المستخدمون المعطلون'),
            ])
            ->actions([
                EditAction::make()->label('تعديل'),
                DeleteAction::make()
                    ->label('تعطيل / نقل للمحذوفات')
                    ->modalHeading('تعطيل حساب المستخدم')
                    ->modalDescription('سيتم تعطيل الحساب ونقله إلى سلة المحذوفات المؤقتة.')
                    ->before(function (User $record, DeleteAction $action) {
                        if ($record->id === Auth::id()) {
                            Notification::make()
                                ->title('إجراء غير مسموح')
                                ->body('لا يمكنك حذف أو تعطيل حسابك الحالي المسجل به في النظام.')
                                ->danger()
                                ->send();
                            $action->cancel();
                        }
                    }),

                RestoreAction::make()
                    ->label('إعادة تفعيل الحساب')
                    ->modalHeading('استرداد وتفعيل حساب المستخدم'),

                ForceDeleteAction::make()
                    ->label('حذف نهائي')
                    ->visible(fn () => Auth::user()?->hasRole(['super_admin', 'المدير العام'])),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->label('تعطيل المحدد')
                        ->before(function ($records, DeleteBulkAction $action) {
                            if ($records->contains(fn ($u) => $u->id === Auth::id())) {
                                Notification::make()
                                    ->title('تنبيه')
                                    ->body('لا يمكنك تعطيل حسابك الشخصي ضمن الحسابات المحددة.')
                                    ->danger()
                                    ->send();
                                $action->cancel();
                            }
                        }),
                    RestoreBulkAction::make()->label('إعادة تفعيل المحدد'),
                    ForceDeleteBulkAction::make()
                        ->label('حذف نهائي للمحدد')
                        ->visible(fn () => Auth::user()?->hasRole(['super_admin', 'المدير العام'])),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => ListUsers::route('/'),
            'create' => CreateUser::route('/create'),
            'edit'   => EditUser::route('/{record}/edit'),
        ];
    }
}
