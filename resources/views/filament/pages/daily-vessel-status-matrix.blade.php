<x-filament-panels::page>
    <style>
        .vessel-matrix-container {
            font-family: 'Segoe UI', Tahoma, Arial, sans-serif;
            color: #1e293b;
            direction: rtl;
        }

        /* ─── بطاقة الفلترة المعتمدة بالنظام ─── */
        .vessel-filter-card {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 14px;
            padding: 16px 20px;
            margin-bottom: 22px;
            box-shadow: 0 1px 4px rgba(0, 0, 0, 0.04);
        }

        .vessel-filter-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 12px;
            margin-bottom: 14px;
            padding-bottom: 12px;
            border-bottom: 1px solid #f1f5f9;
        }

        .filter-panel-title {
            font-size: 0.88rem;
            font-weight: 800;
            color: #1e293b;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .filter-panel-title svg {
            width: 18px !important;
            height: 18px !important;
            min-width: 18px !important;
            min-height: 18px !important;
            max-width: 18px !important;
            max-height: 18px !important;
            flex-shrink: 0 !important;
            display: inline-block !important;
            color: #1B365D !important;
        }

        /* ─── أزرار العمليات الموحدة ─── */
        .actions-controls-group {
            display: flex;
            align-items: center;
            flex-wrap: wrap;
            gap: 8px;
        }

        .action-bar-btn svg {
            width: 16px !important;
            height: 16px !important;
            min-width: 16px !important;
            flex-shrink: 0 !important;
        }

        .action-bar-btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 7px 14px;
            border-radius: 8px;
            font-size: 0.82rem;
            font-weight: 700;
            cursor: pointer;
            text-decoration: none;
            border: none;
            transition: all 0.2s ease;
            line-height: 1.2;
            white-space: nowrap;
        }

        .action-bar-btn.print {
            background: #1B365D;
            color: #ffffff;
        }

        .action-bar-btn.print:hover {
            background: #0f2d59;
            box-shadow: 0 2px 6px rgba(15, 45, 89, 0.3);
        }

        .action-bar-btn.pdf {
            background: #b91c1c;
            color: #ffffff;
        }

        .action-bar-btn.pdf:hover {
            background: #991b1b;
            box-shadow: 0 2px 6px rgba(185, 28, 28, 0.3);
        }

        .action-bar-btn.excel {
            background: #059669;
            color: #ffffff;
        }

        .action-bar-btn.excel:hover {
            background: #047857;
            box-shadow: 0 2px 6px rgba(5, 150, 105, 0.3);
        }

        /* ─── بطاقات المؤشرات السريعة (KPIs) ─── */
        .kpi-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 14px;
            margin-bottom: 22px;
        }

        .kpi-card {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 14px 16px;
            box-shadow: 0 1px 4px rgba(0, 0, 0, 0.03);
            display: flex;
            flex-direction: column;
            gap: 4px;
            position: relative;
            overflow: hidden;
        }

        .kpi-card::before {
            content: '';
            position: absolute;
            top: 0;
            right: 0;
            bottom: 0;
            width: 4px;
        }

        .kpi-card.blue::before {
            background: #1B365D;
        }

        .kpi-card.amber::before {
            background: #d97706;
        }

        .kpi-card.emerald::before {
            background: #059669;
        }

        .kpi-card.purple::before {
            background: #7c3aed;
        }

        .kpi-title {
            font-size: 0.8rem;
            font-weight: 700;
            color: #64748b;
        }

        .kpi-value {
            font-size: 1.6rem;
            font-weight: 800;
            color: #0f172a;
            line-height: 1.1;
        }

        .kpi-sub {
            font-size: 0.75rem;
            color: #94a3b8;
        }

        /* ─── جدول الموقف المعتمد طبق الأصل ─── */
        .vessel-table-wrapper {
            background: #ffffff;
            border: 1px solid #cbd5e1;
            border-radius: 12px;
            overflow-x: auto;
            box-shadow: 0 4px 14px rgba(0, 0, 0, 0.04);
            margin-bottom: 24px;
        }

        .vessel-table {
            width: 100%;
            border-collapse: collapse;
            text-align: center;
            font-size: 0.84rem;
        }

        .vessel-table th,
        .vessel-table td {
            border: 1px solid #cbd5e1;
            padding: 7px 4px;
            font-size: 0.84rem;
            vertical-align: middle;
        }

        .vessel-table thead tr.main-header th {
            background-color: #1B365D;
            color: #ffffff;
            font-weight: 700;
            font-size: 0.84rem;
            line-height: 1.3;
            padding: 8px 4px;
        }

        .vessel-table thead tr.sub-header th {
            background-color: #24497D;
            color: #ffffff;
            font-weight: 700;
            font-size: 0.80rem;
            line-height: 1.25;
            padding: 6px 4px;
        }

        .vessel-table tbody tr:nth-child(even) {
            background-color: #f8fafc;
        }

        .vessel-table tbody tr:hover {
            background-color: #f1f5f9;
        }

        .col-date {
            font-weight: 700;
            color: #1e293b;
            background: #fafafa;
            white-space: nowrap;
        }

        .date-cell {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            white-space: nowrap;
        }

        .edit-link {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            color: #ea580c;
            text-decoration: none;
            line-height: 1;
            padding: 2px;
            border-radius: 4px;
            transition: all 0.15s ease;
        }

        .edit-link:hover {
            color: #c2410c;
            background: #ffedd5;
            transform: scale(1.15);
        }

        .pencil-icon {
            width: 14px;
            height: 14px;
            flex-shrink: 0;
        }

        .col-total-berthed {
            background-color: #f0f4f8;
            font-weight: 700;
            color: #1B365D;
        }

        .col-total-overall {
            background-color: #e6f4ea;
            font-weight: 700;
            color: #137333;
        }

        .col-anchorage {
            font-weight: 700;
            color: #b45309;
        }

        .total-row td {
            background-color: #e2e8f0 !important;
            color: #0f2d59 !important;
            font-weight: 700 !important;
            font-size: 0.84rem !important;
            padding: 7px 4px;
            border-top: 2px solid #94a3b8 !important;
        }

        .avg-row td {
            background-color: #f1f5f9 !important;
            color: #475569 !important;
            font-weight: 700 !important;
            font-size: 0.84rem !important;
            padding: 6px 4px;
        }

        .row-label {
            line-height: 1.25 !important;
            font-size: 0.82rem !important;
            font-weight: 700 !important;
            white-space: normal;
        }

        .sub-unit {
            display: block;
            font-size: 0.74rem;
            font-weight: 600;
            color: #64748b;
            margin-top: 1px;
        }

        /* ─── تنسيق الطباعة الرسمية الوزارية ─── */
        @media print {

            html,
            body {
                height: auto !important;
                overflow: visible !important;
                background: #ffffff !important;
            }

            .fi-topbar,
            .fi-sidebar,
            .fi-header,
            .vessel-single-line-bar,
            .kpi-grid,
            .action-link,
            .edit-link {
                display: none !important;
            }

            .fi-main,
            .fi-page,
            .vessel-matrix-container,
            .vessel-table-wrapper {
                display: block !important;
                width: 100% !important;
                margin: 0 !important;
                padding: 0 !important;
                border: none !important;
                box-shadow: none !important;
                overflow: visible !important;
            }

            .vessel-table {
                width: 100% !important;
                border-collapse: collapse !important;
            }

            .vessel-table th,
            .vessel-table td {
                border: 1px solid #000000 !important;
                font-size: 7.5pt !important;
                padding: 4px 2px !important;
            }

            .print-header {
                display: block !important;
                text-align: center;
                margin-bottom: 15px;
            }
        }

        .print-header {
            display: none;
        }
    </style>

    @php
        $cleanFrom = $this->cleanFromDate;
        $cleanTo = $this->cleanToDate;
    @endphp

    <div class="vessel-matrix-container">
        <!-- ─── ترويسة خاصة بالطباعة الرسمية ─── -->
        <div class="print-header">
            <h2 style="font-size: 15pt; font-weight: bold; margin: 0; color: #1B365D;">جمهورية العراق - وزارة النقل</h2>
            <h3 style="font-size: 13pt; font-weight: bold; margin: 4px 0; color: #1B365D;">الشركة العامة لموانئ العراق
                (GCPI) - قسم الشؤون الملاحية والسيطرة</h3>
            <h4 style="font-size: 12pt; font-weight: bold; margin: 6px 0; text-decoration: underline;">إحصائية حركة
                السفن اليومية (في الانتظار والراسية) في الموانئ العراقية</h4>
            <p style="font-size: 10pt; margin: 4px 0; color: #475569;">للفترة من
                {{ \Carbon\Carbon::parse($cleanFrom)->format('d/m/Y') }} لغاية
                {{ \Carbon\Carbon::parse($cleanTo)->format('d/m/Y') }}
            </p>
        </div>

        <!-- ─── بطاقة الفلترة المعتمدة بالنظام ─── -->
        <div class="vessel-filter-card">
            <div class="vessel-filter-header">
                <div class="filter-panel-title">
                    <x-heroicon-o-funnel
                        style="width: 18px; height: 18px; min-width: 18px; max-width: 18px; flex-shrink: 0;"
                        class="text-primary-600" />
                    <span>تصفية وتحديد الفترة الزمنية للموقف</span>
                </div>

                <!-- أزرار المعاينة والطباعة والـ PDF والإكسل (متجاوبة دون خروج عن الشاشة) -->
                <div class="actions-controls-group">
                    <a href="{{ route('admin.reports.daily-vessel-traffic.preview', ['from_date' => $cleanFrom, 'to_date' => $cleanTo]) }}"
                        target="_blank" class="action-bar-btn print"
                        title="معاينة التقرير بهوية وزارة النقل الرسمية وطباعته">
                        <x-heroicon-o-printer class="w-4 h-4" />
                        معاينة وطباعة رسمية
                    </a>

                    <a href="{{ route('admin.reports.daily-vessel-traffic.pdf', ['from_date' => $cleanFrom, 'to_date' => $cleanTo]) }}"
                        target="_blank" class="action-bar-btn pdf" title="تحميل تقرير PDF جاهز للرفع للوزير">
                        <x-heroicon-o-document-text class="w-4 h-4" />
                        تصدير PDF
                    </a>

                    <a href="{{ route('admin.reports.daily-vessel-traffic.excel', ['from_date' => $cleanFrom, 'to_date' => $cleanTo]) }}"
                        class="action-bar-btn excel" title="تحميل ملف Excel مطابق لملف الإكسل المرفق">
                        <x-heroicon-o-arrow-down-tray class="w-4 h-4" />
                        تصدير Excel
                    </a>
                </div>
            </div>

            <!-- نموذج الفلترة الأصلي المعتمد في Filament (تطابق تام مع كافة شاشات النظام) -->
            <div>
                {{ $this->form }}
            </div>
        </div>

        <!-- ─── بطاقات المؤشرات الإحصائية (KPIs) ─── -->
        @php
            $summary = $this->summary;
            $sums = $summary['sums'];
            $avgs = $summary['averages'];
            $peak = $summary['peak_record'];
        @endphp

        <div class="kpi-grid">
            <div class="kpi-card blue">
                <span class="kpi-title">مجموع فترات المكوث (الراسية)</span>
                <span class="kpi-value">{{ number_format($sums['total_berthed']) }}</span>
                <span class="kpi-sub">سفينة / يوم طيلة الفترة (معدل: {{ $avgs['total_berthed'] }} يومياً)</span>
            </div>

            <div class="kpi-card amber">
                <span class="kpi-title">مجموع فترات الانتظار (المخطاف)</span>
                <span class="kpi-value">{{ number_format($sums['anchorage_waiting']) }}</span>
                <span class="kpi-sub">سفينة / يوم في منطقة الانتظار (معدل: {{ $avgs['anchorage_waiting'] }}
                    يومياً)</span>
            </div>

            <div class="kpi-card emerald">
                <span class="kpi-title">الموقف التراكمي الإجمالي</span>
                <span class="kpi-value">{{ number_format($sums['total_overall']) }}</span>
                <span class="kpi-sub">إجمالي حركة السفن (راسية + انتظار)</span>
            </div>

            <div class="kpi-card purple">
                <span class="kpi-title">أعلى يوم ذروة للحركة</span>
                <span class="kpi-value">{{ $peak ? $peak->total_overall : 0 }}</span>
                <span class="kpi-sub">
                    {{ $peak ? 'تاريخ ' . \Carbon\Carbon::parse($peak->record_date)->format('d/m/Y') . ' (' . $peak->total_berthed . ' راسية + ' . $peak->anchorage_waiting . ' انتظار)' : 'لا توجد بيانات' }}
                </span>
            </div>
        </div>

        @if(($sums['control_vessels_in'] ?? 0) > 0 || ($sums['control_vessels_out'] ?? 0) > 0)
            <div
                style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 12px 18px; margin-bottom: 20px; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 12px; box-shadow: 0 1px 3px rgba(0,0,0,0.03);">
                <div style="display: flex; align-items: center; gap: 10px;">
                    <div
                        style="width: 36px; height: 36px; border-radius: 8px; background: #e0f2fe; display: flex; align-items: center; justify-content: center; color: #0284c7; font-size: 1.2rem;">
                        🧭
                    </div>
                    <div>
                        <strong style="color: #1B365D; font-size: 0.9rem;">إحصائية السيطرة والملاحة البحرية للفترة
                            المحددة:</strong>
                        <div style="font-size: 0.78rem; color: #64748b;">حركة السفن المسجلة لدى السيطرة البحرية (دخول،
                            مغادرة، انتظار)</div>
                    </div>
                </div>
                <div style="display: flex; gap: 12px; align-items: center; flex-wrap: wrap;">
                    <div
                        style="background: #eff6ff; border: 1px solid #bfdbfe; border-radius: 8px; padding: 6px 14px; text-align: center;">
                        <span style="display: block; font-size: 0.74rem; font-weight: 700; color: #1e40af;">سفن
                            الدخول</span>
                        <strong
                            style="font-size: 1.15rem; color: #1d4ed8;">{{ number_format($sums['control_vessels_in'] ?? 0) }}</strong>
                    </div>
                    <div
                        style="background: #fef2f2; border: 1px solid #fecaca; border-radius: 8px; padding: 6px 14px; text-align: center;">
                        <span style="display: block; font-size: 0.74rem; font-weight: 700; color: #991b1b;">سفن
                            المغادرة</span>
                        <strong
                            style="font-size: 1.15rem; color: #b91c1c;">{{ number_format($sums['control_vessels_out'] ?? 0) }}</strong>
                    </div>
                    <div
                        style="background: #fffbeb; border: 1px solid #fde68a; border-radius: 8px; padding: 6px 14px; text-align: center;">
                        <span style="display: block; font-size: 0.74rem; font-weight: 700; color: #92400e;">في الانتظار
                            (مخطاف)</span>
                        <strong
                            style="font-size: 1.15rem; color: #d97706;">{{ number_format($sums['anchorage_waiting'] ?? 0) }}</strong>
                    </div>
                    <div
                        style="background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 8px; padding: 6px 14px; text-align: center;">
                        <span style="display: block; font-size: 0.74rem; font-weight: 700; color: #166534;">إجمالي
                            الحركة</span>
                        <strong
                            style="font-size: 1.15rem; color: #15803d;">{{ number_format(($sums['control_vessels_in'] ?? 0) + ($sums['control_vessels_out'] ?? 0)) }}</strong>
                    </div>
                </div>
            </div>
        @endif

        <!-- ─── الجدول الرئيسي المطابق لملف موقف السفن المعدل ─── -->
        <div class="vessel-table-wrapper">
            <table class="vessel-table">
                <thead>
                    <tr class="main-header">
                        <th rowspan="2" style="width: 10%; min-width: 110px;">التاريخ</th>
                        <th colspan="3" style="width: 18%;">السيطرة البحرية والانتظار</th>
                        <th colspan="2" style="width: 13%;">الميناء النفطي</th>
                        <th colspan="5" style="width: 35%;">السفن الراسية في الموانئ والأرصفة</th>
                        <th rowspan="2" style="width: 11%;">إجمالي السفن<br>الراسية</th>
                        <th rowspan="2" style="width: 13%;">الموقف الإجمالي<br>(راسية + انتظار)</th>
                    </tr>
                    <tr class="sub-header">
                        <th style="width: 6%;">سفن<br>الدخول</th>
                        <th style="width: 6%;">سفن<br>المغادرة</th>
                        <th style="width: 6%;">منطقة الانتظار<br>(مخطاف)</th>
                        <th style="width: 6.5%;">الميناء<br>النفطي</th>
                        <th style="width: 6.5%;">منطقة الانتظار</th>
                        <th style="width: 7%;">أم قصر ش<br><span
                                style="font-size: 0.72rem; opacity: 0.9;">(UMQ-N)</span></th>
                        <th style="width: 7%;">أم قصر ج<br><span
                                style="font-size: 0.72rem; opacity: 0.9;">(UMQ-S)</span></th>
                        <th style="width: 7%;">خور الزبير<br><span style="font-size: 0.72rem; opacity: 0.9;">(KZ)</span>
                        </th>
                        <th style="width: 7%;">أبو فلوس<br><span style="font-size: 0.72rem; opacity: 0.9;">(ABF)</span>
                        </th>
                        <th style="width: 7%;">رصيف الغاز<br><span
                                style="font-size: 0.72rem; opacity: 0.9;">(LPG)</span></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($this->records as $r)
                        <tr>
                            <td class="col-date">
                                <div class="date-cell">
                                    <span>{{ \Carbon\Carbon::parse($r->record_date)->format('d/m/Y') }}</span>
                                    <a href="{{ \App\Filament\Resources\DailyVesselStatusResource::getUrl('edit', ['record' => $r->id]) }}"
                                        class="edit-link" title="تعديل قيد هذا اليوم">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"
                                            class="pencil-icon">
                                            <path
                                                d="m2.695 14.762-1.262 3.155a.5.5 0 0 0 .65.65l3.155-1.262a4 4 0 0 0 1.343-.886L17.5 5.501a2.121 2.121 0 0 0-3-3L3.58 13.419a4 4 0 0 0-.885 1.343Z" />
                                        </svg>
                                    </a>
                                </div>
                            </td>
                            <td style="font-weight: 700; color: #1e40af; background: #f8fafc;">
                                {{ (int) $r->control_vessels_in }}
                            </td>
                            <td style="font-weight: 700; color: #991b1b; background: #f8fafc;">
                                {{ (int) $r->control_vessels_out }}
                            </td>
                            <td class="col-anchorage" style="background: #fffbeb;">{{ (int) $r->anchorage_waiting }}</td>
                            <td>{{ (int) $r->oil_port_berth }}</td>
                            <td class="col-anchorage" style="background: #fffbeb;">{{ (int) $r->oil_port_waiting }}</td>
                            <td>{{ (int) $r->um_qasr_north }}</td>
                            <td>{{ (int) $r->um_qasr_south }}</td>
                            <td>{{ (int) $r->khor_al_zubair }}</td>
                            <td>{{ (int) $r->abu_flus }}</td>
                            <td>{{ (int) $r->lpg_berth }}</td>
                            <td class="col-total-berthed">{{ (int) $r->total_berthed }}</td>
                            <td class="col-total-overall">{{ (int) $r->total_overall }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="13" style="padding: 30px; color: #94a3b8; font-size: 1rem;">
                                لا توجد سجلات موقف يومي مسجلة للفترة المحددة (من
                                {{ \Carbon\Carbon::parse($fromDate)->format('d/m/Y') }} إلى
                                {{ \Carbon\Carbon::parse($toDate)->format('d/m/Y') }}).<br>
                                <a href="{{ \App\Filament\Resources\DailyVesselStatusResource::getUrl('create') }}"
                                    style="color: #1B365D; font-weight: bold; text-decoration: underline; margin-top: 8px; display: inline-block;">
                                    انقر هنا لتسجيل موقف جديد
                                </a>
                            </td>
                        </tr>
                    @endforelse

                    @if($this->records->isNotEmpty())
                        <!-- سطر المجموع الكلي (سفينة/يوم) -->
                        <tr class="total-row">
                            <td class="row-label">
                                مجموع فترات المكوث
                                <span class="sub-unit">(سفينة/يوم)</span>
                            </td>
                            <td style="color: #1e40af !important;">{{ number_format($sums['control_vessels_in']) }}</td>
                            <td style="color: #991b1b !important;">{{ number_format($sums['control_vessels_out']) }}</td>
                            <td style="color: #b45309 !important;">{{ number_format($sums['anchorage_waiting']) }}</td>
                            <td>{{ number_format($sums['oil_port_berth']) }}</td>
                            <td style="color: #b45309 !important;">{{ number_format($sums['oil_port_waiting']) }}</td>
                            <td>{{ number_format($sums['um_qasr_north']) }}</td>
                            <td>{{ number_format($sums['um_qasr_south']) }}</td>
                            <td>{{ number_format($sums['khor_al_zubair']) }}</td>
                            <td>{{ number_format($sums['abu_flus']) }}</td>
                            <td>{{ number_format($sums['lpg_berth']) }}</td>
                            <td class="col-total-berthed">{{ number_format($sums['total_berthed']) }}</td>
                            <td class="col-total-overall">{{ number_format($sums['total_overall']) }}</td>
                        </tr>

                        <!-- سطر المعدل اليومي للفترة -->
                        <tr class="avg-row">
                            <td class="row-label">
                                المعدل اليومي للفترة
                                <span class="sub-unit">(سفينة/يوم)</span>
                            </td>
                            <td style="font-weight: 700; color: #1e40af;">{{ $avgs['control_vessels_in'] }}</td>
                            <td style="font-weight: 700; color: #991b1b;">{{ $avgs['control_vessels_out'] }}</td>
                            <td style="font-weight: 700; color: #b45309;">{{ $avgs['anchorage_waiting'] }}</td>
                            <td>{{ $avgs['oil_port_berth'] }}</td>
                            <td style="font-weight: 700; color: #b45309;">{{ $avgs['oil_port_waiting'] }}</td>
                            <td>{{ $avgs['um_qasr_north'] }}</td>
                            <td>{{ $avgs['um_qasr_south'] }}</td>
                            <td>{{ $avgs['khor_al_zubair'] }}</td>
                            <td>{{ $avgs['abu_flus'] }}</td>
                            <td>{{ $avgs['lpg_berth'] }}</td>
                            <td class="col-total-berthed">{{ $avgs['total_berthed'] }}</td>
                            <td class="col-total-overall">{{ $avgs['total_overall'] }}</td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>

        <!-- ─── قسم الملاحظات الرسمية التشغيلية ─── -->
        <div
            style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px; padding: 12px 16px; font-size: 0.85rem; color: #475569;">
            <strong style="color: #1B365D;">📌 ملاحظات تشغيلية واعتصامية:</strong>
            <ul style="margin: 6px 0 0 20px; list-style-type: square; line-height: 1.6;">
                <li>موقف السفن في منطقة الانتظار (المخطاف) يُسجل السفن التي تروم الدخول بانتظار توفر الأرصفة أو استكمال
                    الموافقات الرسمية.</li>
                <li>يُمثل سطر <strong>مجموع فترات المكوث (سفينة/يوم)</strong> الحاصل التراكمي لعدد السفن في الأيام، وهو
                    المعيار الملاحي الدولي المعتمد لحساب كفاءة وأشغال الموانئ.</li>
                <li>الموقف الإجمالي يُحتسب آلياً عبر جمع (السفن الراسية على الأرصفة + سفن المخطاف).</li>
            </ul>
        </div>
    </div>
</x-filament-panels::page>