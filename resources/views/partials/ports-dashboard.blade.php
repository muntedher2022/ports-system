@if(isset($portsDashboard) && $portsDashboard['vessels'])
    @php
        $vessel = $portsDashboard['vessels'];
        $cap = $portsDashboard['capacity'];
        $rev = $portsDashboard['revenue'];
        $monthName = $portsDashboard['month']?->name_ar ?? 'تشرين الأول';
        $yearNum = $portsDashboard['fiscal_year']?->year ?? date('Y');
    @endphp

    <!-- =========================================================
         LIVE MARITIME & FINANCIAL OPERATIONS DASHBOARD
         ========================================================= -->
    <section class="ports-live-section" id="ports-operations-section" aria-label="المؤشرات التشغيلية والمالية للموانئ">

        <!-- شريط الموقف البحري اليومي المنظم (ثابت وواضح بدون حركة عشوائية) -->
        <div class="vessels-summary-ribbon">
            <div class="vessels-ribbon-header">
                <div class="vessels-ribbon-badge">
                    <span class="vessels-ticker-pulse"></span>
                    <x-heroicon-o-signal style="width: 17px; height: 17px; flex-shrink: 0;" />
                    <span>الموقف البحري اليومي</span>
                </div>
                <div class="vessels-ribbon-date">
                    <x-heroicon-o-calendar-days style="width: 14px; height: 14px;" />
                    <span>تحديث: {{ $vessel->record_date?->format('Y/m/d') }}</span>
                </div>
            </div>

            <div class="vessels-ribbon-grid">
                <div class="vessel-stat-box stat-total">
                    <span class="stat-box-label">إجمالي السفن</span>
                    <strong class="stat-box-value">{{ $vessel->total_overall }} <small>سفينة</small></strong>
                </div>

                <div class="vessel-stat-box">
                    <span class="stat-box-label">أم قصر الشمالي</span>
                    <strong class="stat-box-value">{{ $vessel->um_qasr_north }} <small>سفينة</small></strong>
                </div>

                <div class="vessel-stat-box">
                    <span class="stat-box-label">أم قصر الجنوبي</span>
                    <strong class="stat-box-value">{{ $vessel->um_qasr_south }} <small>سفينة</small></strong>
                </div>

                <div class="vessel-stat-box">
                    <span class="stat-box-label">خور الزبير</span>
                    <strong class="stat-box-value">{{ $vessel->khor_al_zubair }} <small>سفينة</small></strong>
                </div>

                <div class="vessel-stat-box">
                    <span class="stat-box-label">أبو فلوس</span>
                    <strong class="stat-box-value">{{ $vessel->abu_flus }} <small>سفينة</small></strong>
                </div>

                <div class="vessel-stat-box stat-oil">
                    <span class="stat-box-label">ميناء البصرة النفطي</span>
                    <strong class="stat-box-value">{{ $vessel->oil_port_berth }} <small>راسية</small> <span
                            class="stat-sep">/</span> {{ $vessel->oil_port_waiting }} <small>انتظار</small></strong>
                </div>

                <div class="vessel-stat-box stat-control">
                    <span class="stat-box-label">السيطرة البحرية</span>
                    <strong class="stat-box-value">
                        <span class="in-val">{{ $vessel->control_vessels_in }} دخول</span>
                        <span class="stat-sep">&bull;</span>
                        <span class="out-val">{{ $vessel->control_vessels_out }} مغادرة</span>
                        <span class="stat-sep">&bull;</span>
                        <span class="wait-val">{{ $vessel->anchorage_waiting }} انتظار</span>
                    </strong>
                </div>
            </div>
        </div>

        <!-- البطاقات الرئيسية الثلاث التفاعلية -->
        <div class="ports-tabs-nav">
            <div class="ports-section-heading">
                <div class="heading-icon-wrap">
                    <x-heroicon-o-chart-bar style="width: 24px; height: 24px;" />
                </div>
                <div>
                    <h3>المؤشرات التشغيلية والمالية للموانئ العراقية</h3>
                    <p>مستخلصة تلقائياً من جداول التحليل المركزية (الطاقة التراكمية ومصفوفة الإيراد) لعام {{ $yearNum }}</p>
                </div>
            </div>

            <div class="ports-tab-buttons">
                <button type="button" class="ports-tab-btn active" data-tab="tab-vessels">
                    <x-heroicon-o-globe-alt style="width: 18px; height: 18px;" />
                    <span>موقف السفن اليومي</span>
                    <span class="tab-chip">{{ $vessel->total_overall }} سفينة</span>
                </button>
                <button type="button" class="ports-tab-btn" data-tab="tab-capacity">
                    <x-heroicon-o-cube-transparent style="width: 18px; height: 18px;" />
                    <span>إجمالي الطاقة الإنتاجية</span>
                    <span class="tab-chip">{{ number_format($cap['total_teu']) }} TEU</span>
                </button>
                <button type="button" class="ports-tab-btn" data-tab="tab-revenue">
                    <x-heroicon-o-banknotes style="width: 18px; height: 18px;" />
                    <span>الإيراد الكلي والصافي</span>
                    <span class="tab-chip">{{ number_format($rev['grand_gross'] / 1000000000, 1) }} مليار د.ع</span>
                </button>
            </div>
        </div>

        <!-- المحتوى الخاص بكل تبويب -->
        <div class="ports-tabs-content">

            <!-- 1. تبويب موقف السفن اليومي -->
            <div class="ports-tab-pane active" id="tab-vessels">
                <div class="kpi-cards-grid">
                    <div class="kpi-metric-card border-blue">
                        <div class="metric-top">
                            <span class="metric-label">إجمالي السفن في الموانئ</span>
                            <span class="metric-badge-blue">آخر تحديث: {{ $vessel->record_date?->format('d/m/Y') }}</span>
                        </div>
                        <div class="metric-main">
                            <span class="metric-num">{{ $vessel->total_overall }}</span>
                            <span class="metric-unit">سفينة وناقلة</span>
                        </div>
                        <div class="metric-sub">
                            <span>يشمل كافة الأرصفة ومنطقة المخطاف والانتظار</span>
                        </div>
                    </div>

                    <div class="kpi-metric-card border-emerald">
                        <div class="metric-top">
                            <span class="metric-label">سفن راسية (على الأرصفة)</span>
                            <span class="metric-badge-emerald">نسبة التشغيل:
                                {{ round(($vessel->total_berthed / max($vessel->total_overall, 1)) * 100) }}%</span>
                        </div>
                        <div class="metric-main">
                            <span class="metric-num text-emerald">{{ $vessel->total_berthed }}</span>
                            <span class="metric-unit">سفينة راسية</span>
                        </div>
                        <div class="metric-sub">
                            <span>قيد التفريغ والتحميل في الأرصفة العاملة</span>
                        </div>
                    </div>

                    <div class="kpi-metric-card border-amber">
                        <div class="metric-top">
                            <span class="metric-label">سفن منطقة الانتظار والمخطاف</span>
                            <span class="metric-badge-amber">جاهزة للرسو</span>
                        </div>
                        <div class="metric-main">
                            <span class="metric-num text-amber">{{ $vessel->anchorage_waiting }}</span>
                            <span class="metric-unit">سفينة بالانتظار</span>
                        </div>
                        <div class="metric-sub">
                            <span>بانتظار توفر أرصفة شاغرة أو اكتمال الإجراءات</span>
                        </div>
                    </div>

                    <div class="kpi-metric-card border-indigo">
                        <div class="metric-top">
                            <span class="metric-label">السيطرة البحرية</span>
                            <span class="metric-badge-indigo">الممر الملاحي والمخطاف</span>
                        </div>
                        <div class="metric-main">
                            <span
                                class="metric-num text-indigo">{{ $vessel->control_vessels_in + $vessel->control_vessels_out }}</span>
                            <span class="metric-unit">حركة ملاحية ({{ $vessel->anchorage_waiting }} بالمخطاف)</span>
                        </div>
                        <div class="metric-sub">
                            <span>{{ $vessel->control_vessels_in }} دخول &bull; {{ $vessel->control_vessels_out }} مغادرة
                                &bull; {{ $vessel->anchorage_waiting }} انتظار في المخطاف</span>
                        </div>
                    </div>
                </div>

                <!-- جدول توزيع السفن على الموانئ التجارية والنفطية -->
                <div class="ports-vessels-breakdown">
                    <div class="breakdown-table-wrap">
                        <table class="ports-mini-table">
                            <thead>
                                <tr>
                                    <th>الميناء / المحطة البحرية</th>
                                    <th>سفن على الأرصفة / الحركة</th>
                                    <th>سفن بالانتظار</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>
                                        <strong>ميناء أم قصر الشمالي</strong>
                                        <span class="sub-text">محطة الحاويات والبضائع العامة</span>
                                    </td>
                                    <td><span class="port-count-badge badge-blue">{{ $vessel->um_qasr_north }} سفينة</span>
                                    </td>
                                    <td><span class="sub-text">—</span></td>
                                </tr>
                                <tr>
                                    <td>
                                        <strong>ميناء أم قصر الجنوبي</strong>
                                        <span class="sub-text">المشتقات النفطية والبضائع</span>
                                    </td>
                                    <td><span class="port-count-badge badge-blue">{{ $vessel->um_qasr_south }} سفينة</span>
                                    </td>
                                    <td><span class="sub-text">—</span></td>
                                </tr>
                                <tr>
                                    <td>
                                        <strong>ميناء خور الزبير</strong>
                                        <span class="sub-text">المشتقات النفطية</span>
                                    </td>
                                    <td><span class="port-count-badge badge-blue">{{ $vessel->khor_al_zubair }} سفينة</span>
                                    </td>
                                    <td><span class="sub-text">—</span></td>
                                </tr>
                                <tr>
                                    <td>
                                        <strong>ميناء أبو فلوس</strong>
                                        <span class="sub-text">محطة الحاويات والبضائع العامة</span>
                                    </td>
                                    <td><span class="port-count-badge badge-blue">{{ $vessel->abu_flus }} سفينة</span></td>
                                    <td><span class="sub-text">—</span></td>
                                </tr>
                                <tr>
                                    <td>
                                        <strong>ميناء البصرة النفطي والمنصات العائمة</strong>
                                        <span class="sub-text">تصدير النفط الخام</span>
                                    </td>
                                    <td><span class="port-count-badge badge-emerald">{{ $vessel->oil_port_berth }}
                                            ناقلة</span></td>
                                    <td><span class="port-count-badge badge-amber">{{ $vessel->oil_port_waiting }}
                                            ناقلة</span></td>
                                </tr>
                                <tr>
                                    <td>
                                        <strong>السيطرة البحرية ومنطقة المخطاف</strong>
                                        <span class="sub-text">الممر الملاحي ومنطقة انتظار السفن</span>
                                    </td>
                                    <td><span class="port-count-badge badge-purple">{{ $vessel->control_vessels_in }} دخول
                                            &bull; {{ $vessel->control_vessels_out }} مغادرة</span></td>
                                    <td><span class="port-count-badge badge-amber">{{ $vessel->anchorage_waiting }} سفينة
                                            بالمخطاف</span></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- 2. تبويب الطاقة الإنتاجية (تراكمي الموانئ الأربعة مطابق لـ Backend) -->
            <div class="ports-tab-pane" id="tab-capacity">
                <div class="kpi-cards-grid" style="grid-template-columns: repeat(auto-fit, minmax(215px, 1fr));">
                    <!-- 1. إجمالي السفن الواصلة (تراكمي) -->
                    <div class="kpi-metric-card border-blue">
                        <div class="metric-top">
                            <span class="metric-label">إجمالي السفن الواصلة (تراكمي)</span>
                            <span class="metric-badge-blue">عدد البواخر الكلي</span>
                        </div>
                        <div class="metric-main">
                            <span class="metric-num">{{ number_format($cap['total_ships']) }}</span>
                            <span class="metric-unit">سفينة</span>
                        </div>
                        <div class="metric-sub">
                            <span>إجمالي السفن للموانئ الأربعة حتى {{ $monthName }} {{ $yearNum }}</span>
                        </div>
                    </div>

                    <!-- 2. المشتقات النفطية (كارد مزدوج: تصدير + استيراد) -->
                    <div class="kpi-metric-card border-amber">
                        <div class="metric-top">
                            <span class="metric-label">المشتقات النفطية</span>
                            <span class="metric-badge-amber">تصدير واستيراد</span>
                        </div>
                        <div style="display: flex; flex-direction: column; gap: 8px; margin: 4px 0;">
                            <div
                                style="display: flex; justify-content: space-between; align-items: baseline; border-bottom: 1px dashed #e2e8f0; padding-bottom: 5px;">
                                <span style="font-size: 0.78rem; font-weight: 700; color: #475569;">إجمالي تصدير مشتقات
                                    نفطية:</span>
                                <strong
                                    style="font-family: 'Cairo', sans-serif; font-size: 1.05rem; font-weight: 900; color: #b45309;">{{ number_format($cap['oil_exported_tons']) }}
                                    <small style="font-size: 0.72rem; font-weight: 600;">طن</small></strong>
                            </div>
                            <div style="display: flex; justify-content: space-between; align-items: baseline;">
                                <span style="font-size: 0.78rem; font-weight: 700; color: #475569;">إجمالي استيراد مشتقات
                                    نفطية:</span>
                                <strong
                                    style="font-family: 'Cairo', sans-serif; font-size: 1.05rem; font-weight: 900; color: #0369a1;">{{ number_format($cap['oil_imported_tons']) }}
                                    <small style="font-size: 0.72rem; font-weight: 600;">طن</small></strong>
                            </div>
                        </div>
                        <div class="metric-sub">
                            <span>المجموع الكلي للمشتقات: {{ number_format($cap['oil_total_tons']) }} طن</span>
                        </div>
                    </div>

                    <!-- 3. الحاويات المستوردة والمصدرة TEU (كارد مزدوج) -->
                    <div class="kpi-metric-card border-purple">
                        <div class="metric-top">
                            <span class="metric-label">حركة الحاويات (المستوردة / المصدرة)</span>
                            <span class="metric-badge-purple">TEU</span>
                        </div>
                        <div style="display: flex; flex-direction: column; gap: 8px; margin: 4px 0;">
                            <div
                                style="display: flex; justify-content: space-between; align-items: baseline; border-bottom: 1px dashed #e2e8f0; padding-bottom: 5px;">
                                <span style="font-size: 0.78rem; font-weight: 700; color: #475569;">إجمالي الحاويات
                                    المستوردة TEU:</span>
                                <strong
                                    style="font-family: 'Cairo', sans-serif; font-size: 1.05rem; font-weight: 900; color: #6d28d9;">{{ number_format($cap['imported_teu']) }}
                                    <small style="font-size: 0.72rem; font-weight: 600;">TEU</small></strong>
                            </div>
                            <div style="display: flex; justify-content: space-between; align-items: baseline;">
                                <span style="font-size: 0.78rem; font-weight: 700; color: #475569;">إجمالي الحاويات المصدرة
                                    TEU:</span>
                                <strong
                                    style="font-family: 'Cairo', sans-serif; font-size: 1.05rem; font-weight: 900; color: #059669;">{{ number_format($cap['exported_teu']) }}
                                    <small style="font-size: 0.72rem; font-weight: 600;">TEU</small></strong>
                            </div>
                        </div>
                        <div class="metric-sub">
                            <span>حاويات نمطية (وارد + صادر)</span>
                        </div>
                    </div>

                    <!-- 4. الطاقة الإنتاجية الكلية للشركة TEU -->
                    <div class="kpi-metric-card border-indigo">
                        <div class="metric-top">
                            <span class="metric-label">الطاقة الإنتاجية الكلية للشركة TEU</span>
                            <span class="metric-badge-indigo">إجمالي الحاويات</span>
                        </div>
                        <div class="metric-main">
                            <span class="metric-num text-indigo">{{ number_format($cap['total_teu']) }}</span>
                            <span class="metric-unit">TEU</span>
                        </div>
                        <div class="metric-sub">
                            <span>مجموع المناولة الكلية للحاويات لعام {{ $yearNum }}</span>
                        </div>
                    </div>

                    <!-- 5. الطاقة الإنتاجية الوزنية للشركة بالطن -->
                    <div class="kpi-metric-card border-emerald">
                        <div class="metric-top">
                            <span class="metric-label">الطاقة الإنتاجية الوزنية للشركة بالطن</span>
                            <span class="metric-badge-emerald">الوزن الكلي</span>
                        </div>
                        <div class="metric-main">
                            <span class="metric-num text-emerald">{{ number_format($cap['total_tonnage']) }}</span>
                            <span class="metric-unit">طن</span>
                        </div>
                        <div class="metric-sub">
                            <span>إجمالي الحمولات والبضائع والحاويات والمشتقات</span>
                        </div>
                    </div>
                </div>

                <div class="ports-vessels-breakdown">
                    <div class="breakdown-table-wrap">
                        <table class="ports-mini-table">
                            <thead>
                                <tr>
                                    <th>الميناء التجاري</th>
                                    <th>الطاقة الإنتاجية الكلية (طن)</th>
                                    <th>إجمالي السفن</th>
                                    <th>الحاويات (TEU)</th>
                                    <th>أوزان البضائع (طن)</th>
                                    <th>حصة الطاقة الكلية</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($cap['ports'] as $pRec)
                                    <tr>
                                        <td><strong>{{ $pRec['name'] }}</strong></td>
                                        <td><span class="currency-text text-indigo font-bold"
                                                style="font-size: 0.95rem;">{{ number_format($pRec['total_tonnage']) }}
                                                طن</span></td>
                                        <td><span class="port-count-badge badge-blue">{{ number_format($pRec['ships']) }}
                                                سفينة</span></td>
                                        <td>
                                            @if($pRec['teu'] > 0)
                                                <span class="port-count-badge badge-purple">{{ number_format($pRec['teu']) }}
                                                    TEU</span>
                                            @else
                                                <span class="sub-text">المشتقات النفطية</span>
                                            @endif
                                        </td>
                                        <td><span class="currency-text">{{ number_format($pRec['cargo']) }} طن</span></td>
                                        <td>
                                            <div class="progress-bar-wrap">
                                                @php $tonnageShare = $cap['total_tonnage'] > 0 ? round(($pRec['total_tonnage'] / $cap['total_tonnage']) * 100, 1) : 0; @endphp
                                                <div class="progress-bar-fill fill-emerald"
                                                    style="width: {{ min(max($tonnageShare, 0), 100) }}%;"></div>
                                                <span class="progress-label">{{ $tonnageShare }}%</span>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- 3. تبويب الإيرادات المالية (مطابق لمصفوفة الإيراد الكلي والصافي في Backend) -->
            <div class="ports-tab-pane" id="tab-revenue">
                <div class="kpi-cards-grid">
                    <div class="kpi-metric-card border-emerald">
                        <div class="metric-top">
                            <span class="metric-label">الإيراد الكلي التراكمي (Gross)</span>
                            <span class="metric-badge-emerald">عام {{ $yearNum }} كاملاً</span>
                        </div>
                        <div class="metric-main">
                            <span
                                class="metric-num text-emerald">{{ number_format($rev['grand_gross'] / 1000000000, 2) }}</span>
                            <span class="metric-unit">مليار د.ع</span>
                        </div>
                        <div class="metric-sub">
                            <span>{{ number_format($rev['grand_gross']) }} دينار عراقي</span>
                        </div>
                    </div>

                    <div class="kpi-metric-card border-blue">
                        <div class="metric-top">
                            <span class="metric-label">الإيراد الصافي التراكمي (Net)</span>
                            <span class="metric-badge-blue">عام {{ $yearNum }} كاملاً</span>
                        </div>
                        <div class="metric-main">
                            <span class="metric-num text-blue">{{ number_format($rev['grand_net'] / 1000000000, 2) }}</span>
                            <span class="metric-unit">مليار د.ع</span>
                        </div>
                        <div class="metric-sub">
                            <span>{{ number_format($rev['grand_net']) }} دينار عراقي</span>
                        </div>
                    </div>

                    @php
                        $topCenter = collect($rev['centers'])->sortByDesc('total_gross')->first();
                        $topCenterGross = $topCenter['total_gross'] ?? 0;
                        $topCenterNet = $topCenter['total_net'] ?? 0;
                        $topCenterName = $topCenter['name'] ?? '—';
                    @endphp
                    <div class="kpi-metric-card border-indigo">
                        <div class="metric-top">
                            <span class="metric-label">أعلى مركز إيراد ({{ $topCenterName }})</span>
                            <span class="metric-badge-indigo">المركز الأول</span>
                        </div>
                        <div class="metric-main">
                            <span class="metric-num text-indigo">{{ number_format($topCenterGross / 1000000000, 2) }}</span>
                            <span class="metric-unit">مليار د.ع</span>
                        </div>
                        <div class="metric-sub">
                            <span>الكلي: {{ number_format($topCenterGross) }} د.ع &bull; الصافي:
                                {{ number_format($topCenterNet) }} د.ع</span>
                        </div>
                    </div>
                </div>

                <div class="ports-vessels-breakdown">
                    <div class="breakdown-table-wrap">
                        <table class="ports-mini-table">
                            <thead>
                                <tr>
                                    <th>مركز الإيراد</th>
                                    <th>الإيراد الكلي السنوي (د.ع)</th>
                                    <th>الإيراد الصافي السنوي (د.ع)</th>
                                    <th>المساهمة في الإيراد الكلي</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($rev['centers'] as $cRec)
                                    <tr>
                                        <td><strong>{{ $cRec['name'] }}</strong></td>
                                        <td><span class="currency-text font-bold"
                                                style="font-size: 1rem;">{{ number_format($cRec['total_gross']) }} د.ع</span>
                                        </td>
                                        <td><span class="currency-text text-emerald font-bold"
                                                style="font-size: 0.95rem;">{{ number_format($cRec['total_net'] ?? 0) }}
                                                د.ع</span></td>
                                        <td>
                                            <div class="progress-bar-wrap">
                                                @php $share = $rev['grand_gross'] > 0 ? round(($cRec['total_gross'] / $rev['grand_gross']) * 100, 1) : 0; @endphp
                                                <div class="progress-bar-fill fill-emerald"
                                                    style="width: {{ min(max($share, 0), 100) }}%;"></div>
                                                <span class="progress-label">{{ $share }}%</span>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div>
    </section>
@endif