<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // ------------------ حماية التراخيص عند إقلاع الخدمات ------------------
        if (!app()->runningInConsole()) {
            $check = \App\Licensing\LicensingService::check();
            if (!$check['valid']) {
                $currentPath = trim(request()->path(), '/');
                $excludedPaths = [
                    'api/license',
                    'license/reset',
                    'license/deactivate',
                    'intercom/api/license',
                    'intercomsaas/api/license',
                    'intercom_saas/api/license',
                    'api/health/uploads',
                    'activate',
                ];

                $isExcluded = false;
                foreach ($excludedPaths as $path) {
                    if ($currentPath === $path || str_starts_with($currentPath, $path)) {
                        $isExcluded = true;
                        break;
                    }
                }

                if (!$isExcluded) {
                    if (request()->is('api/*') || request()->is('*/api/*') || request()->expectsJson()) {
                        abort(response()->json([
                            'status' => 'license_error',
                            'reason' => $check['reason'],
                            'hwid' => $check['hwid'] ?? \App\Licensing\HardwareFingerprint::get(),
                            'message' => $check['message'],
                            'activation_url' => url('/activate')
                        ], 402));
                    }

                    $hwid = $check['hwid'] ?? \App\Licensing\HardwareFingerprint::get();
                    $message = $check['message'];
                    $activateUrl = url('api/license/activate');

                    $html = \App\Licensing\VerifyLicenseMiddleware::getActivationPageHtml($hwid, $message, $activateUrl);
                    abort(response($html, 402)->header('Content-Type', 'text/html; charset=utf-8'));
                }
            }
        }
        // ---------------------------------------------------------------------

        if (!class_exists('Report')) {
            class_alias(\App\Models\Report::class, 'Report');
        }
    }
}
