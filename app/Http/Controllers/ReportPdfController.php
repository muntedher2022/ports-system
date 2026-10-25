<?php

namespace App\Http\Controllers;

use App\Models\FiscalYear;
use App\Services\ActivityLogger;
use App\Services\PdfReportService;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ReportPdfController extends Controller
{
    protected PdfReportService $pdfService;

    public function __construct(PdfReportService $pdfService)
    {
        $this->pdfService = $pdfService;
    }

    /**
     * 1. تحميل PDF لمصفوفة الإيراد الكلي
     */
    public function exportTotalRevenueMatrix(Request $request): Response
    {
        $fiscalYearId = $request->integer('fiscal_year_id')
            ?: (FiscalYear::where('is_current', true)->first()?->id ?? FiscalYear::first()?->id);

        ActivityLogger::log('exported_pdf', 'تصدير تقرير مصفوفة الإيراد الكلي والصافي للمراكز السبعة (PDF)');

        $pdfContent = $this->pdfService->generateTotalRevenueMatrixPdf($fiscalYearId);

        return response($pdfContent, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="تقرير_الإيراد_الكلي_للمراكز_السبعة.pdf"',
        ]);
    }

    /**
     * 2. تحميل PDF لملخص الطاقة الإنتاجية التراكمي (ورقة total)
     */
    public function exportTotalCumulativeCapacity(Request $request): Response
    {
        $fiscalYearId = $request->integer('fiscal_year_id')
            ?: (FiscalYear::where('is_current', true)->first()?->id ?? FiscalYear::first()?->id);
        $monthNumber = $request->integer('month_number', 7);
        $portId = $request->filled('port_id') ? $request->integer('port_id') : null;
        $onlyTotal = $request->boolean('only_total') || $request->input('port_id') === 'total';

        ActivityLogger::log('exported_pdf', 'تصدير تقرير ملخص الطاقة الإنتاجية التراكمي (PDF)');

        $pdfContent = $this->pdfService->generateTotalCumulativeCapacityPdf($fiscalYearId, $monthNumber, $portId, $onlyTotal);

        return response($pdfContent, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="تقرير_إجمالي_الطاقة_الإنتاجية.pdf"',
        ]);
    }

    /**
     * 3. تحميل PDF لمقارنة الطاقة الإنتاجية بين عامين (YoY)
     */
    public function exportCapacityComparison(Request $request): Response
    {
        $currYearId = $request->integer('curr_year_id')
            ?: (FiscalYear::where('is_current', true)->first()?->id ?? FiscalYear::orderBy('year', 'desc')->first()?->id);
        $prevYearId = $request->integer('prev_year_id')
            ?: (FiscalYear::where('year', 2025)->first()?->id ?? $currYearId);
        $monthNumber = $request->integer('month_number', 7);

        ActivityLogger::log('exported_pdf', 'تصدير تقرير مقارنة الطاقة الإنتاجية بين عامين YoY (PDF)');

        $pdfContent = $this->pdfService->generateCapacityComparisonPdf($prevYearId, $currYearId, $monthNumber);

        return response($pdfContent, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="تقرير_مقارنة_الطاقة_الإنتاجية.pdf"',
        ]);
    }

    /**
     * 4. تحميل PDF لمقارنة الإيراد لكل التشكيلات بين عامين (YoY)
     */
    public function exportRevenueComparison(Request $request): Response
    {
        $currYearId = $request->integer('curr_year_id')
            ?: (FiscalYear::where('is_current', true)->first()?->id ?? FiscalYear::orderBy('year', 'desc')->first()?->id);
        $prevYearId = $request->integer('prev_year_id')
            ?: (FiscalYear::where('year', 2025)->first()?->id ?? $currYearId);

        ActivityLogger::log('exported_pdf', 'تصدير تقرير مقارنة الإيراد لكل التشكيلات بين عامين YoY (PDF)');

        $pdfContent = $this->pdfService->generateRevenueComparisonPdf($prevYearId, $currYearId);

        return response($pdfContent, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="تقرير_مقارنة_الإيراد_لكل_التشكيلات.pdf"',
        ]);
    }

    /**
     * 5. تحميل PDF لمقارنة الأداء متعددة السنوات (3 سنوات فأكثر)
     */
    public function exportMultiYearComparison(Request $request): Response
    {
        $comparisonType = $request->input('comparison_type', 'capacity');
        $periodScope = $request->input('period_scope', 'month');
        $monthNumber = $request->integer('month_number', 7);
        $portId = $request->filled('port_id') ? $request->integer('port_id') : null;

        $yearIds = $request->input('year_ids', []);
        if (is_string($yearIds)) {
            $yearIds = explode(',', $yearIds);
        }
        $yearIds = array_map('intval', array_filter((array) $yearIds));

        ActivityLogger::log('exported_pdf', 'تصدير تقرير مقارنة الأداء متعددة السنوات (PDF)');

        $pdfContent = $this->pdfService->generateMultiYearComparisonPdf(
            $comparisonType,
            $periodScope,
            $monthNumber,
            $portId,
            $yearIds
        );

        return response($pdfContent, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="تقرير_مقارنة_الأداء_متعددة_السنوات.pdf"',
        ]);
    }

    /**
     * 6. تحميل PDF لتقرير الانحراف المعياري والاستقرار
     */
    public function exportStandardDeviation(Request $request): Response
    {
        $fiscalYearId = $request->integer('fiscal_year_id')
            ?: (FiscalYear::where('is_current', true)->first()?->id ?? FiscalYear::orderBy('year', 'desc')->first()?->id);
        $portId = $request->filled('port_id') ? $request->integer('port_id') : null;
        $metric = $request->input('metric', 'tonnage');

        ActivityLogger::log('exported_pdf', 'تصدير تقرير تحليل الانحراف المعياري والاستقرار (PDF)');

        $pdfContent = $this->pdfService->generateStandardDeviationPdf($fiscalYearId, $portId, $metric);

        return response($pdfContent, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="تقرير_الانحراف_المعياري_والاستقرار.pdf"',
        ]);
    }

    /**
     * 7. معاينة طباعة الموقف اليومي لحركة السفن (A4 Landscape)
     */
    public function previewDailyVesselTraffic(Request $request)
    {
        $fromDate = $request->input('from_date') ?: now()->startOfMonth()->format('Y-m-d');
        $toDate = $request->input('to_date') ?: now()->endOfMonth()->format('Y-m-d');

        $records = \App\Models\DailyVesselStatus::whereDate('record_date', '>=', $fromDate)
            ->whereDate('record_date', '<=', $toDate)
            ->orderBy('record_date', 'asc')
            ->get();

        $count = $records->count();
        $sums = [
            'control_vessels_in' => $records->sum('control_vessels_in'),
            'control_vessels_out' => $records->sum('control_vessels_out'),
            'oil_port_waiting' => $records->sum('oil_port_waiting'),
            'oil_port_berth' => $records->sum('oil_port_berth'),
            'anchorage_waiting' => $records->sum('anchorage_waiting'),
            'um_qasr_north' => $records->sum('um_qasr_north'),
            'um_qasr_south' => $records->sum('um_qasr_south'),
            'khor_al_zubair' => $records->sum('khor_al_zubair'),
            'abu_flus' => $records->sum('abu_flus'),
            'lpg_berth' => $records->sum('lpg_berth'),
            'total_berthed' => $records->sum('total_berthed'),
            'total_overall' => $records->sum('total_overall'),
        ];
        $averages = [];
        foreach ($sums as $k => $v) {
            $averages[$k] = $count > 0 ? round($v / $count, 1) : 0;
        }

        $fromFormatted = \Carbon\Carbon::parse($fromDate)->format('d/m/Y');
        $toFormatted = \Carbon\Carbon::parse($toDate)->format('d/m/Y');

        ActivityLogger::log('viewed_report', "معاينة طباعة إحصائية حركة السفن اليومية ({$fromFormatted} - {$toFormatted})");

        return view('reports.daily-vessel-traffic-report', [
            'records' => $records,
            'fromDate' => $fromDate,
            'toDate' => $toDate,
            'fromFormatted' => $fromFormatted,
            'toFormatted' => $toFormatted,
            'sums' => $sums,
            'averages' => $averages,
        ]);
    }

    /**
     * 8. تحميل PDF مباشر لإحصائية حركة السفن اليومية عبر Spatie PDF
     */
    public function exportDailyVesselTrafficPdf(Request $request): Response
    {
        $fromDate = $request->input('from_date') ?: now()->startOfMonth()->format('Y-m-d');
        $toDate = $request->input('to_date') ?: now()->endOfMonth()->format('Y-m-d');

        $records = \App\Models\DailyVesselStatus::whereDate('record_date', '>=', $fromDate)
            ->whereDate('record_date', '<=', $toDate)
            ->orderBy('record_date', 'asc')
            ->get();

        $count = $records->count();
        $sums = [
            'control_vessels_in' => $records->sum('control_vessels_in'),
            'control_vessels_out' => $records->sum('control_vessels_out'),
            'oil_port_waiting' => $records->sum('oil_port_waiting'),
            'oil_port_berth' => $records->sum('oil_port_berth'),
            'anchorage_waiting' => $records->sum('anchorage_waiting'),
            'um_qasr_north' => $records->sum('um_qasr_north'),
            'um_qasr_south' => $records->sum('um_qasr_south'),
            'khor_al_zubair' => $records->sum('khor_al_zubair'),
            'abu_flus' => $records->sum('abu_flus'),
            'lpg_berth' => $records->sum('lpg_berth'),
            'total_berthed' => $records->sum('total_berthed'),
            'total_overall' => $records->sum('total_overall'),
        ];
        $averages = [];
        foreach ($sums as $k => $v) {
            $averages[$k] = $count > 0 ? round($v / $count, 1) : 0;
        }

        $fromFormatted = \Carbon\Carbon::parse($fromDate)->format('d/m/Y');
        $toFormatted = \Carbon\Carbon::parse($toDate)->format('d/m/Y');

        ActivityLogger::log('exported_pdf', "تصدير إحصائية حركة السفن اليومية (PDF) للفترة ({$fromFormatted} - {$toFormatted})");

        $pdf = \Spatie\LaravelPdf\Facades\Pdf::view('reports.daily-vessel-traffic-report', [
            'records' => $records,
            'fromDate' => $fromDate,
            'toDate' => $toDate,
            'fromFormatted' => $fromFormatted,
            'toFormatted' => $toFormatted,
            'sums' => $sums,
            'averages' => $averages,
        ])
        ->format('a4')
        ->landscape();

        $fileName = 'موقف_حركة_السفن_' . str_replace('/', '-', $fromFormatted) . '_' . str_replace('/', '-', $toFormatted) . '.pdf';

        return response($pdf->generatePdfContent(), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="' . $fileName . '"',
        ]);
    }
}
