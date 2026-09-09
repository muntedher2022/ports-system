<?php

use App\Exports\PortRecordTemplateExport;
use App\Exports\RevenueRecordTemplateExport;
use App\Http\Controllers\ReportPdfController;
use Illuminate\Support\Facades\Route;
use Maatwebsite\Excel\Facades\Excel;

Route::get('/', function () {
    return redirect('/admin');
});

Route::middleware(['auth'])->prefix('admin/reports')->name('admin.reports.')->group(function () {
    Route::get('total-revenue-matrix/pdf', [ReportPdfController::class, 'exportTotalRevenueMatrix'])->name('total-revenue-matrix.pdf');
    Route::get('total-cumulative-capacity/pdf', [ReportPdfController::class, 'exportTotalCumulativeCapacity'])->name('total-cumulative-capacity.pdf');
    Route::get('capacity-comparison/pdf', [ReportPdfController::class, 'exportCapacityComparison'])->name('capacity-comparison.pdf');
    Route::get('revenue-comparison/pdf', [ReportPdfController::class, 'exportRevenueComparison'])->name('revenue-comparison.pdf');
    Route::get('multi-year-comparison/pdf', [ReportPdfController::class, 'exportMultiYearComparison'])->name('multi-year-comparison.pdf');
    Route::get('standard-deviation/pdf', [ReportPdfController::class, 'exportStandardDeviation'])->name('standard-deviation.pdf');

    // تصدير Excel
    Route::get('monthly-port-records/excel', [\App\Http\Controllers\ReportExcelController::class, 'exportMonthlyPortRecords'])->name('monthly-port-records.excel');
    Route::get('revenue-records/excel', [\App\Http\Controllers\ReportExcelController::class, 'exportRevenueRecords'])->name('revenue-records.excel');
});

// ─── مسارات الحاويات المتخلفة والخطرة ───
Route::middleware(['auth'])->prefix('admin/containers')->name('admin.containers.')->group(function () {
    Route::get('export-excel', [\App\Http\Controllers\ReportExcelController::class, 'exportContainerStatus'])->name('export-excel');
});

// ─── مسارات المواد والبضائع المتخلفة والخطرة ───
Route::middleware(['auth'])->prefix('admin/cargo-status')->name('admin.cargo-status.')->group(function () {
    Route::get('export-excel', [\App\Http\Controllers\ReportExcelController::class, 'exportCargoStatus'])->name('export');
});

// ─── تحميل قوالب Excel (متوافق مع Chrome وEdge وكل المتصفحات) ───
Route::middleware(['auth'])->prefix('admin/templates')->name('admin.templates.')->group(function () {
    Route::get('port-records', function () {
        return response()->download(public_path('templates/port_records_template.xlsx'), 'قالب_البيانات_التشغيلية.xlsx');
    })->name('port-records');

    Route::get('revenue-records', function () {
        return response()->download(public_path('templates/revenue_records_template.xlsx'), 'قالب_بيانات_الإيراد.xlsx');
    })->name('revenue-records');
});
