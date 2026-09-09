<?php

namespace App\Services;

use App\Models\FiscalYear;
use App\Models\Month;
use App\Models\Port;
use App\Models\RevenueCenter;
use App\Models\RevenueRecord;
use TCPDF;

class PdfReportService
{
    protected PortAnalyticsService $portService;
    protected RevenueAnalyticsService $revenueService;

    public function __construct(PortAnalyticsService $portService, RevenueAnalyticsService $revenueService)
    {
        $this->portService = $portService;
        $this->revenueService = $revenueService;
    }

    /**
     * إنشاء كائن TCPDF مهيأ للغة العربية والتقارير الرسمية
     */
    protected function createPdf(string $title, string $orientation = 'L'): TCPDF
    {
        $pdf = new TCPDF($orientation, 'mm', 'A4', true, 'UTF-8', false);

        $pdf->SetCreator('نظام إدارة الطاقة الإنتاجية والإيراد - GCPI');
        $pdf->SetAuthor('الشركة العامة لموانئ العراق');
        $pdf->SetTitle($title);
        $pdf->SetSubject($title);

        $pdf->setRTL(true);
        $pdf->SetFont('aealarabiya', '', 11);

        // إعدادات الهوامش
        $pdf->SetMargins(10, 8, 10);
        $pdf->SetHeaderMargin(4);
        $pdf->SetFooterMargin(6);
        $pdf->SetAutoPageBreak(true, 8);
        $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(true);

        return $pdf;
    }


    /**
     * ترويسة رسمية عامة للتقرير
     */
    protected function getReportHeaderHtml(string $title, string $subtitle = ''): string
    {
        $dateStr = date('Y/m/d H:i');
        return <<<HTML
        <table cellpadding="2" style="width: 100%; border-bottom: 2px solid #0f172a; margin-bottom: 6px;">
            <tr>
                <td style="width: 30%; text-align: right; font-size: 9pt; font-weight: bold; color: #334155;">
                    جمهورية العراق<br>
                    وزارة النقل<br>
                    الشركة العامة لموانئ العراق<br>
                    مكتب المدير العام<br>
                    المتابعة المركزية والعمليات
                </td>
                <td style="width: 40%; text-align: center;">
                    <h2 style="margin: 0; font-size: 13pt; color: #0f172a; font-weight: bold;">{$title}</h2>
                    <div style="font-size: 10pt; color: #475569; margin-top: 2px;">{$subtitle}</div>
                </td>
                <td style="width: 30%; text-align: left; font-size: 8pt; color: #64748b;">
                    تاريخ الطباعة: {$dateStr}<br>
                    نظام إدارة الطاقة الإنتاجية والإيراد
                </td>
            </tr>
        </table>
        <br>
HTML;
    }

    /**
     * بطاقات الملخص الإحصائي العلوي (KPI Cards)
     */
    protected function getKpiCardsHtml(array $summary, bool $isAll = false): string
    {
        $ships = number_format($summary['total_ships']);
        $tons = number_format(round($summary['total_tonnage']), 0);
        $teu = number_format($summary['total_teu']);
        $rev = number_format($summary['total_revenue'], 0);

        $teuLabel = $isAll ? 'الطاقة الإنتاجية الكلية للشركة TEU' : 'طاقة الميناء الكلية TEU';
        $revLabel = $isAll ? 'إيراد الموانئ الأربعة فقط' : 'إيراد الميناء الكلي';

        return <<<HTML
        <table cellpadding="3" style="width: 100%; margin-bottom: 6px;">
            <tr>
                <td style="width: 25%; padding: 1px;">
                    <div style="border: 1px solid #cbd5e1; border-top: 3px solid #2563eb; background-color: #f8fafc; border-radius: 4px; text-align: center; padding: 4px;">
                        <div style="font-size: 8pt; color: #475569; font-weight: bold;">إجمالي البواخر الراسية</div>
                        <div style="font-size: 12pt; color: #1e3a8a; font-weight: bold; margin-top: 2px;">
                            {$ships} <span style="font-size: 7.5pt; color: #64748b; font-weight: normal;">باخرة</span>
                        </div>
                    </div>
                </td>
                <td style="width: 25%; padding: 1px;">
                    <div style="border: 1px solid #cbd5e1; border-top: 3px solid #059669; background-color: #f8fafc; border-radius: 4px; text-align: center; padding: 4px;">
                        <div style="font-size: 8pt; color: #475569; font-weight: bold;">الطاقة الإنتاجية بالطن</div>
                        <div style="font-size: 12pt; color: #047857; font-weight: bold; margin-top: 2px;">
                            {$tons} <span style="font-size: 7.5pt; color: #64748b; font-weight: normal;">طن</span>
                        </div>
                    </div>
                </td>
                <td style="width: 25%; padding: 1px;">
                    <div style="border: 1px solid #cbd5e1; border-top: 3px solid #0891b2; background-color: #f8fafc; border-radius: 4px; text-align: center; padding: 4px;">
                        <div style="font-size: 8pt; color: #475569; font-weight: bold;">{$teuLabel}</div>
                        <div style="font-size: 12pt; color: #0e7490; font-weight: bold; margin-top: 2px;">
                            {$teu} <span style="font-size: 7.5pt; color: #64748b; font-weight: normal;">TEU</span>
                        </div>
                    </div>
                </td>
                <td style="width: 25%; padding: 1px;">
                    <div style="border: 1px solid #cbd5e1; border-top: 3px solid #d97706; background-color: #f8fafc; border-radius: 4px; text-align: center; padding: 4px;">
                        <div style="font-size: 8pt; color: #475569; font-weight: bold;">{$revLabel}</div>
                        <div style="font-size: 10.5pt; color: #b45309; font-weight: bold; margin-top: 2px;">
                            {$rev} <span style="font-size: 7pt; color: #64748b; font-weight: normal;">د.ع</span>
                        </div>
                    </div>
                </td>
            </tr>
        </table>
HTML;
    }

    /**
     * 1. تقرير مصفوفة الإيراد الكلي والصافي للمراكز السبعة
     */
    public function generateTotalRevenueMatrixPdf(int $fiscalYearId): string
    {
        $fiscalYear = FiscalYear::find($fiscalYearId);
        $allCenters = RevenueCenter::where('is_active', true)->orderBy('sort_order')->get();
        $allMonths  = Month::orderBy('month_number')->get();

        $records = RevenueRecord::where('fiscal_year_id', $fiscalYearId)->get();

        // تصفية المراكز التي تحتوي على إيرادات مسجلة فقط طوال السنة (استبعاد أي مركز ليس له بيانات طوال السنة مثل التفتيش البحري)
        $centers = $allCenters->filter(function ($center) use ($records) {
            return $records->where('revenue_center_id', $center->id)->sum('gross_revenue') > 0
                || $records->where('revenue_center_id', $center->id)->sum('net_revenue') > 0;
        })->values();

        if ($centers->isEmpty()) {
            $centers = $allCenters;
        }

        $recordsGrouped = $records->groupBy(fn ($r) => $r->month_id . '_' . $r->revenue_center_id);

        // تصفية الأشهر لغاية آخر شهر تم إدخال بيانات له في السنة (استبعاد الأشهر الفارغة لنهاية السنة)
        $maxMonthId = $records->filter(fn($r) => (float)$r->gross_revenue > 0 || (float)$r->net_revenue > 0)->max('month_id');
        $months = $maxMonthId 
            ? $allMonths->where('id', '<=', $maxMonthId)->values()
            : $allMonths;

        $lastMonth = $months->last() ?? $allMonths->last();

        $pdf = $this->createPdf('الايراد الكلي والصافي للشركة', 'L');
        $pdf->AddPage();

        $headerHtml = $this->getReportHeaderHtml(
            'الايراد الكلي والصافي للشركة',
            "لغاية شهر {$lastMonth->name_ar} لسنة {$fiscalYear->year} (بالدينار العراقي)"
        );

        $wMonth = '8%';
        $wGross = '13%';
        $wNet   = '13%';
        
        $centerCount = max(1, count($centers));
        // المساحة المتبقية للمراكز = 100% - (8% + 13% + 13%) = 66%
        $wCenterPercent = number_format(66 / $centerCount, 2) . '%';

        $tableHtml = '<table cellpadding="3" border="1" style="border-collapse: collapse; border-color: #94a3b8; width: 100%; font-size: 8.5pt; text-align: center;">';
        $tableHtml .= '<thead><tr style="background-color: #0f172a; color: #ffffff; font-weight: bold; font-size: 9pt;">';
        $tableHtml .= "<th style=\"width: {$wMonth}; text-align: right;\">الشهر</th>";
        
        foreach ($centers as $center) {
            $tableHtml .= "<th style=\"width: {$wCenterPercent};\">{$center->name_ar}</th>";
        }
        
        $tableHtml .= "<th style=\"width: {$wGross}; background-color: #1e3a8a;\">الإيراد الكلي</th>";
        $tableHtml .= "<th style=\"width: {$wNet}; background-color: #065f46;\">الإيراد الصافي</th>";
        $tableHtml .= '</tr></thead><tbody>';

        $runningTotal = 0;
        $centerTotals = array_fill_keys($centers->pluck('id')->toArray(), 0);
        $prevCenterVals = [];   // لتتبع قيم كل مركز في الشهر السابق
        $prevMonthGross = null; // لتتبع الإيراد الكلي للشهر السابق
        $prevMonthNet = null;   // لتتبع الإيراد الصافي للشهر السابق
        $grandGrossTotal = 0;
        $grandNetTotal = 0;

        foreach ($months as $idx => $month) {
            $monthGross = 0;
            $monthNet = 0;
            $hasData = false;
            $bgColor = ($idx % 2 === 0) ? '#ffffff' : '#f8fafc';

            $rowHtml = "<tr style=\"background-color: {$bgColor};\">";
            $rowHtml .= "<td style=\"width: {$wMonth}; text-align: right; font-weight: bold;\">{$month->name_ar}</td>";

            foreach ($centers as $center) {
                $key = $month->id . '_' . $center->id;
                $record = $recordsGrouped->get($key)?->first();
                $gross = $record ? (float) $record->gross_revenue : 0;
                $net   = $record ? (float) $record->net_revenue : 0;

                if ($record && ($gross > 0 || $net > 0)) {
                    $hasData = true;
                }

                $monthGross += $gross;
                $monthNet += $net;
                $centerTotals[$center->id] += $gross;

                // تلوين الرقم حسب الاتجاه (أخضر صاعد / أحمر نازل)
                $prevGross = $prevCenterVals[$center->id] ?? null;
                if ($gross > 0 && $prevGross !== null && $prevGross > 0) {
                    $numColor = $gross > $prevGross ? '#15803d' : '#dc2626';
                    $grossStr = '<span style="color:' . $numColor . '; font-weight:bold;">' . number_format($gross, 0) . '</span>';
                } elseif ($gross > 0) {
                    $grossStr = number_format($gross, 0);
                } else {
                    $grossStr = '-';
                }
                if ($gross > 0) {
                    $prevCenterVals[$center->id] = $gross;
                }

                $rowHtml .= "<td style=\"width: {$wCenterPercent}; white-space: nowrap;\">{$grossStr}</td>";
            }

            if ($hasData) {
                $runningTotal += $monthGross;
            }

            $grandGrossTotal += $monthGross;
            $grandNetTotal += $monthNet;

            // تلوين الإيراد الكلي الشهري
            if ($monthGross > 0 && $prevMonthGross !== null && $prevMonthGross > 0) {
                $grossColor = $monthGross > $prevMonthGross ? '#15803d' : '#dc2626';
                $mGrossStr = '<span style="color:' . $grossColor . '; font-weight:bold;">' . number_format($monthGross, 0) . '</span>';
            } else {
                $mGrossStr = $monthGross > 0 ? number_format($monthGross, 0) : '-';
            }
            if ($monthGross > 0) { $prevMonthGross = $monthGross; }

            // تلوين الإيراد الصافي الشهري
            if ($monthNet > 0 && $prevMonthNet !== null && $prevMonthNet > 0) {
                $netColor = $monthNet > $prevMonthNet ? '#15803d' : '#dc2626';
                $mNetStr = '<span style="color:' . $netColor . '; font-weight:bold;">' . number_format($monthNet, 0) . '</span>';
            } else {
                $mNetStr = $monthNet > 0 ? number_format($monthNet, 0) : '-';
            }
            if ($monthNet > 0) { $prevMonthNet = $monthNet; }

            $rowHtml .= "<td style=\"width: {$wGross}; font-weight: bold; background-color: #eff6ff; white-space: nowrap;\">{$mGrossStr}</td>";
            $rowHtml .= "<td style=\"width: {$wNet}; font-weight: bold; background-color: #ecfdf5; white-space: nowrap;\">{$mNetStr}</td>";
            $rowHtml .= "</tr>";

            $tableHtml .= $rowHtml;
        }

        // صف المجموع السنوي
        $tableHtml .= '<tr style="background-color: #0f172a; color: #ffffff; font-weight: bold; font-size: 8pt;">';
        $tableHtml .= "<td style=\"width: {$wMonth}; text-align: right;\">المجموع السنوي</td>";
        foreach ($centers as $center) {
            $tableHtml .= "<td style=\"width: {$wCenterPercent}; white-space: nowrap;\">" . number_format($centerTotals[$center->id] ?? 0, 0) . "</td>";
        }
        $tableHtml .= "<td style=\"width: {$wGross}; background-color: #1e3a8a; white-space: nowrap;\">" . number_format($grandGrossTotal, 0) . "</td>";
        $tableHtml .= "<td style=\"width: {$wNet}; background-color: #065f46; white-space: nowrap;\">" . number_format($grandNetTotal, 0) . "</td>";
        $tableHtml .= '</tr>';

        $tableHtml .= '</tbody></table>';

        $pdf->writeHTML($headerHtml . $tableHtml, true, false, true, false, '');
        return $pdf->Output('تقرير_الإيراد_الكلي_للمراكز_السبعة.pdf', 'S');
    }

    /**
     * 2. تقرير إجمالي الطاقة الإنتاجية الشامل
     */
    public function generateTotalCumulativeCapacityPdf(int $fiscalYearId, int $endMonthNumber, ?int $portId = null, bool $onlyTotal = false): string
    {
        $fiscalYear = FiscalYear::find($fiscalYearId);
        $month = Month::where('month_number', $endMonthNumber)->first();
        $ports = Port::where('is_active', true)->orderBy('sort_order')->get();

        $pdf = $this->createPdf('تقرير إجمالي الطاقة الإنتاجية', 'P');

        if ($portId) {
            $port = Port::find($portId);
            $pdf->AddPage();
            $this->addSinglePortCumulativePage($pdf, $fiscalYear, $month, $port);
        } elseif ($onlyTotal) {
            // تصدير إجمالي الشركة فقط (صفحة واحدة)
            $this->addAllPortsCumulativePage($pdf, $fiscalYear, $month);
        } else {
            // التقرير الشامل: الصفحة 1 = إجمالي الشركة للموانئ الأربعة مجتمعة
            $this->addAllPortsCumulativePage($pdf, $fiscalYear, $month);

            // تصفية الموانئ التي تحتوي على بيانات تشغيلية فقط واستبعاد أي ميناء بدون بيانات مثل المعقل
            $portsWithData = $ports->filter(function ($port) use ($fiscalYear, $month) {
                $summary = $this->portService->getCumulativeSummary($fiscalYear->id, $month->month_number, $port->id);
                return ($summary['total_ships'] > 0 || $summary['total_tonnage'] > 0 || $summary['total_teu'] > 0 || $summary['total_revenue'] > 0);
            })->values();

            // الصفحات التالية: كل صفحة فيها ميناء وتحته ميناء آخر
            $wCol1 = '8%';
            $wCol2 = '48%';
            $wCol3 = '30%';
            $wCol4 = '14%';

            for ($i = 0; $i < count($portsWithData); $i += 2) {
                $pdf->AddPage();
                $pHeader = $this->getReportHeaderHtml(
                    "اجمالي الطاقة الانتاجية لموانئ الشركة",
                    "للفترة من 1 / 1 / {$fiscalYear->year} لغاية نهاية شهر {$month->name_ar} {$fiscalYear->year}"
                );

                $pageContent = $pHeader;

                for ($j = 0; $j < 2; $j++) {
                    if (!isset($portsWithData[$i + $j])) break;
                    $port = $portsWithData[$i + $j];
                    $summary = $this->portService->getCumulativeSummary($fiscalYear->id, $month->month_number, $port->id);
                    $kpiCards = $this->getKpiCardsHtml($summary, false);

                    $pageContent .= "<div style=\"background-color: #1e3a8a; color: #ffffff; padding: 4px 10px; font-size: 12pt; font-weight: bold; text-align: center; border-radius: 4px; margin-bottom: 4px;\">{$port->name_ar}</div>";
                    $pageContent .= $kpiCards;
                    $pageContent .= '<table cellpadding="2.5" border="1" style="border-collapse: collapse; border-color: #94a3b8; width: 100%; font-size: 8pt; margin-bottom: 4px;">';
                    $pageContent .= '<thead><tr style="background-color: #f1f5f9; font-weight: bold; text-align: center; font-size: 8.5pt;">';
                    $pageContent .= "<th style=\"width: {$wCol1};\">ت</th>";
                    $pageContent .= "<th style=\"width: {$wCol2}; text-align: right;\">المؤشر التشغيلي</th>";
                    $pageContent .= "<th style=\"width: {$wCol3};\">الكمية التراكمية للفترة</th>";
                    $pageContent .= "<th style=\"width: {$wCol4};\">الوحدة</th>";
                    $pageContent .= '</tr></thead><tbody>';

                    $rows = [
                        ['1', 'عدد البواخر الكلي', number_format($summary['total_ships']), 'باخرة'],
                        ['2', 'الطاقة الانتاجية الكلية للميناء بالطن', number_format(round($summary['total_tonnage']), 0), 'طن'],
                        ['3', 'معدل الاوزان الشهري', number_format(round($summary['monthly_average_weight']), 0), 'طن'],
                        ['4', 'معدل الاوزان اليومي', number_format(round($summary['daily_average_weight']), 0), 'طن'],
                        ['5', 'تصدير مشتقات نفطية', number_format(round($summary['oil_exported_tons']), 0), 'طن'],
                        ['6', 'استيراد مشتقات نفطية', number_format(round($summary['oil_imported_tons']), 0), 'طن'],
                        ['7', 'عدد الحاويات المستوردة', number_format($summary['imported_containers_count']), 'حاوية'],
                        ['8', 'عدد الحاويات المستوردة TEU', number_format($summary['imported_teu']), 'حاوية'],
                        ['9', 'عدد الحاويات المصدرة', number_format($summary['exported_containers_count']), 'حاوية'],
                        ['10', 'عدد الحاويات المصدرة TEU', number_format($summary['exported_teu']), 'حاوية'],
                        ['11', 'عدد الحاويات المصدرة مليان', number_format($summary['exported_full_count']), 'حاوية'],
                        ['12', 'عدد الحاويات المصدرة فارغ', number_format($summary['exported_empty_count']), 'حاوية'],
                        ['13', 'الطاقة الانتاجية الكلية للميناء TEU', number_format($summary['total_teu']), 'حاوية'],
                        ['14', 'الايراد الكلي للميناء', number_format($summary['total_revenue'], 0), 'دينار'],
                    ];

                    foreach ($rows as $idx => $r) {
                        $bg = ($idx % 2 === 0) ? '#ffffff' : '#f8fafc';
                        $pageContent .= "<tr style=\"background-color: {$bg};\">";
                        $pageContent .= "<td style=\"width: {$wCol1}; text-align: center; font-weight: bold;\">{$r[0]}</td>";
                        $pageContent .= "<td style=\"width: {$wCol2}; text-align: right; font-weight: bold;\">{$r[1]}</td>";
                        $pageContent .= "<td style=\"width: {$wCol3}; text-align: center; font-weight: bold; color: #1e3a8a;\">{$r[2]}</td>";
                        $pageContent .= "<td style=\"width: {$wCol4}; text-align: center;\">{$r[3]}</td>";
                        $pageContent .= "</tr>";
                    }
                    $pageContent .= '</tbody></table>';

                    if ($j === 0 && isset($portsWithData[$i + 1])) {
                        $pageContent .= '<div style="margin-top: 4px; margin-bottom: 4px; border-bottom: 1.5px dashed #94a3b8;"></div>';
                    }
                }

                $pdf->writeHTML($pageContent, true, false, true, false, '');
            }
        }

        return $pdf->Output('تقرير_إجمالي_الطاقة_الإنتاجية_الشامل.pdf', 'S');
    }

    protected function addAllPortsCumulativePage(TCPDF $pdf, FiscalYear $fiscalYear, Month $month): void
    {
        $pdf->AddPage();
        $summary = $this->portService->getCumulativeSummary($fiscalYear->id, $month->month_number, null);

        $headerHtml = $this->getReportHeaderHtml(
            "اجمالي الطاقة الانتاجية للموانئ الأربعة مجتمعة (إجمالي الشركة)",
            "للفترة من 1 / 1 / {$fiscalYear->year} لغاية نهاية شهر {$month->name_ar} {$fiscalYear->year}"
        );

        $companyBanner = '<div style="background-color: #0f172a; color: #ffffff; padding: 3px 10px; font-size: 12pt; font-weight: bold; text-align: center; border-radius: 4px; margin-bottom: 3px;">إجمالي الشركة ككل (الموانئ الأربعة مجتمعة)</div>';
        $kpiCards = $this->getKpiCardsHtml($summary, true);

        $wCol1 = '7%';
        $wCol2 = '49%';
        $wCol3 = '30%';
        $wCol4 = '14%';

        $tableHtml = '<table cellpadding="2.5" border="1" style="border-collapse: collapse; border-color: #94a3b8; width: 100%; font-size: 10pt;">';
        $tableHtml .= '<thead><tr style="background-color: #1e3a8a; color: #ffffff; font-weight: bold; text-align: center; font-size: 10.5pt;">';
        $tableHtml .= "<th style=\"width: {$wCol1};\">ت</th>";
        $tableHtml .= "<th style=\"width: {$wCol2}; text-align: right;\">المؤشر التشغيلي</th>";
        $tableHtml .= "<th style=\"width: {$wCol3};\">الكمية التراكمية للفترة</th>";
        $tableHtml .= "<th style=\"width: {$wCol4};\">الوحدة</th>";
        $tableHtml .= '</tr></thead><tbody>';

        $rows = [
            ['1', 'عدد البواخر الكلي', number_format($summary['total_ships']), 'باخرة'],
            ['2', 'معدل الاوزان الشهري', number_format(round($summary['monthly_average_weight']), 0), 'طن'],
            ['3', 'معدل الاوزان اليومي', number_format(round($summary['daily_average_weight']), 0), 'طن'],
            ['4', 'تصدير مشتقات نفطية', number_format(round($summary['oil_exported_tons']), 0), 'طن'],
            ['5', 'استيراد مشتقات نفطية', number_format(round($summary['oil_imported_tons']), 0), 'طن'],
            ['6', 'عدد الحاويات المستوردة', number_format($summary['imported_containers_count']), 'حاوية'],
            ['7', 'عدد الحاويات المستوردة TEU', number_format($summary['imported_teu']), 'حاوية'],
            ['8', 'عدد الحاويات المصدرة', number_format($summary['exported_containers_count']), 'حاوية'],
            ['9', 'عدد الحاويات المصدرة TEU', number_format($summary['exported_teu']), 'حاوية'],
            ['10', 'عدد الحاويات المصدرة مليان', number_format($summary['exported_full_count']), 'حاوية'],
            ['11', 'عدد الحاويات المصدرة فارغ', number_format($summary['exported_empty_count']), 'حاوية'],
            ['12', 'الطاقة الانتاجية الكلية للشركة TEU', number_format($summary['total_teu']), 'حاوية'],
            ['13', 'الطاقة الانتاجية الكلية للشركة بالطن', number_format(round($summary['total_tonnage']), 0), 'طن'],
            ['14', 'مجموع وزن الحاويات', number_format(round($summary['total_containers_weight']), 0), 'طن'],
            ['15', 'مجموع وزن البضائع', number_format(round($summary['total_cargo_weight']), 0), 'طن'],
            ['16', 'مجموع وزن المشتقات النفطية', number_format(round($summary['oil_total_tons']), 0), 'طن'],
            ['17', 'ايراد الموانئ الأربعة فقط', number_format($summary['total_revenue'], 0), 'دينار'],
        ];

        foreach ($rows as $idx => $r) {
            $bg = ($idx % 2 === 0) ? '#ffffff' : '#f8fafc';
            $tableHtml .= "<tr style=\"background-color: {$bg};\">";
            $tableHtml .= "<td style=\"width: {$wCol1}; text-align: center; font-weight: bold;\">{$r[0]}</td>";
            $tableHtml .= "<td style=\"width: {$wCol2}; text-align: right; font-weight: bold;\">{$r[1]}</td>";
            $tableHtml .= "<td style=\"width: {$wCol3}; text-align: center; font-weight: bold; color: #1e3a8a;\">{$r[2]}</td>";
            $tableHtml .= "<td style=\"width: {$wCol4}; text-align: center;\">{$r[3]}</td>";
            $tableHtml .= "</tr>";
        }
        $tableHtml .= '</tbody></table>';

        // جدول المعدلات
        $tableHtml .= '<br><h3 style="font-size: 10pt; color: #0f172a; margin-bottom: 2px; margin-top: 2px;">المعدلات الشهرية واليومية العامة للشركة (الموانئ الأربعة):</h3>';
        $tableHtml .= '<table cellpadding="2.5" border="1" style="border-collapse: collapse; border-color: #94a3b8; width: 100%; font-size: 10pt;">';
        $tableHtml .= '<thead><tr style="background-color: #0369a1; color: #ffffff; font-weight: bold; text-align: center; font-size: 10.5pt;">';
        $tableHtml .= "<th style=\"width: {$wCol1};\">ت</th>";
        $tableHtml .= "<th style=\"width: {$wCol2}; text-align: right;\">المعدل التشغيلي</th>";
        $tableHtml .= "<th style=\"width: {$wCol3};\">القيمة المحسوبة</th>";
        $tableHtml .= "<th style=\"width: {$wCol4};\">الوحدة</th>";
        $tableHtml .= '</tr></thead><tbody>';

        $avgRows = [
            ['18', 'المعدل الشهري للبواخر', number_format(round($summary['monthly_avg_ships']), 0), 'باخرة/شهر'],
            ['19', 'المعدل اليومي للبواخر', number_format(round($summary['daily_avg_ships']), 0), 'باخرة/يوم'],
            ['20', 'المعدل الشهري للحاويات TEU', number_format(round($summary['monthly_avg_teu']), 0), 'حاوية/شهر'],
            ['21', 'المعدل اليومي للحاويات TEU', number_format(round($summary['daily_avg_teu']), 0), 'حاوية/يوم'],
            ['22', 'المعدل الشهري للتصدير النفطي', number_format(round($summary['monthly_avg_oil_export']), 0), 'طن/شهر'],
            ['23', 'المعدل الشهري للاستيراد النفطي', number_format(round($summary['monthly_avg_oil_import']), 0), 'طن/شهر'],
            ['24', 'المعدل اليومي للتصدير النفطي', number_format(round($summary['daily_avg_oil_export']), 0), 'طن/يوم'],
            ['25', 'المعدل اليومي للاستيراد النفطي', number_format(round($summary['daily_avg_oil_import']), 0), 'طن/يوم'],
        ];

        foreach ($avgRows as $idx => $r) {
            $bg = ($idx % 2 === 0) ? '#ffffff' : '#f0f9ff';
            $tableHtml .= "<tr style=\"background-color: {$bg};\">";
            $tableHtml .= "<td style=\"width: {$wCol1}; text-align: center; font-weight: bold;\">{$r[0]}</td>";
            $tableHtml .= "<td style=\"width: {$wCol2}; text-align: right;\">{$r[1]}</td>";
            $tableHtml .= "<td style=\"width: {$wCol3}; text-align: center; font-weight: bold; color: #0284c7;\">{$r[2]}</td>";
            $tableHtml .= "<td style=\"width: {$wCol4}; text-align: center;\">{$r[3]}</td>";
            $tableHtml .= "</tr>";
        }
        $tableHtml .= '</tbody></table>';

        $pdf->writeHTML($headerHtml . $companyBanner . $kpiCards . $tableHtml, true, false, true, false, '');
    }

    protected function addSinglePortCumulativePage(TCPDF $pdf, FiscalYear $fiscalYear, Month $month, Port $port): void
    {
        $summary = $this->portService->getCumulativeSummary($fiscalYear->id, $month->month_number, $port->id);

        $portCleanName = preg_replace('/^ميناء\s+/u', '', $port->name_ar);

        $headerHtml = $this->getReportHeaderHtml(
            "اجمالي الطاقة الانتاجية لميناء<br>({$portCleanName})",
            "للفترة من 1 / 1 / {$fiscalYear->year} لغاية نهاية شهر {$month->name_ar} {$fiscalYear->year}"
        );

        $portBanner = "<div style=\"background-color: #1e3a8a; color: #ffffff; padding: 6px 12px; font-size: 14pt; font-weight: bold; text-align: center; border-radius: 4px; margin-bottom: 6px;\">{$port->name_ar}</div>";
        $kpiCards = $this->getKpiCardsHtml($summary, false);

        $wCol1 = '7%';
        $wCol2 = '49%';
        $wCol3 = '30%';
        $wCol4 = '14%';

        $tableHtml = '<table cellpadding="4.5" border="1" style="border-collapse: collapse; border-color: #94a3b8; width: 100%; font-size: 11pt;">';
        $tableHtml .= '<thead><tr style="background-color: #1e3a8a; color: #ffffff; font-weight: bold; text-align: center; font-size: 12pt;">';
        $tableHtml .= "<th style=\"width: {$wCol1};\">ت</th>";
        $tableHtml .= "<th style=\"width: {$wCol2}; text-align: right;\">المؤشر التشغيلي</th>";
        $tableHtml .= "<th style=\"width: {$wCol3};\">الكمية التراكمية للفترة</th>";
        $tableHtml .= "<th style=\"width: {$wCol4};\">الوحدة</th>";
        $tableHtml .= '</tr></thead><tbody>';

        $rows = [
            ['1', 'عدد البواخر الكلي', number_format($summary['total_ships']), 'باخرة'],
            ['2', 'الطاقة الانتاجية الكلية للميناء بالطن', number_format(round($summary['total_tonnage']), 0), 'طن'],
            ['3', 'معدل الاوزان الشهري', number_format(round($summary['monthly_average_weight']), 0), 'طن'],
            ['4', 'معدل الاوزان اليومي', number_format(round($summary['daily_average_weight']), 0), 'طن'],
            ['5', 'تصدير مشتقات نفطية', number_format(round($summary['oil_exported_tons']), 0), 'طن'],
            ['6', 'استيراد مشتقات نفطية', number_format(round($summary['oil_imported_tons']), 0), 'طن'],
            ['7', 'عدد الحاويات المستوردة', number_format($summary['imported_containers_count']), 'حاوية'],
            ['8', 'عدد الحاويات المستوردة TEU', number_format($summary['imported_teu']), 'حاوية'],
            ['9', 'عدد الحاويات المصدرة', number_format($summary['exported_containers_count']), 'حاوية'],
            ['10', 'عدد الحاويات المصدرة TEU', number_format($summary['exported_teu']), 'حاوية'],
            ['11', 'عدد الحاويات المصدرة مليان', number_format($summary['exported_full_count']), 'حاوية'],
            ['12', 'عدد الحاويات المصدرة فارغ', number_format($summary['exported_empty_count']), 'حاوية'],
            ['13', 'الطاقة الانتاجية الكلية للميناء TEU', number_format($summary['total_teu']), 'حاوية'],
            ['14', 'الايراد الكلي للميناء', number_format($summary['total_revenue'], 0), 'دينار'],
        ];

        foreach ($rows as $idx => $r) {
            $bg = ($idx % 2 === 0) ? '#ffffff' : '#f8fafc';
            $tableHtml .= "<tr style=\"background-color: {$bg};\">";
            $tableHtml .= "<td style=\"width: {$wCol1}; text-align: center; font-weight: bold;\">{$r[0]}</td>";
            $tableHtml .= "<td style=\"width: {$wCol2}; text-align: right; font-weight: bold;\">{$r[1]}</td>";
            $tableHtml .= "<td style=\"width: {$wCol3}; text-align: center; font-weight: bold; color: #1e3a8a;\">{$r[2]}</td>";
            $tableHtml .= "<td style=\"width: {$wCol4}; text-align: center;\">{$r[3]}</td>";
            $tableHtml .= "</tr>";
        }
        $tableHtml .= '</tbody></table>';

        $pdf->writeHTML($headerHtml . $portBanner . $kpiCards . $tableHtml, true, false, true, false, '');
    }

    /**
     * 3. تقرير مقارنة الطاقة الإنتاجية الشامل بين عامين (YoY) - عمودي بخط كبير
     */
    public function generateCapacityComparisonPdf(int $prevYearId, int $currYearId, int $endMonthNumber): string
    {
        $prevYear = FiscalYear::find($prevYearId)?->year ?? 2025;
        $currYear = FiscalYear::find($currYearId)?->year ?? 2026;
        $month = Month::where('month_number', $endMonthNumber)->first();
        $ports = Port::where('is_active', true)->orderBy('sort_order')->get();

        $pdf = $this->createPdf('مقارنة الأداء للطاقة الإنتاجية', 'P');

        // الصفحة 1: جدول إجمالي الموانئ الأربعة مجتمعة
        $pdf->AddPage();
        $companyComp = $this->portService->getCapacityComparison($prevYearId, $currYearId, $endMonthNumber, null);

        $headerHtml = $this->getReportHeaderHtml(
            "مقارنة الأداء للطاقة الإنتاجية للموانئ الأربعة مجتمعة بين عامي {$prevYear} و {$currYear}",
            "للفترة التراكمية من بداية العام لغاية شهر {$month->name_ar}"
        );

        $companyBanner = '<div style="background-color: #0f172a; color: #ffffff; padding: 6px 12px; font-size: 14pt; font-weight: bold; text-align: center; border-radius: 4px; margin-bottom: 6px;">إجمالي الطاقة الإنتاجية للموانئ الأربعة مجتمعة (إجمالي الشركة)</div>';

        $wCol1 = '26%';
        $wCol2 = '17%';
        $wCol3 = '17%';
        $wCol4 = '8%';
        $wCol5 = '17%';
        $wCol6 = '15%';

        $tableHtml = '<table cellpadding="4.5" border="1" style="border-collapse: collapse; border-color: #94a3b8; width: 100%; font-size: 10pt; text-align: center;">';
        $tableHtml .= '<thead><tr style="background-color: #1e3a8a; color: #ffffff; font-weight: bold; font-size: 10.5pt;">';
        $tableHtml .= "<th style=\"width: {$wCol1}; text-align: right;\">المؤشر التشغيلي</th>";
        $tableHtml .= "<th style=\"width: {$wCol2};\">سنة {$prevYear}</th>";
        $tableHtml .= "<th style=\"width: {$wCol3};\">سنة {$currYear}</th>";
        $tableHtml .= "<th style=\"width: {$wCol4};\">الوحدة</th>";
        $tableHtml .= "<th style=\"width: {$wCol5};\">الفارق</th>";
        $tableHtml .= "<th style=\"width: {$wCol6};\">% التغير</th>";
        $tableHtml .= '</tr></thead><tbody>';

        foreach ($companyComp['comparison'] as $idx => $row) {
            $bg = ($idx % 2 === 0) ? '#ffffff' : '#f8fafc';
            $diffColor = $row['diff'] >= 0 ? '#065f46' : '#991b1b';
            $diffSign = $row['diff'] > 0 ? '+' : '';

            $tableHtml .= "<tr style=\"background-color: {$bg};\">";
            $tableHtml .= "<td style=\"width: {$wCol1}; text-align: right; font-weight: bold;\">{$row['label']}</td>";
            $tableHtml .= "<td style=\"width: {$wCol2};\">" . number_format(round($row['prev_val']), 0) . "</td>";
            $tableHtml .= "<td style=\"width: {$wCol3}; font-weight: bold; color: #1e3a8a;\">" . number_format(round($row['curr_val']), 0) . "</td>";
            $tableHtml .= "<td style=\"width: {$wCol4};\">{$row['unit']}</td>";
            $tableHtml .= "<td style=\"width: {$wCol5}; font-weight: bold; color: {$diffColor};\">{$diffSign}" . number_format(round($row['diff']), 0) . "</td>";
            $tableHtml .= "<td style=\"width: {$wCol6}; font-weight: bold; color: {$diffColor};\">{$diffSign}{$row['percent']}%</td>";
            $tableHtml .= "</tr>";
        }
        $tableHtml .= '</tbody></table>';
        $pdf->writeHTML($headerHtml . $companyBanner . $tableHtml, true, false, true, false, '');

        // الصفحات التالية: كل صفحة فيها ميناء وتحته ميناء - عمودي بخط كبير 10pt - 12.5pt
        $wP1 = '26%';
        $wP2 = '17%';
        $wP3 = '17%';
        $wP4 = '8%';
        $wP5 = '17%';
        $wP6 = '15%';

        // تصفية الموانئ التي تحتوي على بيانات تشغيلية فقط واستبعاد أي ميناء بدون بيانات
        $portsWithData = $ports->filter(function ($port) use ($prevYearId, $currYearId, $endMonthNumber) {
            $pComp = $this->portService->getCapacityComparison($prevYearId, $currYearId, $endMonthNumber, $port->id);
            foreach ($pComp['comparison'] as $row) {
                if (($row['prev_val'] ?? 0) > 0 || ($row['curr_val'] ?? 0) > 0) {
                    return true;
                }
            }
            return false;
        })->values();

        for ($i = 0; $i < count($portsWithData); $i += 2) {
            $pdf->AddPage();
            $pHeader = $this->getReportHeaderHtml(
                "مقارنة الأداء للطاقة الإنتاجية للموانئ بين عامي {$prevYear} و {$currYear}",
                "للفترة التراكمية لغاية نهاية شهر {$month->name_ar}"
            );

            $pageContent = $pHeader;

            for ($j = 0; $j < 2; $j++) {
                if (!isset($portsWithData[$i + $j])) break;
                $port = $portsWithData[$i + $j];
                $pComp = $this->portService->getCapacityComparison($prevYearId, $currYearId, $endMonthNumber, $port->id);

                $pageContent .= "<div style=\"background-color: #1e3a8a; color: #ffffff; padding: 4px 10px; font-size: 12.5pt; font-weight: bold; text-align: center; border-radius: 4px; margin-bottom: 3px;\">{$port->name_ar}</div>";
                $pageContent .= '<table cellpadding="3" border="1" style="border-collapse: collapse; border-color: #cbd5e1; width: 100%; font-size: 9pt; text-align: center;">';
                $pageContent .= "<thead><tr style=\"background-color: #f1f5f9; font-weight: bold; font-size: 9.5pt;\">";
                $pageContent .= "<th style=\"width: {$wP1}; text-align: right;\">المؤشر</th>";
                $pageContent .= "<th style=\"width: {$wP2};\">سنة {$prevYear}</th>";
                $pageContent .= "<th style=\"width: {$wP3};\">سنة {$currYear}</th>";
                $pageContent .= "<th style=\"width: {$wP4};\">الوحدة</th>";
                $pageContent .= "<th style=\"width: {$wP5};\">الفارق</th>";
                $pageContent .= "<th style=\"width: {$wP6};\">% التغير</th>";
                $pageContent .= "</tr></thead><tbody>";

                foreach ($pComp['comparison'] as $idx => $r) {
                    $diffColor = $r['diff'] >= 0 ? '#065f46' : '#991b1b';
                    $diffSign = $r['diff'] > 0 ? '+' : '';
                    $bg = ($idx % 2 === 0) ? '#ffffff' : '#f8fafc';

                    $pageContent .= "<tr style=\"background-color: {$bg};\">";
                    $pageContent .= "<td style=\"width: {$wP1}; text-align: right; font-weight: bold;\">{$r['label']}</td>";
                    $pageContent .= "<td style=\"width: {$wP2};\">" . number_format(round($r['prev_val']), 0) . "</td>";
                    $pageContent .= "<td style=\"width: {$wP3}; font-weight: bold; color: #1e3a8a;\">" . number_format(round($r['curr_val']), 0) . "</td>";
                    $pageContent .= "<td style=\"width: {$wP4};\">{$r['unit']}</td>";
                    $pageContent .= "<td style=\"width: {$wP5}; color: {$diffColor}; font-weight: bold;\">{$diffSign}" . number_format(round($r['diff']), 0) . "</td>";
                    $pageContent .= "<td style=\"width: {$wP6}; color: {$diffColor}; font-weight: bold;\">{$diffSign}{$r['percent']}%</td>";
                    $pageContent .= "</tr>";
                }
                $pageContent .= '</tbody></table>';

                // فاصل أنيق بين الميناء الأول والثاني في نفس الصفحة
                if ($j === 0 && isset($portsWithData[$i + 1])) {
                    $pageContent .= '<div style="margin-top: 5px; margin-bottom: 5px; border-bottom: 1.5px dashed #94a3b8;"></div>';
                }
            }

            $pdf->writeHTML($pageContent, true, false, true, false, '');
        }

        return $pdf->Output('تقرير_مقارنة_الطاقة_الإنتاجية_الشامل.pdf', 'S');
    }

    /**
     * 4. تقرير مقارنة الإيراد الشامل لكل التشكيلات بين عامين (YoY) - تنسيق عمودي بخط كبير وواضح
     */
    public function generateRevenueComparisonPdf(int $prevYearId, int $currYearId): string
    {
        $compData = $this->revenueService->getYearOverYearRevenueComparison($prevYearId, $currYearId);
        $prevYear = $compData['prevYear'];
        $currYear = $compData['currYear'];
        $companyTable = $compData['tables']['company'];

        $pdf = $this->createPdf('مقارنة الإيراد لكل التشكيلات', 'P');

        // الصفحة 1: جدول إجمالي الشركة ككل بخط كبير 11pt - 14pt
        $pdf->AddPage();

        $headerHtml = $this->getReportHeaderHtml(
            "مقارنة الإيراد لكل التشكيلات",
            "مقارنة سنوية وشهرية بين عامي {$prevYear} و {$currYear} (بالدينار العراقي)"
        );

        $companyBanner = '<div style="background-color: #064e3b; color: #ffffff; padding: 6px 12px; font-size: 14pt; font-weight: bold; text-align: center; border-radius: 4px; margin-bottom: 6px;">إجمالي الشركة ككل (الموانئ السبعة ومقر الشركة)</div>';

        $wCol1 = '20%';
        $wCol2 = '22%';
        $wCol3 = '22%';
        $wCol4 = '22%';
        $wCol5 = '14%';

        $tableHtml = '<table cellpadding="4.5" border="1" style="border-collapse: collapse; border-color: #94a3b8; width: 100%; font-size: 10.5pt; text-align: center;">';
        $tableHtml .= '<thead><tr style="background-color: #064e3b; color: #ffffff; font-weight: bold; font-size: 11.5pt;">';
        $tableHtml .= "<th style=\"width: {$wCol1}; text-align: right;\">الشهر</th>";
        $tableHtml .= "<th style=\"width: {$wCol2};\">سنة {$prevYear} (د.ع)</th>";
        $tableHtml .= "<th style=\"width: {$wCol3};\">سنة {$currYear} (د.ع)</th>";
        $tableHtml .= "<th style=\"width: {$wCol4};\">الفارق (د.ع)</th>";
        $tableHtml .= "<th style=\"width: {$wCol5};\">نسبة التغير %</th>";
        $tableHtml .= '</tr></thead><tbody>';

        foreach ($companyTable['rows'] as $idx => $row) {
            $bg = ($idx % 2 === 0) ? '#ffffff' : '#f8fafc';
            $diffColor = $row['diff'] >= 0 ? '#065f46' : '#991b1b';
            $diffSign = $row['diff'] > 0 ? '+' : '';

            $prevStr = $row['prev_val'] > 0 ? number_format($row['prev_val'], 0) : '—';
            $currStr = $row['curr_val'] > 0 ? number_format($row['curr_val'], 0) : '—';
            $diffStr = $row['diff'] != 0 ? ($diffSign . number_format($row['diff'], 0)) : '—';
            $pctStr  = ($row['prev_val'] > 0 && $row['curr_val'] > 0) ? ($diffSign . $row['percent'] . '%') : '—';

            $tableHtml .= "<tr style=\"background-color: {$bg};\">";
            $tableHtml .= "<td style=\"width: {$wCol1}; text-align: right; font-weight: bold;\">{$row['month_name']}</td>";
            $tableHtml .= "<td style=\"width: {$wCol2};\">{$prevStr}</td>";
            $tableHtml .= "<td style=\"width: {$wCol3}; font-weight: bold; color: #065f46;\">{$currStr}</td>";
            $tableHtml .= "<td style=\"width: {$wCol4}; font-weight: bold; color: {$diffColor};\">{$diffStr}</td>";
            $tableHtml .= "<td style=\"width: {$wCol5}; font-weight: bold; color: {$diffColor};\">{$pctStr}</td>";
            $tableHtml .= "</tr>";
        }

        // المجموع السنوي
        $totDiffColor = $companyTable['total_diff'] >= 0 ? '#6ee7b7' : '#fca5a5';
        $totDiffSign = $companyTable['total_diff'] > 0 ? '+' : '';
        $tableHtml .= '<tr style="background-color: #064e3b; color: #ffffff; font-weight: bold; font-size: 11.5pt;">';
        $tableHtml .= "<td style=\"width: {$wCol1}; text-align: right;\">المجموع السنوي</td>";
        $tableHtml .= "<td style=\"width: {$wCol2};\">" . number_format($companyTable['total_prev'], 0) . "</td>";
        $tableHtml .= "<td style=\"width: {$wCol3};\">" . number_format($companyTable['total_curr'], 0) . "</td>";
        $tableHtml .= "<td style=\"width: {$wCol4}; color: {$totDiffColor};\">{$totDiffSign}" . number_format($companyTable['total_diff'], 0) . "</td>";
        $tableHtml .= "<td style=\"width: {$wCol5}; color: {$totDiffColor};\">{$totDiffSign}{$companyTable['total_pct']}%</td>";
        $tableHtml .= '</tr></tbody></table>';

        $pdf->writeHTML($headerHtml . $companyBanner . $tableHtml, true, false, true, false, '');

        // الصفحات التالية: كل صفحة فيها تشكيلين / مركزين في وضع عمودي (Portrait) بخط كبير 10pt - 13pt
        $centerKeys = array_values(array_filter(array_keys($compData['tables']), fn ($k) => $k !== 'company'));

        $wCR1 = '20%';
        $wCR2 = '22%';
        $wCR3 = '22%';
        $wCR4 = '22%';
        $wCR5 = '14%';

        for ($i = 0; $i < count($centerKeys); $i += 2) {
            $pdf->AddPage();
            $cHeader = $this->getReportHeaderHtml(
                "مقارنة الإيراد للتشكيلات بين عامي {$prevYear} و {$currYear}",
                "تفاصيل الموانئ ومقر الشركة (بالدينار العراقي)"
            );

            $pageContent = $cHeader;

            for ($j = 0; $j < 2; $j++) {
                if (!isset($centerKeys[$i + $j])) break;
                $key = $centerKeys[$i + $j];
                $cTable = $compData['tables'][$key];

                $pageContent .= "<div style=\"background-color: #1e293b; color: #ffffff; padding: 4px 10px; font-size: 12.5pt; font-weight: bold; text-align: center; border-radius: 4px; margin-bottom: 3px;\">{$cTable['center_name']}</div>";
                $pageContent .= '<table cellpadding="3" border="1" style="border-collapse: collapse; border-color: #cbd5e1; width: 100%; font-size: 9.5pt; text-align: center; margin-bottom: 6px;">';
                $pageContent .= "<thead><tr style=\"background-color: #f1f5f9; font-weight: bold; font-size: 10pt;\">";
                $pageContent .= "<th style=\"width: {$wCR1}; text-align: right;\">الشهر</th>";
                $pageContent .= "<th style=\"width: {$wCR2};\">سنة {$prevYear} (د.ع)</th>";
                $pageContent .= "<th style=\"width: {$wCR3};\">سنة {$currYear} (د.ع)</th>";
                $pageContent .= "<th style=\"width: {$wCR4};\">الفارق (د.ع)</th>";
                $pageContent .= "<th style=\"width: {$wCR5};\">نسبة التغير %</th>";
                $pageContent .= "</tr></thead><tbody>";

                foreach ($cTable['rows'] as $r) {
                    $diffColor = $r['diff'] >= 0 ? '#065f46' : '#991b1b';
                    $diffSign = $r['diff'] > 0 ? '+' : '';
                    $pStr = $r['prev_val'] > 0 ? number_format($r['prev_val'], 0) : '—';
                    $cStr = $r['curr_val'] > 0 ? number_format($r['curr_val'], 0) : '—';
                    $dStr = $r['diff'] != 0 ? ($diffSign . number_format($r['diff'], 0)) : '—';
                    $pctStr  = ($r['prev_val'] > 0 && $r['curr_val'] > 0) ? ($diffSign . $r['percent'] . '%') : '—';

                    $pageContent .= '<tr>';
                    $pageContent .= "<td style=\"width: {$wCR1}; text-align: right; font-weight: bold;\">{$r['month_name']}</td>";
                    $pageContent .= "<td style=\"width: {$wCR2};\">{$pStr}</td>";
                    $pageContent .= "<td style=\"width: {$wCR3}; font-weight: bold; color: #047857;\">{$cStr}</td>";
                    $pageContent .= "<td style=\"width: {$wCR4}; color: {$diffColor}; font-weight: bold;\">{$dStr}</td>";
                    $pageContent .= "<td style=\"width: {$wCR5}; color: {$diffColor}; font-weight: bold;\">{$pctStr}</td>";
                    $pageContent .= '</tr>';
                }

                $totDColor = $cTable['total_diff'] >= 0 ? '#065f46' : '#991b1b';
                $totDSign = $cTable['total_diff'] > 0 ? '+' : '';
                $pageContent .= '<tr style="background-color: #f1f5f9; font-weight: bold; font-size: 10pt;">';
                $pageContent .= "<td style=\"width: {$wCR1}; text-align: right;\">المجموع</td>";
                $pageContent .= "<td style=\"width: {$wCR2};\">" . number_format($cTable['total_prev'], 0) . "</td>";
                $pageContent .= "<td style=\"width: {$wCR3}; color: #047857;\">" . number_format($cTable['total_curr'], 0) . "</td>";
                $pageContent .= "<td style=\"width: {$wCR4}; color: {$totDColor};\">{$totDSign}" . number_format($cTable['total_diff'], 0) . "</td>";
                $pageContent .= "<td style=\"width: {$wCR5}; color: {$totDColor};\">{$totDSign}{$cTable['total_pct']}%</td>";
                $pageContent .= '</tr>';

                $pageContent .= '</tbody></table>';

                // فاصل أنيق بين الميناء الأول والثاني في نفس الصفحة
                if ($j === 0 && isset($centerKeys[$i + 1])) {
                    $pageContent .= '<div style="margin-top: 5px; margin-bottom: 5px; border-bottom: 1.5px dashed #94a3b8;"></div>';
                }
            }

            $pdf->writeHTML($pageContent, true, false, true, false, '');
        }

        return $pdf->Output('تقرير_مقارنة_الإيراد_الشامل_لكل_التشكيلات.pdf', 'S');
    }

    /**
     * 5. تحميل PDF لمقارنة الأداء متعددة السنوات (3 سنوات فأكثر)
     */
    public function generateMultiYearComparisonPdf(
        string $comparisonType,
        string $periodScope,
        int $monthNumber,
        ?int $portId,
        array $yearIds
    ): string {
        $years = FiscalYear::whereIn('id', $yearIds)->orderBy('year', 'asc')->get();
        if ($years->isEmpty()) {
            $years = FiscalYear::orderBy('year', 'asc')->get();
        }

        $month = Month::where('month_number', $monthNumber)->first();
        $monthName = $month?->name_ar ?? 'تموز';
        $port = $portId ? Port::find($portId) : null;
        $portName = $port ? $port->name_ar : 'عموم موانئ الشركة (الموانئ الأربعة مجتمعة)';

        $title = $comparisonType === 'capacity' 
            ? 'مقارنة الطاقة الإنتاجية التشغيلية متعددة السنوات'
            : 'مقارنة الإيرادات المالية لكافة المراكز متعددة السنوات';

        $subtitle = ($periodScope === 'month')
            ? "لشهر {$monthName} — للسنوات (" . $years->pluck('year')->implode(' - ') . ")"
            : "المجموع السنوي الكامل / التراكمي — للسنوات (" . $years->pluck('year')->implode(' - ') . ")";

        if ($comparisonType === 'capacity') {
            $subtitle .= " — {$portName}";
        }

        $pdf = $this->createPdf($title, 'L');
        $pdf->AddPage();

        $headerHtml = $this->getReportHeaderHtml($title, $subtitle);

        $yearCount = count($years);
        $wMetric = '30%';
        $wDiff = '10%';
        $wPct = '10%';
        $wYear = number_format(50 / max(1, $yearCount), 2) . '%';

        $tableHtml = '<table cellpadding="3" border="1" style="border-collapse: collapse; border-color: #94a3b8; width: 100%; font-size: 8.5pt; text-align: center;">';
        $tableHtml .= '<thead><tr style="background-color: #0f172a; color: #ffffff; font-weight: bold; font-size: 9pt;">';
        $tableHtml .= "<th style=\"width: {$wMetric}; text-align: right;\">" . ($comparisonType === 'capacity' ? 'المؤشر / البيان التشغيلي' : 'مركز الإيراد / التشكيل') . "</th>";

        foreach ($years as $y) {
            $tableHtml .= "<th style=\"width: {$wYear};\">سنة {$y->year}</th>";
        }
        $tableHtml .= "<th style=\"width: {$wDiff}; background-color: #1e3a8a;\">الفارق الإجمالي</th>";
        $tableHtml .= "<th style=\"width: {$wPct}; background-color: #065f46;\">نسبة التغير %</th>";
        $tableHtml .= '</tr></thead><tbody>';

        if ($comparisonType === 'capacity') {
            $analyticsService = app(PortAnalyticsService::class);
            $metrics = [
                'total_ships'          => 'إجمالي عدد البواخر والناقلات (سفينة)',
                'container_ships'      => 'عدد بواخر الحاويات',
                'cargo_ships'          => 'عدد بواخر البضائع المتنوعة',
                'oil_tankers'          => 'عدد الناقلات النفطية',
                'total_containers'     => 'إجمالي عدد الحاويات (مستوردة + TEU)',
                'imported_containers'  => 'عدد الحاويات المستوردة (حاوية)',
                'imported_teu'         => 'الحاويات المستوردة مكافئ (TEU)',
                'exported_containers'  => 'إجمالي الحاويات المصدرة (فارغ + مليان)',
                'exported_teu'         => 'الحاويات المصدرة مكافئ (TEU)',
                'total_tonnage'        => 'الطاقة الإنتاجية الكلية (طن)',
                'imported_cargo_tons'  => 'وزن البضائع المستوردة بالطن',
                'exported_cargo_tons'  => 'وزن البضائع المصدرة بالطن',
                'general_cargo_tons'   => 'وزن البضائع المتنوعة بالطن',
                'oil_total_tons'       => 'إجمالي أطنان النفط والمشتقات (طن)',
                'imported_cars'        => 'عدد السيارات المستوردة (سيارة)',
                'daily_avg_tonnage'    => 'المعدل اليومي لمناولة البضائع (طن/يوم)',
                'monthly_avg_tonnage'  => 'المعدل الشهري لمناولة البضائع (طن/شهر)',
            ];

            foreach ($metrics as $key => $label) {
                $isPrimary = in_array($key, ['total_tonnage', 'total_ships', 'total_containers', 'oil_total_tons']);
                $bgColor = $isPrimary ? '#eff6ff' : '#ffffff';
                $fontWeight = $isPrimary ? 'bold' : 'normal';

                $rowHtml = "<tr style=\"background-color: {$bgColor}; font-weight: {$fontWeight};\">";
                $rowHtml .= "<td style=\"width: {$wMetric}; text-align: right;\">{$label}</td>";

                $rowVals = [];
                foreach ($years as $y) {
                    if ($periodScope === 'month') {
                        $q = \App\Models\MonthlyPortRecord::where('fiscal_year_id', $y->id)->where('month_id', $monthNumber);
                        if ($portId) { $q->where('port_id', $portId); }
                        $recs = $q->get();
                        $val = match($key) {
                            'total_ships'         => $recs->sum('total_container_ships') + $recs->sum('general_cargo_ships') + $recs->sum('oil_tankers_count') + $recs->sum('car_carrier_ships'),
                            'container_ships'     => $recs->sum('total_container_ships'),
                            'cargo_ships'         => $recs->sum('general_cargo_ships'),
                            'oil_tankers'         => $recs->sum('oil_tankers_count'),
                            'total_containers'    => $recs->sum('imported_containers_count') + $recs->sum('exported_containers_count'),
                            'imported_containers' => $recs->sum('imported_containers_count'),
                            'imported_teu'        => $recs->sum('imported_teu'),
                            'exported_containers' => $recs->sum('exported_containers_count'),
                            'exported_teu'        => $recs->sum('exported_teu'),
                            'total_tonnage'       => (float) $recs->sum('imported_containers_weight_tons') + (float) $recs->sum('exported_full_weight_tons') + (float) $recs->sum('general_cargo_weight_tons') + (float) $recs->sum('oil_total_tons') + (float) $recs->sum('imported_cars_weight_tons'),
                            'imported_cargo_tons' => (float) $recs->sum('imported_containers_weight_tons') + (float) $recs->sum('imported_cars_weight_tons'),
                            'exported_cargo_tons' => (float) $recs->sum('exported_full_weight_tons'),
                            'general_cargo_tons'  => (float) $recs->sum('general_cargo_weight_tons'),
                            'oil_total_tons'      => (float) $recs->sum('oil_total_tons'),
                            'imported_cars'       => $recs->sum('imported_cars_count'),
                            'daily_avg_tonnage'   => ((float) $recs->sum('imported_containers_weight_tons') + (float) $recs->sum('exported_full_weight_tons') + (float) $recs->sum('general_cargo_weight_tons') + (float) $recs->sum('oil_total_tons') + (float) $recs->sum('imported_cars_weight_tons')) / 30,
                            'monthly_avg_tonnage' => (float) $recs->sum('imported_containers_weight_tons') + (float) $recs->sum('exported_full_weight_tons') + (float) $recs->sum('general_cargo_weight_tons') + (float) $recs->sum('oil_total_tons') + (float) $recs->sum('imported_cars_weight_tons'),
                            default               => 0,
                        };
                    } else {
                        $sum = $analyticsService->getCumulativeSummary($y->id, 12, $portId);
                        $val = match($key) {
                            'total_ships'         => $sum['total_ships'] ?? 0,
                            'container_ships'     => $sum['container_ships'] ?? 0,
                            'cargo_ships'         => $sum['cargo_ships'] ?? 0,
                            'oil_tankers'         => $sum['oil_tankers'] ?? 0,
                            'total_containers'    => ($sum['imported_containers'] ?? 0) + ($sum['exported_containers'] ?? 0),
                            'imported_containers' => $sum['imported_containers'] ?? 0,
                            'imported_teu'        => $sum['imported_teu'] ?? 0,
                            'exported_containers' => $sum['exported_containers'] ?? 0,
                            'exported_teu'        => $sum['exported_teu'] ?? 0,
                            'total_tonnage'       => $sum['total_tonnage'] ?? 0,
                            'imported_cargo_tons' => $sum['imported_cargo_tons'] ?? 0,
                            'exported_cargo_tons' => $sum['exported_cargo_tons'] ?? 0,
                            'general_cargo_tons'  => $sum['general_cargo_tons'] ?? 0,
                            'oil_total_tons'      => $sum['oil_total_tons'] ?? 0,
                            'imported_cars'       => $sum['imported_cars'] ?? 0,
                            'daily_avg_tonnage'   => $sum['daily_avg_tonnage'] ?? 0,
                            'monthly_avg_tonnage' => $sum['monthly_avg_tonnage'] ?? 0,
                            default               => 0,
                        };
                    }
                    $rowVals[] = (float) $val;
                    $rowHtml .= "<td style=\"width: {$wYear};\">" . number_format($val, 0) . "</td>";
                }

                $firstVal = reset($rowVals);
                $lastVal = end($rowVals);
                $diff = $lastVal - $firstVal;
                $pct = $firstVal > 0 ? (($diff / $firstVal) * 100) : 0;
                $diffColor = $diff >= 0 ? '#047857' : '#b91c1c';
                $diffSign = $diff > 0 ? '+' : '';

                $rowHtml .= "<td style=\"width: {$wDiff}; color: {$diffColor}; font-weight: bold;\">{$diffSign}" . number_format($diff, 0) . "</td>";
                $rowHtml .= "<td style=\"width: {$wPct}; color: {$diffColor}; font-weight: bold;\">{$diffSign}" . number_format($pct, 1) . "%</td>";
                $rowHtml .= "</tr>";

                $tableHtml .= $rowHtml;
            }

        } else {
            // مقارنة الإيرادات المالية
            $centers = RevenueCenter::where('is_active', true)->orderBy('sort_order')->get();
            $grandGross = array_fill(0, count($years), 0);
            $grandNet   = array_fill(0, count($years), 0);

            foreach ($centers as $c) {
                $rowHtml = "<tr>";
                $rowHtml .= "<td style=\"width: {$wMetric}; text-align: right; font-weight: bold;\">{$c->name_ar}</td>";

                $rowVals = [];
                foreach ($years as $idx => $y) {
                    $q = \App\Models\RevenueRecord::where('fiscal_year_id', $y->id)->where('revenue_center_id', $c->id);
                    if ($periodScope === 'month') {
                        $q->where('month_id', $monthNumber);
                    }
                    $val = (float) $q->sum('gross_revenue');
                    $rowVals[] = $val;
                    $grandGross[$idx] += $val;
                    $rowHtml .= "<td style=\"width: {$wYear};\">" . number_format($val, 0) . "</td>";
                }

                $firstVal = reset($rowVals);
                $lastVal = end($rowVals);
                $diff = $lastVal - $firstVal;
                $pct = $firstVal > 0 ? (($diff / $firstVal) * 100) : 0;
                $diffColor = $diff >= 0 ? '#047857' : '#b91c1c';
                $diffSign = $diff > 0 ? '+' : '';

                $rowHtml .= "<td style=\"width: {$wDiff}; color: {$diffColor}; font-weight: bold;\">{$diffSign}" . number_format($diff, 0) . "</td>";
                $rowHtml .= "<td style=\"width: {$wPct}; color: {$diffColor}; font-weight: bold;\">{$diffSign}" . number_format($pct, 1) . "%</td>";
                $rowHtml .= "</tr>";

                $tableHtml .= $rowHtml;
            }

            // حساب الفارق الكلي الذكي للإيراد الكلي
            $nonZeroG = array_filter($grandGross, fn($v) => $v > 0);
            if (count($nonZeroG) >= 2) {
                $firstG = reset($nonZeroG);
                $lastG = end($nonZeroG);
                $diffG = $lastG - $firstG;
                $pctG = ($diffG / $firstG) * 100;
            } else {
                $firstG = reset($grandGross);
                $lastG = end($grandGross);
                $diffG = $lastG - $firstG;
                $pctG = $firstG > 0 ? (($diffG / $firstG) * 100) : 0;
            }
            $gSign = $diffG > 0 ? '+' : '';
            $gColor = $diffG >= 0 ? '#86efac' : '#fca5a5'; // أخضر للموجب وأحمر للسالب

            // صف إجمالي الإيراد الكلي
            $tableHtml .= '<tr style="background-color: #0f172a; color: #ffffff; font-weight: bold; font-size: 9pt;">';
            $tableHtml .= "<td style=\"width: {$wMetric}; text-align: right;\">إجمالي الإيراد الكلي للشركة</td>";
            foreach ($grandGross as $gVal) {
                $tableHtml .= "<td style=\"width: {$wYear}; color: #fde047;\">" . number_format($gVal, 0) . "</td>";
            }
            $tableHtml .= "<td style=\"width: {$wDiff}; color: {$gColor};\">{$gSign}" . number_format($diffG, 0) . "</td>";
            $tableHtml .= "<td style=\"width: {$wPct}; color: {$gColor};\">{$gSign}" . number_format($pctG, 1) . "%</td>";
            $tableHtml .= '</tr>';

            // حساب الإيراد الصافي للشركة
            foreach ($years as $idx => $y) {
                $q = \App\Models\RevenueRecord::where('fiscal_year_id', $y->id);
                if ($periodScope === 'month') {
                    $q->where('month_id', $monthNumber);
                }
                $grandNet[$idx] = (float) $q->sum('net_revenue');
            }

            $nonZeroN = array_filter($grandNet, fn($v) => $v > 0);
            if (count($nonZeroN) >= 2) {
                $firstN = reset($nonZeroN);
                $lastN = end($nonZeroN);
                $diffN = $lastN - $firstN;
                $pctN = ($diffN / $firstN) * 100;
            } else {
                $firstN = reset($grandNet);
                $lastN = end($grandNet);
                $diffN = $lastN - $firstN;
                $pctN = $firstN > 0 ? (($diffN / $firstN) * 100) : 0;
            }
            $nSign = $diffN > 0 ? '+' : '';
            $nColor = $diffN >= 0 ? '#86efac' : '#fca5a5';

            // صف الإيراد الصافي لعموم الشركة
            $tableHtml .= '<tr style="background-color: #064e3b; color: #ffffff; font-weight: bold; font-size: 9pt;">';
            $tableHtml .= "<td style=\"width: {$wMetric}; text-align: right;\">الإيراد الصافي لعموم الشركة</td>";
            foreach ($grandNet as $nVal) {
                $tableHtml .= "<td style=\"width: {$wYear}; color: #a7f3d0;\">" . number_format($nVal, 0) . "</td>";
            }
            $tableHtml .= "<td style=\"width: {$wDiff}; color: {$nColor};\">{$nSign}" . number_format($diffN, 0) . "</td>";
            $tableHtml .= "<td style=\"width: {$wPct}; color: {$nColor};\">{$nSign}" . number_format($pctN, 1) . "%</td>";
            $tableHtml .= '</tr>';
        }

        $tableHtml .= '</tbody></table>';

        $pdf->writeHTML($headerHtml . $tableHtml, true, false, true, false, '');
        return $pdf->Output('تقرير_مقارنة_الأداء_متعددة_السنوات.pdf', 'S');
    }

    /**
     * 6. تحميل PDF لتقرير تحليل الانحراف المعياري والاستقرار
     */
    public function generateStandardDeviationPdf(
        int $fiscalYearId,
        ?int $portId = null,
        string $metric = 'tonnage'
    ): string {
        $fiscalYear = FiscalYear::find($fiscalYearId);
        $port = $portId ? Port::find($portId) : null;
        $portName = $port ? $port->name_ar : 'عموم موانئ الشركة (الموانئ الأربعة مجتمعة)';

        $service = app(PortAnalyticsService::class);
        $stats = $service->getYearlyStandardDeviation($fiscalYearId, $portId);
        $mStats = $stats[$metric] ?? [];

        $metricTitle = match($metric) {
            'tonnage' => 'الطاقة الإنتاجية الكلية (بالطن)',
            'revenue' => 'الإيرادات المالية الكلية (بالدينار العراقي)',
            'ships'   => 'حركة البواخر والناقلات الواصلة (سفينة)',
            'teu'     => 'الحاويات المكافئة المتداولة (TEU)',
            default   => 'الأداء العام',
        };

        $unit = match($metric) {
            'tonnage' => 'طن',
            'revenue' => 'د.ع',
            'ships'   => 'سفينة',
            'teu'     => 'TEU',
            default   => '',
        };

        $title = "تقرير تحليل الانحراف المعياري واستقرار الأداء — {$metricTitle}";
        $subtitle = "لسنة {$fiscalYear->year} — {$portName}";

        $pdf = $this->createPdf($title, 'P'); // Portrait A4
        $pdf->AddPage();

        $headerHtml = $this->getReportHeaderHtml($title, $subtitle);

        // ملخص المؤشرات الإحصائية
        $summaryHtml = '<table cellpadding="4" border="1" style="border-collapse: collapse; border-color: #cbd5e1; width: 100%; font-size: 9.5pt; margin-bottom: 15px; text-align: center;">';
        $summaryHtml .= '<tr style="background-color: #0f172a; color: #ffffff; font-weight: bold;">';
        $summaryHtml .= '<th style="width: 25%;">المتوسط الشهري (μ)</th>';
        $summaryHtml .= '<th style="width: 25%;">الانحراف المعياري (σ)</th>';
        $summaryHtml .= '<th style="width: 25%;">معامل التشتت (CV%)</th>';
        $summaryHtml .= '<th style="width: 25%;">تقييم الاستقرار</th>';
        $summaryHtml .= '</tr>';
        $summaryHtml .= '<tr style="font-weight: bold; background-color: #f8fafc;">';
        $summaryHtml .= '<td style="width: 25%;">' . number_format($mStats['mean'] ?? 0, 1) . ' ' . $unit . '</td>';
        $summaryHtml .= '<td style="width: 25%; color: #1e3a8a;">± ' . number_format($mStats['std_dev'] ?? 0, 1) . ' ' . $unit . '</td>';
        $summaryHtml .= '<td style="width: 25%; color: #047857;">' . number_format($mStats['cv'] ?? 0, 1) . '%</td>';
        $summaryHtml .= '<td style="width: 25%; color: #0f172a;">' . ($mStats['stability'] ?? '—') . '</td>';
        $summaryHtml .= '</tr></table>';

        // جدول المصفوفة الشهرية
        $tableHtml = '<table cellpadding="3.5" border="1" style="border-collapse: collapse; border-color: #94a3b8; width: 100%; font-size: 8.5pt; text-align: center;">';
        $tableHtml .= '<thead><tr style="background-color: #1e3a8a; color: #ffffff; font-weight: bold; font-size: 9pt;">';
        $tableHtml .= '<th style="width: 20%; text-align: right;">الشهر</th>';
        $tableHtml .= "<th style=\"width: 22%;\">القيمة الفعلية ({$unit})</th>";
        $tableHtml .= '<th style="width: 22%;">الانحراف عن المتوسط (x - μ)</th>';
        $tableHtml .= '<th style="width: 16%;">معامل Z-Score</th>';
        $tableHtml .= '<th style="width: 20%;">تقييم حالة الشهر</th>';
        $tableHtml .= '</tr></thead><tbody>';

        $mean = $mStats['mean'] ?? 0;
        $stdDev = $mStats['std_dev'] ?? 1;

        foreach ($stats['monthly'] as $mData) {
            $val = (float) ($mData[$metric] ?? 0);
            if ($val > 0) {
                $dev = $val - $mean;
                $zScore = $stdDev > 0 ? ($dev / $stdDev) : 0;
                $devSign = $dev > 0 ? '+' : '';
                $devColor = $dev >= 0 ? '#047857' : '#b91c1c';

                $assessment = match(true) {
                    $zScore >= 1.0  => 'نشاط مرتفع فوق المعدل',
                    $zScore >= -1.0 => 'ضمن النطاق الطبيعي المستقر',
                    default         => 'تراجع تحت المعدل الطبيعي',
                };
                $zStr = ($zScore > 0 ? '+' : '') . number_format($zScore, 2) . ' σ';
                $devStr = $devSign . number_format($dev, 0);
            } else {
                $devStr = '—';
                $zStr = '—';
                $assessment = 'لا توجد بيانات مسجلة';
                $devColor = '#64748b';
            }

            $tableHtml .= '<tr>';
            $tableHtml .= "<td style=\"width: 20%; text-align: right; font-weight: bold;\">{$mData['month_name']}</td>";
            $tableHtml .= "<td style=\"width: 22%; font-weight: bold;\">" . ($val > 0 ? number_format($val, 0) : '—') . "</td>";
            $tableHtml .= "<td style=\"width: 22%; color: {$devColor}; font-weight: bold;\">{$devStr}</td>";
            $tableHtml .= "<td style=\"width: 16%;\">{$zStr}</td>";
            $tableHtml .= "<td style=\"width: 20%;\">{$assessment}</td>";
            $tableHtml .= '</tr>';
        }

        $tableHtml .= '</tbody></table>';

        // دليل الإجراءات والمعالجات التصحيحية الموصى بها
        $actionGuideHtml = '<div style="margin-top: 12px;">';
        $actionGuideHtml .= '<table cellpadding="3.5" border="1" style="border-collapse: collapse; border-color: #cbd5e1; width: 100%; font-size: 8pt; margin-top: 8px;">';
        $actionGuideHtml .= '<tr style="background-color: #1e3a8a; color: #ffffff; font-weight: bold; font-size: 8.5pt;">';
        $actionGuideHtml .= '<th colspan="3" style="text-align: right;">🛠️ دليل الإجراءات التشغيلية والتصحيحية الموصى بها عند حدوث الانحراف</th>';
        $actionGuideHtml .= '</tr>';
        $actionGuideHtml .= '<tr style="background-color: #f1f5f9; font-weight: bold; text-align: center; color: #0f172a;">';
        $actionGuideHtml .= '<th style="width: 22%;">مستوى الانحراف (Z-Score)</th>';
        $actionGuideHtml .= '<th style="width: 38%;">التشخيص والأسباب المحتملة</th>';
        $actionGuideHtml .= '<th style="width: 40%;">المعالجة والإجراء التنفيذي الفوري</th>';
        $actionGuideHtml .= '</tr>';

        // 1. تراجع تحت المعدل
        $actionGuideHtml .= '<tr>';
        $actionGuideHtml .= '<td style="width: 22%; background-color: #fef2f2; color: #991b1b; font-weight: bold; text-align: center;">';
        $actionGuideHtml .= 'انحراف سلبي حاد<br/><span style="font-size: 7pt; color: #b91c1c;">(Z &lt; -1.0 σ — تراجع)</span>';
        $actionGuideHtml .= '</td>';
        $actionGuideHtml .= '<td style="width: 38%; font-size: 7.5pt; color: #334155;">';
        $actionGuideHtml .= '• أعطال طارئة في رافعات الأرصفة أو معدات المناولة.<br/>';
        $actionGuideHtml .= '• تأخر خروج الشاحنات وتكدس البضائع في الساحات.<br/>';
        $actionGuideHtml .= '• اضطرابات ملاحية أو تحول خطوط لموانئ مجاورة.';
        $actionGuideHtml .= '</td>';
        $actionGuideHtml .= '<td style="width: 40%; font-size: 7.5pt; color: #1e293b;">';
        $actionGuideHtml .= '1. استنفار فرق الصيانة وإعادة المعدات للخدمة فوراً.<br/>';
        $actionGuideHtml .= '2. تشغيل مناوبات إضافية (24/7) لتسريع الشحن والتفريغ.<br/>';
        $actionGuideHtml .= '3. تقديم تسهيلات تسعيرية وجمركية لتحفيز الوكلاء الملاحيين.';
        $actionGuideHtml .= '</td>';
        $actionGuideHtml .= '</tr>';

        // 2. النطاق الطبيعي المستقر
        $actionGuideHtml .= '<tr>';
        $actionGuideHtml .= '<td style="width: 22%; background-color: #f0fdf4; color: #14532d; font-weight: bold; text-align: center;">';
        $actionGuideHtml .= 'أداء طبيعي مستقر<br/><span style="font-size: 7pt; color: #15803d;">(±1.0 σ — كفاءة متوازنة)</span>';
        $actionGuideHtml .= '</td>';
        $actionGuideHtml .= '<td style="width: 38%; font-size: 7.5pt; color: #334155;">';
        $actionGuideHtml .= '• توازن مثالي بين الطاقة الاستيعابية وحجم المناولة.<br/>';
        $actionGuideHtml .= '• انسيابية حركة البواخر واستقرار عوائد الرسوم والخدمات.';
        $actionGuideHtml .= '</td>';
        $actionGuideHtml .= '<td style="width: 40%; font-size: 7.5pt; color: #1e293b;">';
        $actionGuideHtml .= '1. الالتزام الصارم ببرامج الصيانة الوقائية المجدولة.<br/>';
        $actionGuideHtml .= '2. مراقبة سرعة دوران الأرصفة (Berth Turnaround Time).<br/>';
        $actionGuideHtml .= '3. تعزيز تدريب الكوادر البحرية والمشغلين.';
        $actionGuideHtml .= '</td>';
        $actionGuideHtml .= '</tr>';

        // 3. طفرة إنتاجية / ضغط
        $actionGuideHtml .= '<tr>';
        $actionGuideHtml .= '<td style="width: 22%; background-color: #eff6ff; color: #1e3a8a; font-weight: bold; text-align: center;">';
        $actionGuideHtml .= 'طفرة / ضغط تشغيلي<br/><span style="font-size: 7pt; color: #1d4ed8;">(Z &gt; +1.0 σ — ذروة النشاط)</span>';
        $actionGuideHtml .= '</td>';
        $actionGuideHtml .= '<td style="width: 38%; font-size: 7.5pt; color: #334155;">';
        $actionGuideHtml .= '• زيادة تدفق البواخر بما يفوق الطاقة التشغيلية المعتادة.<br/>';
        $actionGuideHtml .= '• احتمالية تكدس الحاويات وازدحام منطقة المخطاف.';
        $actionGuideHtml .= '</td>';
        $actionGuideHtml .= '<td style="width: 40%; font-size: 7.5pt; color: #1e293b;">';
        $actionGuideHtml .= '1. فتح ساحات تخزين إضافية وإعادة جدولة الأرصفة بمرونة.<br/>';
        $actionGuideHtml .= '2. استدعاء طواقم دعم لتسريع المناولة ومنع التأخير.<br/>';
        $actionGuideHtml .= '3. توثيق أسباب الطفرة لبناء استدامة تشغيلية طويلة الأجل.';
        $actionGuideHtml .= '</td>';
        $actionGuideHtml .= '</tr>';

        // توصية المؤشر المخصص
        $metricAction = match($metric) {
            'tonnage' => '<strong>توصية الطاقة الإنتاجية (طن):</strong> تسريع معدلات التفريغ بالساعة (TPH)، صيانة السيور الناقلة ومضخات السوائل، والتنسيق المسبق مع أساطيل النقل البري.',
            'revenue' => '<strong>توصية الإيرادات المالية:</strong> تدقيق رسوم الخدمات الملاحية والرسو، تسريع التحصيل الإلكتروني، ومتابعة الفارق بين الإيراد الكلي والصافي لضبط التكاليف.',
            'ships'   => '<strong>توصية حركة البواخر:</strong> ضمان جاهزية قاطرات السحب وزوارق الإرشاد، تأمين أعماق القنوات الملاحية، وتطبيق خطة التخصيص المسبق للأرصفة (Berth Allocation).',
            'teu'     => '<strong>توصية تداول الحاويات (TEU):</strong> خفض فترة مكوث الحاويات (Dwell Time)، تسريع إعادة الحاويات الفارغة، وتكثيف تشغيل رافعات الساحات (RTG).',
            default   => '<strong>توصية عامة:</strong> الالتزام بالمعايير التشغيلية المعتمدة وتكثيف التنسيق بين إدارات الموانئ والملاحة.',
        };

        $actionGuideHtml .= '<tr style="background-color: #f8fafc;">';
        $actionGuideHtml .= "<td colspan=\"3\" style=\"font-size: 7.8pt; color: #0f172a; padding: 5px 8px;\">{$metricAction}</td>";
        $actionGuideHtml .= '</tr>';

        $actionGuideHtml .= '</table></div>';

        $pdf->writeHTML($headerHtml . $summaryHtml . $tableHtml . $actionGuideHtml, true, false, true, false, '');
        return $pdf->Output('تقرير_الانحراف_المعياري_والاستقرار.pdf', 'S');
    }
}
