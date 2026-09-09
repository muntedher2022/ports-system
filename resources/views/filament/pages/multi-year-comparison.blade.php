<x-filament-panels::page>
    @php
        $d = $this->data;
        $selectedYears = $d['selectedYears'];
    @endphp

    <style>
        .gcpi-dashboard {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            color: #1e293b;
            direction: rtl;
        }
        .gcpi-card {
            background: #ffffff;
            border-radius: 16px;
            border: 1px solid #e2e8f0;
            box-shadow: 0 4px 20px -2px rgba(0, 0, 0, 0.05);
            margin-bottom: 24px;
            overflow: hidden;
            transition: all 0.2s ease;
        }
        .gcpi-card-header {
            background: linear-gradient(135deg, #0f172a 0%, #1e3a8a 100%);
            color: #ffffff;
            padding: 20px 24px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 12px;
        }
        .gcpi-card-header h3 {
            margin: 0;
            font-size: 1.25rem;
            font-weight: 800;
            letter-spacing: -0.02em;
        }
        .gcpi-filter-bar {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 14px;
            padding: 16px 20px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 16px;
            margin-bottom: 24px;
        }
        .gcpi-filter-group {
            display: flex;
            align-items: center;
            gap: 10px;
            flex-wrap: wrap;
        }
        .gcpi-filter-label {
            font-weight: 700;
            font-size: 0.88rem;
            color: #475569;
        }
        .gcpi-select {
            background-color: #ffffff;
            border: 1.5px solid #cbd5e1;
            border-radius: 10px;
            padding: 7px 14px;
            font-size: 0.9rem;
            font-weight: 700;
            color: #0f172a;
            outline: none;
            cursor: pointer;
            transition: border-color 0.2s, box-shadow 0.2s;
        }
        .gcpi-select:focus {
            border-color: #2563eb;
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.15);
        }
        .gcpi-tab-btn {
            padding: 10px 20px;
            border-radius: 12px;
            font-weight: 700;
            font-size: 0.95rem;
            border: 1.5px solid transparent;
            cursor: pointer;
            transition: all 0.2s ease;
            text-align: center;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }
        .gcpi-tab-btn svg {
            width: 20px !important;
            height: 20px !important;
            min-width: 20px;
            max-width: 20px;
        }
        .gcpi-tab-btn.active {
            background: #1e3a8a;
            color: #ffffff;
            border-color: #1e3a8a;
            box-shadow: 0 4px 14px rgba(30, 58, 138, 0.25);
        }
        .gcpi-tab-btn:not(.active) {
            background: #f1f5f9;
            color: #475569;
            border-color: #e2e8f0;
        }
        .gcpi-tab-btn:not(.active):hover {
            background: #e2e8f0;
            color: #0f172a;
        }
        .gcpi-table-container {
            width: 100%;
            overflow-x: auto;
            border-radius: 0 0 16px 16px;
        }
        .gcpi-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.88rem;
            text-align: right;
            white-space: nowrap;
        }
        .gcpi-table thead tr {
            background: #f1f5f9;
            border-bottom: 2px solid #cbd5e1;
        }
        .gcpi-table th {
            padding: 12px 16px;
            font-weight: 800;
            color: #1e293b;
            text-align: center;
        }
        .gcpi-table th.text-right {
            text-align: right;
        }
        .gcpi-table td {
            padding: 11px 16px;
            border-bottom: 1px solid #f1f5f9;
            color: #334155;
            text-align: center;
            font-variant-numeric: tabular-nums;
        }
        .gcpi-table td.text-right {
            text-align: right;
        }
        .gcpi-table tbody tr:hover {
            background-color: #f8fafc;
        }
        .gcpi-table tbody tr.primary-row {
            background-color: #eff6ff;
            font-weight: 800;
        }
        .gcpi-table tbody tr.primary-row td {
            color: #1e3a8a;
        }
        .gcpi-table tr.footer-gross {
            background-color: #0f172a !important;
        }
        .gcpi-table tr.footer-gross td {
            color: #ffffff !important;
            font-weight: 800 !important;
            font-size: 0.95rem !important;
            border-top: 2px solid #3b82f6 !important;
            border-bottom: 1px solid #1e293b !important;
            background-color: #0f172a !important;
        }
        .gcpi-table tr.footer-gross td.num-val {
            color: #ffffff !important;
            font-family: monospace, sans-serif;
            letter-spacing: 0.02em;
        }
        .gcpi-table tr.footer-net {
            background-color: #064e3b !important;
        }
        .gcpi-table tr.footer-net td {
            color: #ffffff !important;
            font-weight: 800 !important;
            font-size: 0.95rem !important;
            border-top: 1px solid #059669 !important;
            border-bottom: none !important;
            background-color: #064e3b !important;
        }
        .gcpi-table tr.footer-net td.num-val {
            color: #a7f3d0 !important;
            font-family: monospace, sans-serif;
            letter-spacing: 0.02em;
        }
        .badge-positive {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 3px 8px;
            border-radius: 8px;
            background: #ecfdf5;
            color: #047857;
            font-weight: 700;
            font-size: 0.8rem;
        }
        .badge-negative {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 3px 8px;
            border-radius: 8px;
            background: #fef2f2;
            color: #b91c1c;
            font-weight: 700;
            font-size: 0.8rem;
        }
        .badge-neutral {
            display: inline-flex;
            padding: 3px 8px;
            border-radius: 8px;
            background: #f1f5f9;
            color: #64748b;
            font-weight: 600;
            font-size: 0.8rem;
        }
        .gcpi-btn-pdf {
            background: linear-gradient(135deg, #dc2626 0%, #991b1b 100%);
            color: #ffffff !important;
            padding: 8px 18px;
            border-radius: 10px;
            font-weight: 800;
            font-size: 0.88rem;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            text-decoration: none;
            box-shadow: 0 2px 10px rgba(220, 38, 38, 0.25);
            transition: all 0.2s ease;
            cursor: pointer;
            border: none;
        }
        .gcpi-btn-pdf:hover {
            background: linear-gradient(135deg, #b91c1c 0%, #7f1d1d 100%);
            box-shadow: 0 4px 14px rgba(220, 38, 38, 0.35);
            transform: translateY(-1px);
        }
    </style>

    <div class="gcpi-dashboard">
        <!-- ─── شريط التبويب الرئيسي (طاقة تشغيلية vs إيرادات) ─── -->
        <div class="flex items-center gap-3 mb-6 bg-white dark:bg-gray-900 p-2 rounded-2xl border border-gray-200 dark:border-gray-800 shadow-sm">
            <button 
                type="button"
                wire:click="$set('comparisonType', 'capacity')"
                class="gcpi-tab-btn flex-1 flex items-center justify-center gap-2 {{ $comparisonType === 'capacity' ? 'active' : '' }}"
            >
                <x-heroicon-o-chart-bar class="w-5 h-5" />
                <span>مقارنة الطاقة الإنتاجية التشغيلية متعددة السنوات</span>
            </button>

            <button 
                type="button"
                wire:click="$set('comparisonType', 'revenue')"
                class="gcpi-tab-btn flex-1 flex items-center justify-center gap-2 {{ $comparisonType === 'revenue' ? 'active' : '' }}"
            >
                <x-heroicon-o-banknotes class="w-5 h-5" />
                <span>مقارنة الإيرادات المالية لكافة المراكز متعددة السنوات</span>
            </button>
        </div>

        <!-- ─── شريط الفلاتر التفاعلية ─── -->
        <div class="gcpi-filter-bar">
            <!-- نطاق المقارنة (شهر محدد أم سنة كاملة) -->
            <div class="gcpi-filter-group">
                <span class="gcpi-filter-label">النطاق الزمني:</span>
                <select wire:model.live="periodScope" class="gcpi-select">
                    <option value="month">شهر محدد عبر كافة السنوات</option>
                    <option value="full_year">المجموع التراكمي السنوي الكامل</option>
                </select>
            </div>

            @if($periodScope === 'month')
                <!-- اختيار الشهر -->
                <div class="gcpi-filter-group">
                    <span class="gcpi-filter-label">الشهر:</span>
                    <select wire:model.live="selectedMonthNumber" class="gcpi-select">
                        @foreach($d['months'] as $m)
                            <option value="{{ $m->month_number }}">{{ $m->name_ar }} ({{ $m->month_number }})</option>
                        @endforeach
                    </select>
                </div>
            @endif

            @if($comparisonType === 'capacity')
                <!-- اختيار الميناء -->
                <div class="gcpi-filter-group">
                    <span class="gcpi-filter-label">الميناء:</span>
                    <select wire:model.live="selectedPortId" class="gcpi-select">
                        <option value="">🏢 إجمالي كافة الموانئ الأربعة معاً</option>
                        @foreach($d['ports'] as $p)
                            <option value="{{ $p->id }}">{{ $p->name_ar }}</option>
                        @endforeach
                    </select>
                </div>
            @endif

            <!-- اختيار السنوات للمقارنة (تحديد متعدد) -->
            <div class="gcpi-filter-group">
                <span class="gcpi-filter-label">السنوات المقارنة:</span>
                <div class="flex items-center gap-2 bg-white px-3 py-1.5 rounded-lg border border-gray-300">
                    @foreach($d['allYears'] as $y)
                        <label class="inline-flex items-center gap-1 text-xs font-bold text-gray-700 cursor-pointer">
                            <input 
                                type="checkbox" 
                                value="{{ $y->id }}" 
                                wire:model.live="selectedYearIds"
                                class="rounded border-gray-300 text-blue-600 focus:ring-blue-500 w-4 h-4"
                            />
                            <span>{{ $y->year }}</span>
                        </label>
                    @endforeach
                </div>
            </div>

            <!-- زر تصدير PDF -->
            <div class="gcpi-filter-group">
                @php
                    $pdfParams = [
                        'comparison_type' => $comparisonType,
                        'period_scope'    => $periodScope,
                        'month_number'    => $selectedMonthNumber,
                        'port_id'         => $selectedPortId,
                        'year_ids'        => implode(',', $selectedYearIds),
                    ];
                @endphp
                <a href="{{ route('admin.reports.multi-year-comparison.pdf', $pdfParams) }}" target="_blank" class="gcpi-btn-pdf">
                    📄 تصدير تقرير PDF
                </a>
            </div>
        </div>

        @if(count($selectedYears) < 2)
            <div class="p-6 bg-amber-50 border border-amber-200 rounded-xl text-amber-800 font-bold text-center">
                يرجى اختيار سنتين على الأقل من فلاتر السنوات أعلاه لإجراء المقارنة.
            </div>
        @else
            @if($comparisonType === 'capacity')
                <!-- ═══════════════════════════════════════════════════════════════════════════ -->
                <!-- ─── جدول مقارنة الطاقة الإنتاجية التشغيلية متعددة السنوات ─── -->
                <!-- ═══════════════════════════════════════════════════════════════════════════ -->
                <div class="gcpi-card">
                    <div class="gcpi-card-header">
                        <div>
                            <h3>
                                📊 مقارنة الطاقة الإنتاجية التشغيلية 
                                @if($periodScope === 'month')
                                    — لشهر ({{ $d['monthName'] }})
                                @else
                                    — المجموع التراكمي لكامل السنة
                                @endif
                            </h3>
                            <span class="text-xs text-blue-200 mt-1 block">
                                @if($selectedPortId)
                                    البيانات خاصة بـ: {{ $d['ports']->firstWhere('id', $selectedPortId)?->name_ar }}
                                @else
                                    البيانات تمثل إجمالي الشركة العامة لموانئ العراق (الموانئ الأربعة مجتمعة)
                                @endif
                                — للسنوات: {{ $selectedYears->pluck('year')->implode(' vs ') }}
                            </span>
                        </div>
                    </div>

                    <div class="gcpi-table-container">
                        <table class="gcpi-table">
                            <thead>
                                <tr>
                                    <th class="text-right" style="width: 32%;">المؤشر / البيان التشغيلي</th>
                                    @foreach($selectedYears as $y)
                                        <th style="width: {{ number_format(48 / count($selectedYears), 1) }}%;">
                                            سنة {{ $y->year }}
                                        </th>
                                    @endforeach
                                    <th style="width: 10%;">الفارق الإجمالي</th>
                                    <th style="width: 10%;">نسبة التغير</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($d['capacityRows'] as $row)
                                    <tr class="{{ $row['is_primary'] ? 'primary-row' : '' }}">
                                        <td class="text-right font-bold">{{ $row['label'] }}</td>
                                        @foreach($selectedYears as $y)
                                            <td class="font-bold">
                                                {{ number_format($row['values'][$y->id] ?? 0, 0) }}
                                            </td>
                                        @endforeach

                                        <!-- الفارق -->
                                        <td>
                                            @if($row['diff'] > 0)
                                                <span class="text-emerald-600 font-bold" dir="ltr">+{{ number_format($row['diff'], 0) }}</span>
                                            @elseif($row['diff'] < 0)
                                                <span class="text-rose-600 font-bold" dir="ltr">{{ number_format($row['diff'], 0) }}</span>
                                            @else
                                                <span class="text-gray-400 font-bold">-</span>
                                            @endif
                                        </td>

                                        <!-- نسبة التغير -->
                                        <td>
                                            @if($row['pct_change'] > 0)
                                                <span class="badge-positive" dir="ltr">
                                                    ▲ +{{ number_format($row['pct_change'], 1) }}%
                                                </span>
                                            @elseif($row['pct_change'] < 0)
                                                <span class="badge-negative" dir="ltr">
                                                    ▼ {{ number_format($row['pct_change'], 1) }}%
                                                </span>
                                            @else
                                                <span class="badge-neutral">0%</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

            @else
                <!-- ═══════════════════════════════════════════════════════════════════════════ -->
                <!-- ─── جدول مقارنة الإيرادات المالية لكافة المراكز متعددة السنوات ─── -->
                <!-- ═══════════════════════════════════════════════════════════════════════════ -->
                <div class="gcpi-card">
                    <div class="gcpi-card-header" style="background: linear-gradient(135deg, #064e3b 0%, #047857 100%);">
                        <div>
                            <h3>
                                💰 مقارنة الإيرادات المالية لكافة المراكز والتشكيلات
                                @if($periodScope === 'month')
                                    — لشهر ({{ $d['monthName'] }})
                                @else
                                    — المجموع السنوي التراكمي
                                @endif
                            </h3>
                            <span class="text-xs text-emerald-200 mt-1 block">
                                مقارنة إيرادات التشكيلات الثمانية للسنوات: {{ $selectedYears->pluck('year')->implode(' vs ') }} (بالدينار العراقي)
                            </span>
                        </div>
                    </div>

                    <div class="gcpi-table-container">
                        <table class="gcpi-table">
                            <thead>
                                <tr>
                                    <th class="text-right" style="width: 28%;">مركز الإيراد / التشكيل</th>
                                    @foreach($selectedYears as $y)
                                        <th style="width: {{ number_format(50 / count($selectedYears), 1) }}%;">
                                            إيراد {{ $y->year }} (د.ع)
                                        </th>
                                    @endforeach
                                    <th style="width: 11%;">الفارق (د.ع)</th>
                                    <th style="width: 11%;">نسبة النمو %</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($d['revenueRows'] as $row)
                                    <tr>
                                        <td class="text-right font-bold text-gray-900">{{ $row['center_name'] }}</td>
                                        @foreach($selectedYears as $y)
                                            <td class="font-bold font-mono">
                                                {{ number_format($row['values'][$y->id] ?? 0, 0) }}
                                            </td>
                                        @endforeach

                                        <!-- الفارق -->
                                        <td>
                                            @if($row['diff'] > 0)
                                                <span class="text-emerald-600 font-bold font-mono" dir="ltr">+{{ number_format($row['diff'], 0) }}</span>
                                            @elseif($row['diff'] < 0)
                                                <span class="text-rose-600 font-bold font-mono" dir="ltr">{{ number_format($row['diff'], 0) }}</span>
                                            @else
                                                <span class="text-gray-400 font-bold">-</span>
                                            @endif
                                        </td>

                                        <!-- نسبة التغير -->
                                        <td>
                                            @if($row['pct_change'] > 0)
                                                <span class="badge-positive" dir="ltr">
                                                    ▲ +{{ number_format($row['pct_change'], 1) }}%
                                                </span>
                                            @elseif($row['pct_change'] < 0)
                                                <span class="badge-negative" dir="ltr">
                                                    ▼ {{ number_format($row['pct_change'], 1) }}%
                                                </span>
                                            @else
                                                <span class="badge-neutral">0%</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach

                                <!-- صف مجموع الإيراد الكلي -->
                                <tr class="footer-gross">
                                    <td class="text-right">مجموع الإيراد الكلي لعموم الشركة</td>
                                    @foreach($selectedYears as $y)
                                        <td class="num-val">
                                            {{ number_format($d['grandGrossTotals'][$y->id] ?? 0, 0) }}
                                        </td>
                                    @endforeach
                                    <td class="num-val">
                                        @if($d['grossDiff'] > 0)
                                            <span class="text-emerald-400 font-bold" dir="ltr">+{{ number_format($d['grossDiff'], 0) }}</span>
                                        @elseif($d['grossDiff'] < 0)
                                            <span class="text-rose-400 font-bold" dir="ltr">{{ number_format($d['grossDiff'], 0) }}</span>
                                        @else
                                            <span>-</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($d['grossPct'] > 0)
                                            <span class="badge-positive" dir="ltr">▲ +{{ number_format($d['grossPct'], 1) }}%</span>
                                        @elseif($d['grossPct'] < 0)
                                            <span class="badge-negative" dir="ltr">▼ {{ number_format($d['grossPct'], 1) }}%</span>
                                        @else
                                            <span class="badge-neutral">0%</span>
                                        @endif
                                    </td>
                                </tr>

                                <!-- صف الإيراد الصافي -->
                                <tr class="footer-net">
                                    <td class="text-right">الإيراد الصافي لعموم الشركة</td>
                                    @foreach($selectedYears as $y)
                                        <td class="num-val">
                                            {{ number_format($d['grandNetTotals'][$y->id] ?? 0, 0) }}
                                        </td>
                                    @endforeach
                                    <td class="num-val">
                                        @if($d['netDiff'] > 0)
                                            <span class="text-emerald-200 font-bold" dir="ltr">+{{ number_format($d['netDiff'], 0) }}</span>
                                        @elseif($d['netDiff'] < 0)
                                            <span class="text-rose-200 font-bold" dir="ltr">{{ number_format($d['netDiff'], 0) }}</span>
                                        @else
                                            <span>-</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($d['netPct'] > 0)
                                            <span class="badge-positive" dir="ltr">▲ +{{ number_format($d['netPct'], 1) }}%</span>
                                        @elseif($d['netPct'] < 0)
                                            <span class="badge-negative" dir="ltr">▼ {{ number_format($d['netPct'], 1) }}%</span>
                                        @else
                                            <span class="badge-neutral">0%</span>
                                        @endif
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif
        @endif
    </div>
</x-filament-panels::page>
