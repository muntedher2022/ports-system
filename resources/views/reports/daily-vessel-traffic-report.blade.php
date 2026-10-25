<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>إحصائية حركة السفن اليومية - الشركة العامة لموانئ العراق</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@400;500;700;800;900&display=swap" rel="stylesheet">
    <style>
        @page {
            size: A4 landscape;
            margin: 8mm 8mm 8mm 8mm;
        }
        * {
            box-sizing: border-box;
            font-family: 'Tajawal', -apple-system, BlinkMacSystemFont, 'Segoe UI', Tahoma, sans-serif !important;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }
        body {
            direction: rtl;
            font-size: 8.5pt;
            color: #0f172a;
            background: #e2e8f0;
            margin: 0;
            padding: 20px 0;
        }

        /* ─── شريط التحكم العلوي (يختفي عند الطباعة) ─── */
        .no-print-bar {
            width: 280mm;
            max-width: 96%;
            margin: 0 auto 16px auto;
            background: #ffffff;
            padding: 12px 24px;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.08);
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
        }
        .no-print-bar .title {
            font-size: 1.05rem;
            font-weight: 800;
            color: #1B365D;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .btn-group {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 8px 16px;
            font-size: 0.85rem;
            font-weight: 700;
            border-radius: 8px;
            cursor: pointer;
            text-decoration: none;
            border: none;
            transition: all 0.2s;
        }
        .btn-print { background: #1B365D; color: #fff; }
        .btn-print:hover { background: #0f2d59; }
        .btn-pdf { background: #b91c1c; color: #fff; }
        .btn-pdf:hover { background: #991b1b; }
        .btn-excel { background: #059669; color: #fff; }
        .btn-excel:hover { background: #047857; }
        .btn-back { background: #f1f5f9; color: #475569; border: 1px solid #cbd5e1; }
        .btn-back:hover { background: #e2e8f0; color: #0f172a; }

        /* ─── صفحة التقرير الرسمية ─── */
        .report-sheet {
            width: 280mm;
            max-width: 96%;
            margin: 0 auto;
            background: #ffffff;
            padding: 16mm 14mm;
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
            border-radius: 8px;
            box-sizing: border-box;
        }

        /* ─── الترويسة الرسمية الوزارية ─── */
        .official-header {
            width: 100%;
            border-bottom: 2px solid #1B365D;
            padding-bottom: 10px;
            margin-bottom: 12px;
            border-collapse: collapse;
        }
        .official-header td { vertical-align: middle; }
        .header-right {
            text-align: right;
            font-size: 8.5pt;
            font-weight: 700;
            color: #1B365D;
            line-height: 1.4;
        }
        .header-center {
            text-align: center;
        }
        .header-center h1 {
            margin: 0;
            font-size: 13pt;
            font-weight: 900;
            color: #1B365D;
        }
        .header-center .period-text {
            font-size: 10pt;
            font-weight: 700;
            color: #475569;
            margin-top: 3px;
        }
        .header-left {
            text-align: left;
            font-size: 8pt;
            color: #64748b;
            line-height: 1.4;
        }

        /* ─── جدول الإحصائية طبق الأصل ─── */
        .vessel-table {
            width: 100%;
            border-collapse: collapse;
            text-align: center;
            font-size: 8pt;
            margin-bottom: 12px;
        }
        .vessel-table th, .vessel-table td {
            border: 1px solid #cbd5e1;
            padding: 4.5px 3px;
        }
        .vessel-table thead tr.main-th th {
            background-color: #1B365D !important;
            color: #ffffff !important;
            font-weight: 800;
            font-size: 8.5pt;
        }
        .vessel-table thead tr.sub-th th {
            background-color: #24497D !important;
            color: #ffffff !important;
            font-weight: 700;
            font-size: 7.8pt;
        }
        .vessel-table tbody tr:nth-child(even) { background-color: #f8fafc; }
        .col-berthed-total { background-color: #f0f4f8 !important; font-weight: 800; color: #1B365D; }
        .col-overall-total { background-color: #e6f4ea !important; font-weight: 800; color: #137333; }
        
        .total-row td {
            background-color: #e2e8f0 !important;
            color: #0f2d59 !important;
            font-weight: 800 !important;
            font-size: 8.5pt;
            border-top: 2px solid #64748b !important;
        }
        .avg-row td {
            background-color: #f1f5f9 !important;
            color: #334155 !important;
            font-weight: 700 !important;
            font-size: 8pt;
        }

        .notes-box {
            border: 1px solid #cbd5e1;
            background: #f8fafc;
            border-radius: 6px;
            padding: 8px 12px;
            font-size: 8pt;
            color: #475569;
            margin-top: 10px;
            line-height: 1.5;
        }

        /* ─── إعدادات الطباعة الدقيقة ─── */
        @media print {
            body {
                background: #ffffff !important;
                padding: 0 !important;
                margin: 0 !important;
            }
            .no-print-bar { display: none !important; }
            .report-sheet {
                width: 100% !important;
                max-width: 100% !important;
                margin: 0 !important;
                padding: 0 !important;
                box-shadow: none !important;
                border-radius: 0 !important;
            }
            .vessel-table th, .vessel-table td {
                border: 1px solid #000000 !important;
                font-size: 7.5pt !important;
                padding: 3.5px 2px !important;
            }
            .vessel-table thead tr.main-th th {
                background-color: #1B365D !important;
                color: #ffffff !important;
            }
            .vessel-table thead tr.sub-th th {
                background-color: #24497D !important;
                color: #ffffff !important;
            }
            .total-row td {
                background-color: #e2e8f0 !important;
                color: #000000 !important;
            }
        }
    </style>
</head>
<body>

    <!-- ─── شريط الأوامر العلوي (معاينة وطباعة وتصدير) ─── -->
    <div class="no-print-bar">
        <div class="title">
            <span>🚢</span>
            <span>معاينة الموقف اليومي لحركة السفن (طباعة رسمية A4 أفقي)</span>
        </div>
        <div class="btn-group">
            <button onclick="window.print()" class="btn btn-print">
                🖨️ طباعة الآن (Print)
            </button>
            <a href="{{ route('admin.reports.daily-vessel-traffic.pdf', ['from_date' => $fromDate, 'to_date' => $toDate]) }}" class="btn btn-pdf" target="_blank">
                📄 تحميل ملف PDF
            </a>
            <a href="{{ route('admin.reports.daily-vessel-traffic.excel', ['from_date' => $fromDate, 'to_date' => $toDate]) }}" class="btn btn-excel">
                📊 تصدير Excel مطابَق للأصل
            </a>
            <a href="{{ route('filament.admin.pages.daily-vessel-status-matrix') }}" class="btn btn-back">
                ⬅️ العودة للمصفوفة
            </a>
        </div>
    </div>

    <!-- ─── صفحة التقرير الرسمية ─── -->
    <div class="report-sheet">
        <!-- ترويسة التقرير الرسمية -->
        <table class="official-header">
            <tr>
                <td style="width: 25%;" class="header-right">
                    جمهورية العراق<br>
                    وزارة النقل<br>
                    الشركة العامة لموانئ العراق (GCPI)<br>
                    قسم الشؤون الملاحية والسيطرة
                </td>
                <td style="width: 50%;" class="header-center">
                    <h1>إحصائية حركة السفن اليومية (في الانتظار والراسية) في الموانئ العراقية</h1>
                    <div class="period-text">للفترة من {{ $fromFormatted }} لغاية {{ $toFormatted }}</div>
                </td>
                <td style="width: 25%;" class="header-left">
                    تاريخ التقرير: {{ now()->format('Y/m/d H:i') }}<br>
                    الصفة: موقف رسمي وزاري<br>
                    نظام إدارة الموانئ الموحد
                </td>
            </tr>
        </table>

        <!-- الجدول الرئيسي للموقف -->
        <table class="vessel-table">
            <thead>
                <tr class="main-th">
                    <th rowspan="2" style="width: 9%;">التاريخ</th>
                    <th colspan="2" style="width: 17%;">الميناء النفطي</th>
                    <th colspan="6" style="width: 50%;">منطقة الانتظار والسفن الراسية في الموانئ والأرصفة</th>
                    <th rowspan="2" style="width: 11%;">إجمالي السفن<br>الراسية</th>
                    <th rowspan="2" style="width: 13%;">الموقف الإجمالي<br>(راسية + انتظار)</th>
                </tr>
                <tr class="sub-th">
                    <th style="width: 9%;">منطقة انتظار<br>الميناء النفطي</th>
                    <th style="width: 8%;">الميناء<br>النفطي</th>
                    <th style="width: 9%;">منطقة الانتظار<br>(مخطاف)</th>
                    <th style="width: 8%;">أم قصر ش<br>(UMQ-N)</th>
                    <th style="width: 8%;">أم قصر ج<br>(UMQ-S)</th>
                    <th style="width: 8%;">خور الزبير<br>(KZ)</th>
                    <th style="width: 8%;">أبو فلوس<br>(ABF)</th>
                    <th style="width: 9%;">رصيف الغاز<br>(LPG)</th>
                </tr>
            </thead>
            <tbody>
                @forelse($records as $r)
                    <tr>
                        <td style="font-weight: bold; background-color: #fafafa;">{{ \Carbon\Carbon::parse($r->record_date)->format('d/m/Y') }}</td>
                        <td>{{ (int) $r->oil_port_waiting }}</td>
                        <td>{{ (int) $r->oil_port_berth }}</td>
                        <td style="font-weight: bold; color: #b45309;">{{ (int) $r->anchorage_waiting }}</td>
                        <td>{{ (int) $r->um_qasr_north }}</td>
                        <td>{{ (int) $r->um_qasr_south }}</td>
                        <td>{{ (int) $r->khor_al_zubair }}</td>
                        <td>{{ (int) $r->abu_flus }}</td>
                        <td>{{ (int) $r->lpg_berth }}</td>
                        <td class="col-berthed-total">{{ (int) $r->total_berthed }}</td>
                        <td class="col-overall-total">{{ (int) $r->total_overall }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="11" style="padding: 20px; color: #64748b;">لا توجد سجلات مسجلة لهذه الفترة</td>
                    </tr>
                @endforelse

                @if($records->isNotEmpty())
                    <!-- سطر مجموع فترات المكوث (سفينة/يوم) -->
                    <tr class="total-row">
                        <td>مجموع فترات المكوث (سفينة/يوم)</td>
                        <td>{{ number_format($sums['oil_port_waiting']) }}</td>
                        <td>{{ number_format($sums['oil_port_berth']) }}</td>
                        <td>{{ number_format($sums['anchorage_waiting']) }}</td>
                        <td>{{ number_format($sums['um_qasr_north']) }}</td>
                        <td>{{ number_format($sums['um_qasr_south']) }}</td>
                        <td>{{ number_format($sums['khor_al_zubair']) }}</td>
                        <td>{{ number_format($sums['abu_flus']) }}</td>
                        <td>{{ number_format($sums['lpg_berth']) }}</td>
                        <td>{{ number_format($sums['total_berthed']) }}</td>
                        <td style="color: #137333 !important;">{{ number_format($sums['total_overall']) }}</td>
                    </tr>

                    <!-- سطر المعدل اليومي للفترة -->
                    <tr class="avg-row">
                        <td>المعدل اليومي للفترة (سفينة/يوم)</td>
                        <td>{{ $averages['oil_port_waiting'] }}</td>
                        <td>{{ $averages['oil_port_berth'] }}</td>
                        <td>{{ $averages['anchorage_waiting'] }}</td>
                        <td>{{ $averages['um_qasr_north'] }}</td>
                        <td>{{ $averages['um_qasr_south'] }}</td>
                        <td>{{ $averages['khor_al_zubair'] }}</td>
                        <td>{{ $averages['abu_flus'] }}</td>
                        <td>{{ $averages['lpg_berth'] }}</td>
                        <td style="font-weight: bold; color: #1B365D;">{{ $averages['total_berthed'] }}</td>
                        <td style="font-weight: bold; color: #137333;">{{ $averages['total_overall'] }}</td>
                    </tr>
                @endif
            </tbody>
        </table>

        <!-- ملاحظات التقرير -->
        <div class="notes-box">
            <strong>📌 ملاحظات تشغيلية واعتصامية:</strong><br>
            • يُمثل سطر <strong>مجموع فترات المكوث (سفينة/يوم)</strong> الحاصل التراكمي لعدد السفن في الأيام، وهو المعيار الملاحي الدولي المعتمد لحساب كفاءة وأشغال الموانئ.<br>
            • الموقف الإجمالي يُحتسب آلياً عبر جمع: السفن الراسية في الموانئ والأرصفة + سفن منطقة الانتظار (المخطاف).
        </div>
    </div>

</body>
</html>
