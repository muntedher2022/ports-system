<x-filament-panels::page>
    @php
        $d = $this->data;
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
            gap: 12px;
        }
        .gcpi-filter-label {
            font-weight: 700;
            font-size: 0.9rem;
            color: #475569;
        }
        .gcpi-select {
            background-color: #ffffff;
            border: 1.5px solid #cbd5e1;
            border-radius: 10px;
            padding: 8px 16px;
            font-size: 0.95rem;
            font-weight: 700;
            color: #0f172a;
            outline: none;
            cursor: pointer;
            min-width: 140px;
            transition: border-color 0.2s, box-shadow 0.2s;
        }
        .gcpi-select:focus {
            border-color: #2563eb;
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.15);
        }
        .gcpi-stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 16px;
            margin-bottom: 24px;
        }
        .gcpi-stat-box {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 14px;
            padding: 18px 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.03);
            border-right: 5px solid #2563eb;
        }
        .gcpi-stat-box.success { border-right-color: #059669; }
        .gcpi-stat-box.warning { border-right-color: #d97706; }
        .gcpi-stat-box.info { border-right-color: #0284c7; }
        .gcpi-stat-title {
            font-size: 0.8rem;
            font-weight: 700;
            color: #64748b;
            margin-bottom: 6px;
        }
        .gcpi-stat-value {
            font-size: 1.25rem;
            font-weight: 800;
            color: #0f172a;
            font-variant-numeric: tabular-nums;
            direction: ltr;
            text-align: right;
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
            color: #334155;
            border-bottom: 2px solid #cbd5e1;
        }
        .gcpi-table th {
            padding: 14px 16px;
            font-weight: 800;
            font-size: 0.85rem;
            letter-spacing: 0.01em;
            text-align: center;
        }
        .gcpi-table th:first-child, .gcpi-table td:first-child {
            text-align: right;
            font-weight: 800;
        }
        .gcpi-table td {
            padding: 12px 16px;
            border-bottom: 1px solid #e2e8f0;
            color: #1e293b;
            font-variant-numeric: tabular-nums;
            text-align: center;
        }
        .gcpi-table tbody tr:nth-child(even) {
            background-color: #f8fafc;
        }
        .gcpi-table tbody tr:hover {
            background-color: #f0fdf4;
        }
        .gcpi-table tfoot tr {
            background: #0f172a;
            color: #ffffff;
            font-weight: 800;
            font-size: 0.95rem;
        }
        .gcpi-table tfoot td {
            padding: 16px;
            border: none;
            color: #ffffff;
            font-variant-numeric: tabular-nums;
        }
        .gcpi-highlight-col {
            background-color: rgba(37, 99, 235, 0.04);
            font-weight: 700;
            color: #1d4ed8 !important;
        }
        .gcpi-net-col {
            background-color: rgba(5, 150, 105, 0.04);
            font-weight: 700;
            color: #047857 !important;
        }
        .gcpi-badge {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 9999px;
            font-size: 0.75rem;
            font-weight: 700;
        }
        .gcpi-btn-pdf {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: linear-gradient(135deg, #dc2626 0%, #991b1b 100%);
            color: #ffffff !important;
            font-weight: 700;
            font-size: 0.88rem;
            padding: 9px 18px;
            border-radius: 10px;
            text-decoration: none;
            box-shadow: 0 2px 8px rgba(220, 38, 38, 0.25);
            transition: all 0.2s ease;
        }
        .gcpi-btn-pdf:hover {
            background: linear-gradient(135deg, #b91c1c 0%, #7f1d1d 100%);
            box-shadow: 0 4px 14px rgba(220, 38, 38, 0.35);
            transform: translateY(-1px);
        }
        .gcpi-trend-cell {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            width: 100%;
        }
        .gcpi-trend-arrow {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 22px;
            height: 22px;
            min-width: 22px;
            border-radius: 50%;
            flex-shrink: 0;
            cursor: pointer;
            box-shadow: 0 2px 5px rgba(0,0,0,0.15);
            transition: transform 0.15s ease, box-shadow 0.15s ease;
        }
        .gcpi-trend-arrow:hover {
            transform: scale(1.25);
            box-shadow: 0 4px 8px rgba(0,0,0,0.25);
        }
        .gcpi-trend-arrow.up {
            background: linear-gradient(135deg, #16a34a 0%, #15803d 100%) !important;
            color: #ffffff !important;
            border: 1.5px solid #166534;
        }
        .gcpi-trend-arrow.down {
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%) !important;
            color: #ffffff !important;
            border: 1.5px solid #991b1b;
        }
    </style>

    <div class="gcpi-dashboard">
        <!-- شريط الفلاتر والمعلومات والطباعة -->
        <div class="gcpi-filter-bar">
            <div class="gcpi-filter-group">
                <span class="gcpi-filter-label">📅 اختيار السنة المالية:</span>
                <select wire:model.live="selectedFiscalYearId" class="gcpi-select">
                    @foreach($d['fiscalYears'] as $fy)
                        <option value="{{ $fy->id }}">{{ $fy->year }} {{ $fy->is_current ? '(السنة الحالية)' : '' }}</option>
                    @endforeach
                </select>
            </div>
            <div style="display: flex; align-items: center; gap: 12px;">
                <a href="{{ route('admin.reports.revenue-records.excel', ['fiscal_year_id' => $this->selectedFiscalYearId]) }}" class="gcpi-btn-pdf" style="background: linear-gradient(135deg, #059669 0%, #047857 100%); box-shadow: 0 2px 8px rgba(5, 150, 105, 0.25);">
                    📊 تصدير إلى Excel
                </a>
                <a href="{{ route('admin.reports.total-revenue-matrix.pdf', ['fiscal_year_id' => $this->selectedFiscalYearId]) }}" target="_blank" class="gcpi-btn-pdf">
                    📄 تصدير تقرير PDF
                </a>
            </div>
        </div>

        <!-- بطاقات ملخص إحصائي علوي -->
        <div class="gcpi-stats-grid">
            <div class="gcpi-stat-box">
                <div class="gcpi-stat-title">الإيراد الكلي السنوي (Gross Revenue)</div>
                <div class="gcpi-stat-value" style="color: #1e3a8a;">
                    {{ number_format($d['grandGrossTotal'], 0) }} <span style="font-size: 0.75rem; font-weight: normal;">د.ع</span>
                </div>
            </div>
            <div class="gcpi-stat-box success">
                <div class="gcpi-stat-title">الإيراد الصافي السنوي (Net Revenue)</div>
                <div class="gcpi-stat-value" style="color: #047857;">
                    {{ number_format($d['grandNetTotal'], 0) }} <span style="font-size: 0.75rem; font-weight: normal;">د.ع</span>
                </div>
            </div>
            <div class="gcpi-stat-box info">
                <div class="gcpi-stat-title">أعلى مركز إيراد (أم قصر الشمالي)</div>
                <div class="gcpi-stat-value" style="color: #0284c7;">
                    {{ isset($d['centerTotals'][1]) ? number_format($d['centerTotals'][1], 0) : '—' }} <span style="font-size: 0.75rem; font-weight: normal;">د.ع</span>
                </div>
            </div>
            <div class="gcpi-stat-box warning">
                <div class="gcpi-stat-title">عدد مراكز الإيراد</div>
                <div class="gcpi-stat-value" style="color: #b45309;">
                    {{ count($d['centers']) }} <span style="font-size: 0.75rem; font-weight: normal;">مراكز + المقر</span>
                </div>
            </div>
        </div>

        <!-- الجدول الرئيسي للمصفوفة -->
        <div class="gcpi-card">
            <div class="gcpi-card-header">
                <div>
                    <h3>🏢 الإيراد الكلي والصافي لمراكز الإيراد السبعة لعام {{ $d['selectedYear'] }}</h3>
                    <div style="font-size: 0.82rem; opacity: 0.85; margin-top: 4px;">
                        مصفوفة التوزيع الشهري والتراكمي لجميع تشكيلات الشركة العامة لموانئ العراق
                    </div>
                </div>
                <div>
                    <span class="gcpi-badge gcpi-badge-navy">ورقة: الإيراد الكلي</span>
                </div>
            </div>

            <div class="gcpi-table-container">
                <table class="gcpi-table">
                    <thead>
                        <tr>
                            <th style="min-width: 120px;">الشهر</th>
                            @foreach($d['centers'] as $center)
                                <th>{{ $center->name_ar }}</th>
                            @endforeach
                            <th class="gcpi-highlight-col">الإيراد الكلي الشهري</th>
                            <th class="gcpi-net-col">الإيراد الصافي الشهري</th>
                            <th style="background-color: #e2e8f0; color: #0f172a;">مجموع الإيراد الكلي المتراكم</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($d['matrix'] as $rowIndex => $row)
                            <tr>
                                <td>{{ $row['month_name'] }}</td>
                                @foreach($d['centers'] as $center)
                                    <td>
                                        @php
                                            $val = $row['centers'][$center->id] ?? 0;
                                            $prevVal = ($rowIndex > 0) ? ($d['matrix'][$rowIndex - 1]['centers'][$center->id] ?? 0) : null;
                                            $diff = ($prevVal !== null && $prevVal > 0 && $val > 0) ? ($val - $prevVal) : null;
                                        @endphp
                                        @if($val > 0)
                                            <div class="gcpi-trend-cell">
                                                <span style="font-weight: 600;">{{ number_format($val, 0) }}</span>
                                                @if($diff !== null && $diff > 0)
                                                    <span class="gcpi-trend-arrow up" title="صعود عن الشهر السابق: +{{ number_format($diff, 0) }} د.ع">
                                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3.5" stroke-linecap="round" stroke-linejoin="round" style="width: 12px; height: 12px;"><polyline points="18 15 12 9 6 15"></polyline></svg>
                                                    </span>
                                                @elseif($diff !== null && $diff < 0)
                                                    <span class="gcpi-trend-arrow down" title="هبوط عن الشهر السابق: -{{ number_format(abs($diff), 0) }} د.ع">
                                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3.5" stroke-linecap="round" stroke-linejoin="round" style="width: 12px; height: 12px;"><polyline points="6 9 12 15 18 9"></polyline></svg>
                                                    </span>
                                                @endif
                                            </div>
                                        @else
                                            <span style="color: #94a3b8;">—</span>
                                        @endif
                                    </td>
                                @endforeach

                                @php
                                    $grossVal = $row['monthly_gross_total'] ?? 0;
                                    $prevGrossVal = ($rowIndex > 0) ? ($d['matrix'][$rowIndex - 1]['monthly_gross_total'] ?? 0) : null;
                                    $grossDiff = ($prevGrossVal !== null && $prevGrossVal > 0 && $grossVal > 0) ? ($grossVal - $prevGrossVal) : null;

                                    $netVal = $row['monthly_net_total'] ?? 0;
                                    $prevNetVal = ($rowIndex > 0) ? ($d['matrix'][$rowIndex - 1]['monthly_net_total'] ?? 0) : null;
                                    $netDiff = ($prevNetVal !== null && $prevNetVal > 0 && $netVal > 0) ? ($netVal - $prevNetVal) : null;
                                @endphp

                                <td class="gcpi-highlight-col">
                                    @if($grossVal > 0)
                                        <div class="gcpi-trend-cell">
                                            <span style="font-weight: 800;">{{ number_format($grossVal, 0) }}</span>
                                            @if($grossDiff !== null && $grossDiff > 0)
                                                <span class="gcpi-trend-arrow up" title="صعود في الإيراد الكلي الشهري: +{{ number_format($grossDiff, 0) }} د.ع">
                                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3.5" stroke-linecap="round" stroke-linejoin="round" style="width: 12px; height: 12px;"><polyline points="18 15 12 9 6 15"></polyline></svg>
                                                </span>
                                            @elseif($grossDiff !== null && $grossDiff < 0)
                                                <span class="gcpi-trend-arrow down" title="هبوط في الإيراد الكلي الشهري: -{{ number_format(abs($grossDiff), 0) }} د.ع">
                                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3.5" stroke-linecap="round" stroke-linejoin="round" style="width: 12px; height: 12px;"><polyline points="6 9 12 15 18 9"></polyline></svg>
                                                </span>
                                            @endif
                                        </div>
                                    @else
                                        <span style="color: #94a3b8;">—</span>
                                    @endif
                                </td>

                                <td class="gcpi-net-col">
                                    @if($netVal > 0)
                                        <div class="gcpi-trend-cell">
                                            <span style="font-weight: 800;">{{ number_format($netVal, 0) }}</span>
                                            @if($netDiff !== null && $netDiff > 0)
                                                <span class="gcpi-trend-arrow up" title="صعود في الإيراد الصافي الشهري: +{{ number_format($netDiff, 0) }} د.ع">
                                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3.5" stroke-linecap="round" stroke-linejoin="round" style="width: 12px; height: 12px;"><polyline points="18 15 12 9 6 15"></polyline></svg>
                                                </span>
                                            @elseif($netDiff !== null && $netDiff < 0)
                                                <span class="gcpi-trend-arrow down" title="هبوط في الإيراد الصافي الشهري: -{{ number_format(abs($netDiff), 0) }} د.ع">
                                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3.5" stroke-linecap="round" stroke-linejoin="round" style="width: 12px; height: 12px;"><polyline points="6 9 12 15 18 9"></polyline></svg>
                                                </span>
                                            @endif
                                        </div>
                                    @else
                                        <span style="color: #94a3b8;">—</span>
                                    @endif
                                </td>

                                <td style="font-weight: 800; background-color: rgba(226, 232, 240, 0.3);">
                                    @if($row['cumulative_gross_total'] > 0)
                                        {{ number_format($row['cumulative_gross_total'], 0) }}
                                    @else
                                        <span style="color: #94a3b8;">—</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr>
                            <td>المجموع السنوي</td>
                            @foreach($d['centers'] as $center)
                                <td>{{ number_format($d['centerTotals'][$center->id] ?? 0, 0) }}</td>
                            @endforeach
                            <td style="background-color: #1e3a8a; color: #93c5fd;">{{ number_format($d['grandGrossTotal'], 0) }}</td>
                            <td style="background-color: #064e3b; color: #6ee7b7;">{{ number_format($d['grandNetTotal'], 0) }}</td>
                            <td style="background-color: #334155; color: #f8fafc;">{{ number_format($d['grandGrossTotal'], 0) }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
</x-filament-panels::page>
