<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>{{ $reportTitle }}</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Tajawal:wght@400;500;700;800;900&display=swap');

        @page {
            size: A4 portrait;
            margin: 12mm 12mm 15mm 12mm;
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
            line-height: 1.5;
        }

        /* Troweesa / Executive Header */
        .header-table {
            width: 100%;
            border-bottom: 2.5px solid #1e3a8a;
            padding-bottom: 12px;
            margin-bottom: 14px;
        }

        .header-table td {
            vertical-align: middle;
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
            font-size: 16pt;
            font-weight: 800;
            color: #0f172a;
            margin: 0;
            letter-spacing: -0.5px;
        }

        .report-period-badge {
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
            line-height: 1.6;
        }

        .header-meta strong {
            color: #0f172a;
        }

        /* Summary Stats Cards */
        .stats-grid {
            width: 100%;
            margin-bottom: 16px;
            border-collapse: separate;
            border-spacing: 6px;
        }

        .stat-card {
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            border-top: 3px solid #1e3a8a;
            border-radius: 6px;
            padding: 8px 10px;
            text-align: center;
        }

        .stat-card.urgent {
            border-top-color: #dc2626;
            background-color: #fef2f2;
        }

        .stat-card.visits {
            border-top-color: #059669;
            background-color: #f0fdf4;
        }

        .stat-card.other {
            border-top-color: #64748b;
            background-color: #f8fafc;
        }

        .stat-number {
            font-size: 14pt;
            font-weight: 800;
            color: #0f172a;
            line-height: 1.1;
        }

        .stat-label {
            font-size: 7.5pt;
            color: #64748b;
            font-weight: 600;
            margin-top: 3px;
        }

        /* Activity Cards */
        .activity-card {
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            background: #ffffff;
            margin-bottom: 14px;
            page-break-inside: avoid;
            box-shadow: 0 1px 3px rgba(0,0,0,0.04);
            overflow: hidden;
        }

        .card-header-table {
            width: 100%;
            background-color: #f1f5f9;
            border-bottom: 1px solid #cbd5e1;
            padding: 7px 10px;
        }

        .activity-seq {
            display: inline-block;
            background-color: #1e3a8a;
            color: #ffffff;
            font-weight: 800;
            font-size: 9pt;
            padding: 2px 7px;
            border-radius: 4px;
            margin-left: 6px;
        }

        .activity-title {
            font-size: 11.5pt;
            font-weight: 700;
            color: #0f172a;
            margin: 0;
            display: inline;
        }

        .activity-badges {
            text-align: left;
            white-space: nowrap;
        }

        .badge {
            display: inline-block;
            font-size: 7.5pt;
            font-weight: 700;
            padding: 2px 8px;
            border-radius: 4px;
            margin-right: 4px;
        }

        .badge-category {
            background-color: #e0f2fe;
            color: #0369a1;
            border: 1px solid #bae6fd;
        }

        .badge-urgent {
            background-color: #fee2e2;
            color: #b91c1c;
            border: 1px solid #fecaca;
        }

        .badge-important {
            background-color: #fef3c7;
            color: #b45309;
            border: 1px solid #fde68a;
        }

        .badge-routine {
            background-color: #f1f5f9;
            color: #475569;
            border: 1px solid #e2e8f0;
        }

        .badge-status {
            background-color: #dcfce7;
            color: #15803d;
            border: 1px solid #bbf7d0;
        }

        .card-meta-bar {
            padding: 6px 12px;
            background-color: #ffffff;
            border-bottom: 1px dashed #e2e8f0;
            font-size: 8.5pt;
            color: #475569;
        }

        .meta-item {
            display: inline-block;
            margin-left: 18px;
        }

        .meta-item strong {
            color: #1e293b;
        }

        .card-body {
            padding: 10px 12px;
        }

        .description-box {
            font-size: 9.5pt;
            color: #1e293b;
            line-height: 1.6;
            margin-bottom: 8px;
        }

        .notes-box {
            background-color: #f8fafc;
            border-right: 3px solid #0284c7;
            padding: 6px 10px;
            font-size: 8.5pt;
            color: #334155;
            margin-bottom: 8px;
            border-radius: 0 4px 4px 0;
        }

        .notes-title {
            font-weight: 700;
            color: #0284c7;
            margin-bottom: 3px;
        }

        /* Images Grid */
        .images-section-title {
            font-size: 8.5pt;
            font-weight: 700;
            color: #475569;
            margin: 6px 0 4px 0;
            border-top: 1px solid #f1f5f9;
            padding-top: 6px;
        }

        .image-gallery-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 6px;
            table-layout: fixed;
        }

        .image-gallery-table td {
            text-align: center;
            vertical-align: top;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            padding: 5px;
        }

        .photo-box {
            height: 145px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #f1f5f9;
            border-radius: 4px;
            overflow: hidden;
        }

        .activity-photo {
            max-width: 100%;
            max-height: 100%;
            width: auto;
            height: auto;
            object-fit: contain;
            border-radius: 4px;
            display: block;
            margin: 0 auto;
        }

        /* تنسيق خاص وراقي عند وجود صورة واحدة فقط للنشاط */
        .single-photo-card {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            padding: 6px;
            text-align: center;
            margin-top: 4px;
        }

        .single-photo-box {
            height: 230px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #f1f5f9;
            border-radius: 4px;
            overflow: hidden;
        }

        .single-photo {
            max-width: 100%;
            max-height: 100%;
            width: auto;
            height: auto;
            object-fit: contain;
            border-radius: 4px;
            display: block;
            margin: 0 auto;
        }

        .photo-caption {
            font-size: 7.5pt;
            color: #64748b;
            margin-top: 4px;
            padding: 0 4px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        /* Footer & Signatures */
        .signature-table {
            width: 100%;
            margin-top: 25px;
            page-break-inside: avoid;
        }

        .signature-box {
            text-align: center;
            font-size: 9.5pt;
            color: #0f172a;
        }

        .signature-role {
            font-weight: 800;
            margin-bottom: 40px;
        }

        .signature-name {
            font-weight: 700;
            border-top: 1px dotted #94a3b8;
            display: inline-block;
            padding-top: 4px;
            min-width: 160px;
        }

        .footer-note {
            text-align: center;
            font-size: 7.5pt;
            color: #94a3b8;
            margin-top: 18px;
            border-top: 1px solid #e2e8f0;
            padding-top: 6px;
        }

        .no-data {
            text-align: center;
            padding: 40px 20px;
            color: #64748b;
            font-size: 11pt;
            background: #f8fafc;
            border-radius: 8px;
            border: 1px dashed #cbd5e1;
        }
    </style>
</head>
@php
    $logoPath = public_path('images/logo.png');
    $logoBase64 = file_exists($logoPath) ? 'data:image/png;base64,' . base64_encode(file_get_contents($logoPath)) : null;
@endphp
<body>

    <!-- الترويسة الرسمية للتقرير -->
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
                    <img src="{{ $logoBase64 }}" style="width: 72px; height: 72px; object-fit: contain; margin-bottom: 4px; display: inline-block;" alt="شعار الشركة العامة لموانئ العراق">
                @else
                    <div style="font-size: 14pt; margin-bottom: 3px;">🇮🇶</div>
                @endif
                <h1 class="report-main-title">{{ $reportTitle }}</h1>
                <div class="report-period-badge">{{ $titlePeriod }}</div>
            </td>
            <td style="width: 33%;">
                <div class="header-meta">
                    <div><strong>تاريخ الإصدار:</strong> {{ $generatedAt }}</div>
                    <div><strong>جهة الإصدار:</strong>الشركة العامة لموانئ العراق</div>
                    <div><strong>عدد الأنشطة:</strong> {{ $stats['total'] }} نشاط</div>
                </div>
            </td>
        </tr>
    </table>

    <!-- بطاقات الإحصائيات الموجزة -->
    <table class="stats-grid">
        <tr>
            <td class="stat-card">
                <div class="stat-number">{{ $stats['total'] }}</div>
                <div class="stat-label">إجمالي النشاطات</div>
            </td>
            <td class="stat-card">
                <div class="stat-number" style="color: #0369a1;">{{ $stats['meetings'] }}</div>
                <div class="stat-label">اجتماعات رسمية</div>
            </td>
            <td class="stat-card visits">
                <div class="stat-number" style="color: #059669;">{{ $stats['field_visits'] }}</div>
                <div class="stat-label">زيارات وجولات ميدانية</div>
            </td>
            <td class="stat-card">
                <div class="stat-number" style="color: #d97706;">{{ $stats['delegations'] }}</div>
                <div class="stat-label">استقبال وفود رسمية</div>
            </td>
            @if(($stats['conferences'] ?? 0) > 0)
            <td class="stat-card">
                <div class="stat-number" style="color: #7c3aed;">{{ $stats['conferences'] }}</div>
                <div class="stat-label">مؤتمرات وورش عمل</div>
            </td>
            @endif
            @if(($stats['decisions'] ?? 0) > 0)
            <td class="stat-card">
                <div class="stat-number" style="color: #e11d48;">{{ $stats['decisions'] }}</div>
                <div class="stat-label">قرارات وتوجيهات</div>
            </td>
            @endif
            @if(($stats['projects'] ?? 0) > 0)
            <td class="stat-card">
                <div class="stat-number" style="color: #2563eb;">{{ $stats['projects'] }}</div>
                <div class="stat-label">متابعة مشاريع</div>
            </td>
            @endif
            <td class="stat-card other">
                <div class="stat-number" style="color: #475569;">{{ $stats['others'] ?? 0 }}</div>
                <div class="stat-label">نشاطات أخرى</div>
            </td>
            <td class="stat-card urgent">
                <div class="stat-number" style="color: #dc2626;">{{ $stats['urgent'] }}</div>
                <div class="stat-label">أنشطة عاجلة وهامة</div>
            </td>
        </tr>
    </table>

    <!-- عرض النشاطات التفصيلية -->
    @forelse($activities as $index => $activity)
        <div class="activity-card">
            <table class="card-header-table">
                <tr>
                    <td style="width: 70%;">
                        <span class="activity-seq">#{{ $index + 1 }}</span>
                        <h2 class="activity-title">{{ $activity->title }}</h2>
                    </td>
                    <td class="activity-badges" style="width: 30%;">
                        <span class="badge badge-category">{{ $activity->category }}</span>
                        @if($activity->importance === 'urgent')
                            <span class="badge badge-urgent">عاجل جداً</span>
                        @elseif($activity->importance === 'important')
                            <span class="badge badge-important">مهم</span>
                        @else
                            <span class="badge badge-routine">عادي</span>
                        @endif
                    </td>
                </tr>
            </table>

            <div class="card-meta-bar">
                <span class="meta-item">
                    📅 <strong>التاريخ:</strong> {{ $activity->formatted_date }}
                </span>
                @if($activity->location)
                    <span class="meta-item">
                        📍 <strong>الموقع / الجهة:</strong> {{ $activity->location }}
                    </span>
                @endif
                @if($activity->attendees)
                    <span class="meta-item">
                        👥 <strong>المشاركون:</strong> {{ $activity->attendees }}
                    </span>
                @endif
            </div>

            <div class="card-body">
                <!-- الوصف المختصر -->
                <div class="description-box">
                    <strong>الوصف والموجز:</strong>
                    {{ $activity->short_description }}
                </div>

                <!-- الملاحظات والتوجيهات إن وجدت -->
                @if($activity->detailed_notes)
                    <div class="notes-box">
                        <div class="notes-title">📌 القرارات والتوجيهات الصادرة:</div>
                        {!! nl2br(e($activity->detailed_notes)) !!}
                    </div>
                @endif

                <!-- معرض الصور والمرافقات (سلسة بدون قفل الـ PDF) -->
                @php
                    $images = $activity->imageAttachments;
                @endphp

                @if($images->isNotEmpty())
                    <div class="images-section-title">
                        📷 التوثيق المصور والمرافقات ({{ $images->count() }} مرفق):
                    </div>
                    
                    @if($images->count() === 1)
                        @php $singleImg = $images->first(); @endphp
                        <div class="single-photo-card">
                            <div class="single-photo-box">
                                @if($singleImg->base64_data_uri)
                                    <img src="{{ $singleImg->base64_data_uri }}" class="single-photo" alt="{{ $singleImg->caption ?: 'صورة توثيقية' }}">
                                @else
                                    <div style="line-height: 230px; color: #64748b; font-size: 9pt;">مرفق مستندي</div>
                                @endif
                            </div>
                            @if(filled($singleImg->caption))
                                <div class="photo-caption" style="margin-top: 5px; font-size: 8pt;">{{ $singleImg->caption }}</div>
                            @endif
                        </div>
                    @elseif($images->count() === 2)
                        <table class="image-gallery-table">
                            <tr>
                                @foreach($images as $img)
                                    <td style="width: 50%;">
                                        <div class="photo-box" style="height: 170px;">
                                            @if($img->base64_data_uri)
                                                <img src="{{ $img->base64_data_uri }}" class="activity-photo" alt="{{ $img->caption ?: 'صورة توثيقية' }}">
                                            @else
                                                <div style="line-height: 170px; color: #64748b; font-size: 8pt;">مرفق مستندي</div>
                                            @endif
                                        </div>
                                        @if(filled($img->caption))
                                            <div class="photo-caption">{{ $img->caption }}</div>
                                        @endif
                                    </td>
                                @endforeach
                            </tr>
                        </table>
                    @else
                        @foreach($images->chunk(3) as $row)
                            <table class="image-gallery-table">
                                <tr>
                                    @foreach($row as $img)
                                        <td style="width: 33.33%;">
                                            <div class="photo-box">
                                                @if($img->base64_data_uri)
                                                    <img src="{{ $img->base64_data_uri }}" class="activity-photo" alt="{{ $img->caption ?: 'صورة توثيقية' }}">
                                                @else
                                                    <div style="line-height: 145px; color: #64748b; font-size: 8pt;">مرفق مستندي</div>
                                                @endif
                                            </div>
                                            @if(filled($img->caption))
                                                <div class="photo-caption">{{ $img->caption }}</div>
                                            @endif
                                        </td>
                                    @endforeach
                                    @for($i = $row->count(); $i < 3; $i++)
                                        <td style="width: 33.33%; border: none; background: transparent;"></td>
                                    @endfor
                                </tr>
                            </table>
                        @endforeach
                    @endif
                @endif
            </div>
        </div>
    @empty
        <div class="no-data">
            <h3>لا توجد نشاطات مسجلة خلال الفترة المحددة</h3>
            <p>يرجى التأكد من معايير البحث أو اختيار نطاق زمني آخر.</p>
        </div>
    @endforelse

    <div class="footer-note">
        تم استخراج هذا التقرير آلياً عبر نظام إدارة وتوثيق نشاطات المدير العام - الشركة العامة لموانئ العراق | جميع الحقوق محفوظة &copy; {{ date('Y') }}
    </div>

</body>
</html>
