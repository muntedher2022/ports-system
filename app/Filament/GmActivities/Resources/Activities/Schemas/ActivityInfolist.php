<?php

namespace App\Filament\GmActivities\Resources\Activities\Schemas;

use App\Models\Activity;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\ViewEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ActivityInfolist
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
                            ->icon('heroicon-o-information-circle')
                            ->collapsible()
                            ->schema([
                                Grid::make(3)
                                    ->schema([
                                        TextEntry::make('title')
                                            ->label('اسم النشاط')
                                            ->weight('bold')
                                            ->size('lg')
                                            ->columnSpan(2),

                                        IconEntry::make('is_confidential')
                                            ->label('سرية النشاط')
                                            ->boolean()
                                            ->trueIcon('heroicon-s-lock-closed')
                                            ->falseIcon('heroicon-o-lock-open')
                                            ->trueColor('danger')
                                            ->falseColor('gray')
                                            ->columnSpan(1),
                                    ]),

                                Grid::make(3)
                                    ->schema([
                                        TextEntry::make('category')
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

                                        TextEntry::make('activity_date')
                                            ->label('تاريخ النشاط')
                                            ->date('Y/m/d')
                                            ->badge()
                                            ->color('gray'),

                                        TextEntry::make('activity_time')
                                            ->label('وقت النشاط')
                                            ->time('h:i A')
                                            ->placeholder('غير محدد'),
                                    ]),

                                Grid::make(3)
                                    ->schema([
                                        TextEntry::make('location')
                                            ->label('الموقع / المقر')
                                            ->placeholder('ديوان الشركة'),

                                        TextEntry::make('importance')
                                            ->label('درجة الأهمية')
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

                                        TextEntry::make('status')
                                            ->label('حالة الإنجاز')
                                            ->badge()
                                            ->formatStateUsing(fn (string $state): string => match ($state) {
                                                'completed' => 'منجز بالكامل',
                                                'in_progress' => 'قيد المتابعة والتنفيذ',
                                                'scheduled' => 'مجدول وقادم',
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
                                    ]),

                                Grid::make(2)
                                    ->schema([
                                        IconEntry::make('include_in_periodic_report')
                                            ->label('مشمول بالتقرير الدوري')
                                            ->boolean(),

                                        IconEntry::make('is_confidential')
                                            ->label('نشاط سري / خاص')
                                            ->boolean(),
                                    ]),
                            ]),

                        Section::make('الموجز والمخرجات والتوجيهات')
                            ->icon('heroicon-o-document-text')
                            ->collapsible()
                            ->schema([
                                TextEntry::make('short_description')
                                    ->label('الوصف الموجز للنشاط')
                                    ->columnSpanFull(),

                                TextEntry::make('detailed_notes')
                                    ->label('القرارات والتوجيهات الصادرة')
                                    ->placeholder('لا توجد توجيهات إضافية مسجلة')
                                    ->columnSpanFull(),

                                TextEntry::make('attendees')
                                    ->label('أبرز الحاضرين أو الوفد')
                                    ->placeholder('لم يتم تدوين أسماء الحاضرين')
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
                            ->icon('heroicon-o-photo')
                            ->collapsible()
                            ->schema([
                                RepeatableEntry::make('attachments')
                                    ->label('معرض الصور والمرفقات')
                                    ->schema([
                                        ViewEntry::make('card')
                                            ->view('filament.infolists.attachment-card'),
                                    ]),
                            ]),
                    ])
                    ->columnSpan([
                        'default' => 12,
                        'lg' => 5,
                    ]),
            ]);
    }
}
