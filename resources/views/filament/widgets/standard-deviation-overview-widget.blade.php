<x-filament-widgets::widget>
    @php
        $stats = $this->getCachedStats();
    @endphp

    <style>
        .gcpi-sd-row {
            display: grid;
            grid-template-columns: repeat(1, minmax(0, 1fr));
            gap: 12px;
            direction: rtl;
            width: 100%;
        }
        @media (min-width: 640px) {
            .gcpi-sd-row {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }
        @media (min-width: 1024px) {
            .gcpi-sd-row {
                grid-template-columns: repeat(4, minmax(0, 1fr));
            }
        }
        .gcpi-sd-card {
            border-radius: 14px;
            padding: 14px 16px;
            position: relative;
            overflow: hidden;
            transition: all 0.2s ease;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            box-shadow: 0 4px 16px -2px rgba(0, 0, 0, 0.05);
            border: 1.5px solid transparent;
            min-width: 0;
        }
        .gcpi-sd-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px -2px rgba(0, 0, 0, 0.09);
        }
        /* كارد 1: الطاقة الإنتاجية - أزرق نيلي مميز */
        .sd-card-tonnage {
            background: linear-gradient(145deg, #eff6ff 0%, #dbeafe 100%);
            border-color: #93c5fd;
        }
        .sd-card-tonnage .sd-title { color: #1e40af; }
        .sd-card-tonnage .sd-val { color: #1e3a8a; }

        /* كارد 2: الإيراد المالي - زمردي فاخر */
        .sd-card-revenue {
            background: linear-gradient(145deg, #ecfdf5 0%, #d1fae5 100%);
            border-color: #6ee7b7;
        }
        .sd-card-revenue .sd-title { color: #065f46; }
        .sd-card-revenue .sd-val { color: #047857; }

        /* كارد 3: حركة البواخر - بنفسجي ملكي */
        .sd-card-ships {
            background: linear-gradient(145deg, #f5f3ff 0%, #ede9fe 100%);
            border-color: #c4b5fd;
        }
        .sd-card-ships .sd-title { color: #5b21b6; }
        .sd-card-ships .sd-val { color: #4c1d95; }

        /* كارد 4: الحاويات - عنبري دافئ */
        .sd-card-teu {
            background: linear-gradient(145deg, #fffbeb 0%, #fef3c7 100%);
            border-color: #fde68a;
        }
        .sd-card-teu .sd-title { color: #92400e; }
        .sd-card-teu .sd-val { color: #78350f; }

        .sd-header-flex {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 6px;
        }
        .sd-title {
            font-size: 0.8rem;
            font-weight: 800;
            white-space: nowrap;
        }
        .sd-val {
            font-size: 1.05rem;
            font-weight: 800;
            white-space: nowrap;
            letter-spacing: -0.2px;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            font-variant-numeric: tabular-nums;
            margin-bottom: 6px;
            direction: rtl;
        }
        .sd-badge {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 3px 8px;
            border-radius: 6px;
            font-size: 0.72rem;
            font-weight: 700;
            background: rgba(255, 255, 255, 0.75);
            backdrop-filter: blur(4px);
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
            width: fit-content;
            white-space: nowrap;
        }
    </style>

    <div class="gcpi-sd-row">
        @foreach($stats as $s)
            <div class="gcpi-sd-card {{ $s['theme_class'] }}">
                <div class="sd-header-flex">
                    <span class="sd-title">{{ $s['label'] }}</span>
                    <span class="p-1 rounded-md bg-white bg-opacity-70 shadow-sm">
                        {!! $s['icon_svg'] !!}
                    </span>
                </div>

                <!-- القيمة في سطر واحد بدون انقسام -->
                <div class="sd-val" title="{{ $s['value'] }}">
                    {{ $s['value'] }}
                </div>

                <div class="sd-badge">
                    <span>{{ $s['cv_icon'] }}</span>
                    <span>تشتت الأداء (CV: {{ $s['cv'] }}%)</span>
                    <span class="font-normal text-gray-500">— {{ $s['stability'] }}</span>
                </div>
            </div>
        @endforeach
    </div>
</x-filament-widgets::widget>
