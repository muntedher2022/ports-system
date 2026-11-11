<?php

namespace App\Filament\GmActivities\Resources\Activities\Schemas;

use Carbon\Carbon;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;

class ActivityForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns([
                'default' => 1,
                'lg' => 12,
            ])
            ->components([
                // العمود الرئيسي: البيانات الأساسية وتفاصيل الموعد وتحتها الموجز والمخرجات والتوجيهات
                Group::make([
                        Section::make('البيانات الأساسية وتفاصيل الموعد')
                            ->description('تحديد اسم النشاط وتاريخه وتصنيفه وموقعه الرسمي')
                            ->icon('heroicon-o-calendar-days')
                            ->collapsible()
                            ->schema([
                                TextInput::make('title')
                                    ->label('اسم النشاط / الفعالية')
                                    ->placeholder('مثال: اجتماع مجلس الإدارة لمناقشة تقرير خطة الربع الثالث')
                                    ->required()
                                    ->maxLength(255)
                                    ->columnSpanFull(),

                                Grid::make(3)
                                    ->schema([
                                        Select::make('category')
                                            ->label('نوع النشاط')
                                            ->options([
                                                'اجتماع رسمي' => 'اجتماع رسمي',
                                                'زيارة ميدانية' => 'زيارة ميدانية وجولة تفقدية',
                                                'استقبال وفد' => 'استقبال وفد رسمي',
                                                'مؤتمر أو ندوة' => 'مؤتمر أو ورشة عمل',
                                                'قرار وتوجيه' => 'قرار وتوجيهات عليا',
                                                'متابعة مشاريع' => 'متابعة سير المشاريع الاستراتيجية',
                                                'أخرى' => 'نشاطات أخرى',
                                            ])
                                            ->default('اجتماع رسمي')
                                            ->required()
                                            ->native(false),

                                        DatePicker::make('activity_date')
                                            ->label('تاريخ النشاط')
                                            ->default(now())
                                            ->required()
                                            ->native(false)
                                            ->displayFormat('Y/m/d')
                                            ->closeOnDateSelection(),

                                        TimePicker::make('activity_time')
                                            ->label('وقت النشاط (اختياري)')
                                            ->seconds(false)
                                            ->native(false),
                                    ]),

                                Grid::make(3)
                                    ->schema([
                                        TextInput::make('location')
                                            ->label('الموقع / المقر / الجهة')
                                            ->placeholder('مثال: ديوان الشركة')
                                            ->maxLength(255),

                                        Select::make('importance')
                                            ->label('درجة الأهمية')
                                            ->options([
                                                'urgent' => 'عاجل جداً',
                                                'important' => 'مهم',
                                                'routine' => 'عادي / دوري',
                                            ])
                                            ->default('important')
                                            ->required()
                                            ->native(false),

                                        Select::make('status')
                                            ->label('حالة الإنجاز')
                                            ->options([
                                                'completed' => 'منجز ومكتمل',
                                                'in_progress' => 'قيد المتابعة والتنفيذ',
                                                'scheduled' => 'مجدول وقادم',
                                                'postponed' => 'مؤجل',
                                            ])
                                            ->default('completed')
                                            ->required()
                                            ->native(false),
                                    ]),

                                Grid::make(2)
                                    ->schema([
                                        Toggle::make('include_in_periodic_report')
                                            ->label('إدراج في التقرير الدوري')
                                            ->helperText('تحديد ما إذا كان هذا النشاط يظهر ضمن التقارير الدورية التنفيذية')
                                            ->default(true),

                                        Toggle::make('is_confidential')
                                            ->label('نشاط سري / مقتصر على المدير العام')
                                            ->helperText('يقتصر الاطلاع عليه على المدير العام ومدير النظام')
                                            ->default(false),
                                    ]),
                            ]),

                        Section::make('الموجز والمخرجات والتوجيهات')
                            ->description('الوصف التنفيذي المختصر والمخرجات الصادرة والوفود المشاركة')
                            ->icon('heroicon-o-document-text')
                            ->collapsible()
                            ->schema([
                                Textarea::make('short_description')
                                    ->label('الوصف المختصر للنشاط')
                                    ->placeholder('اكتب ملخصاً وافياً وموجزاً لأهم مجريات ومحاور هذا النشاط...')
                                    ->rows(3)
                                    ->required()
                                    ->columnSpanFull()
                                    ->helperText('هذا الوصف هو الذي سيظهر بشكل أساسي في جداول وبطاقات التقارير الدورية'),

                                Textarea::make('detailed_notes')
                                    ->label('التوجيهات والقرارات الصادرة والمخرجات')
                                    ->placeholder('أهم التوجيهات أو الأوامر التي أصدرها السيد المدير العام...')
                                    ->rows(4)
                                    ->columnSpanFull(),

                                Textarea::make('attendees')
                                    ->label('أبرز الحاضرين أو أعضاء الوفد المشارك')
                                    ->placeholder('مثال: معاون المدير العام، مدير قسم الشؤون الهندسية...')
                                    ->rows(2)
                                    ->columnSpanFull(),
                            ]),
                    ])
                    ->columnSpan([
                        'default' => 12,
                        'lg' => 7,
                    ]),

                    // العمود الجانبي: المرافقات والصور التوثيقية بجانب البيانات الأساسية
                    Group::make([
                        Section::make('المرافقات والصور التوثيقية')
                            ->description('إرفاق وتوثيق صور النشاط (يمكنك تحديد عدة صور معاً دفعة واحدة)')
                            ->icon('heroicon-o-photo')
                            ->collapsible()
                            ->schema([
                                FileUpload::make('uploaded_attachments')
                                    ->label('إدراج الصور التوثيقية')
                                    ->helperText('الصورة الأولى في الترتيب تُعتمد تلقائياً كـ «صورة رئيسية بارزة» في التقرير — ويمكنك سحب أي صورة وإفلاتها في البداية لتحديدها كرئيسية.')
                                    ->multiple()
                                    ->image()
                                    ->reorderable()
                                    ->appendFiles()
                                    ->openable()
                                    ->downloadable()
                                    ->previewable()
                                    ->panelLayout('grid')
                                    ->disk('private')
                                    ->directory(function (Get $get): string {
                                        $activityDate = $get('activity_date');
                                        $date = filled($activityDate) ? Carbon::parse($activityDate) : now();
                                        return 'activities_attachments/' . $date->format('Y/m/d');
                                    })
                                    ->visibility('private')
                                    ->maxSize(15360)
                                    ->columnSpanFull(),
                            ]),
                    ])
                    ->columnSpan([
                        'default' => 12,
                        'lg' => 5,
                    ]),
            ]);
    }
}
