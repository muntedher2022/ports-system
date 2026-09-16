<?php

namespace App\Filament\Resources\ContainerStatusRecordResource\RelationManagers;

use App\Models\ContainerEntity;
use App\Models\ContainerItem;
use Filament\Actions\ActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

class ContainerItemsRelationManager extends RelationManager
{
    protected static string $relationship = 'items';
    protected static ?string $title = 'تفاصيل الحاويات الفردية المسجلة';
    protected static ?string $modelLabel = 'حاوية';
    protected static ?string $pluralModelLabel = 'الحاويات';

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('container_number')
                ->label('رقم الحاوية')
                ->required()
                ->maxLength(50)
                ->extraInputAttributes(['style' => 'text-transform: uppercase; font-family: monospace; font-weight: bold;']),

            Select::make('container_entity_id')
                ->label('الجهة / العائدية')
                ->required()
                ->options(ContainerEntity::orderBy('sort_order')->pluck('name_ar', 'id'))
                ->searchable(),

            TextInput::make('size')
                ->label('الحجم (قدم)')
                ->maxLength(20)
                ->placeholder('20 أو 40'),

            TextInput::make('ship_name')
                ->label('اسم الباخرة')
                ->maxLength(100),

            TextInput::make('goods_type')
                ->label('نوع البضاعة')
                ->maxLength(255),

            TextInput::make('consignee')
                ->label('عائدية البضاعة / المستلم')
                ->maxLength(255),

            DatePicker::make('arrival_date')
                ->label('تاريخ الوصول')
                ->native(false)
                ->displayFormat('d/m/Y')
                ->format('Y-m-d')
                ->closeOnDateSelection(),

            TextInput::make('arrival_year')
                ->label('سنة الوصول')
                ->default((string) date('Y'))
                ->required(),

            TextInput::make('berth')
                ->label('الرصيف')
                ->maxLength(50),

            Select::make('status')
                ->label('حالة الحاوية')
                ->required()
                ->options([
                    'in_port'    => 'موجودة في الميناء',
                    'discharged' => 'تم اخراجها',
                ])
                ->default('in_port'),

            Textarea::make('notes')
                ->label('ملاحظات')
                ->rows(2)
                ->columnSpanFull(),
        ]);
    }

    public function table(Table $table): Table
    {
        $owner = $this->getOwnerRecord();

        return $table
            ->columns([
                TextColumn::make('container_number')
                    ->label('رقم الحاوية')
                    ->searchable(isIndividual: true)
                    ->copyable()
                    ->sortable()
                    ->weight('bold'),

                TextColumn::make('entity.name_ar')
                    ->label('الجهة / العائدية')
                    ->searchable(isIndividual: true)
                    ->sortable()
                    ->wrap(),

                TextColumn::make('size')
                    ->label('الحجم')
                    ->sortable(),

                TextColumn::make('ship_name')
                    ->label('اسم الباخرة')
                    ->searchable(isIndividual: true)
                    ->toggleable(),

                TextColumn::make('goods_type')
                    ->label('نوع البضاعة')
                    ->searchable(isIndividual: true)
                    ->wrap()
                    ->limit(35),

                TextColumn::make('arrival_year')
                    ->label('سنة/تاريخ الوصول')
                    ->sortable(),

                TextColumn::make('berth')
                    ->label('الرصيف')
                    ->toggleable(),

                TextColumn::make('status')
                    ->label('الحالة')
                    ->badge()
                    ->formatStateUsing(fn ($record) => $record->status_label)
                    ->color(fn ($record) => $record->status_color)
                    ->sortable(),

                IconColumn::make('is_manually_added')
                    ->label('يدوي')
                    ->boolean()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('container_entity_id')
                    ->label('الجهة')
                    ->options(ContainerEntity::orderBy('sort_order')->pluck('name_ar', 'id')),

                SelectFilter::make('status')
                    ->label('الحالة')
                    ->options([
                        'in_port'    => 'موجودة في الميناء',
                        'discharged' => 'تم اخراجها',
                    ]),
            ])
            ->headerActions([
                CreateAction::make()
                    ->label('إضافة حاوية جديدة لهذا السجل')
                    ->mutateFormDataUsing(function (array $data) use ($owner): array {
                        $data['port_id'] = $owner->port_id;
                        $data['fiscal_year_id'] = $owner->fiscal_year_id;
                        $data['month_id'] = $owner->month_id;
                        $data['container_type'] = $owner->container_type;
                        $data['created_by'] = Auth::id() ?? 1;
                        $data['is_manually_added'] = true;
                        return $data;
                    })
                    ->after(function () use ($owner) {
                        ContainerItem::syncRecordDetails($owner->id);
                    }),
            ])
            ->recordActions([
                ActionGroup::make([
                    ViewAction::make()->label('عرض'),
                    EditAction::make()->label('تعديل')
                        ->after(function () use ($owner) {
                            ContainerItem::syncRecordDetails($owner->id);
                        }),
                    DeleteAction::make()->label('حذف')
                        ->after(function () use ($owner) {
                            ContainerItem::syncRecordDetails($owner->id);
                        }),
                ]),
            ])
            ->bulkActions([
                DeleteBulkAction::make()->label('حذف المحدد')
                    ->after(function () use ($owner) {
                        ContainerItem::syncRecordDetails($owner->id);
                    }),
            ])
            ->defaultSort('id', 'desc');
    }
}
