<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerifyAdminOtp
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (auth()->check()) {
            $user = auth()->user();

            // التحقق من تفعيل التوثيق الثنائي في الترخيص
            $check = \App\Licensing\LicensingService::check();
            $adminOtpEnabled = $check['valid'] && ($check['license']['admin_otp_enabled'] ?? false);

            if (!$adminOtpEnabled) {
                return $next($request);
            }

            $licenseChannel = $check['license']['admin_otp_channel'] ?? 'both';

            // إذا كانت القناة المحددة في الترخيص هي تطبيق المصادقة (TOTP) فقط، نترك التحقق لـ VerifyTotpMiddleware
            if ($licenseChannel === 'totp') {
                return $next($request);
            }

            $routeName = $request->route() ? $request->route()->getName() : '';

            $excludedRoutes = [
                'filament.admin.pages.verify-otp',
                'filament.admin.pages.verify-totp',
                'filament.admin.pages.setup-totp',
                'filament.admin.auth.logout',
            ];

            $isLivewireUpdate = str_contains($request->path(), 'livewire/update');
            $isExcludedRoute = in_array($routeName, $excludedRoutes);

            if ($isExcludedRoute || $isLivewireUpdate) {
                return $next($request);
            }

            // إذا لم يتم التحقق بعد (سواء عبر الواتساب/البريد أو عبر تطبيق المصادقة)
            if (session('admin_otp_verified') !== true && session('totp_verified') !== true) {
                // التأكد من توليد وإرسال الرمز في حال لم يكن مرسلاً بعد أو انتهت صلاحيته
                \App\Services\AdminOtpService::generateAndSend($user, $request->ip(), force: false);

                // Redirect to the verification page
                return redirect()->route('filament.admin.pages.verify-otp');
            }
        }

        return $next($request);
    }
}
