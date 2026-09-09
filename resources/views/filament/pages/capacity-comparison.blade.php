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
            margin-bottom: 28px;
            overflow: hidden;
        }
        .gcpi-card-header {
            background: linear-gradient(135deg, #0f172a 0%, #1e3a8a 100%);
            color: #ffffff;
            padding: 20px 24px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .gcpi-card-header h3 {
            margin: 0;
            font-size: 1.2rem;
            font-weight: 800;
        }
        .gcpi-filter-bar {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 14px;
            padding: 16px 20px;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 16px;
            margin-bottom: 24px;
        }
        .gcpi-filter-item {
            display: flex;
            flex-direction: column;
            gap: 6px;
        }
        .gcpi-filter-label {
            font-weight: 700;
            font-size: 0.85rem;
            color: #475569;
        }
        .gcpi-select {
            background-color: #ffffff;
            border: 1.5px solid #cbd5e1;
            border-radius: 10px;
            padding: 10px 14px;
            font-size: 0.95rem;
            font-weight: 700;
            color: #0f172a;
            outline: none;
            cursor: pointer;
            width: 100%;
            transition: all 0.2s;
        }
        .gcpi-select:focus {
            border-color: #2563eb;
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.15);
        }
        .gcpi-table-container {
            width: 100%;
            overflow-x: auto;
        }
        .gcpi-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.9rem;
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
            background-color: #f0f9ff;
        }
        .gcpi-badge-pill {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 9999px;
            font-weight: 800;
            font-size: 0.8rem;
            font-variant-numeric: tabular-nums;
        }
        .gcpi-badge-positive {
            background-color: #d1fae5;
            color: #065f46;
        }
        .gcpi-badge-negative {
            background-color: #fee2e2;
            color: #991b1b;
        }
        .gcpi-badge-unit {
            background-color: #f1f5f9;
            color: #64748b;
            padding: 3px 8px;
            border-radius: 6px;
            font-size: 0.78rem;
            font-weight: 700;
        }
        .gcpi-ports-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(480px, 1fr));
            gap: 20px;
        }
        .gcpi-btn-pdf {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            background: linear-gradient(135deg, #dc2626 0%, #991b1b 100%);
            color: #ffffff !important;
            font-weight: 700;
            font-size: 0.9rem;
            padding: 10px 20px;
            border-radius: 10px;
            text-decoration: none;
            box-shadow: 0 2px 8px rgba(220, 38, 38, 0.25);
            transition: all 0.2s ease;
            height: 44px;
        }
        .gcpi-btn-pdf:hover {
            background: linear-gradient(135deg, #b91c1c 0%, #7f1d1d 100%);
            box-shadow: 0 4px 14px rgba(220, 38, 38, 0.35);
            transform: translateY(-1px);
        }
    </style>

    <div class="gcpi-dashboard">
        <!-- شريط الفلاتر والاختيار والطباعة -->
        <div class="gcpi-filter-bar">
            <div class="gcpi-filter-item">
                <label class="gcpi-filter-label">📅 سنة المقارنة (الحالية):</label>
                <select wire:model.live="currFiscalYearId" class="gcpi-select">
                    @foreach($d['fiscalYears'] as $fy)
                        <option value="{{ $fy->id }}">{{ $fy->year }} {{ $fy->is_current ? '(الحالية)' : '' }}</option>
                    @endforeach
                </select>
            </div>

            <div class="gcpi-filter-item">
                <label class="gcpi-filter-label">📅 سنة الأساس (السابقة):</label>
                <select wire:model.live="prevFiscalYearId" class="gcpi-select">
                    @foreach($d['fiscalYears'] as $fy)
                        <option value="{{ $fy->id }}">{{ $fy->year }}</option>
                    @endforeach
                </select>
            </div>

            <div class="gcpi-filter-item">
                <label class="gcpi-filter-label">📆 فترة المقارنة لغاية شهر:</label>
                <select wire:model.live="selectedMonthNumber" class="gcpi-select">
                    @foreach($d['months'] as $m)
                        <option value="{{ $m->month_number }}">{{ $m->name_ar }} (شهر {{ $m->month_number }})</option>
                    @endforeach
                </select>
            </div>

            <div class="gcpi-filter-item" style="justify-content: flex-end;">
                <label class="gcpi-filter-label" style="visibility: hidden;">طباعة</label>
                <a href="{{ route('admin.reports.capacity-comparison.pdf', ['prev_year_id' => $this->prevFiscalYearId, 'curr_year_id' => $this->currFiscalYearId, 'month_number' => $this->selectedMonthNumber]) }}" target="_blank" class="gcpi-btn-pdf">
                    📄 تصدير تقرير PDF
                </a>
            </div>
        </div>

        <!-- 1. جدول إجمالي الموانئ الأربعة معاً (Company Total) -->
        <div class="gcpi-card" style="border: 2px solid #3b82f6;">
            <div class="gcpi-card-header" style="background: linear-gradient(135deg, #1e3a8a 0%, #1e40af 100%);">
                <div>
                    <h3>🏛️ إجمالي الطاقة الإنتاجية للموانئ الأربعة مجتمعة (مقارنة {{ $d['prevYear'] }} مقابل {{ $d['currYear'] }})</h3>
                    <div style="font-size: 0.85rem; opacity: 0.9; margin-top: 4px;">
                        المقارنة التراكمية من بداية السنة لغاية شهر {{ $d['monthName'] }}
                    </div>
                </div>
                <div>
                    <span style="background: #ffffff; color: #1e3a8a; padding: 6px 14px; border-radius: 9999px; font-weight: 800; font-size: 0.82rem;">
                        المجموع الكلي
                    </span>
                </div>
            </div>

            <div class="gcpi-table-container">
                <table class="gcpi-table">
                    <thead>
                        <tr>
                            <th style="text-align: right; min-width: 220px;">المؤشر التشغيلي</th>
                            <th>سنة {{ $d['prevYear'] }}</th>
                            <th>سنة {{ $d['currYear'] }}</th>
                            <th>الوحدة</th>
                            <th>الفارق (Delta)</th>
                            <th>نسبة التغير %</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($d['companyComparison'] as $row)
                            <tr>
                                <td style="font-weight: 800; color: #0f172a;">{{ $row['label'] }}</td>
                                <td style="font-weight: 600; color: #475569;">
                                    {{ number_format(round($row['prev_val']), 0) }}
                                </td>
                                <td style="font-weight: 800; color: #1e3a8a; font-size: 1rem;">
                                    {{ number_format(round($row['curr_val']), 0) }}
                                </td>
                                <td><span class="gcpi-badge-unit">{{ $row['unit'] }}</span></td>
                                <td style="font-weight: 800;">
                                    <span class="gcpi-badge-pill {{ $row['diff'] >= 0 ? 'gcpi-badge-positive' : 'gcpi-badge-negative' }}">
                                        {{ $row['diff'] > 0 ? '+' : '' }}{{ number_format(round($row['diff']), 0) }}
                                    </span>
                                </td>
                                <td>
                                    <span class="gcpi-badge-pill {{ $row['percent'] >= 0 ? 'gcpi-badge-positive' : 'gcpi-badge-negative' }}">
                                        {{ $row['percent'] > 0 ? '+' : '' }}{{ $row['percent'] }}%
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- 2. جداول الموانئ الأربعة كل على حدة في شبكة كروت أنيقة -->
        <h3 style="font-size: 1.15rem; font-weight: 800; color: #0f172a; margin-bottom: 16px;">
            ⚓ مقارنة الأداء لكل ميناء على حدة
        </h3>

        <div class="gcpi-ports-grid">
            @foreach($d['portTables'] as $portTable)
                <div class="gcpi-card">
                    <div class="gcpi-card-header" style="background: #334155; padding: 14px 20px;">
                        <h3 style="font-size: 1rem;">⚓ {{ $portTable['port_name'] }}</h3>
                        <span style="font-size: 0.78rem; opacity: 0.85;">{{ $d['prevYear'] }} مقابل {{ $d['currYear'] }}</span>
                    </div>

                    <div class="gcpi-table-container">
                        <table class="gcpi-table" style="font-size: 0.82rem;">
                            <thead>
                                <tr>
                                    <th style="text-align: right;">المؤشر</th>
                                    <th>{{ $d['prevYear'] }}</th>
                                    <th>{{ $d['currYear'] }}</th>
                                    <th>الفارق</th>
                                    <th>التغير</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($portTable['comparison'] as $row)
                                    <tr>
                                        <td style="font-weight: 700;">{{ $row['label'] }}</td>
                                        <td>{{ number_format(round($row['prev_val']), 0) }}</td>
                                        <td style="font-weight: 800; color: #1e3a8a;">{{ number_format(round($row['curr_val']), 0) }}</td>
                                        <td>
                                            <span class="gcpi-badge-pill {{ $row['diff'] >= 0 ? 'gcpi-badge-positive' : 'gcpi-badge-negative' }}" style="font-size: 0.75rem; padding: 2px 6px;">
                                                {{ $row['diff'] > 0 ? '+' : '' }}{{ number_format(round($row['diff']), 0) }}
                                            </span>
                                        </td>
                                        <td>
                                            <span style="font-weight: 700; font-size: 0.75rem; color: {{ $row['percent'] >= 0 ? '#059669' : '#dc2626' }};">
                                                {{ $row['percent'] > 0 ? '+' : '' }}{{ $row['percent'] }}%
                                            </span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</x-filament-panels::page>
