<x-filament-panels::page>
    @php
        $d = $this->data;
        $stat = $d['activeStat'];
        $unit = match($selectedMetric) {
            'tonnage' => 'طن',
            'revenue' => 'د.ع',
            'ships'   => 'سفينة',
            'teu'     => 'TEU',
            default   => '',
        };
        $metricTitle = match($selectedMetric) {
            'tonnage' => 'الطاقة الإنتاجية الكلية (بالطن)',
            'revenue' => 'الإيرادات المالية الكلية (بالدينار)',
            'ships'   => 'حركة البواخر والناقلات الواصلة (سفينة)',
            'teu'     => 'الحاويات المكافئة المتداولة (TEU)',
            default   => '',
        };
    @endphp

    <style>
        .gcpi-dashboard {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            color: #1e293b;
            direction: rtl;
        }
        .gcpi-card {
            background: #ffffff;
            border-radius: 16px;
            border: 1px solid #e2e8f0;
            box-shadow: 0 4px 20px -2px rgba(0, 0, 0, 0.05);
            margin-bottom: 24px;
            overflow: hidden;
            transition: all 0.2s ease;
        }
        .gcpi-card-header {
            background: linear-gradient(135deg, #0f172a 0%, #1e3a8a 100%);
            color: #ffffff;
            padding: 18px 24px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 12px;
        }
        .gcpi-card-header h3 {
            margin: 0;
            font-size: 1.15rem;
            font-weight: 800;
        }
        .gcpi-filter-bar {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 14px;
            padding: 16px 20px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 16px;
            margin-bottom: 24px;
        }
        .gcpi-filter-group {
            display: flex;
            align-items: center;
            gap: 10px;
            flex-wrap: wrap;
        }
        .gcpi-filter-label {
            font-weight: 700;
            font-size: 0.88rem;
            color: #475569;
        }
        .gcpi-select {
            background-color: #ffffff;
            border: 1.5px solid #cbd5e1;
            border-radius: 10px;
            padding: 7px 14px;
            font-size: 0.9rem;
            font-weight: 700;
            color: #0f172a;
            outline: none;
            cursor: pointer;
        }
        .gcpi-tab-btn {
            padding: 9px 18px;
            border-radius: 12px;
            font-weight: 700;
            font-size: 0.9rem;
            border: 1.5px solid transparent;
            cursor: pointer;
            transition: all 0.2s ease;
            text-align: center;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
        }
        .gcpi-tab-btn.active {
            background: #1e3a8a;
            color: #ffffff;
            border-color: #1e3a8a;
            box-shadow: 0 4px 14px rgba(30, 58, 138, 0.25);
        }
        .gcpi-tab-btn:not(.active) {
            background: #f1f5f9;
            color: #475569;
            border-color: #e2e8f0;
        }
        .gcpi-tab-btn:not(.active):hover {
            background: #e2e8f0;
            color: #0f172a;
        }
        .gcpi-stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 16px;
            margin-bottom: 24px;
        }
        .gcpi-stat-box {
            background: #ffffff;
            border-radius: 16px;
            border: 1.5px solid #e2e8f0;
            padding: 18px 20px;
            box-shadow: 0 4px 18px -2px rgba(0,0,0,0.04);
            position: relative;
            overflow: hidden;
            transition: all 0.25s ease;
        }
        .gcpi-stat-box:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 24px -4px rgba(0,0,0,0.08);
        }
        .gcpi-stat-box.blue {
            background: linear-gradient(145deg, #eff6ff 0%, #dbeafe 100%);
            border-color: #93c5fd;
        }
        .gcpi-stat-box.success {
            background: linear-gradient(145deg, #ecfdf5 0%, #d1fae5 100%);
            border-color: #6ee7b7;
        }
        .gcpi-stat-box.warning {
            background: linear-gradient(145deg, #fffbeb 0%, #fef3c7 100%);
            border-color: #fde68a;
        }
        .gcpi-stat-box.danger {
            background: linear-gradient(145deg, #fef2f2 0%, #fee2e2 100%);
            border-color: #fca5a5;
        }
        .gcpi-stat-box.purple {
            background: linear-gradient(145deg, #f5f3ff 0%, #ede9fe 100%);
            border-color: #c4b5fd;
        }

        .gcpi-stat-title {
            font-size: 0.82rem;
            font-weight: 800;
            color: #475569;
            margin-bottom: 6px;
        }
        .gcpi-stat-value {
            font-size: 1.25rem;
            font-weight: 800;
            color: #0f172a;
            font-family: monospace, sans-serif;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            direction: rtl;
        }
        .gcpi-stat-desc {
            font-size: 0.78rem;
            font-weight: 700;
            color: #64748b;
            margin-top: 6px;
        }
        .gcpi-table-container {
            width: 100%;
            overflow-x: auto;
        }
        .gcpi-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.88rem;
            text-align: center;
            white-space: nowrap;
        }
        .gcpi-table thead tr {
            background: #f1f5f9;
            border-bottom: 2px solid #cbd5e1;
        }
        .gcpi-table th {
            padding: 12px 16px;
            font-weight: 800;
            color: #1e293b;
        }
        .gcpi-table td {
            padding: 11px 16px;
            border-bottom: 1px solid #f1f5f9;
            color: #334155;
            font-variant-numeric: tabular-nums;
        }
        .gcpi-table tbody tr:hover {
            background-color: #f8fafc;
        }
        .gcpi-btn-pdf {
            background: linear-gradient(135deg, #dc2626 0%, #991b1b 100%);
            color: #ffffff !important;
            padding: 8px 18px;
            border-radius: 10px;
            font-weight: 800;
            font-size: 0.88rem;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            text-decoration: none;
            box-shadow: 0 2px 10px rgba(220, 38, 38, 0.25);
            transition: all 0.2s ease;
            cursor: pointer;
            border: none;
        }
        .gcpi-btn-pdf:hover {
            background: linear-gradient(135deg, #b91c1c 0%, #7f1d1d 100%);
            box-shadow: 0 4px 14px rgba(220, 38, 38, 0.35);
            transform: translateY(-1px);
        }
        .badge-stat-high {
            background: #dbeafe;
            color: #1e40af;
            padding: 3px 10px;
            border-radius: 8px;
            font-weight: 700;
            font-size: 0.8rem;
        }
        .badge-stat-normal {
            background: #ecfdf5;
            color: #047857;
            padding: 3px 10px;
            border-radius: 8px;
            font-weight: 700;
            font-size: 0.8rem;
        }
        .badge-stat-low {
            background: #fef2f2;
            color: #b91c1c;
            padding: 3px 10px;
            border-radius: 8px;
            font-weight: 700;
            font-size: 0.8rem;
        }
    </style>

    <div class="gcpi-dashboard">
        <!-- ─── أزرار اختيار المؤشر الإحصائي ─── -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-3 mb-6 bg-white dark:bg-gray-900 p-2 rounded-2xl border border-gray-200 dark:border-gray-800 shadow-sm">
            <button 
                type="button"
                wire:click="$set('selectedMetric', 'tonnage')"
                class="gcpi-tab-btn {{ $selectedMetric === 'tonnage' ? 'active' : '' }}"
            >
                <span>📦 الطاقة الإنتاجية (طن)</span>
            </button>

            <button 
                type="button"
                wire:click="$set('selectedMetric', 'revenue')"
                class="gcpi-tab-btn {{ $selectedMetric === 'revenue' ? 'active' : '' }}"
            >
                <span>💰 الإيراد المالي (د.ع)</span>
            </button>

            <button 
                type="button"
                wire:click="$set('selectedMetric', 'ships')"
                class="gcpi-tab-btn {{ $selectedMetric === 'ships' ? 'active' : '' }}"
            >
                <span>🚢 حركة البواخر</span>
            </button>

            <button 
                type="button"
                wire:click="$set('selectedMetric', 'teu')"
                class="gcpi-tab-btn {{ $selectedMetric === 'teu' ? 'active' : '' }}"
            >
                <span>📐 الحاويات المكافئة TEU</span>
            </button>
        </div>

        <!-- ─── شريط الفلاتر ─── -->
        <div class="gcpi-filter-bar">
            <!-- اختيار السنة المالية -->
            <div class="gcpi-filter-group">
                <span class="gcpi-filter-label">السنة المالية:</span>
                <select wire:model.live="selectedFiscalYearId" class="gcpi-select">
                    @foreach($d['fiscalYears'] as $y)
                        <option value="{{ $y->id }}">سنة {{ $y->year }}</option>
                    @endforeach
                </select>
            </div>

            <!-- اختيار الميناء -->
            <div class="gcpi-filter-group">
                <span class="gcpi-filter-label">الميناء / النطاق:</span>
                <select wire:model.live="selectedPortId" class="gcpi-select">
                    <option value="">🏢 إجمالي كافة الموانئ مجتمعة</option>
                    @foreach($d['ports'] as $p)
                        <option value="{{ $p->id }}">{{ $p->name_ar }}</option>
                    @endforeach
                </select>
            </div>

            <!-- زر تصدير PDF -->
            <div class="gcpi-filter-group">
                <a href="{{ route('admin.reports.standard-deviation.pdf', [
                    'fiscal_year_id' => $selectedFiscalYearId,
                    'port_id'        => $selectedPortId,
                    'metric'         => $selectedMetric,
                ]) }}" target="_blank" class="gcpi-btn-pdf">
                    📄 تصدير تقرير الانحراف المعياري PDF
                </a>
            </div>
        </div>

        <!-- ─── بطاقات المؤشرات الإحصائية الرئيسية ─── -->
        <div class="gcpi-stats-grid">
            <!-- 1. الانحراف المعياري -->
            <div class="gcpi-stat-box {{ $stat['cv'] <= 15 ? 'success' : ($stat['cv'] <= 30 ? 'warning' : 'danger') }}">
                <div class="gcpi-stat-title">الانحراف المعياري (Standard Deviation σ)</div>
                <div class="gcpi-stat-value" style="color: #1e3a8a;">
                    ± {{ number_format($stat['std_dev'], 1) }} <span style="font-size: 0.75rem; font-weight: normal;">{{ $unit }}</span>
                </div>
                <div class="gcpi-stat-desc">مدى تباعد الأداء الشهري عن المتوسط</div>
            </div>

            <!-- 2. المتوسط الحسابي -->
            <div class="gcpi-stat-box blue">
                <div class="gcpi-stat-title">المتوسط الشهري (Mean μ)</div>
                <div class="gcpi-stat-value" style="color: #0f172a;">
                    {{ number_format($stat['mean'], 1) }} <span style="font-size: 0.75rem; font-weight: normal;">{{ $unit }}</span>
                </div>
                <div class="gcpi-stat-desc">معدل الأداء الشهري المعتاد للميناء</div>
            </div>

            <!-- 3. معامل التشتت والاستقرار -->
            <div class="gcpi-stat-box {{ $stat['cv'] <= 15 ? 'success' : ($stat['cv'] <= 30 ? 'warning' : 'danger') }}">
                <div class="gcpi-stat-title">معامل التشتت (Coefficient of Var CV)</div>
                <div class="gcpi-stat-value" style="color: {{ $stat['cv'] <= 15 ? '#047857' : ($stat['cv'] <= 30 ? '#d97706' : '#dc2626') }};">
                    {{ number_format($stat['cv'], 1) }}%
                </div>
                <div class="gcpi-stat-desc">تقييم: <strong>{{ $stat['stability'] }}</strong></div>
            </div>

            <!-- 4. النطاق والأدنى والأعلى -->
            <div class="gcpi-stat-box purple">
                <div class="gcpi-stat-title">النطاق التشغيلي (Min — Max)</div>
                <div class="gcpi-stat-value" style="font-size: 1.05rem; color: #6b21a8;">
                    {{ number_format($stat['min'], 0) }} — {{ number_format($stat['max'], 0) }}
                </div>
                <div class="gcpi-stat-desc">أدنى شهر مقابل أعلى شهر إنتاجية</div>
            </div>
        </div>

        <!-- ─── جدول التحليل الشهري للانحراف ومؤشر Z-Score ─── -->
        <div class="gcpi-card">
            <div class="gcpi-card-header">
                <div>
                    <h3>📋 مصفوفة التحليل الشهري للانحراف المعياري — {{ $metricTitle }}</h3>
                    <span class="text-xs text-blue-200 mt-1 block">
                        تتبع انحراف كل شهر عن المعدل العام لمعرفة أشهر الطفرات الإنتاجية وأشهر التراجع
                    </span>
                </div>
            </div>

            <div class="gcpi-table-container">
                <table class="gcpi-table">
                    <thead>
                        <tr>
                            <th style="text-align: right; width: 18%;">الشهر</th>
                            <th style="width: 22%;">القيمة الفعلية ({{ $unit }})</th>
                            <th style="width: 22%;">الانحراف عن المتوسط (x - μ)</th>
                            <th style="width: 16%;">معامل الانحراف القياسي (Z-Score)</th>
                            <th style="width: 22%;">تقييم حالة الأداء</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($d['monthlyRows'] as $r)
                            <tr>
                                <td style="text-align: right; font-weight: 800;">{{ $r['month_name'] }}</td>
                                <td style="font-weight: 800; font-family: monospace;">
                                    {{ $r['val'] > 0 ? number_format($r['val'], 0) : '—' }}
                                </td>
                                <td style="font-family: monospace; font-weight: 700;">
                                    @if($r['val'] > 0)
                                        @if($r['deviation'] > 0)
                                            <span style="color: #047857;" dir="ltr">+{{ number_format($r['deviation'], 0) }}</span>
                                        @elseif($r['deviation'] < 0)
                                            <span style="color: #b91c1c;" dir="ltr">{{ number_format($r['deviation'], 0) }}</span>
                                        @else
                                            <span>0</span>
                                        @endif
                                    @else
                                        <span class="text-gray-400">—</span>
                                    @endif
                                </td>
                                <td style="font-family: monospace; font-weight: 700;">
                                    @if($r['val'] > 0)
                                        <span dir="ltr">{{ ($r['z_score'] > 0 ? '+' : '') . number_format($r['z_score'], 2) }} σ</span>
                                    @else
                                        <span class="text-gray-400">—</span>
                                    @endif
                                </td>
                                <td>
                                    @if($r['assessment_type'] === 'high')
                                        <span class="badge-stat-high">▲ {{ $r['assessment'] }}</span>
                                    @elseif($r['assessment_type'] === 'normal')
                                        <span class="badge-stat-normal">● {{ $r['assessment'] }}</span>
                                    @elseif($r['assessment_type'] === 'low')
                                        <span class="badge-stat-low">▼ {{ $r['assessment'] }}</span>
                                    @else
                                        <span class="text-gray-400 text-xs">—</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- ─── قسم دليل المعالجات والإجراءات التصحيحية المعتمدة ─── -->
        <div class="gcpi-card" style="border: 2px solid #3b82f6; box-shadow: 0 10px 30px -5px rgba(59, 130, 246, 0.1);">
            <div class="gcpi-card-header" style="background: linear-gradient(135deg, #1e3a8a 0%, #0369a1 100%);">
                <div>
                    <h3 style="font-size: 1.25rem;">🛠️ دليل الإجراءات والمعالجات التشغيلية والتصحيحية عند حدوث الانحراف</h3>
                    <span class="text-xs text-blue-100 mt-1 block">
                        بروتوكول اتخاذ القرار وخطة العمل التنفيذية الموصى بها للإدارة العليا وإدارات الموانئ عند تباين مؤشرات الأداء
                    </span>
                </div>
                <div>
                    <span style="background: rgba(255,255,255,0.2); color: #ffffff; padding: 5px 12px; border-radius: 9999px; font-weight: 800; font-size: 0.8rem;">
                        بروتوكول إدارة الانحراف المعياري (Standard Deviation Action Protocol)
                    </span>
                </div>
            </div>

            <div style="padding: 24px; background: #f8fafc;">
                <!-- شبكة مستويات الانحراف الرئيسية الثلاثة -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-5 mb-6">
                    
                    <!-- 1. حالة الانحراف السلبي الحاد (تراجع) -->
                    <div style="background: #ffffff; border-radius: 14px; border: 1.5px solid #fca5a5; border-top: 6px solid #dc2626; padding: 18px; box-shadow: 0 4px 12px rgba(220, 38, 38, 0.06);">
                        <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 12px;">
                            <span style="background: #fee2e2; color: #dc2626; width: 32px; height: 32px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 900; font-size: 1rem;">⚠️</span>
                            <div>
                                <h4 style="margin: 0; font-size: 1rem; font-weight: 800; color: #991b1b;">1. الانحراف السلبي الحاد (Z < -1.0)</h4>
                                <span style="font-size: 0.75rem; color: #b91c1c; font-weight: 700;">تراجع تشغيلي أو مالي ملحوظ تحت المتوسط</span>
                            </div>
                        </div>

                        <div style="font-size: 0.82rem; color: #334155; line-height: 1.6;">
                            <div style="background: #fef2f2; border-radius: 8px; padding: 8px 10px; margin-bottom: 10px; border-right: 3px solid #dc2626;">
                                <strong style="color: #991b1b;">🔍 الأسباب المحتملة:</strong>
                                <ul style="margin: 4px 0 0 0; padding-right: 18px; list-style-type: disc; font-size: 0.78rem; color: #7f1d1d;">
                                    <li>أعطال مفاجئة في رافعات الأرصفة أو معدات المناولة.</li>
                                    <li>تأخيرات ملاحية، ظروف جوية أو اختناق في القنوات.</li>
                                    <li>تعثر في إجراءات التخليص الجمركي وخروج البضائع.</li>
                                    <li>تحول خطوط ملاحية إلى موانئ بديلة.</li>
                                </ul>
                            </div>

                            <strong style="color: #0f172a; display: block; margin-bottom: 4px;">⚡ المعالجة والإجراء الفوري المطلوب:</strong>
                            <ol style="margin: 0; padding-right: 18px; list-style-type: decimal; font-size: 0.8rem; color: #1e293b; display: flex; flex-direction: column; gap: 4px;">
                                <li><strong>فحص الجاهزية الفنية:</strong> تشكيل فريق صيانة طارئ لفحص الرافعات والأرصفة وإعادتها للخدمة فوراً.</li>
                                <li><strong>تكثيف نوبات العمل:</strong> تشغيل مناوبات إضافية (24/7) لتسريع وتيرة تفريغ وشحن البواخر المتأخرة.</li>
                                <li><strong>تسهيل المسار الجمركي:</strong> التنسيق المباشر مع هيئة الجمارك والجهات الساندة لتسريع خروج الشاحنات.</li>
                                <li><strong>حوافز تسويقية:</strong> تقديم تسهيلات وتخفيضات في رسوم الأرصفة لجذب الوكلاء الملاحيين مجدداً.</li>
                            </ol>
                        </div>
                    </div>

                    <!-- 2. حالة الأداء الطبيعي المستقر (الهدف المثالي) -->
                    <div style="background: #ffffff; border-radius: 14px; border: 1.5px solid #86efac; border-top: 6px solid #16a34a; padding: 18px; box-shadow: 0 4px 12px rgba(22, 163, 74, 0.06);">
                        <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 12px;">
                            <span style="background: #dcfce7; color: #16a34a; width: 32px; height: 32px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 900; font-size: 1rem;">✅</span>
                            <div>
                                <h4 style="margin: 0; font-size: 1rem; font-weight: 800; color: #14532d;">2. الأداء الطبيعي المستقر (±1.0 σ)</h4>
                                <span style="font-size: 0.75rem; color: #15803d; font-weight: 700;">ضمن المعدل التشغيلي المستهدف والانتظام العالي</span>
                            </div>
                        </div>

                        <div style="font-size: 0.82rem; color: #334155; line-height: 1.6;">
                            <div style="background: #f0fdf4; border-radius: 8px; padding: 8px 10px; margin-bottom: 10px; border-right: 3px solid #16a34a;">
                                <strong style="color: #14532d;">🎯 التقييم التشغيلي:</strong>
                                <p style="margin: 4px 0 0 0; font-size: 0.78rem; color: #166534;">
                                    العمليات تسير بكفاءة وتوازن مثالي بين الطاقة الاستيعابية وتدفق السفن والبضائع دون اختناقات أو ركود.
                                </p>
                            </div>

                            <strong style="color: #0f172a; display: block; margin-bottom: 4px;">📋 خطوات الحفاظ على الاستقرار:</strong>
                            <ol style="margin: 0; padding-right: 18px; list-style-type: decimal; font-size: 0.8rem; color: #1e293b; display: flex; flex-direction: column; gap: 4px;">
                                <li><strong>الصيانة الوقائية الدورية:</strong> الالتزام الصارم بجداول الصيانة المجدولة لتفادي الأعطال المفاجئة.</li>
                                <li><strong>تثبيت معايير الجودة:</strong> مراقبة معدلات سرعة دوران البواخر (Turnaround Time) على الأرصفة.</li>
                                <li><strong>إدارة المساحات التخزينية:</strong> الحفاظ على نسبة إشغال متوازنة لساحات ومخازن الموانئ.</li>
                                <li><strong>التدريب المستمر:</strong> تطوير مهارات مشغلي الرافعات والكوادر البحرية للحفاظ على الكفاءة.</li>
                            </ol>
                        </div>
                    </div>

                    <!-- 3. حالة الطفرة الإنتاجية والضغط المرتفع -->
                    <div style="background: #ffffff; border-radius: 14px; border: 1.5px solid #93c5fd; border-top: 6px solid #2563eb; padding: 18px; box-shadow: 0 4px 12px rgba(37, 99, 235, 0.06);">
                        <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 12px;">
                            <span style="background: #dbeafe; color: #2563eb; width: 32px; height: 32px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 900; font-size: 1rem;">🚀</span>
                            <div>
                                <h4 style="margin: 0; font-size: 1rem; font-weight: 800; color: #1e3a8a;">3. طفرة إنتاجية / ضغط تشغيلي (Z > +1.0)</h4>
                                <span style="font-size: 0.75rem; color: #1d4ed8; font-weight: 700;">تدفق استثنائي يفوق المعدلات المعتادة بكثير</span>
                            </div>
                        </div>

                        <div style="font-size: 0.82rem; color: #334155; line-height: 1.6;">
                            <div style="background: #eff6ff; border-radius: 8px; padding: 8px 10px; margin-bottom: 10px; border-right: 3px solid #2563eb;">
                                <strong style="color: #1e3a8a;">⚠️ التحديات والمخاطر المترتبة:</strong>
                                <ul style="margin: 4px 0 0 0; padding-right: 18px; list-style-type: disc; font-size: 0.78rem; color: #1e40af;">
                                    <li>مخاطر تكدس الحاويات والبضائع في الساحات.</li>
                                    <li>ازدحام غاطس الميناء وتأخر دخول البواخر للأرصفة.</li>
                                    <li>إجهاد الآليات والمعدات والملاكات العاملة.</li>
                                </ul>
                            </div>

                            <strong style="color: #0f172a; display: block; margin-bottom: 4px;">🛡️ المعالجة وإدارة الطاقة الاستيعابية:</strong>
                            <ol style="margin: 0; padding-right: 18px; list-style-type: decimal; font-size: 0.8rem; color: #1e293b; display: flex; flex-direction: column; gap: 4px;">
                                <li><strong>فتح ساحات تخزين إضافية:</strong> تفعيل مناطق التخزين الاحتياطية وتوجيه البضائع لها فوراً.</li>
                                <li><strong>إعادة جدولة الأرصفة (Dynamic Allocation):</strong> توزيع البواخر على كافة الأرصفة الجاهزة لمنع الانتظار.</li>
                                <li><strong>استدعاء كوادر دعم:</strong> تعزيز طواقم العمل من مرافق أو موانئ مجاورة لتسريع المناولة.</li>
                                <li><strong>توثيق أسباب الطفرة:</strong> دراسة أسباب الزيادة لتثبيتها وتحويلها إلى أداء دائم ومستمر.</li>
                            </ol>
                        </div>
                    </div>

                </div>

                <!-- مصفوفة المعالجة التخصصية الذكية للمؤشر المختار حالياً -->
                <div style="background: #ffffff; border-radius: 14px; border: 1.5px solid #cbd5e1; padding: 18px 20px; box-shadow: 0 2px 8px rgba(0,0,0,0.02);">
                    <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 10px; margin-bottom: 14px; border-bottom: 1.5px solid #e2e8f0; padding-bottom: 10px;">
                        <div style="display: flex; align-items: center; gap: 8px;">
                            <span style="font-size: 1.3rem;">🎯</span>
                            <h4 style="margin: 0; font-size: 1.05rem; font-weight: 800; color: #0f172a;">
                                مصفوفة المعالجة المخصصة لمؤشر: <span style="color: #1e3a8a;">{{ $metricTitle }}</span>
                            </h4>
                        </div>
                        <span style="font-size: 0.8rem; background: #e0f2fe; color: #0369a1; font-weight: 800; padding: 4px 12px; border-radius: 6px;">
                            توصيات تشغيلية مخصصة للمؤشر النشط حالياً
                        </span>
                    </div>

                    @if($selectedMetric === 'tonnage')
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-xs md:text-sm text-slate-700">
                            <div style="background: #f8fafc; border-radius: 10px; padding: 12px; border-right: 4px solid #059669;">
                                <strong style="color: #065f46; display: block; margin-bottom: 6px; font-size: 0.9rem;">📦 معالجات الطاقة الإنتاجية بالطن عند الهبوط:</strong>
                                <ul style="margin: 0; padding-right: 16px; list-style-type: square; display: flex; flex-direction: column; gap: 4px;">
                                    <li>مراجعة معدلات شحن وتفريغ البضائع العامة والصب (Tons per Hour) لكل رصيف.</li>
                                    <li>فحص كفاءة السيور الناقلة ومضخات وتجهيزات المشتقات النفطية في خور الزبير.</li>
                                    <li>تسريع وتيرة مناولة الحاويات الثقيلة وتفريغ ساحات الاستيراد.</li>
                                </ul>
                            </div>
                            <div style="background: #f8fafc; border-radius: 10px; padding: 12px; border-right: 4px solid #2563eb;">
                                <strong style="color: #1e3a8a; display: block; margin-bottom: 6px; font-size: 0.9rem;">📈 إجراءات استدامة الطفرات الوزنية:</strong>
                                <ul style="margin: 0; padding-right: 16px; list-style-type: square; display: flex; flex-direction: column; gap: 4px;">
                                    <li>التنسيق مع شركات النقل البري لتوفير أسطول شاحنات مستمر لنقل البضائع خارج الميناء.</li>
                                    <li>توسيع سعة المخازن المسقفة للبضائع الحساسة لتقليل أوقات انتظار الشحن.</li>
                                    <li>عقد اجتماعات دورية مع كبرى الشركات المستوردة لمواءمة جداول تدفق الحمولات.</li>
                                </ul>
                            </div>
                        </div>

                    @elseif($selectedMetric === 'revenue')
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-xs md:text-sm text-slate-700">
                            <div style="background: #f8fafc; border-radius: 10px; padding: 12px; border-right: 4px solid #d97706;">
                                <strong style="color: #b45309; display: block; margin-bottom: 6px; font-size: 0.9rem;">💰 معالجات الإيراد المالي عند التراجع:</strong>
                                <ul style="margin: 0; padding-right: 16px; list-style-type: square; display: flex; flex-direction: column; gap: 4px;">
                                    <li>تدقيق جداول تحصيل عوائد الموانئ ورسوم الرسو والخدمات الملاحية بدقة.</li>
                                    <li>فحص الفارق بين الإيراد الكلي والصافي ومتابعة نفقات التشغيل لضبط التكاليف.</li>
                                    <li>مراجعة الديون والالتزامات المستحقة على الوكلاء الملاحيين والشركات المشغلة.</li>
                                </ul>
                            </div>
                            <div style="background: #f8fafc; border-radius: 10px; padding: 12px; border-right: 4px solid #059669;">
                                <strong style="color: #065f46; display: block; margin-bottom: 6px; font-size: 0.9rem;">💳 تعزيز استقرار التدفقات النقدية:</strong>
                                <ul style="margin: 0; padding-right: 16px; list-style-type: square; display: flex; flex-direction: column; gap: 4px;">
                                    <li>أتمتة الدفع والتحصيل الإلكتروني لتقليل الفاقد الزمني وضمان الإيداع الفوري.</li>
                                    <li>تنويع مصادر الإيراد من الخدمات اللوجستية الإضافية (تخزين، إمداد، مناولة متخصصة).</li>
                                </ul>
                            </div>
                        </div>

                    @elseif($selectedMetric === 'ships')
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-xs md:text-sm text-slate-700">
                            <div style="background: #f8fafc; border-radius: 10px; padding: 12px; border-right: 4px solid #0284c7;">
                                <strong style="color: #0369a1; display: block; margin-bottom: 6px; font-size: 0.9rem;">🚢 معالجات حركة البواخر والناقلات عند التراجع:</strong>
                                <ul style="margin: 0; padding-right: 16px; list-style-type: square; display: flex; flex-direction: column; gap: 4px;">
                                    <li>فحص أعماق القنوات الملاحية والغواطس والتأكد من عدم وجود عوائق ملاحية.</li>
                                    <li>زيادة كفاءة قاطرات السحب وزوارق الإرشاد البحري وتفادي تأخير صعود المرشدين.</li>
                                    <li>مراجعة سرعة تخليص ومغادرة السفن لتقليل زمن الانتظار في منطقة المخطاف.</li>
                                </ul>
                            </div>
                            <div style="background: #f8fafc; border-radius: 10px; padding: 12px; border-right: 4px solid #1e3a8a;">
                                <strong style="color: #1e3a8a; display: block; margin-bottom: 6px; font-size: 0.9rem;">⚓ إدارة فترات الذروة وازدحام المخطاف:</strong>
                                <ul style="margin: 0; padding-right: 16px; list-style-type: square; display: flex; flex-direction: column; gap: 4px;">
                                    <li>تطبيق نظام التوزيع الذكي للأرصفة (Pre-arrival Berth Planning) قبل وصول البواخر بـ 48 ساعة.</li>
                                    <li>تنسيق عمليات التزود بالوقود والمياه والخدمات اللوجستية بالتوازي مع عمليات التفريغ.</li>
                                </ul>
                            </div>
                        </div>

                    @elseif($selectedMetric === 'teu')
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-xs md:text-sm text-slate-700">
                            <div style="background: #f8fafc; border-radius: 10px; padding: 12px; border-right: 4px solid #6366f1;">
                                <strong style="color: #4338ca; display: block; margin-bottom: 6px; font-size: 0.9rem;">📐 معالجات حركة الحاويات TEU والتكدس:</strong>
                                <ul style="margin: 0; padding-right: 16px; list-style-type: square; display: flex; flex-direction: column; gap: 4px;">
                                    <li>إلزام الخطوط الملاحية بإخلاء وإعادة تصدير الحاويات الفارغة (Empty Return) دورياً.</li>
                                    <li>تقليص فترة مكوث الحاوية (Dwell Time) في ساحات الميناء عبر فرض رسوم تصاعدية للمتأخرات.</li>
                                    <li>زيادة بوابات الدخول والخروج ومسارات الفحص الإشعاعي (Scanners) لتسريع الشاحنات.</li>
                                </ul>
                            </div>
                            <div style="background: #f8fafc; border-radius: 10px; padding: 12px; border-right: 4px solid #059669;">
                                <strong style="color: #065f46; display: block; margin-bottom: 6px; font-size: 0.9rem;">🏗️ تعزيز كفاءة محطات الحاويات:</strong>
                                <ul style="margin: 0; padding-right: 16px; list-style-type: square; display: flex; flex-direction: column; gap: 4px;">
                                    <li>الاعتماد على رافعات الساحات الجسرية (RTG / STS) المتطورة لزيادة سعة الرص الرأسي.</li>
                                    <li>ربط ساحات الحاويات بنظام رقمي متكامل لتتبع مواقع الحاويات وتجنب الحركات المزدوجة.</li>
                                </ul>
                            </div>
                        </div>
                    @endif
                </div>

                <!-- توصية معالجة التشتت السنوي العام -->
                <div style="margin-top: 16px; background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%); color: #ffffff; border-radius: 12px; padding: 14px 18px; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 10px;">
                    <div style="display: flex; align-items: center; gap: 10px;">
                        <span style="font-size: 1.5rem;">📊</span>
                        <div>
                            <strong style="font-size: 0.92rem; display: block;">معامل التشتت الحالي لسنة {{ $d['fiscalYears']->firstWhere('id', $selectedFiscalYearId)?->year }}: {{ number_format($stat['cv'] ?? 0, 1) }}% ({{ $stat['stability'] ?? '—' }})</strong>
                            <span style="font-size: 0.78rem; opacity: 0.85;">
                                @if(($stat['cv'] ?? 0) <= 15)
                                    الأداء مستقر جداً ومنتظم شهرياً. يُنصح بالحفاظ على معايير التشغيل والتعاقدات الحالية.
                                @elseif(($stat['cv'] ?? 0) <= 30)
                                    الأداء متوسط الاستقرار مع تذبذب موسمي معتاد. يُوصى بتنسيق مواعيد تدفق البواخر لتنعيم المنحنى الشهري.
                                @else
                                    تذبذب مرتفع وعدم استقرار حاد. يتطلب الأمر إبرام عقود طويلة الأجل مع الخطوط الملاحية وتكامل توزيع الأحمال بين الموانئ.
                                @endif
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
</x-filament-panels::page>
