<?php

namespace App\Services;

use App\Licensing\LicensingService;
use App\Mail\UserOtpMail;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class AdminOtpService
{
    /**
     * توليد وإرسال رمز التحقق إلى القنوات المحددة في الترخيص (واتساب / بريد إلكتروني / كلاهما)
     */
    public static function generateAndSend($user, ?string $ip = null, bool $force = false): array
    {
        if (!$user) {
            return ['success' => false, 'reason' => 'no_user'];
        }

        $check = LicensingService::check();
        $adminOtpEnabled = $check['valid'] && ($check['license']['admin_otp_enabled'] ?? false);

        if (!$adminOtpEnabled) {
            return ['success' => false, 'reason' => 'disabled'];
        }

        $licenseChannel = $check['license']['admin_otp_channel'] ?? 'both';

        // إذا كان الترخيص مقتصراً على تطبيق المصادقة TOTP فقط، لا حاجة لإرسال OTP
        if ($licenseChannel === 'totp') {
            return ['success' => false, 'reason' => 'totp_only'];
        }

        $sessionOtp = session('admin_otp');
        $expires = session('admin_otp_expires');

        // إذا لم يكن الإرسال إجبارياً، وكان الرمز الحالي ما زال صالحاً وتم إرساله، نتجنب التكرار الزائد
        if (!$force && $sessionOtp && $expires && now()->isBefore($expires) && session('admin_otp_dispatched_at')) {
            return ['success' => true, 'cached' => true, 'channels' => []];
        }

        $hasPhone = !empty($user->phone);
        $hasEmail = !empty($user->email);

        if (!$hasPhone && !$hasEmail) {
            Log::warning("AdminOtpService: User {$user->id} has neither phone nor email.");
            return ['success' => false, 'reason' => 'no_contact_info'];
        }

        // تحديد القنوات الفعلية للإرسال
        if ($licenseChannel === 'whatsapp' && $hasPhone) {
            $channel = 'whatsapp';
        } elseif ($licenseChannel === 'email' && $hasEmail) {
            $channel = 'email';
        } elseif (in_array($licenseChannel, ['both', 'all'])) {
            $channel = ($hasPhone && $hasEmail) ? 'both' : ($hasPhone ? 'whatsapp' : ($hasEmail ? 'email' : 'none'));
        } else {
            $channel = $hasPhone ? 'whatsapp' : ($hasEmail ? 'email' : 'none');
        }

        if ($channel === 'none') {
            Log::warning("AdminOtpService: License requires {$licenseChannel}, but user missing required phone/email.");
            return ['success' => false, 'reason' => 'channel_mismatch'];
        }

        // توليد رمز جديد 6 أرقام وصلاحية 5 دقائق
        $otp = mt_rand(100000, 999999);
        $expiresAt = now()->addMinutes(5);

        session([
            'admin_otp' => $otp,
            'admin_otp_expires' => $expiresAt,
            'admin_otp_channel' => $channel,
            'admin_otp_dispatched_at' => now()->timestamp,
        ]);

        Log::info("AdminOtpService: Generated OTP for user={$user->email}, phone={$user->phone}, channel={$channel}, force=" . ($force ? 'yes' : 'no'));

        $sentChannels = [];
        $clientIp = $ip ?: request()->ip();

        // 1. الإرسال عبر الواتساب
        if (in_array($channel, ['whatsapp', 'both']) && $hasPhone) {
            try {
                $response = Http::timeout(12)->post('http://127.0.0.1:3333/send-otp', [
                    'phone'   => $user->phone,
                    'otp'     => $otp,
                    'project' => 'نظام إدارة الطاقة والإيراد',
                ]);

                if ($response->successful()) {
                    $sentChannels[] = 'الواتساب';
                    Log::info("AdminOtpService: WhatsApp OTP sent successfully to {$user->phone}");
                } else {
                    Log::warning('AdminOtpService: WhatsApp response: ' . $response->body());
                }
            } catch (\Exception $e) {
                Log::error('AdminOtpService: WhatsApp send failed: ' . $e->getMessage());
            }
        }

        // 2. الإرسال عبر البريد الإلكتروني
        if (in_array($channel, ['email', 'both']) && $hasEmail) {
            try {
                Mail::to($user->email)->send(
                    new UserOtpMail(
                        $otp,
                        $clientIp,
                        $user->name ?? $user->username ?? 'المسؤول',
                        'نظام إدارة الطاقة والإيراد'
                    )
                );
                $sentChannels[] = 'البريد الإلكتروني';
                Log::info("AdminOtpService: Email OTP sent successfully to {$user->email}");
            } catch (\Exception $e) {
                Log::error('AdminOtpService: Email send failed: ' . $e->getMessage());
            }
        }

        return [
            'success' => !empty($sentChannels),
            'channels' => $sentChannels,
            'otp' => $otp,
        ];
    }
}
