<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use Illuminate\Http\Request;
use Illuminate\View\View;

class NewsController extends Controller
{
    /**
     * عرض الصفحة الرئيسية للبوابة الإخبارية للأنشطة
     */
    public function index(Request $request): View
    {
        $category = $request->get('category');
        $search = $request->get('q');
        $dateFrom = $request->get('from');
        $dateTo = $request->get('to');
        $importance = $request->get('importance');

        // استبعاد كافة الأنشطة السرية برمجياً وبشكل صارم
        $query = Activity::where('is_confidential', false)
            ->with(['imageAttachments', 'primaryAttachment']);

        if ($category && $category !== 'all') {
            $query->where('category', $category);
        }

        if ($importance && $importance !== 'all') {
            $query->where('importance', $importance);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('short_description', 'like', "%{$search}%")
                  ->orWhere('detailed_notes', 'like', "%{$search}%")
                  ->orWhere('location', 'like', "%{$search}%")
                  ->orWhere('attendees', 'like', "%{$search}%");
            });
        }

        if ($dateFrom) {
            $query->whereDate('activity_date', '>=', $dateFrom);
        }

        if ($dateTo) {
            $query->whereDate('activity_date', '<=', $dateTo);
        }

        // آخر 10 أخبار ونشاطات مضافة لشريط الأخبار المتحرك (يتحدث تلقائياً مع كل إضافة جديدة)
        $breakingNews = Activity::where('is_confidential', false)
            ->latest('id')
            ->take(10)
            ->get();

        // إحصائيات وأعداد التصنيفات لشريط التبويبات
        $categoriesStats = Activity::where('is_confidential', false)
            ->selectRaw('category, count(*) as count')
            ->groupBy('category')
            ->pluck('count', 'category')
            ->toArray();

        $totalPublicActivities = Activity::where('is_confidential', false)->count();

        // الترتيب: حسب تاريخ النشاط الفعلي تنازلياً، ثم وقت النشاط، ثم أحدث إضافة (ID) لضمان تسلسل زمني صحيح
        $activities = $query->orderBy('activity_date', 'desc')
            ->orderBy('activity_time', 'desc')
            ->orderBy('id', 'desc')
            ->paginate(9)
            ->withQueryString();

        // آخر 10 نشاطات مضافة للسلايد شو الرئيسي
        $sliderActivities = Activity::where('is_confidential', false)
            ->with(['imageAttachments', 'primaryAttachment'])
            ->latest('id')
            ->take(10)
            ->get();

        $latestPublicId = Activity::where('is_confidential', false)->max('id') ?? 0;

        // استخراج الخبر البارز (Featured Story) للصفحة الرئيسية في حال عدم وجود بحث أو تصفية معينة
        $featuredActivity = null;
        if (! $category && ! $search && ! $dateFrom && ! $dateTo && $activities->currentPage() === 1) {
            $featuredActivity = $sliderActivities->first() ?: $activities->first();
        }

        // ─── جلب أحدث مؤشرات الموانئ مطابقة تماماً لصفحات Backend ───
        $latestVesselStatus = \App\Models\DailyVesselStatus::latest('record_date')->first();

        // 1. حساب الإيراد الكلي والصافي بنفس خوارزمية صفحة TotalRevenueMatrix في Backend
        $revMatrixPage = new \App\Filament\Pages\TotalRevenueMatrix();
        $revMatrixPage->mount();
        $revData = $revMatrixPage->getDataProperty();

        // 2. حساب الطاقة الإنتاجية بنفس خدمة التحليل المستخدمة في صفحة TotalCumulativeCapacity
        $analyticsService = app(\App\Services\PortAnalyticsService::class);
        $currentFy = \App\Models\FiscalYear::where('is_current', true)->first() ?? \App\Models\FiscalYear::latest('year')->first();
        $latestCapacityMonthId = \App\Models\MonthlyPortRecord::where('fiscal_year_id', $currentFy?->id)->max('month_id');
        $capacityMonth = \App\Models\Month::find($latestCapacityMonthId);
        $latestMonthNum = $capacityMonth?->month_number ?? 10;

        // إجمالي الطاقة الإنتاجية التراكمية لكافة الموانئ حتى أحدث شهر
        $capacityCumulative = $currentFy
            ? $analyticsService->getCumulativeSummary((int) $currentFy->id, (int) $latestMonthNum, null)
            : [];

        // تفصيل كل ميناء تراكمياً
        $activePorts = \App\Models\Port::where('is_active', true)->whereHas('monthlyPortRecords')->orderBy('sort_order')->get();
        $portsBreakdown = [];
        if ($currentFy) {
            foreach ($activePorts as $p) {
                $pSum = $analyticsService->getCumulativeSummary((int) $currentFy->id, (int) $latestMonthNum, (int) $p->id);
                $portsBreakdown[] = [
                    'name' => $p->name_ar,
                    'ships' => $pSum['total_ships'],
                    'teu' => $pSum['total_teu'],
                    'cargo' => $pSum['total_cargo_weight'],
                    'total_tonnage' => $pSum['total_tonnage'],
                ];
            }
        }

        // تفصيل مراكز الإيراد مطابقاً لـ TotalRevenueMatrix
        $centersBreakdown = [];
        foreach ($revData['centers'] as $center) {
            $centersBreakdown[] = [
                'name' => $center->name_ar,
                'total_gross' => $revData['centerTotals'][$center->id] ?? 0,
            ];
        }

        $portsDashboard = [
            'vessels' => $latestVesselStatus,
            'fiscal_year' => $currentFy,
            'month' => $capacityMonth,
            'capacity' => [
                'total_ships' => $capacityCumulative['total_ships'] ?? 0,
                'oil_exported_tons' => $capacityCumulative['oil_exported_tons'] ?? 0,
                'oil_imported_tons' => $capacityCumulative['oil_imported_tons'] ?? 0,
                'oil_total_tons' => $capacityCumulative['oil_total_tons'] ?? 0,
                'imported_teu' => $capacityCumulative['imported_teu'] ?? 0,
                'exported_teu' => $capacityCumulative['exported_teu'] ?? 0,
                'total_teu' => $capacityCumulative['total_teu'] ?? 0,
                'total_cargo_weight' => $capacityCumulative['total_cargo_weight'] ?? 0,
                'total_tonnage' => $capacityCumulative['total_tonnage'] ?? 0,
                'ports' => $portsBreakdown,
            ],
            'revenue' => [
                'grand_gross' => $revData['grandGrossTotal'] ?? 0,
                'grand_net' => $revData['grandNetTotal'] ?? 0,
                'centers' => $centersBreakdown,
            ],
        ];

        return view('news.index', compact(
            'activities',
            'featuredActivity',
            'sliderActivities',
            'latestPublicId',
            'breakingNews',
            'categoriesStats',
            'totalPublicActivities',
            'category',
            'search',
            'importance',
            'dateFrom',
            'dateTo',
            'portsDashboard'
        ));
    }

    /**
     * فحص النشاطات الجديدة المضافة أو المحذوفة بشكل لحظي لتحديث الواجهة تلقائياً دون إعادة تحميل الصفحة
     */
    public function livePulse(Request $request): \Illuminate\Http\JsonResponse
    {
        $lastId = (int) $request->get('last_id', 0);
        $visibleIdsStr = $request->get('visible_ids', '');

        // 1. البحث عن أي نشاطات عامة جديدة أضيفت بمعرّف أكبر من $lastId
        $newActivities = Activity::where('is_confidential', false)
            ->where('id', '>', $lastId)
            ->with(['imageAttachments', 'primaryAttachment'])
            ->orderBy('id', 'asc')
            ->get();

        $items = $newActivities->map(function ($act) {
            $img = $act->primaryAttachment ?: $act->imageAttachments->first();
            return [
                'id' => $act->id,
                'title' => $act->title,
                'short_description' => \Illuminate\Support\Str::limit($act->short_description, 220),
                'category' => $act->category,
                'importance' => $act->importance,
                'importance_label' => $act->importance_label,
                'formatted_date' => $act->formatted_date,
                'location' => $act->location ? \Illuminate\Support\Str::limit($act->location, 25) : null,
                'images_count' => $act->imageAttachments->count(),
                'image_url' => $img ? $img->base64_data_uri : null,
                'url' => route('news.show', $act->id),
                'is_urgent' => ($act->importance === 'urgent' || $act->category === 'قرار وتوجيه'),
            ];
        });

        $currentMaxId = Activity::where('is_confidential', false)->max('id') ?? 0;

        // 2. فحص الأنشطة المعروضة حالياً على شاشة العميل لمعرفة إن كان أي منها قد تم حذفه أو تحويله لسري
        $deletedIds = [];
        $sliderFallback = null;

        if (!empty($visibleIdsStr)) {
            $visibleIds = array_filter(array_map('intval', explode(',', $visibleIdsStr)));
            if (!empty($visibleIds)) {
                // الأنشطة العامة النشطة التي ما زالت موجودة
                $stillActiveIds = Activity::where('is_confidential', false)
                    ->whereIn('id', $visibleIds)
                    ->pluck('id')
                    ->toArray();

                // الأنشطة التي كانت معروضة واختفت (حذفت بالكامل أو أصبحت سرية)
                $deletedIds = array_values(array_diff($visibleIds, $stillActiveIds));
            }
        }

        // إذا تم رصد حذف، نجهز آخر 10 أنشطة حالية لإعادة ملء وترتيب السلايد شو تلقائياً
        if (!empty($deletedIds)) {
            $sliderFallback = Activity::where('is_confidential', false)
                ->with(['imageAttachments', 'primaryAttachment'])
                ->latest('id')
                ->take(10)
                ->get()
                ->map(function ($act) {
                    $img = $act->primaryAttachment ?: $act->imageAttachments->first();
                    return [
                        'id' => $act->id,
                        'title' => $act->title,
                        'short_description' => \Illuminate\Support\Str::limit($act->short_description, 220),
                        'category' => $act->category,
                        'importance' => $act->importance,
                        'importance_label' => $act->importance_label,
                        'formatted_date' => $act->formatted_date,
                        'location' => $act->location ? \Illuminate\Support\Str::limit($act->location, 25) : null,
                        'images_count' => $act->imageAttachments->count(),
                        'image_url' => $img ? $img->base64_data_uri : null,
                        'url' => route('news.show', $act->id),
                    ];
                });
        }

        $totalPublic = Activity::where('is_confidential', false)->count();

        $hasUpdates = ($newActivities->isNotEmpty() || !empty($deletedIds));

        return response()->json([
            'has_updates' => $hasUpdates,
            'latest_id' => $currentMaxId,
            'count' => $newActivities->count(),
            'items' => $items,
            'deleted_ids' => $deletedIds,
            'slider_fallback' => $sliderFallback,
            'total_count' => $totalPublic,
        ]);
    }

    /**
     * عرض التغطية الإخبارية الكاملة لنشاط محدد
     */
    public function show(Activity $activity): View
    {
        // حماية الأنشطة السرية
        if ($activity->is_confidential) {
            abort(404, 'النشاط المطلوب غير متاح أو قيد التدقيق الداخلي.');
        }

        $activity->load(['imageAttachments', 'attachments', 'user']);

        // أنشطة ذات صلة بنفس التصنيف (حسب التسلسل الزمني وتاريخ النشاط)
        $relatedActivities = Activity::where('is_confidential', false)
            ->where('id', '!=', $activity->id)
            ->where('category', $activity->category)
            ->orderBy('activity_date', 'desc')
            ->orderBy('activity_time', 'desc')
            ->orderBy('id', 'desc')
            ->take(3)
            ->get();

        // إذا لم توجد أنشطة كافية بنفس التصنيف، جلب أحدث الأنشطة بحسب التاريخ
        if ($relatedActivities->count() < 3) {
            $moreActivities = Activity::where('is_confidential', false)
                ->where('id', '!=', $activity->id)
                ->whereNotIn('id', $relatedActivities->pluck('id'))
                ->orderBy('activity_date', 'desc')
                ->orderBy('activity_time', 'desc')
                ->orderBy('id', 'desc')
                ->take(3 - $relatedActivities->count())
                ->get();

            $relatedActivities = $relatedActivities->merge($moreActivities);
        }

        $breakingNews = Activity::where('is_confidential', false)
            ->latest('id')
            ->take(10)
            ->get();

        return view('news.show', compact('activity', 'relatedActivities', 'breakingNews'));
    }
}
