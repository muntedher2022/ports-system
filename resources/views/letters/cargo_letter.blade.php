@extends('letters.layout')

@section('content')
    <!-- ترويسة كتاب الصادر المركزي إلى الوزارة (مطابقة للنموذج الأصلي تماماً وتسحب فارغة للختم والقيد) -->
    <div class="letter-header">
        <div class="header-columns">
            <div class="header-ar">
                جمهورية العراق<br>
                وزارة النقل<br>
                الشركة العامة لموانئ العراق<br>
                المتابعة المركزية والعمليات
            </div>
            <div class="header-logo">
                <img src="{{ asset('images/letters/header_logo.jpg') }}" alt="شعار الشركة العامة لموانئ العراق">
            </div>
            <div class="header-en">
                Republic of Iraq<br>
                Ministry of Transport<br>
                General Company For<br>
                Ports of Iraq
            </div>
        </div>

        <div class="header-meta">
            <div>
                العدد:&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<br>
                التاريخ:&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
            </div>
            <div style="direction: ltr; text-align: left; font-family: 'Times New Roman', serif; font-size: 11pt;">
                No.:&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<br>
                Date:&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
            </div>
        </div>
        <div style="height: 1px; background: #000; margin-top: 8px;"></div>
    </div>

    <!-- متن الكتاب الرسمي -->
    <div class="letter-body">
        <!-- التوجيه: 14 غامق -->
        <div class="addressee" style="line-height: 1.45; margin-top: 14px;">
            وزارة النقل<br>
            الدائرة الفنية<br>
            قسم ادارة شؤون الموانئ والنقل البحري
        </div>

        <!-- العنوان: 14 غامق في المنتصف -->
        <div class="subject">
            م / الموقف الشهري للمواد والبضائع المتخلفة لشهر {{ $month_name }} {{ $year }}
        </div>

        <!-- المتن والتحية: 14 -->
        <div class="salutation">
            نهديكم اطيب التحيات..
        </div>

        <div class="paragraph">
            نرافق لكم ربطا قرص (CD) يحتوي على موقف المواد والبضائع المتخلفة لشهر {{ $month_name }} {{ $year }} والتي بلغ عددها كما مبين ادناه.
        </div>

        <!-- عبارة الختام: 14 في المنتصف -->
        <div class="closing">
            للتفضل بالاطلاع مع التقدير.
        </div>

        <!-- جدول الموقف الشهري للمواد والبضائع -->
        <table class="official-table">
            <thead>
                <tr>
                    <th style="width: 28%;">صنف المادة</th>
                    <th style="width: 24%;">قطاع خاص</th>
                    <th style="width: 24%;">قطاع حكومي</th>
                    <th style="width: 24%;">المجموع</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>متخلفة</td>
                    <td>{{ number_format($table['abandoned']['private']) }}</td>
                    <td>{{ number_format($table['abandoned']['government']) }}</td>
                    <td>{{ number_format($table['abandoned']['total']) }}</td>
                </tr>
                <tr class="total-row">
                    <td>المجموع</td>
                    <td>{{ number_format($table['totals']['private']) }}</td>
                    <td>{{ number_format($table['totals']['government']) }}</td>
                    <td>{{ number_format($table['totals']['grand_total']) }}</td>
                </tr>
            </tbody>
        </table>

        <!-- المرافقات: حجم 12 -->
        <div class="attachments-section">
            <div class="attachments-title">المرافقات:</div>
            <div>قرص (CD)</div>
        </div>

        <!-- التوقيع الرسمي للمدير العام -->
        <div class="signature-section">
            <div class="signature-box">
                <div class="signature-name">{{ $signatory_name }}</div>
                <div>{{ $signatory_title }}</div>
                <div>الشركة العامة لموانئ العراق</div>
                <div class="signature-date">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; / &nbsp;&nbsp;{{ $month_number }}&nbsp;&nbsp; / &nbsp;&nbsp;{{ $year }}</div>
            </div>
        </div>
    </div>

    <!-- صورة منه إلى: حجم 12 -->
    <div class="copies-section">
        <div class="copies-title">صورة منه الى /</div>
        <div>• المتابعة المركزية والعمليات ... للحفظ.</div>
        <div>• اضبارة الدائرة.. للحفظ.</div>
    </div>

    <!-- الفوتر الرسمي (مطابق تماماً للنموذج الأصلي الصادر إلى الوزارة) -->
    <div class="letter-footer central-footer">
        <div class="col-left">
            <div>www.scp.gov.iq</div>
            <div>E-mail mot@scp.gov.iq</div>
        </div>
        <div class="col-center">
            <div>العراق - البصرة</div>
            <div>Iraq - Basrah - AL- Maqil</div>
        </div>
        <div class="col-right">
            <div>GCPI-WI01-F01</div>
        </div>
    </div>
@endsection
