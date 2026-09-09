<x-filament-panels::page>
    @php
        $d = $this->data;
        $s = $d['summary'];
        $isAll = $d['isAllPorts'];
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
        }
        .gcpi-card-header {
            background: linear-gradient(135deg, #1e3a8a 0%, #0f172a 100%);
            color: #ffffff;
            padding: 22px 24px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 12px;
        }
        .gcpi-card-header h2 {
            margin: 0;
            font-size: 1.3rem;
            font-weight: 800;
            letter-spacing: -0.02em;
        }
        .gcpi-filter-bar {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 14px;
            padding: 16px 20px;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
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
        .gcpi-metrics-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 16px;
            margin-bottom: 24px;
        }
        .gcpi-metric-card {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 14px;
            padding: 16px 18px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.03);
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }
        .gcpi-metric-title {
            font-size: 0.8rem;
            font-weight: 700;
            color: #64748b;
        }
        .gcpi-metric-num {
            font-size: 1.35rem;
            font-weight: 800;
            margin-top: 8px;
            font-variant-numeric: tabular-nums;
        }
        .gcpi-table-container {
            width: 100%;
            overflow-x: auto;
        }
        .gcpi-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.95rem;
            text-align: right;
        }
        .gcpi-table thead tr {
            background: #f1f5f9;
            color: #334155;
            border-bottom: 2px solid #cbd5e1;
        }
        .gcpi-table th {
            padding: 14px 20px;
            font-weight: 800;
            font-size: 0.9rem;
        }
        .gcpi-table td {
            padding: 14px 20px;
            border-bottom: 1px solid #e2e8f0;
            color: #1e293b;
            font-variant-numeric: tabular-nums;
        }
        .gcpi-table tbody tr:nth-child(even) {
            background-color: #f8fafc;
        }
        .gcpi-table tbody tr:hover {
            background-color: #eff6ff;
        }
        .gcpi-unit-badge {
            background: #f1f5f9;
            color: #475569;
            padding: 4px 10px;
            border-radius: 6px;
            font-weight: 700;
            font-size: 0.8rem;
        }
        .gcpi-val-highlight {
            font-weight: 800;
            font-size: 1.15rem;
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
                <label class="gcpi-filter-label">⚓ اختيار الميناء:</label>
                <select wire:model.live="selectedPortId" class="gcpi-select">
                    <option value="">إجمالي كافة الموانئ الأربعة مجتمعة (إجمالي الشركة)</option>
                    @foreach($d['ports'] as $p)
                        <option value="{{ $p->id }}">{{ $p->name_ar }}</option>
                    @endforeach
                </select>
            </div>

            <div class="gcpi-filter-item">
                <label class="gcpi-filter-label">📅 اختيار السنة المالية:</label>
                <select wire:model.live="selectedFiscalYearId" class="gcpi-select">
                    @foreach($d['fiscalYears'] as $fy)
                        <option value="{{ $fy->id }}">{{ $fy->year }} {{ $fy->is_current ? '(السنة الحالية)' : '' }}</option>
                    @endforeach
                </select>
            </div>

            <div class="gcpi-filter-item">
                <label class="gcpi-filter-label">📆 الفترة التراكمية لغاية نهاية شهر:</label>
                <select wire:model.live="selectedMonthNumber" class="gcpi-select">
                    @foreach($d['months'] as $m)
                        <option value="{{ $m->month_number }}">{{ $m->name_ar }} (شهر {{ $m->month_number }})</option>
                    @endforeach
                </select>
            </div>

            <div class="gcpi-filter-item" style="justify-content: flex-end; display: flex; flex-direction: row; gap: 8px; flex-wrap: wrap;">
                <a href="{{ route('admin.reports.total-cumulative-capacity.pdf', ['fiscal_year_id' => $this->selectedFiscalYearId, 'month_number' => $this->selectedMonthNumber]) }}" target="_blank" class="gcpi-btn-pdf" style="background: linear-gradient(135deg, #0284c7 0%, #0369a1 100%);">
                    📚 تصدير التقرير الشامل (كافة الموانئ + الإجمالي)
                </a>
                @if($this->selectedPortId)
                    <a href="{{ route('admin.reports.total-cumulative-capacity.pdf', ['fiscal_year_id' => $this->selectedFiscalYearId, 'month_number' => $this->selectedMonthNumber, 'port_id' => $this->selectedPortId]) }}" target="_blank" class="gcpi-btn-pdf">
                        📄 تصدير ميناء ({{ $d['selectedPort'] }})
                    </a>
                @else
                    <a href="{{ route('admin.reports.total-cumulative-capacity.pdf', ['fiscal_year_id' => $this->selectedFiscalYearId, 'month_number' => $this->selectedMonthNumber, 'only_total' => 1]) }}" target="_blank" class="gcpi-btn-pdf">
                        📄 تصدير إجمالي الشركة فقط (المحدد حالياً)
                    </a>
                @endif
            </div>
        </div>

        <!-- ملخصات سريعة في الأعلى -->
        <div class="gcpi-metrics-grid">
            <div class="gcpi-metric-card" style="border-top: 4px solid #2563eb;">
                <span class="gcpi-metric-title">إجمالي البواخر الراسية</span>
                <span class="gcpi-metric-num" style="color: #1e3a8a;">{{ number_format($s['total_ships']) }} <span style="font-size: 0.8rem; color: #64748b;">باخرة</span></span>
            </div>
            <div class="gcpi-metric-card" style="border-top: 4px solid #059669;">
                <span class="gcpi-metric-title">الطاقة الإنتاجية الكلية بالطن</span>
                <span class="gcpi-metric-num" style="color: #047857;">{{ number_format(round($s['total_tonnage']), 0) }} <span style="font-size: 0.8rem; color: #64748b;">طن</span></span>
            </div>
            <div class="gcpi-metric-card" style="border-top: 4px solid #0891b2;">
                <span class="gcpi-metric-title">{{ $isAll ? 'الطاقة الإنتاجية الكلية للشركة TEU' : 'الطاقة الإنتاجية للميناء TEU' }}</span>
                <span class="gcpi-metric-num" style="color: #0e7490;">{{ number_format($s['total_teu']) }} <span style="font-size: 0.8rem; color: #64748b;">TEU</span></span>
            </div>
            <div class="gcpi-metric-card" style="border-top: 4px solid #d97706;">
                <span class="gcpi-metric-title">{{ $isAll ? 'إيراد الموانئ الأربعة فقط' : 'الإيراد الكلي للميناء' }}</span>
                <span class="gcpi-metric-num" style="color: #b45309; font-size: 1.15rem;">{{ number_format($s['total_revenue'], 0) }} <span style="font-size: 0.75rem; color: #64748b;">د.ع</span></span>
            </div>
        </div>

        <!-- 1. جدول المؤشرات التراكمية المطابق لورقة total في الإكسل -->
        <div class="gcpi-card">
            <div class="gcpi-card-header">
                <div>
                    <h2>اجمالي الطاقة الانتاجية ({{ $d['selectedPort'] }})</h2>
                    <div style="font-size: 0.85rem; opacity: 0.9; margin-top: 4px;">
                        للفترة من 1 / 1 / {{ $d['selectedYear'] }} لغاية نهاية شهر {{ $d['selectedMonth'] }} {{ $d['selectedYear'] }}
                    </div>
                </div>
                <div>
                    <span style="background: rgba(255,255,255,0.2); color: #ffffff; padding: 6px 14px; border-radius: 9999px; font-weight: 800; font-size: 0.82rem;">
                        ورقة total (المطابقة الشاملة)
                    </span>
                </div>
            </div>

            <div class="gcpi-table-container">
                <table class="gcpi-table">
                    <thead>
                        <tr>
                            <th style="width: 60px; text-align: center;">ت</th>
                            <th>المؤشر التشغيلي</th>
                            <th style="text-align: center; width: 240px;">الكمية التراكمية للفترة</th>
                            <th style="text-align: center; width: 120px;">الوحدة</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td style="text-align: center; color: #64748b; font-weight: bold;">1</td>
                            <td style="font-weight: 800; color: #0f172a;">عدد البواخر الكلي</td>
                            <td style="text-align: center;" class="gcpi-val-highlight" style="color: #1e3a8a;">{{ number_format($s['total_ships']) }}</td>
                            <td style="text-align: center;"><span class="gcpi-unit-badge">باخرة</span></td>
                        </tr>

                        @if(!$isAll)
                            <tr style="background-color: #f0fdf4;">
                                <td style="text-align: center; color: #64748b; font-weight: bold;">2</td>
                                <td style="font-weight: 800; color: #065f46;">الطاقة الانتاجية الكلية للميناء بالطن</td>
                                <td style="text-align: center;" class="gcpi-val-highlight" style="color: #059669;">{{ number_format(round($s['total_tonnage']), 0) }}</td>
                                <td style="text-align: center;"><span class="gcpi-unit-badge" style="background: #d1fae5; color: #065f46;">طن</span></td>
                            </tr>
                        @endif

                        <tr>
                            <td style="text-align: center; color: #64748b; font-weight: bold;">{{ $isAll ? 2 : 3 }}</td>
                            <td style="font-weight: 700;">معدل الاوزان الشهري</td>
                            <td style="text-align: center; font-weight: 700; color: #334155;">{{ number_format(round($s['monthly_average_weight']), 0) }}</td>
                            <td style="text-align: center;"><span class="gcpi-unit-badge">طن</span></td>
                        </tr>

                        <tr>
                            <td style="text-align: center; color: #64748b; font-weight: bold;">{{ $isAll ? 3 : 4 }}</td>
                            <td style="font-weight: 700;">معدل الاوزان اليومي</td>
                            <td style="text-align: center; font-weight: 700; color: #334155;">{{ number_format(round($s['daily_average_weight']), 0) }}</td>
                            <td style="text-align: center;"><span class="gcpi-unit-badge">طن</span></td>
                        </tr>

                        <tr>
                            <td style="text-align: center; color: #64748b; font-weight: bold;">{{ $isAll ? 4 : 5 }}</td>
                            <td style="font-weight: 700; color: #9a3412;">تصدير مشتقات نفطية</td>
                            <td style="text-align: center; font-weight: 700; color: #c2410c;">{{ number_format(round($s['oil_exported_tons']), 0) }}</td>
                            <td style="text-align: center;"><span class="gcpi-unit-badge">طن</span></td>
                        </tr>

                        <tr>
                            <td style="text-align: center; color: #64748b; font-weight: bold;">{{ $isAll ? 5 : 6 }}</td>
                            <td style="font-weight: 700; color: #9a3412;">استيراد مشتقات نفطية</td>
                            <td style="text-align: center; font-weight: 700; color: #c2410c;">{{ number_format(round($s['oil_imported_tons']), 0) }}</td>
                            <td style="text-align: center;"><span class="gcpi-unit-badge">طن</span></td>
                        </tr>

                        <tr>
                            <td style="text-align: center; color: #64748b; font-weight: bold;">{{ $isAll ? 6 : 7 }}</td>
                            <td style="font-weight: 700; color: #0369a1;">عدد الحاويات المستوردة</td>
                            <td style="text-align: center; font-weight: 700; color: #0284c7;">{{ number_format($s['imported_containers_count']) }}</td>
                            <td style="text-align: center;"><span class="gcpi-unit-badge">حاوية</span></td>
                        </tr>

                        <tr style="background-color: #f0f9ff;">
                            <td style="text-align: center; color: #64748b; font-weight: bold;">{{ $isAll ? 7 : 8 }}</td>
                            <td style="font-weight: 800; color: #0c4a6e;">عدد الحاويات المستوردة TEU</td>
                            <td style="text-align: center;" class="gcpi-val-highlight" style="color: #0284c7;">{{ number_format($s['imported_teu']) }}</td>
                            <td style="text-align: center;"><span class="gcpi-unit-badge" style="background: #e0f2fe; color: #0369a1;">حاوية</span></td>
                        </tr>

                        <tr>
                            <td style="text-align: center; color: #64748b; font-weight: bold;">{{ $isAll ? 8 : 9 }}</td>
                            <td style="font-weight: 700; color: #4338ca;">عدد الحاويات المصدرة</td>
                            <td style="text-align: center; font-weight: 700; color: #4f46e5;">{{ number_format($s['exported_containers_count']) }}</td>
                            <td style="text-align: center;"><span class="gcpi-unit-badge">حاوية</span></td>
                        </tr>

                        <tr style="background-color: #eef2ff;">
                            <td style="text-align: center; color: #64748b; font-weight: bold;">{{ $isAll ? 9 : 10 }}</td>
                            <td style="font-weight: 800; color: #312e81;">عدد الحاويات المصدرة TEU</td>
                            <td style="text-align: center;" class="gcpi-val-highlight" style="color: #4f46e5;">{{ number_format($s['exported_teu']) }}</td>
                            <td style="text-align: center;"><span class="gcpi-unit-badge" style="background: #e0e7ff; color: #3730a3;">حاوية</span></td>
                        </tr>

                        <tr>
                            <td style="text-align: center; color: #64748b; font-weight: bold;">{{ $isAll ? 10 : 11 }}</td>
                            <td style="font-weight: 700;">عدد الحاويات المصدرة مليان</td>
                            <td style="text-align: center; font-weight: 700;">{{ number_format($s['exported_full_count']) }}</td>
                            <td style="text-align: center;"><span class="gcpi-unit-badge">حاوية</span></td>
                        </tr>

                        <tr>
                            <td style="text-align: center; color: #64748b; font-weight: bold;">{{ $isAll ? 11 : 12 }}</td>
                            <td style="font-weight: 700;">عدد الحاويات المصدرة فارغ</td>
                            <td style="text-align: center; font-weight: 700;">{{ number_format($s['exported_empty_count']) }}</td>
                            <td style="text-align: center;"><span class="gcpi-unit-badge">حاوية</span></td>
                        </tr>

                        @if(!$isAll)
                            <tr style="background-color: #eff6ff;">
                                <td style="text-align: center; color: #64748b; font-weight: bold;">13</td>
                                <td style="font-weight: 800; color: #1e3a8a;">الطاقة الانتاجية الكلية للميناء TEU</td>
                                <td style="text-align: center;" class="gcpi-val-highlight" style="color: #1e3a8a;">{{ number_format($s['total_teu']) }}</td>
                                <td style="text-align: center;"><span class="gcpi-unit-badge" style="background: #dbeafe; color: #1e3a8a;">حاوية</span></td>
                            </tr>
                            <tr style="background-color: #fefce8; border-top: 2px solid #fef08a;">
                                <td style="text-align: center; color: #854d0e; font-weight: bold;">14</td>
                                <td style="font-weight: 800; color: #854d0e;">الايراد الكلي للميناء</td>
                                <td style="text-align: center;" class="gcpi-val-highlight" style="color: #a16207;">{{ number_format($s['total_revenue'], 0) }}</td>
                                <td style="text-align: center;"><span class="gcpi-unit-badge" style="background: #fef9c3; color: #854d0e;">دينار</span></td>
                            </tr>
                        @else
                            <tr style="background-color: #eff6ff;">
                                <td style="text-align: center; color: #64748b; font-weight: bold;">12</td>
                                <td style="font-weight: 800; color: #1e3a8a;">الطاقة الانتاجية الكلية للشركة TEU</td>
                                <td style="text-align: center;" class="gcpi-val-highlight" style="color: #1e3a8a;">{{ number_format($s['total_teu']) }}</td>
                                <td style="text-align: center;"><span class="gcpi-unit-badge" style="background: #dbeafe; color: #1e3a8a;">حاوية</span></td>
                            </tr>
                            <tr style="background-color: #f0fdf4;">
                                <td style="text-align: center; color: #64748b; font-weight: bold;">13</td>
                                <td style="font-weight: 800; color: #065f46;">الطاقة الانتاجية الكلية للشركة بالطن</td>
                                <td style="text-align: center;" class="gcpi-val-highlight" style="color: #059669;">{{ number_format(round($s['total_tonnage']), 0) }}</td>
                                <td style="text-align: center;"><span class="gcpi-unit-badge" style="background: #d1fae5; color: #065f46;">طن</span></td>
                            </tr>
                            <tr>
                                <td style="text-align: center; color: #64748b; font-weight: bold;">14</td>
                                <td style="font-weight: 800; color: #0f172a;">مجموع وزن الحاويات</td>
                                <td style="text-align: center; font-weight: 800; color: #0f172a;">{{ number_format(round($s['total_containers_weight']), 0) }}</td>
                                <td style="text-align: center;"><span class="gcpi-unit-badge">طن</span></td>
                            </tr>
                            <tr>
                                <td style="text-align: center; color: #64748b; font-weight: bold;">15</td>
                                <td style="font-weight: 800; color: #0f172a;">مجموع وزن البضائع</td>
                                <td style="text-align: center; font-weight: 800; color: #0f172a;">{{ number_format(round($s['total_cargo_weight']), 0) }}</td>
                                <td style="text-align: center;"><span class="gcpi-unit-badge">طن</span></td>
                            </tr>
                            <tr>
                                <td style="text-align: center; color: #64748b; font-weight: bold;">16</td>
                                <td style="font-weight: 800; color: #0f172a;">مجموع وزن المشتقات النفطية</td>
                                <td style="text-align: center; font-weight: 800; color: #0f172a;">{{ number_format(round($s['oil_total_tons']), 0) }}</td>
                                <td style="text-align: center;"><span class="gcpi-unit-badge">طن</span></td>
                            </tr>
                            <tr style="background-color: #fefce8; border-top: 2px solid #fef08a;">
                                <td style="text-align: center; color: #854d0e; font-weight: bold;">17</td>
                                <td style="font-weight: 800; color: #854d0e;">ايراد الموانئ الأربعة فقط</td>
                                <td style="text-align: center;" class="gcpi-val-highlight" style="color: #a16207;">{{ number_format($s['total_revenue'], 0) }}</td>
                                <td style="text-align: center;"><span class="gcpi-unit-badge" style="background: #fef9c3; color: #854d0e;">دينار</span></td>
                            </tr>
                        @endif
                    </tbody>
                </table>
            </div>
        </div>

        <!-- 2. جدول المعدلات الشهرية واليومية للموانئ الأربعة مجتمعة (مطابق لأسفل ورقة total في الإكسل) -->
        @if($isAll)
            <div class="gcpi-card" style="border: 2px solid #0284c7;">
                <div class="gcpi-card-header" style="background: linear-gradient(135deg, #0369a1 0%, #0c4a6e 100%);">
                    <div>
                        <h2>📊 المعدلات الشهرية واليومية العامة للشركة (الموانئ الأربعة)</h2>
                        <div style="font-size: 0.85rem; opacity: 0.9; margin-top: 4px;">
                            المعدلات المحسوبة على أساس الفترة المسجلة ({{ $s['months_count'] }} أشهر / {{ $s['days_count'] }} يوم)
                        </div>
                    </div>
                </div>

                <div class="gcpi-table-container">
                    <table class="gcpi-table">
                        <thead>
                            <tr>
                                <th style="width: 60px; text-align: center;">ت</th>
                                <th>المعدل التشغيلي</th>
                                <th style="text-align: center; width: 240px;">القيمة المحسوبة</th>
                                <th style="text-align: center; width: 120px;">الوحدة</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td style="text-align: center; font-weight: bold; color: #64748b;">18</td>
                                <td style="font-weight: 700;">المعدل الشهري للبواخر</td>
                                <td style="text-align: center; font-weight: 800; color: #1e3a8a;">{{ number_format(round($s['monthly_avg_ships']), 0) }}</td>
                                <td style="text-align: center;"><span class="gcpi-unit-badge">باخرة/شهر</span></td>
                            </tr>
                            <tr>
                                <td style="text-align: center; font-weight: bold; color: #64748b;">19</td>
                                <td style="font-weight: 700;">المعدل اليومي للبواخر</td>
                                <td style="text-align: center; font-weight: 800; color: #1e3a8a;">{{ number_format(round($s['daily_avg_ships']), 0) }}</td>
                                <td style="text-align: center;"><span class="gcpi-unit-badge">باخرة/يوم</span></td>
                            </tr>
                            <tr style="background-color: #f0f9ff;">
                                <td style="text-align: center; font-weight: bold; color: #64748b;">20</td>
                                <td style="font-weight: 800; color: #0284c7;">المعدل الشهري للحاويات TEU</td>
                                <td style="text-align: center; font-weight: 800; color: #0284c7;">{{ number_format(round($s['monthly_avg_teu']), 0) }}</td>
                                <td style="text-align: center;"><span class="gcpi-unit-badge" style="background: #e0f2fe; color: #0369a1;">حاوية/شهر</span></td>
                            </tr>
                            <tr style="background-color: #f0f9ff;">
                                <td style="text-align: center; font-weight: bold; color: #64748b;">21</td>
                                <td style="font-weight: 800; color: #0284c7;">المعدل اليومي للحاويات TEU</td>
                                <td style="text-align: center; font-weight: 800; color: #0284c7;">{{ number_format(round($s['daily_avg_teu']), 0) }}</td>
                                <td style="text-align: center;"><span class="gcpi-unit-badge" style="background: #e0f2fe; color: #0369a1;">حاوية/يوم</span></td>
                            </tr>
                            <tr>
                                <td style="text-align: center; font-weight: bold; color: #64748b;">22</td>
                                <td style="font-weight: 700; color: #c2410c;">المعدل الشهري للتصدير النفطي</td>
                                <td style="text-align: center; font-weight: 800; color: #c2410c;">{{ number_format(round($s['monthly_avg_oil_export']), 0) }}</td>
                                <td style="text-align: center;"><span class="gcpi-unit-badge">طن/شهر</span></td>
                            </tr>
                            <tr>
                                <td style="text-align: center; font-weight: bold; color: #64748b;">23</td>
                                <td style="font-weight: 700; color: #c2410c;">المعدل الشهري للاستيراد النفطي</td>
                                <td style="text-align: center; font-weight: 800; color: #c2410c;">{{ number_format(round($s['monthly_avg_oil_import']), 0) }}</td>
                                <td style="text-align: center;"><span class="gcpi-unit-badge">طن/شهر</span></td>
                            </tr>
                            <tr>
                                <td style="text-align: center; font-weight: bold; color: #64748b;">24</td>
                                <td style="font-weight: 700; color: #c2410c;">المعدل اليومي للتصدير النفطي</td>
                                <td style="text-align: center; font-weight: 800; color: #c2410c;">{{ number_format(round($s['daily_avg_oil_export']), 0) }}</td>
                                <td style="text-align: center;"><span class="gcpi-unit-badge">طن/يوم</span></td>
                            </tr>
                            <tr>
                                <td style="text-align: center; font-weight: bold; color: #64748b;">25</td>
                                <td style="font-weight: 700; color: #c2410c;">المعدل اليومي للاستيراد النفطي</td>
                                <td style="text-align: center; font-weight: 800; color: #c2410c;">{{ number_format(round($s['daily_avg_oil_import']), 0) }}</td>
                                <td style="text-align: center;"><span class="gcpi-unit-badge">طن/يوم</span></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        @endif
    </div>
</x-filament-panels::page>
