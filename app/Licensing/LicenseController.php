<?php

namespace App\Licensing;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class LicenseController extends Controller
{
    /**
     * الحصول على حالة الترخيص وبصمة الجهاز الحالية.
     */
    public function status()
    {
        $check = LicensingService::check();

        return response()->json([
            'is_activated' => $check['valid'],
            'reason' => $check['reason'],
            'hwid' => $check['hwid'] ?? HardwareFingerprint::get(),
            'message' => $check['message'] ?? 'التطبيق مرخص ومفعل بنجاح.',
            'license_info' => $check['license'] ?? null
        ]);
    }

    /**
     * تفعيل التطبيق عن طريق رفع رخصة التفعيل أو إدخال كود الترخيص.
     */
    public function activate(Request $request)
    {
        $request->validate([
            'license_content' => 'required|string',
        ]);

        $licenseContent = $request->input('license_content');

        // محاولة تفعيل الرخصة وحفظها
        $activated = LicensingService::activate($licenseContent);

        if (!$activated) {
            return response()->json([
                'status' => 'error',
                'message' => 'ملف الترخيص غير صالح أو غير متوافق مع بصمة هذا الجهاز أو النطاق.'
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'تم تفعيل التطبيق بنجاح! شكراً لك.'
        ]);
    }

    /**
     * إرسال رمز التحقق (OTP) لرقم هاتف العميل عبر الواتساب.
     */
    public function requestPhoneOtp(Request $request)
    {
        $phone = $request->input('phone_number') ?: $request->query('phone_number');
        if (!$phone) {
            return response()->json(['status' => 'error', 'message' => 'يرجى إدخال رقم الهاتف أولاً.'], 422);
        }

        $serverUrl = env('LICENSING_SERVER_URL', 'https://licensing-manager.test');
        $apiUrl = rtrim($serverUrl, '/') . '/api/license/request-otp-phone';
        $projectSlug = env('LICENSING_PROJECT_SLUG', strtolower(basename(base_path())));

        $payload = json_encode([
            'phone_number' => $phone,
            'hwid' => HardwareFingerprint::get(),
            'project_slug' => $projectSlug
        ]);

        $opts = [
            'http' => [
                'method' => 'POST',
                'header' => "Content-Type: application/json\r\nAccept: application/json\r\n",
                'content' => $payload,
                'timeout' => 10,
                'ignore_errors' => true
            ],
            'ssl' => [
                'verify_peer' => false,
                'verify_peer_name' => false,
            ]
        ];

        $res = @file_get_contents($apiUrl, false, stream_context_create($opts));
        if ($res === false) {
            return response()->json([
                'status' => 'error',
                'message' => 'تعذر الاتصال بخادم التراخيص المركزي. يرجى التأكد من توفر اتصال بالإنترنت.'
            ], 503);
        }

        $code = 200;
        if (isset($http_response_header)) {
            foreach ($http_response_header as $hdr) {
                if (preg_match('/^HTTP\/\d+\.\d+\s+(\d+)/i', $hdr, $m)) {
                    $code = (int)$m[1];
                    break;
                }
            }
        }

        $data = json_decode($res, true) ?: ['status' => 'error', 'message' => 'استجابة غير صالحة من السيرفر.'];
        return response()->json($data, $code);
    }

    /**
     * التحقق من كود الـ OTP وتفعيل النظام بالكامل.
     */
    public function verifyPhoneOtp(Request $request)
    {
        $phone = $request->input('phone_number') ?: $request->query('phone_number');
        $otp = $request->input('otp') ?: $request->query('otp');

        if (!$phone || !$otp) {
            return response()->json(['status' => 'error', 'message' => 'مطلوب رقم الهاتف ورمز التحقق.'], 422);
        }

        $serverUrl = env('LICENSING_SERVER_URL', 'https://licensing-manager.test');
        $apiUrl = rtrim($serverUrl, '/') . '/api/license/verify-otp-phone';
        $projectSlug = env('LICENSING_PROJECT_SLUG', strtolower(basename(base_path())));

        $payload = json_encode([
            'phone_number' => $phone,
            'otp' => $otp,
            'hwid' => HardwareFingerprint::get(),
            'project_slug' => $projectSlug
        ]);

        $opts = [
            'http' => [
                'method' => 'POST',
                'header' => "Content-Type: application/json\r\nAccept: application/json\r\n",
                'content' => $payload,
                'timeout' => 10,
                'ignore_errors' => true
            ],
            'ssl' => [
                'verify_peer' => false,
                'verify_peer_name' => false,
            ]
        ];

        $res = @file_get_contents($apiUrl, false, stream_context_create($opts));
        if ($res === false) {
            return response()->json([
                'status' => 'error',
                'message' => 'تعذر الاتصال بخادم التراخيص المركزي.'
            ], 503);
        }

        $code = 200;
        if (isset($http_response_header)) {
            foreach ($http_response_header as $hdr) {
                if (preg_match('/^HTTP\/\d+\.\d+\s+(\d+)/i', $hdr, $m)) {
                    $code = (int)$m[1];
                    break;
                }
            }
        }

        $data = json_decode($res, true);
        if ($code !== 200 || !$data || empty($data['license_file_content'])) {
            return response()->json($data ?: [
                'status' => 'error',
                'message' => 'فشل التحقق من كود الـ OTP.'
            ], $code ?: 422);
        }

        // تفعيل وحفظ ملف الرخصة محلياً
        $activated = LicensingService::activate($data['license_file_content']);
        if (!$activated) {
            $check = LicensingService::check();
            return response()->json([
                'status' => 'error',
                'message' => $check['message'] ?? 'تم استلام كود التفعيل لكنه غير متوافق مع عتاد هذا الجهاز أو التوقيع الرقمي.'
            ], 422);
        }

        return response()->json([
            'status' => 'success',
            'message' => '🎉 تهانينا! تم تفعيل وتشغيل النظام بنجاح.'
        ]);
    }

    /**
     * إلغاء تفعيل النظام وحذف ملف الترخيص ومسح الذاكرة المؤقتة.
     */
    public function reset()
    {
        LicensingService::deactivate();

        if (request()->expectsJson()) {
            return response()->json([
                'status' => 'success',
                'message' => '✅ تم إلغاء تفعيل النظام وحذف ملف الترخيص بنجاح.'
            ]);
        }

        return redirect('/')->with('message', 'تم إلغاء التفعيل بنجاح.');
    }
}
