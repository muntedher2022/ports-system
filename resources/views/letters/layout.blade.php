<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'كتاب رسمي - الشركة العامة لموانئ العراق' }}</title>
    <style>
        @page {
            size: A4 portrait;
            margin: 0;
        }

        * {
            box-sizing: border-box;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
            color-adjust: exact !important;
        }

        html, body {
            margin: 0;
            padding: 0;
            direction: rtl;
            font-family: 'Times New Roman', Times, serif;
            background-color: #cbd5e1;
            color: #000000;
        }

        body {
            padding: 20px 0;
        }

        /* شريط الأدوات والتحكم العلوي (يختفي عند الطباعة) */
        .no-print-bar {
            width: 210mm;
            max-width: 95%;
            margin: 0 auto 16px auto;
            background: #ffffff;
            padding: 12px 20px;
            border-radius: 12px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -2px rgba(0, 0, 0, 0.1);
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 15px;
            font-family: 'Times New Roman', Times, serif;
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
            font-size: 10pt;
            padding: 4px 12px;
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
            gap: 8px;
            flex-wrap: wrap;
        }

        .btn {
            font-family: 'Times New Roman', Times, serif;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 7px 14px;
            font-size: 11pt;
            font-weight: 700;
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

        .btn-print-yellow {
            background: #d97706;
            color: #ffffff;
        }
        .btn-print-yellow:hover {
            background: #b45309;
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

        /* ─── ورقة A4 الرسمية ─── */
        .a4-sheet {
            width: 210mm;
            height: 297mm;
            min-height: 297mm;
            max-height: 297mm;
            margin: 0 auto;
            background-color: #ffffff;
            background-repeat: no-repeat;
            background-position: top left;
            background-size: 210mm 297mm;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.2), 0 8px 10px -6px rgba(0, 0, 0, 0.1);
            position: relative;
            box-sizing: border-box;
            overflow: hidden;
            font-family: 'Times New Roman', Times, serif;
        }

        /* مذكرة داخلية (فورمة داخلية.pdf) */
        .sheet-internal {
            background-image: url('/images/letters/internal_form.png');
            background-image: url('{{ asset('images/letters/internal_form.png') }}');
            padding: 57mm 22mm 38mm 22mm;
        }

        /* كتاب رسمي صادر مركزي - الورقة الأولى (فورمة.pdf) */
        .sheet-official {
            background-image: url('/images/letters/official_form.png');
            background-image: url('{{ asset('images/letters/official_form.png') }}');
            padding: 66mm 22mm 26mm 22mm;
        }

        /* كتاب رسمي صادر مركزي - الورقة الثانية المروّسة الصفراء (خالية من الفورمة) */
        .sheet-preprinted {
            background-color: #ffffff;
            background-image: none !important;
            padding: 66mm 22mm 26mm 22mm;
        }

        /* المتابعة المركزية والعمليات في هيدر الورقة الصفراء المروّسة */
        .preprinted-header-dept {
            position: absolute;
            top: 27.5mm;
            right: 14mm;
            width: 78mm;
            text-align: center;
            font-family: 'Times New Roman', Times, serif;
            font-size: 16pt;
            font-weight: bold;
            color: #000000;
            line-height: 1.25;
            direction: rtl;
        }

        /* فاصل توضيحي بين الورقتين على الشاشة */
        .sheet-divider {
            width: 210mm;
            max-width: 95%;
            margin: 25px auto 18px auto;
            text-align: center;
            position: relative;
        }
        .sheet-divider::before {
            content: "";
            position: absolute;
            top: 50%;
            left: 0;
            right: 0;
            height: 2px;
            background: #94a3b8;
            z-index: 1;
        }
        .sheet-divider-badge {
            position: relative;
            z-index: 2;
            display: inline-block;
            background: #fef3c7;
            color: #92400e;
            border: 2px solid #f59e0b;
            padding: 6px 20px;
            border-radius: 9999px;
            font-family: 'Times New Roman', Times, serif;
            font-size: 12pt;
            font-weight: bold;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }

        /* كتابة الرقم والتاريخ فقط في المذكرات الداخلية */
        .internal-memo-num {
            position: absolute;
            top: 34.6mm;
            right: 40mm;
            font-family: 'Times New Roman', Times, serif;
            font-size: 14pt;
            font-weight: bold;
            color: #000000;
            line-height: 1;
            direction: rtl;
        }

        .internal-memo-date {
            position: absolute;
            top: 41.8mm;
            right: 36mm;
            font-family: 'Times New Roman', Times, serif;
            font-size: 13.5pt;
            font-weight: bold;
            color: #000000;
            line-height: 1;
            direction: rtl;
        }

        /* حاوية المحتوى */
        .letter-content {
            width: 100%;
            height: 100%;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            box-sizing: border-box;
        }

        .content-main {
            flex: 1;
        }

        /* القواعد الصارمة للخطوط وأحجامها (Times New Roman) */
        .addressee {
            font-family: 'Times New Roman', Times, serif;
            font-size: 14pt;
            font-weight: bold;
            margin-bottom: 12px;
            line-height: 1.5;
            color: #000;
        }

        .subject {
            font-family: 'Times New Roman', Times, serif;
            font-size: 14pt;
            font-weight: bold;
            text-align: center;
            margin: 12px 0 16px 0;
            line-height: 1.6;
            text-decoration: underline;
            text-underline-offset: 4px;
            color: #000;
        }

        .salutation {
            font-family: 'Times New Roman', Times, serif;
            font-size: 14pt;
            font-weight: normal;
            margin-bottom: 8px;
            color: #000;
        }

        .paragraph {
            font-family: 'Times New Roman', Times, serif;
            font-size: 14pt;
            font-weight: normal;
            text-align: justify;
            text-justify: inter-word;
            margin-bottom: 12px;
            line-height: 1.8;
            color: #000;
        }

        .closing {
            font-family: 'Times New Roman', Times, serif;
            font-size: 14pt;
            font-weight: normal;
            text-align: center;
            margin: 14px 0 16px 0;
            color: #000;
        }

        .attachments-section {
            font-family: 'Times New Roman', Times, serif;
            margin-top: 10px;
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

        .signature-spacer-2lines {
            height: 2.8em;
        }

        .signature-section {
            display: flex;
            justify-content: flex-end;
            text-align: center;
        }

        .signature-box {
            min-width: 250px;
            font-family: 'Times New Roman', Times, serif;
            font-size: 14pt;
            font-weight: bold;
            line-height: 1.45;
            color: #000;
        }

        .signature-name {
            font-size: 14pt;
            font-weight: bold;
            margin-bottom: 2px;
        }

        .signature-date {
            margin-top: 4px;
            font-size: 13pt;
            font-weight: bold;
        }

        .copies-section {
            font-family: 'Times New Roman', Times, serif;
            margin-top: 14px;
            padding-top: 4px;
            font-size: 12pt;
            line-height: 1.5;
            color: #000;
        }

        .copies-title {
            font-size: 12pt;
            font-weight: bold;
            margin-bottom: 4px;
        }

        .official-table {
            width: 100%;
            margin: 12px auto;
            border-collapse: collapse;
            font-family: 'Times New Roman', Times, serif;
            font-size: 13.5pt;
            border: 2px solid #000;
        }

        .official-table th, .official-table td {
            border: 1.5px solid #000;
            padding: 6px 10px;
            text-align: center;
            font-weight: 700;
            color: #000;
        }

        .official-table th {
            background-color: #f8fafc;
            font-size: 14pt;
        }

        .official-table tr.total-row {
            background-color: #f1f5f9;
        }

        @media print {
            body {
                background: transparent !important;
                padding: 0 !important;
                margin: 0 !important;
            }
            .no-print-bar {
                display: none !important;
            }
            .sheet-divider {
                display: none !important;
            }
            .a4-sheet {
                box-shadow: none !important;
                margin: 0 !important;
                width: 210mm !important;
                height: 297mm !important;
                min-height: 297mm !important;
                max-height: 297mm !important;
                page-break-after: avoid !important;
                page-break-inside: avoid !important;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }
            .sheet-preprinted {
                page-break-before: always !important;
                break-before: page !important;
            }
            body.print-only-official .sheet-preprinted {
                display: none !important;
            }
            body.print-only-preprinted .sheet-official {
                display: none !important;
            }
        }
    </style>
    <script>
        function printLetter(mode) {
            document.body.classList.remove('print-only-official', 'print-only-preprinted');
            if (mode === 'official') {
                document.body.classList.add('print-only-official');
            } else if (mode === 'preprinted') {
                document.body.classList.add('print-only-preprinted');
            }
            window.print();
            setTimeout(() => {
                document.body.classList.remove('print-only-official', 'print-only-preprinted');
            }, 1000);
        }
    </script>
    @yield('styles')
</head>
<body>

    <!-- شريط الأدوات العلوي -->
    <div class="no-print-bar">
        <div class="title">
            <span>{{ $title }}</span>
            @if(!empty($is_central_out))
                <span class="badge badge-central">صادر مركزي (ورقتان: رسمية كاملة + مروّسة صفراء)</span>
            @else
                <span class="badge badge-memo">مذكرة داخلية رسمية</span>
            @endif
        </div>
        <div class="btn-group">
            @if(!empty($is_central_out))
                <button onclick="printLetter('all')" class="btn btn-print" title="طباعة الورقتين معاً">
                    <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4H7v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                    طباعة الكل (صفحتين)
                </button>
                <button onclick="printLetter('preprinted')" class="btn btn-print-yellow" title="طباعة الورقة الثانية المفرغة على الورق الأصفر المروّس">
                    <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                    طباعة على الورق الأصفر (مفرغة)
                </button>
            @else
                <button onclick="window.print()" class="btn btn-print">
                    <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4H7v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                    طباعة A4 / حفظ PDF
                </button>
            @endif
            <a href="{{ route('admin.official-letters.docx', ['type' => $current_type, 'fiscal_year_id' => $fiscal_year?->id, 'month_id' => $month?->id, 'memo_number' => $memo_number, 'memo_date' => $memo_date]) }}" class="btn btn-docx">
                <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                تحميل ملف Word (.docx)
            </a>
            <a href="{{ url('/admin/official-letters') }}" class="btn btn-back">
                عودة للقائمة
            </a>
        </div>
    </div>

    @yield('content')

</body>
</html>
