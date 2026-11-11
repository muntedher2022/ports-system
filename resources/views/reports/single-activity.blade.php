<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>بطاقة نشاط: {{ $activity->title }}</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Tajawal:wght@400;500;700;800;900&display=swap');

        @page {
            size: A4 portrait;
            margin: 12mm;
        }

        * {
            box-sizing: border-box;
            font-family: 'Tajawal', 'Segoe UI', Tahoma, Arial, sans-serif;
        }

        body {
            direction: rtl;
            text-align: right;
            margin: 0;
            padding: 0;
            color: #1e293b;
            background-color: #ffffff;
            font-size: 11pt;
            line-height: 1.6;
        }

        .header-table {
            width: 100%;
            border-bottom: 2.5px solid #1e3a8a;
            padding-bottom: 12px;
            margin-bottom: 18px;
        }

        .official-title {
            font-size: 9.5pt;
            font-weight: 700;
            color: #1e3a8a;
            line-height: 1.4;
        }

        .report-center {
            text-align: center;
        }

        .report-main-title {
            font-size: 15pt;
            font-weight: 800;
            color: #0f172a;
            margin: 0;
        }

        .sub-badge {
            display: inline-block;
            background-color: #f1f5f9;
            color: #1e3a8a;
            border: 1px solid #cbd5e1;
            padding: 3px 12px;
            border-radius: 20px;
            font-size: 9pt;
            font-weight: 700;
            margin-top: 5px;
        }

        .header-meta {
            text-align: left;
            font-size: 8.5pt;
            color: #475569;
        }

        .activity-details-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 16px;
        }

        .activity-details-table th, .activity-details-table td {
            padding: 8px 12px;
            border: 1px solid #e2e8f0;
            font-size: 9.5pt;
        }

        .activity-details-table th {
            background-color: #f8fafc;
            color: #334155;
            width: 22%;
            font-weight: 700;
            text-align: right;
        }

        .section-box {
            background: #ffffff;
            border: 1px solid #cbd5e1;
            border-radius: 6px;
            padding: 12px;
            margin-bottom: 14px;
        }

        .section-title {
            font-size: 11pt;
            font-weight: 700;
            color: #1e3a8a;
            border-bottom: 1px solid #e2e8f0;
            padding-bottom: 5px;
            margin-bottom: 8px;
        }

        .gallery-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 8px;
            table-layout: fixed;
        }

        .gallery-table td {
            text-align: center;
            vertical-align: top;
            background: #f8fafc;
            border: 1px solid #cbd5e1;
            border-radius: 6px;
            padding: 6px;
        }

        .gallery-box {
            height: 200px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #f1f5f9;
            border-radius: 4px;
            overflow: hidden;
        }

        .gallery-img {
            max-width: 100%;
            max-height: 100%;
            width: auto;
            height: auto;
            object-fit: contain;
            border-radius: 4px;
            display: block;
            margin: 0 auto;
        }

        .single-gallery-card {
            background: #f8fafc;
            border: 1px solid #cbd5e1;
            border-radius: 6px;
            padding: 8px;
            text-align: center;
        }

        .single-gallery-box {
            height: 280px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #f1f5f9;
            border-radius: 4px;
            overflow: hidden;
        }

        .img-caption {
            font-size: 8pt;
            color: #475569;
            margin-top: 4px;
            font-weight: 600;
        }

        .signature-table {
            width: 100%;
            margin-top: 30px;
            page-break-inside: avoid;
        }

        .signature-box {
            text-align: center;
            font-size: 9.5pt;
        }

        .signature-role {
            font-weight: 800;
            margin-bottom: 45px;
        }

        .signature-name {
            font-weight: 700;
            border-top: 1px dotted #94a3b8;
            display: inline-block;
            padding-top: 4px;
            min-width: 160px;
        }
    </style>
</head>
@php
    $logoPath = public_path('images/logo.png');
    $logoBase64 = file_exists($logoPath) ? 'data:image/png;base64,' . base64_encode(file_get_contents($logoPath)) : null;
@endphp
<body>

    <table class="header-table">
        <tr>
            <td style="width: 33%;">
                <div class="official-title">
                    جمهورية العراق<br>
                    وزارة النقل<br>
                    الشركة العامة لموانئ العراق<br>
                    مكتب السيد المدير العام
                </div>
            </td>
            <td style="width: 34%;" class="report-center">
                @if($logoBase64)
                    <img src="{{ $logoBase64 }}" style="width: 78px; height: 78px; object-fit: contain; margin-bottom: 6px; display: inline-block;" alt="شعار الشركة العامة لموانئ العراق">
                @else
                    <div style="font-size: 14pt; margin-bottom: 3px;">🇮🇶</div>
                @endif
            </td>
            <td style="width: 33%;">
                <div class="header-meta">
                    <div><strong>تاريخ الطباعة:</strong> {{ $generatedAt }}</div>
                </div>
            </td>
        </tr>
    </table>

    <table class="activity-details-table">
        <tr>
            <th>عنوان النشاط</th>
            <td colspan="3" style="font-weight: 700; font-size: 11pt; color: #0f172a;">{{ $activity->title }}</td>
        </tr>
        <tr>
            <th>تاريخ النشاط</th>
            <td>{{ $activity->formatted_date }}</td>
            <th>درجة الأهمية</th>
            <td>{{ $activity->importance_label }}</td>
        </tr>
        <tr>
            <th>الموقع / المقر</th>
            <td>{{ $activity->location ?: 'الشركة العامة لموانئ العراق' }}</td>
            <th>الجهة المنظمة / الحاضرون</th>
            <td>{{ $activity->attendees ?: 'حسب جدول الأعمال' }}</td>
        </tr>
    </table>

    <div class="section-box">
        <div class="section-title">الوصف المختصر للنشاط ومحاوره الأساسية</div>
        <div style="font-size: 10pt; color: #1e293b; line-height: 1.6;">
            {{ $activity->short_description }}
        </div>
    </div>

    @if($activity->detailed_notes)
        <div class="section-box" style="border-right: 4px solid #0284c7;">
            <div class="section-title">التوجيهات والقرارات الصادرة والمخرجات</div>
            <div style="font-size: 9.5pt; color: #334155; line-height: 1.6;">
                {!! nl2br(e($activity->detailed_notes)) !!}
            </div>
        </div>
    @endif

    @php
        $images = $activity->imageAttachments;
    @endphp

    @if($images->isNotEmpty())
        <div class="section-box">
            <div class="section-title">التوثيق المصور والمرافقات ({{ $images->count() }} صورة)</div>
            @if($images->count() === 1)
                @php $singleImg = $images->first(); @endphp
                <div class="single-gallery-card">
                    <div class="single-gallery-box">
                        @if($singleImg->base64_data_uri)
                            <img src="{{ $singleImg->base64_data_uri }}" class="gallery-img" alt="{{ $singleImg->caption ?: 'صورة توثيقية' }}">
                        @else
                            <div style="line-height: 280px; color: #64748b; font-size: 9pt;">مرفق مستندي</div>
                        @endif
                    </div>
                    @if(filled($singleImg->caption))
                        <div class="img-caption" style="margin-top: 6px; font-size: 8.5pt;">{{ $singleImg->caption }}</div>
                    @endif
                </div>
            @else
                @foreach($images->chunk(2) as $row)
                    <table class="gallery-table">
                        <tr>
                            @foreach($row as $img)
                                <td style="width: 50%;">
                                    <div class="gallery-box">
                                        @if($img->base64_data_uri)
                                            <img src="{{ $img->base64_data_uri }}" class="gallery-img" alt="{{ $img->caption ?: 'صورة توثيقية' }}">
                                        @else
                                            <div style="line-height: 200px; color: #64748b; font-size: 8pt;">مرفق مستندي</div>
                                        @endif
                                    </div>
                                    @if(filled($img->caption))
                                        <div class="img-caption">{{ $img->caption }}</div>
                                    @endif
                                </td>
                            @endforeach
                            @if($row->count() == 1)
                                <td style="width: 50%; border: none; background: transparent;"></td>
                            @endif
                        </tr>
                    </table>
                @endforeach
            @endif
        </div>
    @endif

</body>
</html>
