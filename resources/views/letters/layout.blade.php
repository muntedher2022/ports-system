<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'كتاب رسمي - الشركة العامة لموانئ العراق' }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@400;500;700;800;900&family=Amiri:wght@400;700&display=swap" rel="stylesheet">
    <style>
        @page {
            size: A4 portrait;
            margin: 10mm 15mm 12mm 15mm;
        }

        * {
            box-sizing: border-box;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        body {
            direction: rtl;
            font-family: 'Amiri', 'Tajawal', serif, sans-serif;
            background-color: #f1f5f9;
            color: #000000;
            margin: 0;
            padding: 20px 0;
            font-size: 14pt;
            line-height: 1.6;
        }

        /* شريط الإجراءات والتحكم العلوي (يختفي عند الطباعة) */
        .no-print-bar {
            width: 210mm;
            max-width: 95%;
            margin: 0 auto 15px auto;
            background: #ffffff;
            padding: 12px 20px;
            border-radius: 12px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -2px rgba(0, 0, 0, 0.1);
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 15px;
            font-family: 'Tajawal', sans-serif;
        }

        .no-print-bar .title {
            font-size: 13pt;
            font-weight: 700;
            color: #1e293b;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .no-print-bar .badge {
            font-size: 9.5pt;
            padding: 3px 10px;
            border-radius: 9999px;
            font-weight: 600;
        }

        .badge-memo {
            background-color: #e0f2fe;
            color: #0369a1;
            border: 1px solid #bae6fd;
        }

        .badge-central {
            background-color: #fef3c7;
            color: #b45309;
            border: 1px solid #fde68a;
        }

        .no-print-bar .btn-group {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .btn {
            font-family: 'Tajawal', sans-serif;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 8px 16px;
            font-size: 10.5pt;
            font-weight: 600;
            border-radius: 8px;
            text-decoration: none;
            cursor: pointer;
            transition: all 0.2s ease;
            border: none;
        }

        .btn-print {
            background: #1e40af;
            color: #ffffff;
        }
        .btn-print:hover {
            background: #1d4ed8;
        }

        .btn-docx {
            background: #0284c7;
            color: #ffffff;
        }
        .btn-docx:hover {
            background: #0369a1;
        }

        .btn-back {
            background: #f1f5f9;
            color: #475569;
            border: 1px solid #cbd5e1;
        }
        .btn-back:hover {
            background: #e2e8f0;
            color: #1e293b;
        }

        /* ورقة A4 الرسمية */
        .a4-sheet {
            width: 210mm;
            min-height: 297mm;
            margin: 0 auto;
            background: #ffffff;
            padding: 12mm 18mm 15mm 18mm;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 8px 10px -6px rgba(0, 0, 0, 0.1);
            position: relative;
            box-sizing: border-box;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        /* ─── ترويسة الكتاب (الهيدر) مطابق للنموذج الأصلي ─── */
        .letter-header {
            width: 100%;
            margin-bottom: 12px;
        }

        .header-columns {
            display: flex;
            justify-content: space-between;
            align-items: center;
            width: 100%;
        }

        .header-ar {
            width: 38%;
            text-align: right;
            font-family: 'Amiri', serif;
            font-size: 13pt;
            font-weight: 700;
            line-height: 1.35;
            color: #000;
        }

        .header-logo {
            width: 24%;
            text-align: center;
        }

        .header-logo img {
            max-width: 130px;
            max-height: 80px;
            object-fit: contain;
        }

        .header-en {
            width: 38%;
            text-align: left;
            direction: ltr;
            font-family: 'Times New Roman', serif;
            font-size: 10.5pt;
            font-weight: 700;
            line-height: 1.25;
            color: #000;
        }

        .header-meta {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 12px;
            font-size: 13pt;
            font-weight: 700;
            color: #000;
        }

        .memo-divider {
            text-align: center;
            margin: 10px 0 14px 0;
            font-size: 12.5pt;
            font-weight: 700;
            letter-spacing: 2px;
            color: #000;
            border-top: 2px solid #000;
            border-bottom: 2px solid #000;
            padding: 3px 0;
        }

        /* ─── متن الكتاب والقواعد الحرفية للخطوط ─── */
        .letter-body {
            flex: 1;
            color: #000;
        }

        /* التوجيه: السيد المدير العام (14 غامق) */
        .addressee {
            font-size: 14pt;
            font-weight: bold;
            margin-bottom: 12px;
            line-height: 1.5;
            color: #000;
        }

        /* العنوان: 14 غامق في المنتصف */
        .subject {
            font-size: 14pt;
            font-weight: bold;
            text-align: center;
            margin: 14px 0 16px 0;
            line-height: 1.5;
            text-decoration: underline;
            text-underline-offset: 4px;
            color: #000;
        }

        /* التحية: 14 */
        .salutation {
            font-size: 14pt;
            font-weight: normal;
            margin-bottom: 10px;
            color: #000;
        }

        /* المتن: 14 */
        .paragraph {
            font-size: 14pt;
            font-weight: normal;
            text-align: justify;
            text-justify: inter-word;
            margin-bottom: 12px;
            line-height: 1.8;
            color: #000;
        }

        /* عبارة الختام: تفضلكم بالاطلاع وامركم مع التقدير ... (14 في المنتصف) */
        .closing {
            font-size: 14pt;
            font-weight: normal;
            text-align: center;
            margin: 14px 0 18px 0;
            color: #000;
        }

        /* الجداول الرسمية في الموقف الشهري */
        .official-table {
            width: 100%;
            margin: 16px auto;
            border-collapse: collapse;
            font-size: 13.5pt;
            border: 2px solid #000;
        }

        .official-table th, .official-table td {
            border: 1.5px solid #000;
            padding: 7px 10px;
            text-align: center;
            font-weight: 700;
        }

        .official-table th {
            background-color: #f8fafc;
            font-size: 14pt;
        }

        .official-table tr.total-row {
            background-color: #f1f5f9;
        }

        /* المرافقات: حجم 12 */
        .attachments-section {
            margin-top: 14px;
            font-size: 12pt;
            line-height: 1.6;
            color: #000;
        }

        .attachments-title {
            font-size: 12pt;
            font-weight: bold;
            text-decoration: underline;
            margin-bottom: 4px;
        }

        /* التوقيع الرسمي */
        .signature-section {
            margin-top: 25px;
            display: flex;
            justify-content: flex-end;
            text-align: center;
        }

        .signature-box {
            min-width: 250px;
            font-size: 14pt;
            font-weight: bold;
            line-height: 1.5;
            color: #000;
        }

        .signature-name {
            font-size: 14.5pt;
            font-weight: 900;
            margin-bottom: 2px;
        }

        .signature-date {
            margin-top: 6px;
            font-size: 13pt;
        }

        /* صورة منه إلى: حجم 12 */
        .copies-section {
            margin-top: 24px;
            border-top: 1px dashed #64748b;
            padding-top: 8px;
            font-size: 12pt;
            line-height: 1.5;
            color: #1e293b;
        }

        .copies-title {
            font-size: 12pt;
            font-weight: bold;
            margin-bottom: 4px;
        }

        /* ─── الفوتر الرسمي مطابق للنموذج الأصلي ─── */
        .letter-footer {
            width: 100%;
            margin-top: 15px;
            padding-top: 6px;
            border-top: 1px solid #94a3b8;
            font-family: 'Tajawal', 'Segoe UI', Tahoma, sans-serif;
            font-size: 9.5pt;
            color: #334155;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .memo-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            direction: ltr;
        }

        .central-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 9pt;
            line-height: 1.35;
        }

        .central-footer .col-left {
            text-align: left;
            direction: ltr;
        }

        .central-footer .col-center {
            text-align: center;
        }

        .central-footer .col-right {
            text-align: right;
            direction: ltr;
            font-weight: 600;
        }

        @media print {
            body {
                background: transparent;
                padding: 0;
                margin: 0;
            }
            .no-print-bar {
                display: none !important;
            }
            .a4-sheet {
                box-shadow: none;
                margin: 0;
                padding: 0;
                width: 100%;
                min-height: auto;
            }
        }
    </style>
    @yield('styles')
</head>
<body>

    <!-- شريط الأدوات العلوي -->
    <div class="no-print-bar">
        <div class="title">
            <span>{{ $title }}</span>
            @if(!empty($is_central_out))
                <span class="badge badge-central">صادر مركزي (يسحب فارغاً للختم)</span>
            @else
                <span class="badge badge-memo">مذكرة داخلية رسمية</span>
            @endif
        </div>
        <div class="btn-group">
            <button onclick="window.print()" class="btn btn-print">
                <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4H7v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                طباعة A4 / حفظ PDF
            </button>
            <a href="{{ route('admin.official-letters.docx', ['type' => $current_type, 'fiscal_year_id' => $fiscal_year?->id, 'month_id' => $month?->id, 'memo_number' => $memo_number, 'memo_date' => $memo_date]) }}" class="btn btn-docx">
                <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                تحميل ملف Word (.docx)
            </a>
            <a href="{{ url('/admin/official-letters') }}" class="btn btn-back">
                عودة للقائمة
            </a>
        </div>
    </div>

    <!-- ورقة A4 -->
    <div class="a4-sheet">
        @yield('content')
    </div>

</body>
</html>
