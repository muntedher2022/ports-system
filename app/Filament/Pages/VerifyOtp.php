<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class VerifyOtp extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $title = 'التحقق الثنائي للمشرف';
    protected static ?string $navigationLabel = 'التحقق الثنائي';
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-shield-check';
    protected static bool $shouldRegisterNavigation = false;

    protected string $view = 'filament.pages.verify-otp';

    protected static string $layout = 'filament-panels::components.layout.simple';

    public ?string $code = '';
    public int $countdown = 300; // 5 minutes

    public function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                TextInput::make('code')
                    ->required()
                    ->numeric()
                    ->maxLength(6)
                    ->hiddenLabel()
                    ->extraInputAttributes([
                        'style' => 'text-align: center; font-size: 24px; font-family: monospace; letter-spacing: 6px;',
                        'placeholder' => '******'
                    ])
            ]);
    }

    public function mount()
    {
        if (!auth()->check()) {
            return redirect()->to(filament()->getPanel('admin')->getLoginUrl());
        }

        if (session('admin_otp_verified') === true) {
            return redirect()->to(filament()->getPanel('admin')->getUrl());
        }

        $check = \App\Licensing\LicensingService::check();
        $adminOtpEnabled = $check['valid'] && ($check['license']['admin_otp_enabled'] ?? false);
        if (!$adminOtpEnabled) {
            return redirect()->to(filament()->getPanel('admin')->getUrl());
        }

        $expires = session('admin_otp_expires');
        if ($expires) {
            $diff = now()->diffInSeconds($expires, false);
            $this->countdown = $diff > 0 ? (int)$diff : 0;
        }
    }

    public function verify()
    {
        $sessionOtp = session('admin_otp');
        $expires = session('admin_otp_expires');

        if (!$sessionOtp || now()->isAfter($expires)) {
            Notification::make()
                ->title('انتهت صلاحية الرمز')
                ->body('انتهت صلاحية رمز التحقق. يرجى طلب رمز جديد.')
                ->danger()
                ->send();
            return;
        }

        if (trim($this->code) === (string)$sessionOtp) {
            session(['admin_otp_verified' => true]);

            Notification::make()
                ->title('تم التحقق بنجاح')
                ->body('مرحباً بك في لوحة الإدارة.')
                ->success()
                ->send();

            return redirect()->to(filament()->getPanel('admin')->getUrl());
        }

        Notification::make()
            ->title('رمز خاطئ')
            ->body('الرمز الذي أدخلته غير صحيح. يرجى المحاولة مرة أخرى.')
            ->danger()
            ->send();
    }

    public function resend()
    {
        $user = auth()->user();
        if (!$user || (empty($user->email) && empty($user->phone))) {
            Notification::make()
                ->title('خطأ في إرسال الرمز')
                ->body('لا يوجد رقم هاتف أو بريد إلكتروني مسجل في حسابك.')
                ->danger()
                ->send();
            return;
        }

        $otp = mt_rand(100000, 999999);
        $expires = now()->addMinutes(5);

        $hasPhone = !empty($user->phone);
        $hasEmail = !empty($user->email);

        $check = \App\Licensing\LicensingService::check();
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
            'admin_otp_expires' => $expires,
            'admin_otp_channel' => $channel,
        ]);

        $this->countdown = 300;
        $this->code = '';

        $sentChannels = [];

        // 1. الواتساب
        if (in_array($channel, ['whatsapp', 'both']) && $hasPhone) {
            try {
                $response = \Illuminate\Support\Facades\Http::timeout(5)->post('http://127.0.0.1:3333/send-otp', [
                    'phone'   => $user->phone,
                    'otp'     => $otp,
                    'project' => config('app.name', 'نظام الموانئ') . " - {$user->name}",
                ]);
                if ($response->successful()) {
                    $sentChannels[] = 'الواتساب';
                }
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error('Ports Resend WhatsApp OTP failed: ' . $e->getMessage());
            }
        }

        // 2. البريد الإلكتروني
        if (in_array($channel, ['email', 'both']) && $hasEmail) {
            try {
                \Illuminate\Support\Facades\Mail::raw("رمز التحقق الثنائي الجديد الخاص بك لنظام " . config('app.name', 'نظام الموانئ') . " هو: {$otp}", function ($message) use ($user) {
                    $message->to($user->email)
                            ->subject('رمز التحقق الثنائي - ' . config('app.name', 'نظام الموانئ'));
                });
                $sentChannels[] = 'البريد الإلكتروني';
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error('Ports Resend Email OTP failed: ' . $e->getMessage());
            }
        }

        if (!empty($sentChannels)) {
            Notification::make()
                ->title('تم إرسال الرمز بنجاح')
                ->body('تم إرسال رمز تحقق جديد إلى ' . implode(' و ', $sentChannels) . '.')
                ->success()
                ->send();
        } else {
            Notification::make()
                ->title('خطأ في إرسال الرمز')
                ->body('تعذر إرسال الرمز حالياً، يرجى مراجعة مسؤول النظام.')
                ->danger()
                ->send();
        }
    }
}
