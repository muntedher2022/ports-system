<?php

namespace App\Http\Controllers;

use App\Models\FiscalYear;
use App\Models\Month;
use App\Models\Port;
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
            'Content-Type'        => 'application/pdf',
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

        $pdfContent = $this->pdfService->generateTotalCumulativeCapacityPdf($fiscalYearId, $monthNumber, $portId, $onlyTotal);

        return response($pdfContent, 200, [
            'Content-Type'        => 'application/pdf',
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

        $pdfContent = $this->pdfService->generateCapacityComparisonPdf($prevYearId, $currYearId, $monthNumber);

        return response($pdfContent, 200, [
            'Content-Type'        => 'application/pdf',
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

        $pdfContent = $this->pdfService->generateRevenueComparisonPdf($prevYearId, $currYearId);

        return response($pdfContent, 200, [
            'Content-Type'        => 'application/pdf',
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

        $pdfContent = $this->pdfService->generateMultiYearComparisonPdf(
            $comparisonType,
            $periodScope,
            $monthNumber,
            $portId,
            $yearIds
        );

        return response($pdfContent, 200, [
            'Content-Type'        => 'application/pdf',
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

        $pdfContent = $this->pdfService->generateStandardDeviationPdf($fiscalYearId, $portId, $metric);

        return response($pdfContent, 200, [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => 'inline; filename="تقرير_الانحراف_المعياري_والاستقرار.pdf"',
        ]);
    }
}
