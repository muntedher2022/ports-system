<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerifyTotpMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!auth()->check()) {
            return $next($request);
        }

        $user = auth()->user();

        // فحص ترخيص النظام إن كان يحدد قناة التحقق
        $check = \App\Licensing\LicensingService::check();
        $adminOtpEnabled = $check['valid'] && ($check['license']['admin_otp_enabled'] ?? false);
        $licenseChannel = $check['license']['admin_otp_channel'] ?? 'both';

        // TOTP لا يُفعَّل إلا إذا كانت القناة في الترخيص تدعمه (totp أو all)
        $totpChannelActive = in_array($licenseChannel, ['totp', 'all']);

        // إذا كان الترخيص يُلزم الـ TOTP للمشرف، والمستخدم مدير
        $isLicenseEnforcedTotp = $adminOtpEnabled && $totpChannelActive && method_exists($user, 'isAdmin') && $user->isAdmin();

        // TOTP على مستوى المستخدم: يُشترط أيضاً أن تكون القناة تدعم TOTP
        $isUserEnforcedTotp = $adminOtpEnabled && $totpChannelActive
            && method_exists($user, 'isTotpRequired') && $user->isTotpRequired();

        // هل المستخدم ملزم بـ TOTP
        $mustVerifyTotp = $isUserEnforcedTotp || $isLicenseEnforcedTotp;

        if (!$mustVerifyTotp) {
            return $next($request);
        }

        $routeName = $request->route()?->getName() ?? '';

        $excludedRoutes = [
            'filament.admin.pages.verify-otp',
            'filament.admin.pages.verify-totp',
            'filament.admin.pages.setup-totp',
            'filament.admin.auth.logout',
        ];

        $isLivewireUpdate = str_contains($request->path(), 'livewire/update');
        $isExcludedRoute  = in_array($routeName, $excludedRoutes);

        if ($isExcludedRoute || $isLivewireUpdate) {
            return $next($request);
        }

        // إذا تم التحقق مسبقاً في هذه الجلسة بأي من الطريقتين (OTP أو TOTP)
        if (session('totp_verified') === true || session('admin_otp_verified') === true) {
            return $next($request);
        }

        // إذا كانت القناة all، نترك التوجيه لـ verify-otp حيث يختار المستخدم بين الواتساب وتطبيق المصادقة
        if ($licenseChannel === 'all') {
            return $next($request);
        }

        // الحالة 1: المستخدم ملزم بتطبيق المصادقة ولكن لم يقم بالربط بعد -> يُوجّه لإعداد التطبيق
        if (!$user->hasTotpSetup()) {
            return redirect()->route('filament.admin.pages.setup-totp');
        }

        // الحالة 2: المستخدم ملزم وقام بالربط ولكن لم يتحقق في هذه الجلسة -> يُوجّه للتحقق
        if (session('totp_verified') !== true) {
            return redirect()->route('filament.admin.pages.verify-totp');
        }

        return $next($request);
    }
}
