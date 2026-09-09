<x-filament-widgets::widget>
    @php
        $stats = $this->getCachedStats();
    @endphp

    <style>
        .gcpi-stats-row {
            display: grid;
            grid-template-columns: repeat(1, minmax(0, 1fr));
            gap: 12px;
            direction: rtl;
            width: 100%;
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
            border-radius: 14px;
            border: 1px solid #e2e8f0;
            padding: 14px 16px;
            box-shadow: 0 4px 16px -2px rgba(0, 0, 0, 0.04);
            position: relative;
            overflow: hidden;
            transition: all 0.2s ease;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            min-width: 0;
        }
        .gcpi-modern-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px -2px rgba(0, 0, 0, 0.08);
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
            margin-bottom: 6px;
        }
        .card-label-text {
            font-size: 0.8rem;
            font-weight: 700;
            color: #64748b;
            white-space: nowrap;
        }
        .card-value-single-line {
            font-size: 1.05rem;
            font-weight: 800;
            color: #0f172a;
            white-space: nowrap;
            letter-spacing: -0.2px;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            font-variant-numeric: tabular-nums;
            margin-bottom: 4px;
            direction: rtl;
        }
        .card-desc-flex {
            display: flex;
            align-items: center;
            gap: 5px;
            font-size: 0.74rem;
            font-weight: 700;
            white-space: nowrap;
        }
        .text-positive { color: #047857; }
        .text-negative { color: #dc2626; }
    </style>

    <div class="gcpi-stats-row">
        @foreach($stats as $s)
            <div class="gcpi-modern-card {{ $s['color_class'] }}">
                <div class="card-header-flex">
                    <span class="card-label-text">{{ $s['label'] }}</span>
                    <span class="p-1.5 rounded-lg {{ $s['icon_bg'] }}">
                        {!! $s['icon_svg'] !!}
                    </span>
                </div>

                <!-- القيمة في سطر واحد بدون انقسام -->
                <div class="card-value-single-line" title="{{ $s['value'] }}">
                    {{ $s['value'] }}
                </div>

                <div class="card-desc-flex {{ $s['diff'] >= 0 ? 'text-positive' : 'text-negative' }}">
                    <span>{{ $s['diff'] >= 0 ? '▲' : '▼' }}</span>
                    <span dir="ltr">{{ $s['diff'] >= 0 ? '+' : '' }}{{ $s['diff'] }}%</span>
                    <span class="text-gray-500 font-normal">عن عام {{ $s['prev_year'] }}</span>
                </div>
            </div>
        @endforeach
    </div>
</x-filament-widgets::widget>
