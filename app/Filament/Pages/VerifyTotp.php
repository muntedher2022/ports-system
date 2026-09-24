<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use PragmaRX\Google2FA\Google2FA;

class VerifyTotp extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $title = 'التحقق الثنائي';
    protected static ?string $navigationLabel = 'التحقق الثنائي';
    protected static bool $shouldRegisterNavigation = false;

    public static function getNavigationIcon(): string|\BackedEnum|null
    {
        return 'heroicon-o-shield-check';
    }

    protected string $view = 'filament.pages.verify-totp';
    protected static string $layout = 'filament-panels::components.layout.simple';

    public ?string $code = '';

    public function form(Schema $schema): Schema
    {
        return $schema->schema([
            TextInput::make('code')
                ->label('رمز التحقق')
                ->required()
                ->numeric()
                ->maxLength(6)
                ->minLength(6)
                ->placeholder('******')
                ->extraInputAttributes([
                    'style' => 'text-align: center; font-size: 28px; font-family: monospace; letter-spacing: 8px;',
                    'autofocus' => 'autofocus',
                ]),
        ]);
    }

    public function mount(): void
    {
        if (!auth()->check()) {
            redirect()->to(filament()->getPanel('admin')->getLoginUrl());
            return;
        }

        $user = auth()->user();

        // إذا لم يكن المستخدم ملزماً بـ TOTP، نمرره للوحة مباشرة
        if (!$user->isTotpRequired()) {
            session(['totp_verified' => true]);
            redirect()->to(filament()->getPanel('admin')->getUrl());
            return;
        }

        // إذا كان ملزماً لكن لم يربط جهازه بعد، نرسله لشاشة الإعداد
        if (!$user->hasTotpSetup()) {
            redirect()->route('filament.admin.pages.setup-totp');
            return;
        }

        // إذا تم التحقق مسبقاً في هذه الجلسة
        if (session('totp_verified') === true) {
            redirect()->to(filament()->getPanel('admin')->getUrl());
            return;
        }
    }

    public function verify(): void
    {
        $user = auth()->user();

        $formData = [];
        try {
            $formData = $this->form->getState();
        } catch (\Exception $e) {
            //
        }
        $code = trim((string)($formData['code'] ?? $this->code));

        if (empty($code)) {
            $this->validate(['code' => 'required|digits:6']);
            $code = trim((string)$this->code);
        }

        if (!$user->hasTotpSetup()) {
            session(['totp_verified' => true]);
            redirect()->to(filament()->getPanel('admin')->getUrl());
            return;
        }

        $google2fa = new Google2FA();
        $secret = decrypt($user->two_factor_secret);
        $expectedOtp = $google2fa->getCurrentOtp($secret);

        \Illuminate\Support\Facades\Log::info("VerifyTotp Login attempt for {$user->email}: input={$code}, expected_now={$expectedOtp}");

        // نافذة 8 خطوات = ±4 دقائق تسامح
        $valid = $google2fa->verifyKey($secret, $code, 8);

        if ($valid) {
            session(['totp_verified' => true]);

            Notification::make()
                ->title('تم التحقق بنجاح')
                ->body('مرحباً بك في لوحة الإدارة.')
                ->success()
                ->send();

            redirect()->to(filament()->getPanel('admin')->getUrl());
        } else {
            $this->code = '';
            \Illuminate\Support\Facades\Log::warning("VerifyTotp failed for {$user->email}: input={$code}, expected_now={$expectedOtp}");

            Notification::make()
                ->title('رمز غير صحيح')
                ->body("الرمز الذي أدخلته ({$code}) غير صحيح أو انتهت صلاحيته. يرجى التأكد من الرمز والمحاولة مجدداً.")
                ->danger()
                ->send();
        }
    }
}
