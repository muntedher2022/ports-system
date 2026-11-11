<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Spatie\LaravelPdf\Facades\Pdf;

class ActivityReportController extends Controller
{
    /**
     * توليد التقرير الدوري الرسمي كملف PDF عالي الجودة
     */
    public function generatePeriodicPdf(Request $request): mixed
    {
        $user = auth()->user();
        abort_unless(
            $user && ($user->isGeneralManager() || $user->hasAnyRole(['مسؤول المتابعة المركزية والعمليات', 'operations_manager']) || $user->user_type === 'operations_manager'),
            403,
            'غير مصرح لك بتوليد تقارير نشاطات المدير العام.'
        );

        $reportType = $request->get('type', 'custom'); // weekly, monthly, yearly, custom
        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');
        $category = $request->get('category');
        $importance = $request->get('importance');
        $status = $request->get('status');
        $onlyReportActivities = $request->boolean('only_included_in_report', true);

        $query = Activity::with(['attachments', 'user']);

        // تصفية الأنشطة حسب تحديد إدراجها بالتقرير الدوري
        if ($onlyReportActivities) {
            $query->where('include_in_periodic_report', true);
        }

        // حساب عنوان ونطاق التقرير بناءً على التواريخ
        $titlePeriod = '';
        $now = Carbon::now();

        if ($startDate && $endDate) {
            $startCarbon = Carbon::parse($startDate);
            $endCarbon = Carbon::parse($endDate);

            if ($startCarbon->isSameDay($endCarbon)) {
                $titlePeriod = 'ليوم: ' . $startCarbon->format('Y/m/d');
                $reportTitle = 'تقرير نشاطات المدير العام';
            } elseif ($startCarbon->diffInDays($endCarbon) == 6) {
                $titlePeriod = 'للأسبوع: من ' . $startCarbon->format('Y/m/d') . ' إلى ' . $endCarbon->format('Y/m/d');
                $reportTitle = 'التقرير الأسبوعي لنشاطات المدير العام';
            } elseif ($startCarbon->isSameMonth($endCarbon) && $startCarbon->day == 1 && $endCarbon->day == $startCarbon->daysInMonth) {
                $titlePeriod = 'لشهر ' . $startCarbon->translatedFormat('F') . ' لسنة ' . $startCarbon->year;
                $reportTitle = 'التقرير الشهري لنشاطات المدير العام';
            } else {
                $titlePeriod = 'للفترة من ' . $startCarbon->format('Y/m/d') . ' إلى ' . $endCarbon->format('Y/m/d');
                $reportTitle = 'تقرير نشاطات المدير العام للفترة المحددة';
            }
        } elseif ($reportType === 'monthly') {
            $month = (int) ($request->get('month', $now->month));
            $year = (int) ($request->get('year', $now->year));
            $start = Carbon::create($year, $month, 1)->startOfMonth();
            $end = $start->copy()->endOfMonth();
            $startDate = $start->format('Y-m-d');
            $endDate = $end->format('Y-m-d');
            $titlePeriod = 'لشهر ' . $start->translatedFormat('F') . ' لسنة ' . $year;
            $reportTitle = 'التقرير الشهري لنشاطات المدير العام';
        } elseif ($reportType === 'weekly') {
            $start = $startDate ? Carbon::parse($startDate)->startOfWeek(Carbon::SATURDAY) : $now->copy()->startOfWeek(Carbon::SATURDAY);
            $end = $start->copy()->endOfWeek(Carbon::FRIDAY);
            $startDate = $start->format('Y-m-d');
            $endDate = $end->format('Y-m-d');
            $titlePeriod = 'للأسبوع: من ' . $start->format('Y/m/d') . ' إلى ' . $end->format('Y/m/d');
            $reportTitle = 'التقرير الأسبوعي لنشاطات المدير العام';
        } elseif ($startDate) {
            $titlePeriod = 'ابتداءً من تاريخ ' . Carbon::parse($startDate)->format('Y/m/d');
            $reportTitle = 'تقرير نشاطات المدير العام';
        } elseif ($endDate) {
            $titlePeriod = 'حتى تاريخ ' . Carbon::parse($endDate)->format('Y/m/d');
            $reportTitle = 'تقرير نشاطات المدير العام';
        } else {
            $titlePeriod = 'كافة النشاطات المسجلة';
            $reportTitle = 'التقرير الشامل لنشاطات المدير العام';
        }

        if ($startDate) {
            $query->whereDate('activity_date', '>=', $startDate);
        }
        if ($endDate) {
            $query->whereDate('activity_date', '<=', $endDate);
        }
        if ($category && $category !== 'all') {
            $query->where('category', $category);
        }
        if ($importance && $importance !== 'all') {
            $query->where('importance', $importance);
        }
        if ($status && $status !== 'all') {
            $query->where('status', $status);
        }

        // إخفاء الأنشطة السرية لغير المخولين
        if (! auth()->user()->isGeneralManager()) {
            $query->where('is_confidential', false);
        }

        $activities = $query->orderBy('activity_date', 'asc')
            ->orderBy('activity_time', 'asc')
            ->get();

        // إحصائيات التقرير
        $stats = [
            'total' => $activities->count(),
            'completed' => $activities->where('status', 'completed')->count(),
            'in_progress' => $activities->where('status', 'in_progress')->count(),
            'meetings' => $activities->where('category', 'اجتماع رسمي')->count(),
            'field_visits' => $activities->where('category', 'زيارة ميدانية')->count(),
            'delegations' => $activities->where('category', 'استقبال وفد')->count(),
            'conferences' => $activities->where('category', 'مؤتمر أو ندوة')->count(),
            'decisions' => $activities->where('category', 'قرار وتوجيه')->count(),
            'projects' => $activities->where('category', 'متابعة مشاريع')->count(),
            'others' => $activities->filter(function ($a) {
                return ! in_array($a->category, ['اجتماع رسمي', 'زيارة ميدانية', 'استقبال وفد', 'مؤتمر أو ندوة', 'قرار وتوجيه', 'متابعة مشاريع']);
            })->count(),
            'urgent' => $activities->where('importance', 'urgent')->count(),
        ];

        $viewData = [
            'activities' => $activities,
            'reportTitle' => $reportTitle,
            'titlePeriod' => $titlePeriod,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'stats' => $stats,
            'generatedAt' => Carbon::now()->format('Y/m/d H:i'),
            'generatedBy' => auth()->user()->name,
        ];

        $filename = 'تقرير_نشاطات_المدير_العام_' . date('Y_m_d_His') . '.pdf';

        return Pdf::view('reports.executive-periodic', $viewData)
            ->format('a4')
            ->portrait()
            ->margins(10, 10, 10, 10)
            ->name($filename)
            ->inline();
    }

    /**
     * توليد بطاقة إحاطة لنشاط واحد
     */
    public function generateSingleActivityPdf(Activity $activity): mixed
    {
        $user = auth()->user();
        abort_unless(
            $user && ($user->isGeneralManager() || $user->hasAnyRole(['مسؤول المتابعة المركزية والعمليات', 'operations_manager']) || $user->user_type === 'operations_manager'),
            403,
            'غير مصرح لك بتوليد تقارير نشاطات المدير العام.'
        );

        if ($activity->is_confidential && ! $user->isGeneralManager()) {
            abort(403, 'غير مصرح باستعراض هذا النشاط السري');
        }

        $activity->load(['attachments', 'user']);

        $viewData = [
            'activity' => $activity,
            'generatedAt' => Carbon::now()->format('Y/m/d H:i'),
            'generatedBy' => auth()->user()->name,
        ];

        $filename = 'نشاط_' . str_replace(' ', '_', $activity->title) . '_' . date('Y_m_d') . '.pdf';

        return Pdf::view('reports.single-activity', $viewData)
            ->format('a4')
            ->portrait()
            ->margins(10, 10, 10, 10)
            ->name($filename)
            ->inline();
    }
}
