<?php

namespace App\Filament\GmActivities\Pages;

use App\Models\Activity;
use BackedEnum;
use Carbon\Carbon;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Pages\Page;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ExecutiveReports extends Page implements HasTable
{
    use InteractsWithTable;

    protected static string | BackedEnum | null $navigationIcon = 'heroicon-o-document-chart-bar';

    protected static ?string $navigationLabel = 'التقارير الدورية الرسمية';

    protected static string | \UnitEnum | null $navigationGroup = 'الديوان والتوثيق';

    protected static ?string $title = 'إصدار التقارير الدورية لنشاطات المدير العام';

    protected static ?int $navigationSort = 2;

    protected string $view = 'filament.pages.executive-reports';

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill([
            'preset' => 'this_month',
            'start_date' => now()->startOfMonth()->format('Y-m-d'),
            'end_date' => now()->format('Y-m-d'),
            'category' => 'all',
            'importance' => 'all',
            'status' => 'all',
            'only_included_in_report' => true,
        ]);
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('print_pdf_header')
                ->label('تصدير وطباعة تقرير PDF فوري')
                ->icon('heroicon-o-printer')
                ->color('success')
                ->size('lg')
                ->url(fn (): string => route('reports.periodic.pdf', array_filter($this->data ?? [], fn ($val) => !is_null($val) && $val !== '')))
                ->openUrlInNewTab(),
        ];
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->statePath('data')
            ->columns(1)
            ->components([
                Section::make('معايير تصفية وتوليد التقرير الدوري بين تاريخين')
                    ->description('حدد نطاق الفترة الزمنية (أسبوع معين، أيام محددة أو نطاق زمني) والبيانات المراد تضمينها في وثيقة الـ PDF')
                    ->icon('heroicon-o-adjustments-horizontal')
                    ->columnSpanFull()
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                Select::make('preset')
                                    ->label('تحديد سريع للفترة (اختياري)')
                                    ->options([
                                        'custom' => 'تحديد مخصص بين تاريخين',
                                        'today' => 'أنشطة اليوم فقط',
                                        'this_week' => 'الأسبوع الحالي (السبت - الجمعة)',
                                        'last_7_days' => 'آخر 7 أيام',
                                        'this_month' => 'الشهر الحالي',
                                        'last_30_days' => 'آخر 30 يوماً',
                                        'this_year' => 'السنة الحالية',
                                    ])
                                    ->default('this_month')
                                    ->live()
                                    ->afterStateUpdated(function ($state, callable $set) {
                                        $now = Carbon::now();
                                        match ($state) {
                                            'today' => [
                                                $set('start_date', $now->format('Y-m-d')),
                                                $set('end_date', $now->format('Y-m-d')),
                                            ],
                                            'this_week' => [
                                                $set('start_date', $now->copy()->startOfWeek(Carbon::SATURDAY)->format('Y-m-d')),
                                                $set('end_date', $now->copy()->endOfWeek(Carbon::FRIDAY)->format('Y-m-d')),
                                            ],
                                            'last_7_days' => [
                                                $set('start_date', $now->copy()->subDays(6)->format('Y-m-d')),
                                                $set('end_date', $now->format('Y-m-d')),
                                            ],
                                            'this_month' => [
                                                $set('start_date', $now->copy()->startOfMonth()->format('Y-m-d')),
                                                $set('end_date', $now->copy()->endOfMonth()->format('Y-m-d')),
                                            ],
                                            'last_30_days' => [
                                                $set('start_date', $now->copy()->subDays(29)->format('Y-m-d')),
                                                $set('end_date', $now->format('Y-m-d')),
                                            ],
                                            'this_year' => [
                                                $set('start_date', $now->copy()->startOfYear()->format('Y-m-d')),
                                                $set('end_date', $now->copy()->endOfYear()->format('Y-m-d')),
                                            ],
                                            default => null,
                                        };
                                    }),

                                DatePicker::make('start_date')
                                    ->label('من تاريخ')
                                    ->native(false)
                                    ->displayFormat('Y/m/d')
                                    ->required()
                                    ->live(),

                                DatePicker::make('end_date')
                                    ->label('إلى تاريخ')
                                    ->native(false)
                                    ->displayFormat('Y/m/d')
                                    ->required()
                                    ->live(),
                            ]),

                        Grid::make(4)
                            ->schema([
                                Select::make('category')
                                    ->label('نوع النشاط')
                                    ->options([
                                        'all' => 'كافة النشاطات',
                                        'اجتماع رسمي' => 'اجتماع رسمي',
                                        'زيارة ميدانية' => 'زيارة ميدانية وجولة تفقدية',
                                        'استقبال وفد' => 'استقبال وفد رسمي',
                                        'مؤتمر أو ندوة' => 'مؤتمر أو ورشة عمل',
                                        'قرار وتوجيه' => 'قرار وتوجيهات عليا',
                                        'متابعة مشاريع' => 'متابعة المشاريع',
                                        'أخرى' => 'نشاطات أخرى',
                                    ])
                                    ->default('all')
                                    ->live(),

                                Select::make('importance')
                                    ->label('درجة الأهمية')
                                    ->options([
                                        'all' => 'كافة الدرجات',
                                        'urgent' => 'عاجل جداً',
                                        'important' => 'مهم',
                                        'routine' => 'عادي / دوري',
                                    ])
                                    ->default('all')
                                    ->live(),

                                Select::make('status')
                                    ->label('حالة الإنجاز')
                                    ->options([
                                        'all' => 'كافة الحالات',
                                        'completed' => 'منجز ومكتمل',
                                        'in_progress' => 'قيد المتابعة والتنفيذ',
                                        'scheduled' => 'مجدول وقادم',
                                    ])
                                    ->default('all')
                                    ->live(),

                                Toggle::make('only_included_in_report')
                                    ->label('المشمولة بالتقرير فقط')
                                    ->helperText('استبعاد الأنشطة غير المدرجة بالتقرير')
                                    ->default(true)
                                    ->live(),
                            ]),
                    ]),
            ]);
    }

    public function updatedData(): void
    {
        $this->resetTable();
    }

    public function getFilteredActivitiesQuery(): Builder
    {
        $startDate = $this->data['start_date'] ?? null;
        $endDate = $this->data['end_date'] ?? null;
        $category = $this->data['category'] ?? 'all';
        $importance = $this->data['importance'] ?? 'all';
        $status = $this->data['status'] ?? 'all';
        $onlyIncluded = $this->data['only_included_in_report'] ?? true;

        $query = Activity::query()->with(['attachments', 'user']);

        if ($startDate) {
            $query->whereDate('activity_date', '>=', $startDate);
        }
        if ($endDate) {
            $query->whereDate('activity_date', '<=', $endDate);
        }

        if ($onlyIncluded) {
            $query->where('include_in_periodic_report', true);
        }

        if ($category && $category !== 'all') {
            $query->where('category', $category);
        }
        if ($importance && $importance !== 'all') {
            $query->where('importance', $importance);
        }
        if ($status !== 'all') {
            $query->where('status', $status);
        }

        if (! auth()->user()?->isGeneralManager()) {
            $query->where('is_confidential', false);
        }

        return $query->orderBy('activity_date', 'asc');
    }

    public function table(Table $table): Table
    {
        return $table
            ->query($this->getFilteredActivitiesQuery())
            ->heading('قائمة الأنشطة المشمولة بالتقرير المختار')
            ->description('يتم تحديث هذه القائمة والإحصائيات مباشرة وفق معايير التصفية أعلاه')
            ->columns([
                TextColumn::make('title')
                    ->label('اسم النشاط')
                    ->weight('bold')
                    ->description(fn (Activity $record): string => \Illuminate\Support\Str::limit($record->short_description, 80))
                    ->grow(true)
                    ->wrap(),

                TextColumn::make('activity_date')
                    ->label('التاريخ')
                    ->date('Y/m/d')
                    ->badge()
                    ->color('gray'),

                TextColumn::make('category')
                    ->label('نوع النشاط')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'اجتماع رسمي' => 'info',
                        'زيارة ميدانية' => 'success',
                        'استقبال وفد' => 'warning',
                        'مؤتمر أو ندوة' => 'purple',
                        'قرار وتوجيه' => 'danger',
                        'متابعة مشاريع' => 'primary',
                        default => 'gray',
                    }),

                TextColumn::make('location')
                    ->label('الموقع / المقر')
                    ->placeholder('ديوان الشركة')
                    ->wrap()
                    ->lineClamp(2),

                TextColumn::make('importance')
                    ->label('الأهمية')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'urgent' => 'عاجل جداً',
                        'important' => 'مهم',
                        'routine' => 'عادي',
                        default => $state,
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'urgent' => 'danger',
                        'important' => 'warning',
                        'routine' => 'gray',
                        default => 'gray',
                    }),

                TextColumn::make('status')
                    ->label('الحالة')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'completed' => 'منجز',
                        'in_progress' => 'قيد المتابعة',
                        'scheduled' => 'مجدول',
                        'postponed' => 'مؤجل',
                        default => $state,
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'completed' => 'success',
                        'in_progress' => 'info',
                        'scheduled' => 'warning',
                        'postponed' => 'danger',
                        default => 'gray',
                    }),

                TextColumn::make('attachments_count')
                    ->label('الصور')
                    ->counts('attachments')
                    ->badge()
                    ->color('success'),
            ])
            ->recordActions([
                \Filament\Actions\Action::make('pdf')
                    ->label('بطاقة PDF')
                    ->icon('heroicon-o-document-arrow-down')
                    ->color('success')
                    ->url(fn (Activity $record): string => route('activities.single.pdf', ['activity' => $record]))
                    ->openUrlInNewTab(),
            ])
            ->emptyStateHeading('لا توجد نشاطات مسجلة تطابق هذه المعايير')
            ->emptyStateDescription('يرجى تعديل معايير التصفية أو اختيار نطاق زمني آخر.')
            ->emptyStateIcon('heroicon-o-document-magnifying-glass');
    }

    public function generatePdf()
    {
        $params = array_filter($this->data ?? [], fn ($val) => !is_null($val) && $val !== '');
        $url = route('reports.periodic.pdf', $params);
        return redirect()->away($url);
    }
}
