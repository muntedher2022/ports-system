@php
    use Filament\Support\View\ComponentAttributeBag as FilamentComponentAttributeBag;
@endphp

@props([
    'debounce' => '500ms',
    'onBlur' => false,
    'placeholder' => __('filament-tables::table.fields.search.placeholder'),
    'wireModel' => 'tableSearch',
])

@php
    $wireModelAttribute = $onBlur ? 'wire:model.live.blur' : "wire:model.live.debounce.{$debounce}";
@endphp

@if ($wireModel === 'tableColumnSearches.status')
    <div
        x-id="['input']"
        {{ $attributes->class(['fi-ta-search-field']) }}
    >
        <label x-bind:for="$id('input')" class="fi-sr-only">
            {{ __('filament-tables::table.fields.search.label') }}
        </label>

        <x-filament::input.wrapper
            inline-prefix
            :prefix-icon="\Filament\Support\Icons\Heroicon::Funnel"
            :prefix-icon-alias="\Filament\Tables\View\TablesIconAlias::SEARCH_FIELD"
            :wire:target="$wireModel"
        >
            <x-filament::input.select
                :attributes="
                    (new FilamentComponentAttributeBag)->merge([
                        'inlinePrefix' => true,
                        'wire:key' => $this->getId() . '.table.' . $wireModel . '.field.select',
                        'wire:model.live' => $wireModel,
                        'x-bind:id' => '$id(\'input\')',
                    ], escape: false)
                "
            >
                <option value="">(كل الحالات)</option>
                <option value="in_port">موجودة في الميناء</option>
                <option value="discharged">تم اخراجها</option>
            </x-filament::input.select>
        </x-filament::input.wrapper>
    </div>
@elseif (str_contains($wireModel, 'container_type'))
    <div
        x-id="['input']"
        {{ $attributes->class(['fi-ta-search-field']) }}
    >
        <label x-bind:for="$id('input')" class="fi-sr-only">
            {{ __('filament-tables::table.fields.search.label') }}
        </label>

        <x-filament::input.wrapper
            inline-prefix
            :prefix-icon="\Filament\Support\Icons\Heroicon::Funnel"
            :prefix-icon-alias="\Filament\Tables\View\TablesIconAlias::SEARCH_FIELD"
            :wire:target="$wireModel"
        >
            <x-filament::input.select
                :attributes="
                    (new FilamentComponentAttributeBag)->merge([
                        'inlinePrefix' => true,
                        'wire:key' => $this->getId() . '.table.' . $wireModel . '.field.select',
                        'wire:model.live' => $wireModel,
                        'x-bind:id' => '$id(\'input\')',
                    ], escape: false)
                "
            >
                <option value="">(كل الأنواع)</option>
                <option value="abandoned">متخلفة</option>
                <option value="dangerous">خطرة</option>
            </x-filament::input.select>
        </x-filament::input.wrapper>
    </div>
@elseif (str_contains(strtolower($wireModel), 'month'))
    @php
        $monthsList = \App\Models\Month::orderBy('month_number')->get();
    @endphp
    <div
        x-id="['input']"
        {{ $attributes->class(['fi-ta-search-field']) }}
    >
        <label x-bind:for="$id('input')" class="fi-sr-only">
            {{ __('filament-tables::table.fields.search.label') }}
        </label>

        <x-filament::input.wrapper
            inline-prefix
            :prefix-icon="\Filament\Support\Icons\Heroicon::Funnel"
            :prefix-icon-alias="\Filament\Tables\View\TablesIconAlias::SEARCH_FIELD"
            :wire:target="$wireModel"
        >
            <x-filament::input.select
                :attributes="
                    (new FilamentComponentAttributeBag)->merge([
                        'inlinePrefix' => true,
                        'wire:key' => $this->getId() . '.table.' . $wireModel . '.field.select',
                        'wire:model.live' => $wireModel,
                        'x-bind:id' => '$id(\'input\')',
                    ], escape: false)
                "
            >
                <option value="">(كل الأشهر)</option>
                @foreach ($monthsList as $m)
                    <option value="{{ $m->month_number }}">{{ $m->month_number }} - {{ $m->name_ar }}</option>
                @endforeach
            </x-filament::input.select>
        </x-filament::input.wrapper>
    </div>
@elseif (str_contains(strtolower($wireModel), 'fiscalyear') || str_contains(strtolower($wireModel), 'fiscal_year'))
    @php
        $yearsList = \App\Models\FiscalYear::orderBy('year', 'desc')->get();
    @endphp
    <div
        x-id="['input']"
        {{ $attributes->class(['fi-ta-search-field']) }}
    >
        <label x-bind:for="$id('input')" class="fi-sr-only">
            {{ __('filament-tables::table.fields.search.label') }}
        </label>

        <x-filament::input.wrapper
            inline-prefix
            :prefix-icon="\Filament\Support\Icons\Heroicon::Funnel"
            :prefix-icon-alias="\Filament\Tables\View\TablesIconAlias::SEARCH_FIELD"
            :wire:target="$wireModel"
        >
            <x-filament::input.select
                :attributes="
                    (new FilamentComponentAttributeBag)->merge([
                        'inlinePrefix' => true,
                        'wire:key' => $this->getId() . '.table.' . $wireModel . '.field.select',
                        'wire:model.live' => $wireModel,
                        'x-bind:id' => '$id(\'input\')',
                    ], escape: false)
                "
            >
                <option value="">(كل السنوات)</option>
                @foreach ($yearsList as $fy)
                    <option value="{{ $fy->year }}">{{ $fy->year }} {{ $fy->is_current ? '(الحالية)' : '' }}</option>
                @endforeach
            </x-filament::input.select>
        </x-filament::input.wrapper>
    </div>
@elseif (str_contains(strtolower($wireModel), 'port'))
    @php
        $portsList = \App\Models\Port::where('is_active', true)->where(fn($q) => $q->where('has_container_status', true)->orWhereHas('containerStatusRecords'))->orderBy('sort_order')->get();
        if ($portsList->isEmpty()) {
            $portsList = \App\Models\Port::where('is_active', true)->orderBy('sort_order')->get();
        }
    @endphp
    <div
        x-id="['input']"
        {{ $attributes->class(['fi-ta-search-field']) }}
    >
        <label x-bind:for="$id('input')" class="fi-sr-only">
            {{ __('filament-tables::table.fields.search.label') }}
        </label>

        <x-filament::input.wrapper
            inline-prefix
            :prefix-icon="\Filament\Support\Icons\Heroicon::Funnel"
            :prefix-icon-alias="\Filament\Tables\View\TablesIconAlias::SEARCH_FIELD"
            :wire:target="$wireModel"
        >
            <x-filament::input.select
                :attributes="
                    (new FilamentComponentAttributeBag)->merge([
                        'inlinePrefix' => true,
                        'wire:key' => $this->getId() . '.table.' . $wireModel . '.field.select',
                        'wire:model.live' => $wireModel,
                        'x-bind:id' => '$id(\'input\')',
                    ], escape: false)
                "
            >
                <option value="">(كل الموانئ)</option>
                @foreach ($portsList as $p)
                    <option value="{{ $p->name_ar }}">{{ $p->name_ar }}</option>
                @endforeach
            </x-filament::input.select>
        </x-filament::input.wrapper>
    </div>
@else
    <div
        x-id="['input']"
        {{ $attributes->class(['fi-ta-search-field']) }}
    >
        <label x-bind:for="$id('input')" class="fi-sr-only">
            {{ __('filament-tables::table.fields.search.label') }}
        </label>

        <x-filament::input.wrapper
            inline-prefix
            :prefix-icon="\Filament\Support\Icons\Heroicon::MagnifyingGlass"
            :prefix-icon-alias="\Filament\Tables\View\TablesIconAlias::SEARCH_FIELD"
            :wire:target="$wireModel"
        >
            <x-filament::input
                :attributes="
                    (new FilamentComponentAttributeBag)->merge([
                        'autocomplete' => 'off',
                        'inlinePrefix' => true,
                        'maxlength' => 1000,
                        'placeholder' => $placeholder,
                        'type' => 'search',
                        'wire:key' => $this->getId() . '.table.' . $wireModel . '.field.input',
                        $wireModelAttribute => $wireModel,
                        'x-bind:id' => '$id(\'input\')',
                        'x-on:keyup' => 'if ($event.key === \'Enter\') { $wire.$refresh() }',
                    ], escape: false)
                "
            />
        </x-filament::input.wrapper>
    </div>
@endif
