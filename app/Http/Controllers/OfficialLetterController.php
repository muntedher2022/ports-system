<?php

namespace App\Http\Controllers;

use App\Services\ActivityLogger;
use App\Services\OfficialLettersService;
use Illuminate\Http\Request;

class OfficialLetterController extends Controller
{
    /**
     * معاينة الكتاب أو المذكرة بصيغة A4 جاهزة للطباعة
     */
    public function preview(Request $request, string $type)
    {
        $fiscalYearId = $request->integer('fiscal_year_id') ?: null;
        $monthId = $request->integer('month_id') ?: null;
        $memoNumber = $request->filled('memo_number') ? $request->input('memo_number') : null;
        $memoDate = $request->filled('memo_date') ? $request->input('memo_date') : null;

        $types = OfficialLettersService::getLetterTypes();
        if (! isset($types[$type])) {
            abort(404, 'نوع الكتاب أو المذكرة غير موجود.');
        }

        $data = OfficialLettersService::getLetterData($type, $fiscalYearId, $monthId, $memoNumber, $memoDate);
        $data['types'] = $types;
        $data['current_type'] = $type;

        ActivityLogger::log('viewed_letter', "معاينة طباعة: {$types[$type]['title']} لشهر {$data['month_name']} {$data['year']}");

        $viewName = match ($type) {
            'revenue_memo' => 'letters.revenue_memo',
            'capacity_memo' => 'letters.capacity_memo',
            'containers_letter' => 'letters.containers_letter',
            'cargo_letter' => 'letters.cargo_letter',
            default => 'letters.revenue_memo',
        };

        return view($viewName, $data);
    }

    /**
     * تحميل ملف Word (.docx) أصلي مغذى بالبيانات الرسمية من النظام
     */
    public function downloadDocx(Request $request, string $type)
    {
        $fiscalYearId = $request->integer('fiscal_year_id') ?: null;
        $monthId = $request->integer('month_id') ?: null;
        $memoNumber = $request->filled('memo_number') ? $request->input('memo_number') : null;
        $memoDate = $request->filled('memo_date') ? $request->input('memo_date') : null;

        $types = OfficialLettersService::getLetterTypes();
        if (! isset($types[$type])) {
            abort(404, 'نوع الكتاب أو المذكرة غير موجود.');
        }

        $data = OfficialLettersService::getLetterData($type, $fiscalYearId, $monthId, $memoNumber, $memoDate);

        $filePath = OfficialLettersService::generateDocx($type, $data);

        ActivityLogger::log('downloaded_letter_docx', "تحميل ملف Word: {$types[$type]['title']} لشهر {$data['month_name']} {$data['year']}");

        return response()->download($filePath)->deleteFileAfterSend(true);
    }
}
