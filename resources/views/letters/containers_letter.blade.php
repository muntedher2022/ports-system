@extends('letters.layout')

@section('content')
    <!-- ═══════════════════════════════════════════════════════════ -->
    <!-- الورقة الأولى: النسخة الرسمية الكاملة المروّسة بالفورمة     -->
    <!-- ═══════════════════════════════════════════════════════════ -->
    <div class="a4-sheet sheet-official">
        <div class="letter-content">
            <div class="content-main">
                <!-- التوجيه: 14 غامق -->
                <div class="addressee" style="line-height: 1.45; margin-top: 6px;">
                    وزارة النقل<br>
                    الدائرة الفنية<br>
                    قسم ادارة شؤون الموانئ والنقل البحري
                </div>

                <!-- العنوان: 14 غامق في المنتصف -->
                <div class="subject">
                    م / الموقف الشهري للحاويات المتخلفة والخطرة لشهر {{ $month_name }} {{ $year }}
                </div>

                <!-- المتن والتحية: 14 -->
                <div class="salutation">
                    نهديكم اطيب التحيات..
                </div>

                <div class="paragraph">
                    نرافق لكم ربطا قرص (CD) يحتوي على موقف الحاويات المتخلفة والخطرة لشهر {{ $month_name }} {{ $year }} والتي بلغ عددها كما مبين ادناه.
                </div>

                <!-- عبارة الختام: 14 في المنتصف -->
                <div class="closing">
                    للتفضل بالاطلاع مع التقدير.
                </div>

                <!-- جدول الموقف الشهري للحاويات -->
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
                        <tr>
                            <td>خطرة</td>
                            <td>{{ number_format($table['dangerous']['private']) }}</td>
                            <td>{{ number_format($table['dangerous']['government']) }}</td>
                            <td>{{ number_format($table['dangerous']['total']) }}</td>
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

                <!-- التوقيع الرسمي للمدير العام: 14 غامق -->
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
        </div>
    </div>

    <!-- فاصل توضيحي على الشاشة يختفي عند الطباعة -->
    <div class="sheet-divider">
        <span class="sheet-divider-badge">الورقة الثانية: نسخة الطباعة على الورق المروّس (الورقة الصفراء - خالية من الفورمة)</span>
    </div>

    <!-- ═══════════════════════════════════════════════════════════ -->
    <!-- الورقة الثانية: نسخة مفرغة للسحب على الورق الأصفر المروّس   -->
    <!-- ═══════════════════════════════════════════════════════════ -->
    <div class="a4-sheet sheet-preprinted">
        <!-- طباعة (المتابعة المركزية والعمليات) فقط في الهيدر بالموضع المحدد للقسم -->
        <div class="preprinted-header-dept">
            المتابعة المركزية والعمليات
        </div>

        <div class="letter-content">
            <div class="content-main">
                <!-- التوجيه: 14 غامق -->
                <div class="addressee" style="line-height: 1.45; margin-top: 6px;">
                    وزارة النقل<br>
                    الدائرة الفنية<br>
                    قسم ادارة شؤون الموانئ والنقل البحري
                </div>

                <!-- العنوان: 14 غامق في المنتصف -->
                <div class="subject">
                    م / الموقف الشهري للحاويات المتخلفة والخطرة لشهر {{ $month_name }} {{ $year }}
                </div>

                <!-- المتن والتحية: 14 -->
                <div class="salutation">
                    نهديكم اطيب التحيات..
                </div>

                <div class="paragraph">
                    نرافق لكم ربطا قرص (CD) يحتوي على موقف الحاويات المتخلفة والخطرة لشهر {{ $month_name }} {{ $year }} والتي بلغ عددها كما مبين ادناه.
                </div>

                <!-- عبارة الختام: 14 في المنتصف -->
                <div class="closing">
                    للتفضل بالاطلاع مع التقدير.
                </div>

                <!-- جدول الموقف الشهري للحاويات -->
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
                        <tr>
                            <td>خطرة</td>
                            <td>{{ number_format($table['dangerous']['private']) }}</td>
                            <td>{{ number_format($table['dangerous']['government']) }}</td>
                            <td>{{ number_format($table['dangerous']['total']) }}</td>
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

                <!-- التوقيع الرسمي للمدير العام: 14 غامق -->
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
        </div>
    </div>
@endsection
