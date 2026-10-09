@extends('letters.layout')

@section('content')
    <!-- ترويسة المذكرة الرسمية (مطابقة لنموذج 87) -->
    <div class="letter-header">
        <div class="header-columns">
            <div class="header-ar">
                وزارة النقل<br>
                الشركة العامة لموانئ العراق<br>
                المتابعة المركزية والعمليات
            </div>
            <div class="header-logo">
                <img src="{{ asset('images/letters/header_logo.jpg') }}" alt="شعار الشركة العامة لموانئ العراق">
            </div>
            <div class="header-en">
                Ministry of Transport<br>
                General Company For Ports of Iraq<br>
                Central Follow up and Operations
            </div>
        </div>

        <div class="header-meta">
            <div>العـــــدد: م.ت {{ $memo_number }}</div>
            <div>التاريـخ: {{ $memo_date }}</div>
        </div>

        <div class="memo-divider">
            ـــــــــــــــــــــــــــــــــــــــــــــــــــــــــ مذكـــرة داخلــــــية ـــــــــــــــــــــــــــــــــــــــــــــــــــــــــ
        </div>
    </div>

    <!-- متن المذكرة -->
    <div class="letter-body">
        <!-- التوجيه: 14 غامق -->
        <div class="addressee">
            الســــــــــيد الــــــــــمدير العــــــــــام
        </div>

        <!-- العنوان: 14 غامق في المنتصف -->
        <div class="subject">
            م/ الايراد الكلي والايراد الصافي المتحقق للشركة لشهر {{ $month_name }} ({{ $month_number }}/ {{ $year }})
        </div>

        <!-- المتن والتحية: 14 -->
        <div class="salutation">
            تحية طيبة..
        </div>

        <div class="paragraph">
            نرافق لسيادتكم ربطا الإيراد الكلي والايراد الصافي المتحقق للشركة لشهر {{ $month_name }} ({{ $month_number }}/ {{ $year }}) وحسب البيانات الواردة الينا من القسم المالي.
        </div>

        <!-- عبارة الختام: 14 في المنتصف -->
        <div class="closing">
            تفضلكم بالاطلاع وامركم ... مع التقدير
        </div>

        <!-- المرفقات: حجم 12 -->
        <div class="attachments-section">
            <div class="attachments-title">المرفقات:</div>
            <div>1. جدول بالإيراد الكلي والإيراد الصافي المتحقق لموانئ واقسام الشركة لشهر {{ $month_name }} ({{ $month_number }}/ {{ $year }}).</div>
            <div>2. جدول مقارنة بالإيراد الكلي والإيراد الصافي المتحقق لشهر {{ $month_name }} لعام {{ $prev_year }} - {{ $year }}.</div>
        </div>

        <!-- التوقيع الرسمي -->
        <div class="signature-section">
            <div class="signature-box">
                <div class="signature-name">{{ $signatory_name }}</div>
                <div>{{ $signatory_title }}</div>
            </div>
        </div>
    </div>

    <!-- صورة منه إلى: حجم 12 -->
    <div class="copies-section">
        <div class="copies-title">صورة منه الى /</div>
        <div>• مكتب معاون المدير العام للشؤون الفنية.. للتفضل بالاطلاع مع التقدير.</div>
        <div>• مكتب معاون المدير العام للشؤون الإدارية والمالية.. للتفضل بالاطلاع مع التقدير.</div>
        <div>• المتابعة المركزية والعمليات ... للحفظ.</div>
    </div>

    <!-- الفوتر الرسمي (مطابق تماماً لنموذج م.ت 87) -->
    <div class="letter-footer memo-footer">
        <div>E-mail: mot@gcpi.gov.iq</div>
        <div>GCPI-WI01-F02</div>
    </div>
@endsection
