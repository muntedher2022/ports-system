<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use PragmaRX\Google2FA\Google2FA;

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
    public string $method = 'whatsapp'; // 'whatsapp' or 'totp'
    public bool $hasTotpSetup = false;
    public string $licenseChannel = 'both';

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

        if (session('admin_otp_verified') === true || session('totp_verified') === true) {
            return redirect()->to(filament()->getPanel('admin')->getUrl());
        }

        $user = auth()->user();
        $this->hasTotpSetup = $user && method_exists($user, 'hasTotpSetup') && $user->hasTotpSetup();

        $check = \App\Licensing\LicensingService::check();
        $adminOtpEnabled = $check['valid'] && ($check['license']['admin_otp_enabled'] ?? false);
        if (!$adminOtpEnabled) {
            return redirect()->to(filament()->getPanel('admin')->getUrl());
        }

        $this->licenseChannel = $check['license']['admin_otp_channel'] ?? 'both';

        // إذا كانت القناة حصراً totp، نجعل الطريقة الافتراضية تطبيق المصادقة
        if (!$this->hasTotpSetup) {
            $this->method = 'whatsapp';
        } elseif ($this->licenseChannel === 'totp') {
            $this->method = 'totp';
        }

        $expires = session('admin_otp_expires');
        $sessionOtp = session('admin_otp');

        // إذا لم يكن هناك رمز نشط مرسل في هذه الجلسة، نولده ونرسله فوراً (حماية في حال دخل المستخدم مباشرة إلى الصفحة)
        if ($this->licenseChannel !== 'totp') {
            if (!$sessionOtp || !session('admin_otp_dispatched_at') || now()->isAfter($expires)) {
                \App\Services\AdminOtpService::generateAndSend($user, request()->ip(), force: true);
                $expires = session('admin_otp_expires');
            }
        }

        if ($expires) {
            $diff = now()->diffInSeconds($expires, false);
            $this->countdown = $diff > 0 ? (int)$diff : 0;
        }
    }

    public function setMethod(string $method): void
    {
        if ($method === 'totp' && !$this->hasTotpSetup) {
            Notification::make()
                ->title('غير متوفر')
                ->body('لم يتم ربط تطبيق المصادقة بحسابك مسبقاً. يرجى تسجيل الدخول برمز الواتساب / البريد الإلكتروني أولاً.')
                ->warning()
                ->send();
            return;
        }

        if (in_array($method, ['whatsapp', 'totp'])) {
            $this->method = $method;
            $this->code = '';
        }
    }

    public function verify()
    {
        $code = trim((string)$this->code);
        if (empty($code)) {
            Notification::make()
                ->title('حقل مطلوب')
                ->body('يرجى إدخال رمز التحقق المكون من 6 أرقام.')
                ->danger()
                ->send();
            return;
        }

        $user = auth()->user();

        // 1. التحقق عبر تطبيق المصادقة (TOTP)
        if ($this->method === 'totp') {
            if (!$user || !method_exists($user, 'hasTotpSetup') || !$user->hasTotpSetup()) {
                Notification::make()
                    ->title('تطبيق المصادقة غير مربوط بعد')
                    ->body('لم تقم بربط حسابك بتطبيق المصادقة بعد. يرجى مسح رمز QR أولاً أو التحقق عبر الواتساب.')
                    ->warning()
                    ->send();
                return;
            }

            try {
                $google2fa = new Google2FA();
                $secret = decrypt($user->two_factor_secret);
                // نافذة 8 خطوات = ±4 دقائق تسامح
                $valid = $google2fa->verifyKey($secret, $code, 8);
            } catch (\Exception $e) {
                $valid = false;
            }

            if ($valid) {
                session([
                    'admin_otp_verified' => true,
                    'totp_verified' => true,
                ]);

                Notification::make()
                    ->title('تم التحقق بنجاح')
                    ->body('مرحباً بك في لوحة الإدارة.')
                    ->success()
                    ->send();

                return redirect()->to(filament()->getPanel('admin')->getUrl());
            }

            Notification::make()
                ->title('رمز تطبيق المصادقة غير صحيح')
                ->body('الرمز الذي أدخلته غير صحيح أو انتهت صلاحيته (30 ثانية). يرجى التأكد من التطبيق والمحاولة مجدداً.')
                ->danger()
                ->send();
            return;
        }

        // 2. التحقق عبر الواتساب / البريد الإلكتروني (OTP)
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

        if ($code === (string)$sessionOtp) {
            session([
                'admin_otp_verified' => true,
                'totp_verified' => true,
            ]);

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

        $result = \App\Services\AdminOtpService::generateAndSend($user, request()->ip(), force: true);

        $this->countdown = 300;
        $this->code = '';

        if (!empty($result['channels'])) {
            Notification::make()
                ->title('تم إرسال الرمز بنجاح')
                ->body('تم إرسال رمز تحقق جديد إلى ' . implode(' و ', $result['channels']) . '.')
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
