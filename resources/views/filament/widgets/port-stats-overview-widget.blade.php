<x-filament-widgets::widget>
    @php
        $stats = $this->getCachedStats();
    @endphp

    <style>
        .gcpi-stats-row {
            display: grid;
            grid-template-columns: repeat(1, minmax(0, 1fr));
            gap: 14px;
            direction: rtl;
            width: 100%;
            margin-bottom: 8px;
        }
        @media (min-width: 640px) {
            .gcpi-stats-row {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }
        @media (min-width: 1024px) {
            .gcpi-stats-row {
                grid-template-columns: repeat(4, minmax(0, 1fr));
            }
        }
        .gcpi-modern-card {
            background: #ffffff;
            border-radius: 16px;
            border: 1px solid #e2e8f0;
            padding: 16px 18px;
            box-shadow: 0 4px 20px -2px rgba(0, 0, 0, 0.04);
            position: relative;
            overflow: hidden;
            transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            min-width: 0;
        }
        .gcpi-modern-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 25px -3px rgba(0, 0, 0, 0.08);
            border-color: #cbd5e1;
        }
        .gcpi-modern-card.card-blue {
            border-top: 4px solid #2563eb;
            background: linear-gradient(180deg, #f8fafc 0%, #ffffff 100%);
        }
        .gcpi-modern-card.card-cyan {
            border-top: 4px solid #0284c7;
            background: linear-gradient(180deg, #f0f9ff 0%, #ffffff 100%);
        }
        .gcpi-modern-card.card-emerald {
            border-top: 4px solid #059669;
            background: linear-gradient(180deg, #ecfdf5 0%, #ffffff 100%);
        }
        .gcpi-modern-card.card-amber {
            border-top: 4px solid #d97706;
            background: linear-gradient(180deg, #fffbeb 0%, #ffffff 100%);
        }
        .card-header-flex {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 8px;
        }
        .card-label-text {
            font-size: 0.82rem;
            font-weight: 700;
            color: #475569;
            white-space: nowrap;
        }
        .card-value-single-line {
            font-size: 1.25rem;
            font-weight: 900;
            color: #0f172a;
            white-space: nowrap;
            letter-spacing: -0.3px;
            font-family: 'IBM Plex Sans Arabic', sans-serif;
            font-variant-numeric: tabular-nums;
            margin-bottom: 6px;
            direction: rtl;
        }
        .card-desc-flex {
            display: flex;
            align-items: center;
            gap: 6px;
            font-size: 0.75rem;
            font-weight: 700;
            white-space: nowrap;
        }
        .pill-diff-positive {
            display: inline-flex;
            align-items: center;
            gap: 2px;
            padding: 2px 6px;
            border-radius: 6px;
            background-color: #dcfce7;
            color: #15803d;
            font-weight: 800;
            font-size: 0.72rem;
        }
        .pill-diff-negative {
            display: inline-flex;
            align-items: center;
            gap: 2px;
            padding: 2px 6px;
            border-radius: 6px;
            background-color: #fee2e2;
            color: #b91c1c;
            font-weight: 800;
            font-size: 0.72rem;
        }
    </style>

    <div class="gcpi-stats-row">
        @foreach($stats as $s)
            <div class="gcpi-modern-card {{ $s['color_class'] }}">
                <div class="card-header-flex">
                    <span class="card-label-text">{{ $s['label'] }}</span>
                    <span class="p-2 rounded-xl {{ $s['icon_bg'] }} shadow-sm">
                        {!! $s['icon_svg'] !!}
                    </span>
                </div>

                <div class="card-value-single-line" title="{{ $s['value'] }}">
                    {{ $s['value'] }}
                </div>

                <div class="card-desc-flex">
                    <span class="{{ $s['diff'] >= 0 ? 'pill-diff-positive' : 'pill-diff-negative' }}" dir="ltr">
                        <span>{{ $s['diff'] >= 0 ? '▲ +' : '▼ ' }}{{ $s['diff'] }}%</span>
                    </span>
                    <span class="text-slate-500 font-normal text-[0.72rem]">مقارنة مع سنة {{ $s['prev_year'] }}</span>
                </div>
            </div>
        @endforeach
    </div>
</x-filament-widgets::widget>
