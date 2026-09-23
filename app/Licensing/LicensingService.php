<?php

namespace App\Licensing;

use Exception;

class LicensingService
{
    // ملف حفظ الرخصة محلياً
    private static function getLicensePath(): string
    {
        return storage_path('app/license.lic');
    }

    // ملف حفظ وقت آخر تشغيل لمنع التلاعب بساعة الكمبيوتر
    private static function getLastRunPath(): string
    {
        return storage_path('app/.last_run');
    }

    // ملف حفظ وقت آخر تحقق أونلاين
    private static function getLastOnlinePath(): string
    {
        return storage_path('app/.last_online');
    }

    // ذاكرة التخزين المؤقت للطلب الحالي لتجنب إعادة التحقق في نفس الـ Request
    private static ?array $cachedResult = null;

    /**
     * المفتاح العام (RSA Public Key) الخاص بك.
     * سيتم استخدامه للتحقق من أن ملف الرخصة قد تم توقيعه بواسطة مفتاحك الخاص فقط.
     * عند تشفير هذا الملف بـ ionCube لن يستطيع أحد رؤية أو استبدال هذا المفتاح.
     */
    private static string $publicKey = <<<EOD
-----BEGIN PUBLIC KEY-----
MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAmlD++qeXoEUlZnuLKWcG
RjZnQY+a4PP9iLFzYZeoBkytdIgJrZLZZL2zpcAREH0h8SUwYUpqS7BC3dPvmoRF
kFpdWQ/pDsHlMSuKONGyweRoFQCmy3/BCj0Az2GSdTKG1e8iBnPBxOwqRFE7P0eH
6Skw3zGO/skbat2hszBqY5hg42UlRUznlMf/lOdkrpjLv+xfgpnl10QG2wAezIgh
nJCRLKsN0Sn/bqyBQMU2LxjlkUqxJMP9q31kJyGzAb9gS4DTEIpcVIp1sE85/l6N
NAOOt8tHAlKv3/6o8JRWCsNY98+EcjgeHtPkoOEslVV172M3NN/ntr6XQh1zrR/C
wQIDAQAB
-----END PUBLIC KEY-----
EOD;

    /**
     * تحديث المفتاح العام للتحقق.
     */
    public static function setPublicKey(string $key): void
    {
        self::$publicKey = $key;
    }

    /**
     * فحص ترخيص التطبيق.
     *
     * @return array يحتوي على حالة الترخيص والسبب وبيانات الرخصة إن وجدت
     */
    public static function check(): array
    {
        if (self::$cachedResult !== null) {
            return self::$cachedResult;
        }

        $licenseFile = self::getLicensePath();

        // 1. التأكد من وجود ملف الترخيص
        if (!file_exists($licenseFile)) {
            return self::$cachedResult = [
                'valid' => false,
                'reason' => 'missing_license',
                'hwid' => HardwareFingerprint::get(),
                'message' => 'ملف الترخيص غير موجود. يرجى تفعيل البرنامج.'
            ];
        }

        // 2. قراءة وتحليل ملف الترخيص
        $content = file_get_contents($licenseFile);
        $payload = json_decode($content, true);

        if (!$payload || !isset($payload['data']) || !isset($payload['signature'])) {
            return self::$cachedResult = [
                'valid' => false,
                'reason' => 'invalid_format',
                'hwid' => HardwareFingerprint::get(),
                'message' => 'ملف الترخيص تالف أو غير صالح.'
            ];
        }

        // 3. التحقق من التوقيع الرقمي (RSA Signature Verification)
        $dataJson = json_encode($payload['data']);
        $signature = base64_decode($payload['signature']);
        $verify = @openssl_verify($dataJson, $signature, self::$publicKey, OPENSSL_ALGO_SHA256);

        if ($verify !== 1) {
            return self::$cachedResult = [
                'valid' => false,
                'reason' => 'invalid_signature',
                'hwid' => HardwareFingerprint::get(),
                'message' => 'التوقيع الرقمي للرخصة غير صالح (تم التلاعب بالملف).'
            ];
        }

        $data = $payload['data'];

        // 4. التحقق من نوع الترخيص ومطابقة النطاق أو العتاد
        if ($data['type'] === 'local') {
            // تشغيل محلي: مطابقة بصمة العتاد (Hardware ID)
            $currentHwid = HardwareFingerprint::get();
            if ($currentHwid !== $data['hwid']) {
                return self::$cachedResult = [
                    'valid' => false,
                    'reason' => 'hwid_mismatch',
                    'hwid' => $currentHwid,
                    'message' => 'ملف الترخيص هذا مخصص لجهاز كمبيوتر آخر.'
                ];
            }
        } elseif ($data['type'] === 'hosted') {
            // استضافة ويب: مطابقة اسم النطاق (Domain)
            $currentDomain = (app()->bound('request') && request()) ? strtolower(request()->getHost()) : 'localhost';
            $licensedDomain = strtolower($data['domain']);

            // إزالة www. للمقارنة الدقيقة
            $currentDomainClean = preg_replace('/^www\./', '', $currentDomain);
            $licensedDomainClean = preg_replace('/^www\./', '', $licensedDomain);

            if ($currentDomainClean !== $licensedDomainClean && $currentDomainClean !== 'localhost' && $currentDomainClean !== '127.0.0.1') {
                return self::$cachedResult = [
                    'valid' => false,
                    'reason' => 'domain_mismatch',
                    'message' => "هذا الترخيص مخصص للنطاق ($licensedDomain) ولا يعمل على النطاق الحالي ($currentDomain)."
                ];
            }
        }

        // 5. التحقق من تاريخ انتهاء الصلاحية
        if (!empty($data['expires_at'])) {
            $expiryTimestamp = strtotime($data['expires_at']);
            $currentTimestamp = time();

            if ($currentTimestamp > $expiryTimestamp) {
                return self::$cachedResult = [
                    'valid' => false,
                    'reason' => 'expired',
                    'message' => 'انتهت صلاحية هذا الترخيص في: ' . $data['expires_at']
                ];
            }
        }

        // 6. منع التلاعب بالوقت والتاريخ (Time Tampering Prevention)
        $timeCheck = self::checkTimeTampering();
        if (!$timeCheck['ok']) {
            return self::$cachedResult = [
                'valid' => false,
                'reason' => 'time_tampered',
                'message' => 'تم اكتشاف تلاعب في ساعة النظام. يرجى ضبط الوقت والتاريخ الحاليين.'
            ];
        }

        // 7. التحقق الدوري أونلاين من سيرفر التراخيص
        $onlineCheck = self::checkOnlineLicense($data);
        if (!$onlineCheck['valid']) {
            return self::$cachedResult = [
                'valid' => false,
                'reason' => $onlineCheck['reason'],
                'message' => $onlineCheck['message']
            ];
        }

        // حفظ الرخصة والتحقق بنجاح
        return self::$cachedResult = [
            'valid' => true,
            'reason' => null,
            'license' => $data
        ];
    }

    /**
     * التحقق من عدم إرجاع تاريخ الساعة للخلف لتجنب انتهاء الرخصة.
     */
    private static function checkTimeTampering(): array
    {
        $lastRunFile = self::getLastRunPath();
        $currentTime = time();

        if (file_exists($lastRunFile)) {
            $content = file_get_contents($lastRunFile);
            $lastRunData = json_decode($content, true);

            if ($lastRunData && isset($lastRunData['timestamp'])) {
                $lastTimestamp = (int)$lastRunData['timestamp'];

                // إذا كان الوقت الحالي أصغر من آخر وقت تشغيل مسجل، فهناك تلاعب بالساعة
                if ($currentTime < $lastTimestamp - 600) { // سماح بـ 10 دقائق فروقات بسيطة
                    return ['ok' => false];
                }
            }
        }

        // تحديث ملف آخر تشغيل بالوقت الحالي
        try {
            $secureData = json_encode([
                'timestamp' => $currentTime,
                'hash' => md5($currentTime . 'salt_for_security')
            ]);
            file_put_contents($lastRunFile, $secureData);
        } catch (Exception $e) {
            // تجاهل مشاكل الكتابة للمجلدات المحمية
        }

        return ['ok' => true];
    }

    /**
     * التحقق من حالة الرخصة دورياً من السيرفر أونلاين.
     */
    private static function checkOnlineLicense(array $data): array
    {
        $lastOnlineFile = self::getLastOnlinePath();
        $currentTime = time();

        // فحص كل 24 ساعة (86400 ثانية)
        $checkInterval = 86400;

        if (file_exists($lastOnlineFile)) {
            $content = @file_get_contents($lastOnlineFile);
            $onlineData = json_decode($content, true);

            if ($onlineData && isset($onlineData['timestamp']) && isset($onlineData['hash'])) {
                $lastCheckTime = (int)$onlineData['timestamp'];
                $expectedHash = md5($lastCheckTime . 'salt_for_online_security');

                // التأكد من عدم التلاعب بالملف وعدم تقديم الساعة للمستقبل
                if ($onlineData['hash'] === $expectedHash && $currentTime >= $lastCheckTime && ($currentTime - $lastCheckTime < $checkInterval)) {
                    return ['valid' => true];
                }
            }
        }

        // تحضير رابط التحقق
        $serverUrl = env('LICENSING_SERVER_URL', 'https://licensing-manager.test');
        $licenseKey = $data['license_key'] ?? '';
        $hwid = $data['hwid'] ?? '';
        $domain = (app()->bound('request') && request()) ? request()->getHost() : 'localhost';

        $apiUrl = rtrim($serverUrl, '/') . '/api/license/verify?' . http_build_query([
            'license_key' => $licenseKey,
            'hwid' => $hwid,
            'domain' => $domain
        ]);

        // إجراء طلب HTTP في الخلفية بمهلة زمنية قصيرة (3 ثواني) لتجنب بطء التصفح
        $options = [
            'http' => [
                'method' => 'GET',
                'timeout' => 3, // مهلة 3 ثواني كحد أقصى
                'ignore_errors' => true // قراءة أكواد خطأ HTTP مثل 403 و 404 بدون فشل الطلب
            ],
            'ssl' => [
                'verify_peer' => false,
                'verify_peer_name' => false,
            ]
        ];

        $context = stream_context_create($options);
        $response = @file_get_contents($apiUrl, false, $context);

        // إذا فشل الاتصال بالسيرفر (لا يوجد إنترنت أو السيرفر متوقف)
        if ($response === false) {
            // فترة سماح: نحدث وقت الفحص لكي لا نحاول مجدداً في كل طلب ونسبب بطئاً
            $secureData = json_encode([
                'timestamp' => $currentTime,
                'hash' => md5($currentTime . 'salt_for_online_security')
            ]);
            @file_put_contents($lastOnlineFile, $secureData);
            return ['valid' => true]; // نسمح للمستخدم بالدخول أونلاين مؤقتاً (Grace Period)
        }

        $httpCode = 200;
        if (isset($http_response_header)) {
            foreach ($http_response_header as $header) {
                if (preg_match('/^HTTP\/\d+\.\d+\s+(\d+)/i', $header, $matches)) {
                    $httpCode = (int)$matches[1];
                    break;
                }
            }
        }

        $result = json_decode($response, true);

        // إذا كان الكود 403 (موقوف/منتهي) أو 404 (محذوف) أو كانت الاستجابة صريحة بالإيقاف أو انتهاء الصلاحية
        if ($httpCode === 403 || $httpCode === 404 || (isset($result['status']) && in_array($result['status'], ['suspended', 'revoked', 'expired']))) {
            // حذف ملف الرخصة والملفات المؤقتة فوراً لإيقاف التفعيل
            @unlink(self::getLicensePath());
            @unlink(self::getLastRunPath());
            @unlink($lastOnlineFile);

            $reason = (isset($result['status']) && $result['status'] === 'expired') ? 'expired' : 'revoked';
            $msg = $result['message'] ?? ($reason === 'expired' ? 'انتهت صلاحية هذا الترخيص.' : 'تم إيقاف أو إلغاء تفعيل هذا الترخيص من قبل المطور.');

            return [
                'valid' => false,
                'reason' => $reason,
                'message' => $msg
            ];
        }

        // إذا كان التفعيل نشطاً وسليماً
        if ($httpCode === 200 && isset($result['status']) && $result['status'] === 'active') {
            // تحديث تاريخ الفحص الأخير بنجاح مع التوقيع لمنع التلاعب
            $secureData = json_encode([
                'timestamp' => $currentTime,
                'hash' => md5($currentTime . 'salt_for_online_security')
            ]);
            @file_put_contents($lastOnlineFile, $secureData);
        }

        return ['valid' => true];
    }

    /**
     * تفعيل التطبيق بحفظ ملف الرخصة المرسل.
     */
    public static function activate(string $licenseContent): bool
    {
        // التحقق المبدئي من صحة التنسيق
        $payload = json_decode($licenseContent, true);
        if (!$payload || !isset($payload['data']) || !isset($payload['signature'])) {
            return false;
        }

        // حفظ الملف
        $licenseFile = self::getLicensePath();
        file_put_contents($licenseFile, $licenseContent);

        // مسح الذاكرة المؤقتة لإعادة الفحص
        self::$cachedResult = null;

        // التحقق من صحته برمجياً بعد الحفظ
        // (سيقوم بالاتصال أونلاين فوراً لعدم وجود ملف .last_online بعد)
        $check = self::check();
        if (!$check['valid']) {
            // إذا كانت الرخصة غير صالحة بعد الحفظ، نقوم بحذف الملف
            @unlink($licenseFile);
            return false;
        }

        // تهيئة ملف التحقق أونلاين بالوقت الحالي عند نجاح التفعيل مع التوقيع
        $secureData = json_encode([
            'timestamp' => time(),
            'hash' => md5(time() . 'salt_for_online_security')
        ]);
        @file_put_contents(self::getLastOnlinePath(), $secureData);

        return true;
    }

    /**
     * إلغاء تفعيل النظام وحذف ملف الترخيص وبيانات القفل ومسح الذاكرة المؤقتة.
     */
    public static function deactivate(): bool
    {
        $licenseFile = self::getLicensePath();
        $lastRunFile = self::getLastRunPath();
        $lastOnlineFile = self::getLastOnlinePath();

        if (file_exists($licenseFile)) {
            @unlink($licenseFile);
        }
        if (file_exists($licenseFile . '.bak')) {
            @unlink($licenseFile . '.bak');
        }
        if (file_exists($lastRunFile)) {
            @unlink($lastRunFile);
        }
        if (file_exists($lastOnlineFile)) {
            @unlink($lastOnlineFile);
        }

        self::$cachedResult = null;

        if (function_exists('cache')) {
            try {
                cache()->flush();
            } catch (Exception $e) {}
        }

        return true;
    }
}
