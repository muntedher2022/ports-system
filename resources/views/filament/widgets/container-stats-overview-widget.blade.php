<x-filament-widgets::widget>
    @php
        $stats = $this->getStats();
    @endphp

    <style>
        .gcpi-container-widget-wrapper {
            margin-top: 4px;
            direction: rtl;
        }
        .gcpi-container-section-title {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 10px;
            padding: 0 4px;
        }
        .gcpi-container-section-title h3 {
            font-size: 0.95rem;
            font-weight: 800;
            color: #1e293b;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .gcpi-container-stats-row {
            display: grid;
            grid-template-columns: repeat(1, minmax(0, 1fr));
            gap: 12px;
            width: 100%;
        }
        @media (min-width: 640px) {
            .gcpi-container-stats-row {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }
        @media (min-width: 1024px) {
            .gcpi-container-stats-row {
                grid-template-columns: repeat(4, minmax(0, 1fr));
            }
        }
        .gcpi-c-card {
            background: #ffffff;
            border-radius: 14px;
            border: 1px solid #e2e8f0;
            padding: 14px 16px;
            box-shadow: 0 4px 16px -2px rgba(0, 0, 0, 0.04);
            transition: all 0.2s ease;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            text-decoration: none;
        }
        .gcpi-c-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px -2px rgba(0, 0, 0, 0.08);
        }
        .c-card-amber {
            border-top: 4px solid #d97706;
            background: linear-gradient(180deg, #fffbeb 0%, #ffffff 100%);
        }
        .c-card-rose {
            border-top: 4px solid #e11d48;
            background: linear-gradient(180deg, #fff1f2 0%, #ffffff 100%);
        }
        .c-card-indigo {
            border-top: 4px solid #4f46e5;
            background: linear-gradient(180deg, #eef2ff 0%, #ffffff 100%);
        }
        .c-card-teal {
            border-top: 4px solid #0d9488;
            background: linear-gradient(180deg, #f0fdfa 0%, #ffffff 100%);
        }
    </style>

    <div class="gcpi-container-widget-wrapper">
        <div class="gcpi-container-section-title">
            <h3>
                <span>📦</span>
                <span>موقف الحاويات المتخلفة والخطرة (سنة {{ $stats['currentYear'] }})</span>
            </h3>
            <a href="{{ url('/admin/container-status-matrix') }}" class="text-xs font-bold text-blue-600 hover:text-blue-800 flex items-center gap-1">
                <span>استعراض مصفوفة الحاويات الشاملة</span>
                <span dir="ltr">←</span>
            </a>
        </div>

        <div class="gcpi-container-stats-row">
            <!-- 1. الحاويات المتخلفة -->
            <a href="{{ url('/admin/container-status-matrix?type=abandoned') }}" class="gcpi-c-card c-card-amber">
                <div class="flex items-center justify-between mb-1.5">
                    <span class="text-xs font-bold text-amber-900">إجمالي الحاويات المتخلفة</span>
                    <span class="p-1.5 rounded-lg bg-amber-100 text-amber-700">📦</span>
                </div>
                <div class="text-lg font-extrabold text-gray-900 mb-1 font-mono">
                    {{ number_format($stats['abandonedTotal']) }} <span class="text-xs font-normal text-gray-500">حاوية</span>
                </div>
                <div class="text-[0.72rem] text-gray-600 font-semibold flex items-center justify-between">
                    <span>🏛️ حكومي: {{ number_format($stats['abandonedGov']) }}</span>
                    <span>🏢 خاص: {{ number_format($stats['abandonedPrivate']) }}</span>
                </div>
            </a>

            <!-- 2. الحاويات الخطرة -->
            <a href="{{ url('/admin/container-status-matrix?type=dangerous') }}" class="gcpi-c-card c-card-rose">
                <div class="flex items-center justify-between mb-1.5">
                    <span class="text-xs font-bold text-rose-900">إجمالي الحاويات الخطرة</span>
                    <span class="p-1.5 rounded-lg bg-rose-100 text-rose-700">⚠️</span>
                </div>
                <div class="text-lg font-extrabold text-rose-700 mb-1 font-mono">
                    {{ number_format($stats['dangerousTotal']) }} <span class="text-xs font-normal text-rose-500">حاوية</span>
                </div>
                <div class="text-[0.72rem] text-rose-600 font-semibold">
                    <span>🔴 تتطلب معالجة ومتابعة مستمرة</span>
                </div>
            </a>

            <!-- 3. الميناء الأكثر تسجيلاً -->
            <div class="gcpi-c-card c-card-indigo">
                <div class="flex items-center justify-between mb-1.5">
                    <span class="text-xs font-bold text-indigo-900">أعلى ميناء في رصيد الحاويات</span>
                    <span class="p-1.5 rounded-lg bg-indigo-100 text-indigo-700">⚓</span>
                </div>
                <div class="text-base font-extrabold text-indigo-950 mb-1 truncate">
                    {{ $stats['topPortName'] }}
                </div>
                <div class="text-[0.72rem] text-indigo-600 font-semibold">
                    <span>إجمالي الرصيد: {{ number_format($stats['topPortCount']) }} حاوية</span>
                </div>
            </div>

            <!-- 4. عدد الجهات والوزارات المسجلة -->
            <a href="{{ url('/admin/container-entities') }}" class="gcpi-c-card c-card-teal">
                <div class="flex items-center justify-between mb-1.5">
                    <span class="text-xs font-bold text-teal-900">الجهات والوزارات المسجلة</span>
                    <span class="p-1.5 rounded-lg bg-teal-100 text-teal-700">🏛️</span>
                </div>
                <div class="text-lg font-extrabold text-teal-900 mb-1 font-mono">
                    {{ number_format($stats['entitiesCount']) }} <span class="text-xs font-normal text-teal-600">جهة / وزارة</span>
                </div>
                <div class="text-[0.72rem] text-teal-600 font-semibold">
                    <span>اضغط لإدارة وتحديث بيانات الجهات</span>
                </div>
            </a>
        </div>
    </div>
</x-filament-widgets::widget>
