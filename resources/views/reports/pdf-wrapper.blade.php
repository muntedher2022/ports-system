<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>{{ $title ?? 'تقرير رسمي' }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@400;500;700;800;900&display=swap" rel="stylesheet">
    <style>
        @page {
            size: A4 {{ ($orientation ?? 'L') === 'P' ? 'portrait' : 'landscape' }};
            margin: 8mm 8mm 8mm 8mm;
        }
        * {
            box-sizing: border-box;
            font-family: 'Tajawal', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif !important;
        }
        body {
            direction: rtl;
            font-size: 8.5pt;
            color: #0f172a;
            background: #ffffff;
            margin: 0;
            padding: 0;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }
        .pdf-page {
            page-break-after: always;
            break-after: page;
            width: 100%;
            position: relative;
        }
        .pdf-page:last-child {
            page-break-after: avoid;
            break-after: avoid;
        }
        table {
            border-collapse: collapse;
            width: 100%;
        }
        th, td {
            vertical-align: middle;
        }
    </style>
</head>
<body>
    @foreach($pages as $pageHtml)
        <div class="pdf-page">
            {!! $pageHtml !!}
        </div>
    @endforeach
</body>
</html>
