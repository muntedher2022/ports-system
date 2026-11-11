<?php

namespace App\Http\Controllers;

use App\Models\ActivityAttachment;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ProtectedAttachmentController extends Controller
{
    /**
     * استعراض الملف المحمي بشكل آمن مع التحقق من صلاحية المستخدم
     */
    public function stream(Request $request, ActivityAttachment $attachment): BinaryFileResponse|Response
    {
        // إذا كان النشاط سرياً، لا يراه إلا المدير العام أو المشرف
        if ($attachment->activity && $attachment->activity->is_confidential) {
            if (! auth()->check() || ! auth()->user()->hasAnyRole(['super_admin', 'director_general'])) {
                abort(403, 'هذا النشاط سري ومخصص للمدير العام فقط');
            }
        }

        $fullPath = $attachment->absolute_file_path;

        if (! $fullPath || ! file_exists($fullPath)) {
            abort(404, 'الملف المطلوب غير موجود على الخادم');
        }

        $mimeType = $attachment->file_type ?: mime_content_type($fullPath) ?: 'application/octet-stream';

        return response()->file($fullPath, [
            'Content-Type' => $mimeType,
            'Cache-Control' => 'public, max-age=86400',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }

    /**
     * تحميل الملف المرفق مع الحفاظ على اسمه الأصلي
     */
    public function download(Request $request, ActivityAttachment $attachment): BinaryFileResponse
    {
        if ($attachment->activity && $attachment->activity->is_confidential) {
            if (! auth()->check() || ! auth()->user()->hasAnyRole(['super_admin', 'director_general'])) {
                abort(403, 'هذا النشاط سري ومخصص للمدير العام فقط');
            }
        }

        $fullPath = $attachment->absolute_file_path;

        if (! $fullPath || ! file_exists($fullPath)) {
            abort(404, 'الملف المطلوب غير موجود على الخادم');
        }

        $downloadName = $attachment->file_name ?: basename($fullPath);

        return response()->download($fullPath, $downloadName, [
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }
}
