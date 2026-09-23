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
            // التحقق من تفعيل التوثيق الثنائي في الترخيص
            $check = \App\Licensing\LicensingService::check();
            $adminOtpEnabled = $check['valid'] && ($check['license']['admin_otp_enabled'] ?? false);

            if (!$adminOtpEnabled) {
                return $next($request);
            }

            $routeName = $request->route() ? $request->route()->getName() : '';

            $excludedRoutes = [
                'filament.admin.pages.verify-otp',
                'filament.admin.auth.logout',
            ];

            $isLivewireUpdate = str_contains($request->path(), 'livewire/update');
            $isExcludedRoute = in_array($routeName, $excludedRoutes);

            if ($isExcludedRoute || $isLivewireUpdate) {
                return $next($request);
            }

            // إذا لم يتم التحقق بعد
            if (session('admin_otp_verified') !== true) {
                $sessionOtp = session('admin_otp');
                $expires = session('admin_otp_expires');

                if (!$sessionOtp || now()->isAfter($expires)) {
                    $otp = mt_rand(100000, 999999);
                    $newExpires = now()->addMinutes(5);

                    $user = auth()->user();
                    $hasPhone = $user && !empty($user->phone);
                    $hasEmail = $user && !empty($user->email);

                    $licenseChannel = $check['license']['admin_otp_channel'] ?? 'both';
                    if ($licenseChannel === 'whatsapp' && $hasPhone) {
                        $channel = 'whatsapp';
                    } elseif ($licenseChannel === 'email' && $hasEmail) {
                        $channel = 'email';
                    } elseif ($licenseChannel === 'both') {
                        $channel = ($hasPhone && $hasEmail) ? 'both' : ($hasPhone ? 'whatsapp' : ($hasEmail ? 'email' : 'none'));
                    } else {
                        $channel = $hasPhone ? 'whatsapp' : ($hasEmail ? 'email' : 'none');
                    }

                    session([
                        'admin_otp' => $otp,
                        'admin_otp_expires' => $newExpires,
                        'admin_otp_channel' => $channel,
                    ]);

                    // 1. إرسال كود التحقق مباشرة إلى رقم واتساب الموظف/المسؤول المسجل في حسابه
                    if (in_array($channel, ['whatsapp', 'both']) && $hasPhone) {
                        try {
                            \Illuminate\Support\Facades\Http::timeout(5)->post('http://127.0.0.1:3333/send-otp', [
                                'phone'   => $user->phone,
                                'otp'     => $otp,
                                'project' => 'نظام إدارة الموانئ والطاقة',
                            ]);
                        } catch (\Exception $e) {
                            \Illuminate\Support\Facades\Log::error('Ports WhatsApp OTP failed: ' . $e->getMessage());
                        }
                    }

                    // 2. إرسال كود التحقق إلى البريد الإلكتروني بتنسيق HTML الأنيق
                    if (in_array($channel, ['email', 'both']) && $hasEmail) {
                        try {
                            \Illuminate\Support\Facades\Mail::to($user->email)->send(
                                new \App\Mail\UserOtpMail($otp, $request->ip(), $user->name ?? $user->username ?? 'المسؤول', 'نظام إدارة الموانئ البحرية والطاقة')
                            );
                        } catch (\Exception $e) {
                            \Illuminate\Support\Facades\Log::error('Ports Email OTP failed: ' . $e->getMessage());
                        }
                    }
                }

                // Redirect to the verification page
                return redirect()->route('filament.admin.pages.verify-otp');
            }
        }

        return $next($request);
    }
}
