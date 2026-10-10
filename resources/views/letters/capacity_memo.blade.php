@extends('letters.layout')

@section('content')
    <div class="a4-sheet sheet-internal">
        <!-- كتابة الرقم والتاريخ فقط لأن (العدد: م.ت) و(التاريخ:) مطبوعان في أصل النموذج -->
        <div class="internal-memo-num">{{ $memo_number }}</div>
        <div class="internal-memo-date">{{ $memo_date }}</div>

        <div class="letter-content">
            <div class="content-main">
                <!-- التوجيه: 14 غامق -->
                <div class="addressee">
                    الســــــــــيد المــــدير الــــــــــعام
                </div>

                <!-- العنوان: 14 غامق في المنتصف -->
                <div class="subject">
                    م/ الطاقة الانتاجية لشـــــــــهر {{ $month_name }} ({{ $month_number }}/ {{ $year }})
                </div>

                <!-- المتن والتحية: 14 -->
                <div class="salutation">
                    تحية طيبة..
                </div>

                <div class="paragraph">
                    نرافق لسيادتكم ربطا الطاقة الانتاجية لشهر {{ $month_name }} ({{ $month_number }}/{{ $year }}) للمــــــوانئ الأربعـــــة (ام قصر الشمالي - ام قصر الجنوبي - خور الزبير - ابو فلوس) مع اجمالي الطاقة الانتاجية للشركة للشهر أعلاه. وحسب البيانات الواردة الينا من ادارات تلك الموانئ.
                </div>

                <!-- عبارة الختام: 14 في المنتصف -->
                <div class="closing">
                    تفضلكم بالاطلاع وامركم مع التقدير ...
                </div>

                <!-- المرفقات: حجم 12 -->
                <div class="attachments-section">
                    <div class="attachments-title">المرفقات:</div>
                    <div>1. جدول الطاقة الانتاجية لشهر {{ $month_name }} ({{ $month_number }}/{{ $year }}) لموانئ ام قصر (ام قصر الجنوبي - ام قصر الشمالي - خور الزبير - ابو فلوس).</div>
                    <div>2. جدول اجمالي الطاقة الانتاجية للشركة لشهر {{ $month_name }} ({{ $month_number }}/{{ $year }}).</div>
                    <div>3. جدول مقارنة الطاقة الإنتاجية لشهر {{ $month_name }} لعام {{ $prev_year }} - {{ $year }}.</div>
                </div>

                <!-- فراغ سطرين كاملين بين المرافقات واسم الموقع احمد حسين درويش -->
                <div class="signature-spacer-2lines"></div>

                <!-- التوقيع الرسمي: 14 غامق -->
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
        </div>
    </div>
@endsection
