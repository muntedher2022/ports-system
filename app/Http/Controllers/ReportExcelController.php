<?php

namespace App\Http\Controllers;

use App\Exports\CargoStatusExport;
use App\Exports\ContainerStatusExport;
use App\Exports\MonthlyPortRecordExport;
use App\Exports\RevenueRecordExport;
use App\Models\CargoStatusRecord;
use App\Models\FiscalYear;
use App\Models\Month;
use App\Models\MonthlyPortRecord;
use App\Models\Port;
use App\Models\RevenueCenter;
use App\Models\RevenueRecord;
use App\Services\ActivityLogger;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ReportExcelController extends Controller
{
    public function exportMonthlyPortRecords(Request $request): BinaryFileResponse
    {
        $portId = $request->input('port_id') ? (int) $request->input('port_id') : null;
        $fiscalYearId = $request->input('fiscal_year_id') ? (int) $request->input('fiscal_year_id') : null;

        $portName = $portId ? Port::find($portId)?->name_ar : 'كافة_الموانئ';
        $yearName = $fiscalYearId ? FiscalYear::find($fiscalYearId)?->year : 'كافة_السنوات';

        // تسجيل النشاط في سجل التتبع
        ActivityLogger::log(
            'exported_excel',
            "تصدير السجلات التشغيلية للموانئ إلى Excel ({$portName} - {$yearName})",
            MonthlyPortRecord::class,
            null,
            ['port_id' => $portId, 'fiscal_year_id' => $fiscalYearId]
        );

        $fileName = 'البيانات_التشغيلية_' . str_replace(' ', '_', $portName) . '_' . $yearName . '_' . date('Ymd_His') . '.xlsx';

        return Excel::download(
            new MonthlyPortRecordExport($portId, $fiscalYearId),
            $fileName
        );
    }

    public function exportRevenueRecords(Request $request): BinaryFileResponse
    {
        $centerId = $request->input('revenue_center_id') ? (int) $request->input('revenue_center_id') : null;
        $fiscalYearId = $request->input('fiscal_year_id') ? (int) $request->input('fiscal_year_id') : null;

        $centerName = $centerId ? RevenueCenter::find($centerId)?->name_ar : 'كافة_المراكز_السبعة';
        $yearName = $fiscalYearId ? FiscalYear::find($fiscalYearId)?->year : 'كافة_السنوات';

        // تسجيل النشاط في سجل التتبع
        ActivityLogger::log(
            'exported_excel',
            "تصدير سجلات الإيراد للمراكز السبعة إلى Excel ({$centerName} - {$yearName})",
            RevenueRecord::class,
            null,
            ['revenue_center_id' => $centerId, 'fiscal_year_id' => $fiscalYearId]
        );

        $fileName = 'سجلات_الإيراد_' . str_replace(' ', '_', $centerName) . '_' . $yearName . '_' . date('Ymd_His') . '.xlsx';

        return Excel::download(
            new RevenueRecordExport($centerId, $fiscalYearId),
            $fileName
        );
    }

    public function exportContainerStatus(Request $request): BinaryFileResponse
    {
        $type         = $request->input('type', 'abandoned');
        $fiscalYearId = $request->input('year') ? (int) $request->input('year') : ($request->input('fiscal_year_id') ? (int) $request->input('fiscal_year_id') : null);
        $monthId      = $request->input('month') ? (int) $request->input('month') : ($request->input('month_id') ? (int) $request->input('month_id') : null);
        $portId       = $request->input('port') ? (int) $request->input('port') : null;

        $typeLabel = $type === 'abandoned' ? 'متخلفة' : 'خطرة';
        $monthObj  = $monthId ? Month::find($monthId) : Month::first();
        $yearObj   = $fiscalYearId ? FiscalYear::find($fiscalYearId) : FiscalYear::where('is_current', true)->first();

        $monthName = $monthObj ? "شهر_{$monthObj->month_number}" : 'شهر';
        $yearName  = $yearObj ? $yearObj->year : date('Y');

        ActivityLogger::log(
            'exported_excel',
            "تصدير موقف الحاويات {$typeLabel} إلى Excel ({$yearName} - {$monthName})",
        );

        $fileName  = "موقف_الحاويات_{$typeLabel}_{$monthName}_{$yearName}.xlsx";

        return Excel::download(
            new ContainerStatusExport($type, $fiscalYearId, $monthId, $portId),
            $fileName
        );
    }

    public function exportCargoStatus(Request $request): BinaryFileResponse
    {
        $type         = $request->input('type', 'abandoned');
        $fiscalYearId = $request->input('year') ? (int) $request->input('year') : ($request->input('fiscal_year_id') ? (int) $request->input('fiscal_year_id') : null);
        $monthId      = $request->input('month') ? (int) $request->input('month') : ($request->input('month_id') ? (int) $request->input('month_id') : null);
        $portId       = $request->input('port') ? (int) $request->input('port') : null;

        $typeLabel = $type === 'abandoned' ? 'متخلفة' : 'خطرة';
        $monthObj  = $monthId ? Month::find($monthId) : Month::first();
        $yearObj   = $fiscalYearId ? FiscalYear::find($fiscalYearId) : FiscalYear::where('is_current', true)->first();

        $monthName = $monthObj ? "شهر_{$monthObj->month_number}" : 'شهر';
        $yearName  = $yearObj ? $yearObj->year : date('Y');

        ActivityLogger::log(
            'exported_excel',
            "تصدير موقف المواد والبضائع {$typeLabel} إلى Excel ({$yearName} - {$monthName})",
            CargoStatusRecord::class
        );

        $fileName  = "موقف_المواد_والبضائع_{$typeLabel}_{$monthName}_{$yearName}.xlsx";

        return Excel::download(
            new CargoStatusExport($type, $fiscalYearId, $monthId, $portId),
            $fileName
        );
    }
}
