<?php

namespace App\Filament\Pages;

use App\Enums\NavigationGroup;
use Filament\Pages\Page;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use PragmaRX\Google2FA\Google2FA;

class SetupTotp extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $title = 'إعداد تطبيق المصادقة (TOTP)';
    protected static ?string $navigationLabel = 'إعداد المصادقة الثنائية';
    protected static ?int $navigationSort = 3;
    protected static string|\UnitEnum|null $navigationGroup = NavigationGroup::SystemAdmin;

    public static function shouldRegisterNavigation(): bool
    {
        return auth()->check();
    }

    public static function canAccess(): bool
    {
        return auth()->check();
    }

    public static function getNavigationIcon(): string|\BackedEnum|null
    {
        return Heroicon::OutlinedQrCode;
    }

    protected string $view = 'filament.pages.setup-totp';

    public ?string $code = '';
    public string $qrCodeUrl = '';
    public string $secretKey = '';
    public string $encryptedSecret = '';
    public bool $isEnabled = false;

    private function getGoogle2FA(): Google2FA
    {
        return new Google2FA();
    }

    // خاصية computed للمفتاح المعروض للمستخدم
    public function getSecretKeyProperty(): string
    {
        if (!empty($this->secretKey)) {
            return $this->secretKey;
        }
        if (!empty($this->encryptedSecret)) {
            try {
                return decrypt($this->encryptedSecret);
            } catch (\Exception $e) {
                return '';
            }
        }
        return '';
    }

    public function mount(): void
    {
        if (!auth()->check()) {
            redirect()->to(filament()->getPanel('admin')->getLoginUrl());
            return;
        }

        $user = auth()->user();

        $this->isEnabled = method_exists($user, "hasTotpEnabled") ? $user->hasTotpEnabled() : (method_exists($user, "hasTotpSetup") ? $user->hasTotpSetup() : false);

        if (!$this->isEnabled) {
            $google2fa = $this->getGoogle2FA();

            // نربط المفتاح السري بحساب المستخدم في قاعدة البيانات مباشرة لمنع تداخل الجلسات
            if (!empty($user->two_factor_secret)) {
                try {
                    $secret = decrypt($user->two_factor_secret);
                } catch (\Exception $e) {
                    $secret = $google2fa->generateSecretKey();
                    $user->forceFill(['two_factor_secret' => encrypt($secret)])->save();
                }
            } else {
                $secret = $google2fa->generateSecretKey();
                $user->forceFill(['two_factor_secret' => encrypt($secret)])->save();
            }

            $this->secretKey = $secret;
            $this->encryptedSecret = encrypt($secret);

            $this->qrCodeUrl = $google2fa->getQRCodeUrl(
                config('app.name', 'نظام إدارة الطاقة الإنتاجية'),
                $user->email,
                $secret
            );
        }
    }

    public function form(Schema $schema): Schema
    {
        return $schema->schema([
            TextInput::make('code')
                ->label('رمز التحقق من التطبيق')
                ->required()
                ->numeric()
                ->maxLength(6)
                ->minLength(6)
                ->placeholder('أدخل الرمز المكون من 6 أرقام')
                ->extraInputAttributes([
                    'style' => 'text-align: center; font-size: 24px; font-family: monospace; letter-spacing: 8px;',
                ]),
        ]);
    }

    public function enable(): void
    {
        $user = auth()->user();
        $google2fa = $this->getGoogle2FA();

        // استخراج الكود إما من حالة الفورم أو من الخاصية مباشرة
        $formData = [];
        try {
            $formData = $this->form->getState();
        } catch (\Exception $e) {
            // تجاهل خطأ الفاليديشن هنا ونفحصه بالأسفل
        }
        $code = trim((string)($formData['code'] ?? $this->code));

        if (empty($code)) {
            $this->validate(['code' => 'required|digits:6']);
            $code = trim((string)$this->code);
        }

        // جلب المفتاح السري مباشرة من قاعدة بيانات المستخدم
        if (empty($user->two_factor_secret)) {
            Notification::make()->title('خطأ')->body('لا يوجد مفتاح سري مسجل لحسابك، يرجى إعادة تحميل الصفحة.')->danger()->send();
            return;
        }

        try {
            $secret = decrypt($user->two_factor_secret);
        } catch (\Exception $e) {
            Notification::make()->title('خطأ')->body('بيانات المفتاح تالفة، يرجى إعادة تحميل الصفحة.')->danger()->send();
            return;
        }

        $expectedOtp = $google2fa->getCurrentOtp($secret);
        \Illuminate\Support\Facades\Log::info("TOTP verification: user={$user->email}, entered={$code}, expected_now={$expectedOtp}");

        // نافذة 8 خطوات = ±4 دقائق تسامح في فارق التوقيت بين الهاتف والسيرفر
        $valid = $google2fa->verifyKey($secret, $code, 8);

        if ($valid) {
            // حفظ وتأكيد المفتاح
            $user->forceFill([
                'is_totp_required'        => true,
                'two_factor_confirmed_at' => now(),
            ])->save();

            session(['totp_verified' => true]);
            session()->forget('totp_setup_secret');

            \Illuminate\Support\Facades\Log::info("TOTP enabled successfully for user: {$user->email}");

            Notification::make()
                ->title('✅ تم تفعيل المصادقة الثنائية بنجاح!')
                ->body('تم ربط تطبيق المصادقة بنجاح، مرحباً بك في لوحة الإدارة.')
                ->success()
                ->send();

            redirect()->to(filament()->getPanel('admin')->getUrl());
            return;
        } else {
            \Illuminate\Support\Facades\Log::warning("TOTP failed for {$user->email}: entered '{$code}' but current expected is '{$expectedOtp}'");

            Notification::make()
                ->title('رمز غير صحيح')
                ->body("الرمز الذي أدخلته ({$code}) غير مطابق أو انتهت صلاحيته. تأكد من ضبط ساعة الهاتف تلقائياً واختيار الحساب المطابق في التطبيق.")
                ->danger()
                ->send();
        }
    }

    public function disable(): void
    {
        $user = auth()->user();
        $user->forceFill([
            'two_factor_secret'       => null,
            'two_factor_confirmed_at' => null,
        ])->save();

        session()->forget('totp_setup_secret');

        $google2fa = $this->getGoogle2FA();
        $secret = $google2fa->generateSecretKey();
        $user->forceFill(['two_factor_secret' => encrypt($secret)])->save();
        $this->secretKey = $secret;
        $this->encryptedSecret = encrypt($secret);

        $this->qrCodeUrl = $google2fa->getQRCodeUrl(
            config('app.name', 'نظام إدارة الطاقة الإنتاجية'),
            $user->email,
            $secret
        );
        $this->isEnabled = false;
        $this->code = '';

        Notification::make()
            ->title('تم تعطيل المصادقة الثنائية')
            ->body('يمكنك إعادة تفعيلها في أي وقت.')
            ->warning()
            ->send();
    }
}
