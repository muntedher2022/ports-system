<?php

namespace App\Licensing;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerifyLicenseMiddleware
{
    /**
     * اعتراض الطلبات والتحقق من ترخيص التشغيل.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // قائمة بالمسارات المستثناة من الفحص (مسارات التفعيل والتحقق من الحالة)
        $excludedPaths = [
            'api/license/status',
            'api/license/activate',
            'api/license/request-otp-phone',
            'api/license/verify-otp-phone',
            'api/license/reset',
            'api/license/deactivate',
            'license/reset',
            'license/deactivate',
            'intercom/api/license/status',
            'intercom/api/license/activate',
            'intercom/api/license/request-otp-phone',
            'intercom/api/license/verify-otp-phone',
            'intercomsaas/api/license/status',
            'intercomsaas/api/license/activate',
            'intercom_saas/api/license/status',
            'intercom_saas/api/license/activate',
            'api/health/uploads', // صفحة التحقق من المجلدات
        ];

        $currentPath = trim($request->path(), '/');

        // استثناء المسارات المحددة لتجنب القفل الدائري للتطبيق
        foreach ($excludedPaths as $path) {
            if ($currentPath === $path || str_starts_with($currentPath, $path)) {
                return $next($request);
            }
        }

        // فحص حالة الترخيص
        $licenseCheck = LicensingService::check();

        if (!$licenseCheck['valid']) {
            // إذا كان الطلب عبارة عن طلب API (يحتوي على api/ أو يطلب JSON)، نرجع استجابة JSON كود 402
            if ($request->is('api/*') || $request->is('*/api/*') || $request->expectsJson()) {
                return response()->json([
                    'status' => 'license_error',
                    'reason' => $licenseCheck['reason'],
                    'hwid' => $licenseCheck['hwid'] ?? HardwareFingerprint::get(),
                    'message' => $licenseCheck['message'],
                    'activation_url' => url('/activate')
                ], Response::HTTP_PAYMENT_REQUIRED);
            }

            // إذا كان تصفح عادي عبر الويب، نرجع واجهة التفعيل HTML الأنيقة والذاتية
            $hwid = $licenseCheck['hwid'] ?? HardwareFingerprint::get();
            $message = $licenseCheck['message'];
            
            // تحديد مسار API التفعيل بدقة وبطريقة نسبية لتجنب مشاكل النطاقات وبروتوكولات HTTPS
            $activateUrl = '/api/license/activate';

            return response(self::getActivationPageHtml($hwid, $message, $activateUrl), 200)
                ->header('Content-Type', 'text/html; charset=utf-8');
        }

        return $next($request);
    }

    /**
     * واجهة تفعيل النظام بلغة HTML و CSS وتصميم عصري داكن تدعم التفعيل الفوري بالواتساب والتفعيل اليدوي.
     */
    public static function getActivationPageHtml(string $hwid, string $message, string $activateUrl, string $requestOtpUrl = '', string $verifyOtpUrl = ''): string
    {
        $requestOtpUrl = $requestOtpUrl ?: '/api/license/request-otp-phone';
        $verifyOtpUrl = $verifyOtpUrl ?: '/api/license/verify-otp-phone';

        return <<<HTML
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تفعيل ترخيص النظام</title>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;800&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Cairo', sans-serif;
            background: linear-gradient(135deg, #0f172a 0%, #1e1b4b 100%);
            color: #f8fafc;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            margin: 0;
            padding: 20px;
            box-sizing: border-box;
        }
        .container {
            background: rgba(30, 41, 59, 0.85);
            backdrop-filter: blur(16px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 18px;
            padding: 32px 28px;
            width: 100%;
            max-width: 480px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
            text-align: center;
        }
        h2 {
            margin-top: 0;
            color: #38bdf8;
            font-size: 24px;
            font-weight: 800;
            margin-bottom: 6px;
        }
        p.desc {
            color: #94a3b8;
            font-size: 13px;
            line-height: 1.6;
            margin-bottom: 20px;
        }
        /* نظام التبويبات Tabs */
        .tabs-nav {
            display: flex;
            background: rgba(15, 23, 42, 0.6);
            padding: 4px;
            border-radius: 10px;
            margin-bottom: 20px;
            border: 1px solid rgba(255, 255, 255, 0.05);
        }
        .tab-btn {
            flex: 1;
            padding: 8px 12px;
            border: none;
            background: transparent;
            color: #94a3b8;
            font-size: 13px;
            font-weight: 700;
            cursor: pointer;
            border-radius: 7px;
            font-family: 'Cairo', sans-serif;
            transition: all 0.2s ease;
        }
        .tab-btn.active {
            background: #0284c7;
            color: #ffffff;
            box-shadow: 0 2px 8px rgba(2, 132, 199, 0.4);
        }
        .tab-content {
            display: none;
            text-align: right;
        }
        .tab-content.active {
            display: block;
        }
        .form-group {
            margin-bottom: 16px;
        }
        label {
            display: block;
            font-size: 13px;
            color: #cbd5e1;
            margin-bottom: 6px;
            font-weight: 600;
        }
        input[type="text"] {
            width: 100%;
            background: rgba(15, 23, 42, 0.8);
            border: 1px solid rgba(255, 255, 255, 0.12);
            border-radius: 8px;
            padding: 12px 14px;
            color: #f8fafc;
            font-size: 14px;
            font-family: 'Cairo', sans-serif;
            box-sizing: border-box;
            outline: none;
            transition: all 0.2s;
        }
        input[type="text"]:focus {
            border-color: #38bdf8;
            box-shadow: 0 0 0 2px rgba(56, 189, 248, 0.2);
        }
        .hwid-box {
            background: rgba(15, 23, 42, 0.6);
            border: 1px dashed rgba(56, 189, 248, 0.4);
            border-radius: 8px;
            padding: 12px 14px;
            margin-bottom: 18px;
            font-family: monospace;
            font-size: 14px;
            font-weight: bold;
            color: #38bdf8;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .btn-copy {
            background: #38bdf8;
            color: #0f172a;
            border: none;
            border-radius: 6px;
            padding: 5px 12px;
            font-size: 12px;
            font-weight: bold;
            cursor: pointer;
            font-family: 'Cairo', sans-serif;
            transition: all 0.2s;
        }
        .btn-copy:hover {
            background: #0ea5e9;
        }
        textarea {
            width: 100%;
            height: 110px;
            background: rgba(15, 23, 42, 0.8);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 8px;
            padding: 12px;
            color: #f8fafc;
            font-family: monospace;
            font-size: 11px;
            resize: none;
            box-sizing: border-box;
            margin-bottom: 16px;
            outline: none;
            text-align: left;
            direction: ltr;
        }
        textarea:focus {
            border-color: #38bdf8;
            box-shadow: 0 0 0 2px rgba(56, 189, 248, 0.2);
        }
        .btn-action {
            width: 100%;
            background: #10b981;
            color: white;
            border: none;
            border-radius: 8px;
            padding: 12px;
            font-size: 15px;
            font-weight: 700;
            cursor: pointer;
            font-family: 'Cairo', sans-serif;
            transition: all 0.2s ease;
        }
        .btn-action:hover {
            background: #059669;
        }
        .btn-action:disabled {
            background: #475569;
            cursor: not-allowed;
        }
        .btn-whatsapp {
            background: #16a34a;
        }
        .btn-whatsapp:hover {
            background: #15803d;
        }
        .alert {
            margin-top: 16px;
            padding: 12px;
            border-radius: 8px;
            font-size: 13px;
            display: none;
            text-align: right;
            line-height: 1.5;
        }
        .alert-error {
            background: rgba(239, 68, 68, 0.15);
            border: 1px solid rgba(239, 68, 68, 0.3);
            color: #fca5a5;
        }
        .alert-success {
            background: rgba(16, 185, 129, 0.15);
            border: 1px solid rgba(16, 185, 129, 0.3);
            color: #6ee7b7;
        }
        .otp-input {
            text-align: center !important;
            font-size: 24px !important;
            letter-spacing: 8px !important;
            font-weight: bold !important;
            font-family: monospace !important;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>تفعيل ترخيص النظام</h2>
        <p class="desc">يرجى تفعيل ترخيص البرنامج للاستمرار في استخدام كافة المميزات.</p>

        <!-- التبويبات -->
        <div class="tabs-nav">
            <button class="tab-btn active" onclick="switchTab('wa')">📲 تفعيل الواتساب (تلقائي)</button>
            <button class="tab-btn" onclick="switchTab('manual')">📋 تفعيل يدوي (أوفلاين)</button>
        </div>

        <!-- تبويب الواتساب -->
        <div id="tab-wa" class="tab-content active">
            <!-- خطوة 1: إدخال الهاتف -->
            <div id="wa-step-phone">
                <div class="form-group">
                    <label>رقم هاتفك المسجل لدى المطور:</label>
                    <input type="text" id="wa-phone-input" placeholder="مثال: 07700000000" dir="ltr" style="text-align:right;">
                </div>
                <button class="btn-action btn-whatsapp" id="btn-request-otp" onclick="requestOtp()">📲 إرسال كود التفعيل إلى الواتساب</button>
            </div>

            <!-- خطوة 2: إدخال OTP -->
            <div id="wa-step-otp" style="display:none;">
                <p style="font-size:13px; color:#cbd5e1; margin-bottom:12px; text-align:center;">
                    تم إرسال رمز تفعيل مكون من 6 أرقام إلى حساب الواتساب الخاص بك بنجاح.
                </p>
                <div class="form-group">
                    <label style="text-align:center;">أدخل كود التحقق (OTP):</label>
                    <input type="text" id="wa-otp-input" class="otp-input" placeholder="••••••" maxlength="6">
                </div>
                <button class="btn-action" id="btn-verify-otp" onclick="verifyOtp()">✅ تأكيد وتفعيل النظام</button>
                <div style="text-align:center; margin-top:12px;">
                    <a href="javascript:void(0)" onclick="resetWaStep()" style="color:#38bdf8; font-size:12px; text-decoration:none;">تغيير رقم الهاتف أو إعادة الإرسال</a>
                </div>
            </div>
        </div>

        <!-- تبويب التفعيل اليدوي -->
        <div id="tab-manual" class="tab-content">
            <div style="font-size: 13px; color: #94a3b8; margin-bottom: 6px; font-weight: 600;">بصمة عتاد الجهاز الحالية (Hardware ID):</div>
            <div class="hwid-box">
                <span id="hwid-text">{$hwid}</span>
                <button class="btn-copy" onclick="copyHwid()">نسخ البصمة</button>
            </div>

            <div style="font-size: 13px; color: #94a3b8; margin-bottom: 6px; font-weight: 600;">أدخل كود رخصة التفعيل المستلمة:</div>
            <textarea id="license-content" placeholder="قم بلصق كود الرخصة بالكامل هنا..."></textarea>

            <button class="btn-action" id="btn-manual-submit" onclick="submitManualActivation()">تفعيل وتشغيل النظام</button>
        </div>

        <div id="alert-msg" class="alert"></div>

        <div style="margin-top:22px; border-top:1px solid rgba(255,255,255,0.06); padding-top:14px; display:flex; justify-content:center;">
            <button onclick="resetLicense()" style="background:transparent; border:none; color:#64748b; font-size:12px; cursor:pointer; font-family:'Cairo', sans-serif; display:flex; align-items:center; gap:6px; transition:color 0.2s;" onmouseover="this.style.color='#ef4444'" onmouseout="this.style.color='#64748b'">
                <span>🗑️</span>
                <span>إعادة ضبط وحذف ملف الترخيص (Reset License)</span>
            </button>
        </div>
    </div>

    <script>
        function resetLicense() {
            if (!confirm("هل أنت متأكد من رغبتك في حذف ملف الترخيص وإعادة ضبط النظام؟")) {
                return;
            }
            fetch('/api/license/reset', { method: 'POST', headers: { 'Accept': 'application/json' } })
                .then(res => res.json().then(data => {
                    showAlert(data.message || "تم حذف ملف الترخيص بنجاح.", true);
                    setTimeout(() => location.reload(), 1000);
                }))
                .catch(() => {
                    window.location.href = '/license/reset';
                });
        }

        function switchTab(type) {
            document.querySelectorAll(".tab-btn").forEach(btn => btn.classList.remove("active"));
            document.querySelectorAll(".tab-content").forEach(tab => tab.classList.remove("active"));
            document.getElementById("alert-msg").style.display = "none";

            if (type === 'wa') {
                document.querySelectorAll(".tab-btn")[0].classList.add("active");
                document.getElementById("tab-wa").classList.add("active");
            } else {
                document.querySelectorAll(".tab-btn")[1].classList.add("active");
                document.getElementById("tab-manual").classList.add("active");
            }
        }

        function showAlert(msg, isSuccess = false) {
            var alertBox = document.getElementById("alert-msg");
            alertBox.className = isSuccess ? "alert alert-success" : "alert alert-error";
            alertBox.innerText = (isSuccess ? "✅ " : "❌ ") + msg;
            alertBox.style.display = "block";
        }

        function copyHwid() {
            var hwidText = document.getElementById("hwid-text").innerText;
            navigator.clipboard.writeText(hwidText).then(function() {
                var btn = document.querySelector(".btn-copy");
                btn.innerText = "تم النسخ!";
                btn.style.background = "#10b981";
                setTimeout(function() {
                    btn.innerText = "نسخ البصمة";
                    btn.style.background = "#38bdf8";
                }, 2000);
            });
        }

        // 1. طلب رمز OTP عبر الواتساب
        function requestOtp() {
            var phone = document.getElementById("wa-phone-input").value.trim();
            var btn = document.getElementById("btn-request-otp");

            if (!phone) {
                showAlert("يرجى إدخال رقم هاتفك أولاً.");
                return;
            }

            btn.disabled = true;
            btn.innerText = "جاري إرسال الرمز للواتساب...";
            document.getElementById("alert-msg").style.display = "none";

            fetch('{$requestOtpUrl}', {
                method: "POST",
                headers: { "Content-Type": "application/json", "Accept": "application/json" },
                body: JSON.stringify({ phone_number: phone })
            })
            .then(res => res.json().then(data => {
                btn.disabled = false;
                btn.innerText = "📲 إرسال كود التفعيل إلى الواتساب";

                if (res.ok && data.status === 'success') {
                    document.getElementById("wa-step-phone").style.display = "none";
                    document.getElementById("wa-step-otp").style.display = "block";
                    showAlert("تم إرسال كود التفعيل بنجاح! يرجى فحص رسائل الواتساب الخاصة بك.", true);
                    document.getElementById("wa-otp-input").focus();
                } else {
                    showAlert(data.message || "فشل إرسال كود التفعيل عبر الواتساب.");
                }
            }))
            .catch(err => {
                btn.disabled = false;
                btn.innerText = "📲 إرسال كود التفعيل إلى الواتساب";
                showAlert("حدث خطأ أثناء الاتصال بالخادم. يرجى التأكد من توفر اتصال بالإنترنت.");
            });
        }

        // 2. التحقق من كود الـ OTP وتفعيل النظام
        function verifyOtp() {
            var phone = document.getElementById("wa-phone-input").value.trim();
            var otp = document.getElementById("wa-otp-input").value.trim();
            var btn = document.getElementById("btn-verify-otp");

            if (!otp || otp.length < 4) {
                showAlert("يرجى إدخال كود التحقق (OTP) بشكل صحيح.");
                return;
            }

            btn.disabled = true;
            btn.innerText = "جاري التحقق وتفعيل النظام...";
            document.getElementById("alert-msg").style.display = "none";

            fetch('{$verifyOtpUrl}', {
                method: "POST",
                headers: { "Content-Type": "application/json", "Accept": "application/json" },
                body: JSON.stringify({ phone_number: phone, otp: otp })
            })
            .then(res => res.json().then(data => {
                if (res.ok && data.status === 'success') {
                    showAlert("🎉 تم تفعيل ترخيص النظام بنجاح! جاري تشغيل البرنامج...", true);
                    setTimeout(function() {
                        window.location.reload();
                    }, 1800);
                } else {
                    btn.disabled = false;
                    btn.innerText = "✅ تأكيد وتفعيل النظام";
                    showAlert(data.message || "كود التحقق غير صحيح أو انتهت صلاحيته.");
                }
            }))
            .catch(err => {
                btn.disabled = false;
                btn.innerText = "✅ تأكيد وتفعيل النظام";
                showAlert("تعذر الاتصال بالخادم للتحقق من الكود.");
            });
        }

        function resetWaStep() {
            document.getElementById("wa-step-phone").style.display = "block";
            document.getElementById("wa-step-otp").style.display = "none";
            document.getElementById("alert-msg").style.display = "none";
            document.getElementById("wa-otp-input").value = "";
        }

        // 3. التفعيل اليدوي (أوفلاين)
        function submitManualActivation() {
            var content = document.getElementById("license-content").value.trim();
            var btn = document.getElementById("btn-manual-submit");

            if (!content) {
                showAlert("يرجى لصق كود رخصة التفعيل أولاً.");
                return;
            }

            btn.disabled = true;
            btn.innerText = "جاري التحقق من التفعيل...";
            document.getElementById("alert-msg").style.display = "none";

            fetch('{$activateUrl}', {
                method: "POST",
                headers: { "Content-Type": "application/json", "Accept": "application/json" },
                body: JSON.stringify({ license_content: content })
            })
            .then(res => res.json().then(data => {
                if (res.ok) {
                    showAlert("✅ تم تفعيل ترخيص النظام بنجاح! جاري تشغيل البرنامج...", true);
                    setTimeout(function() {
                        window.location.reload();
                    }, 2000);
                } else {
                    btn.disabled = false;
                    btn.innerText = "تفعيل وتشغيل النظام";
                    showAlert(data.message || "فشلت عملية التفعيل. تأكد من صحة كود الرخصة.");
                }
            }))
            .catch(err => {
                btn.disabled = false;
                btn.innerText = "تفعيل وتشغيل النظام";
                showAlert("حدث خطأ أثناء الاتصال بالخادم الداخلي للبرنامج.");
            });
        }
    </script>
</body>
</html>
HTML;
    }
}
