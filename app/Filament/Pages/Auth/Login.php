<?php

namespace App\Filament\Pages\Auth;

use Filament\Auth\Pages\Login as BaseLogin;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Component;

class Login extends BaseLogin
{
    /**
     * تخصيص حقل تسجيل الدخول لقبول البريد الإلكتروني أو رقم الهاتف
     */
    protected function getEmailFormComponent(): Component
    {
        return TextInput::make('email')
            ->label('البريد الإلكتروني أو رقم الهاتف')
            ->placeholder('example@domain.com أو 0770xxxxxxx')
            ->required()
            ->autocomplete()
            ->autofocus()
            ->extraInputAttributes(['dir' => 'ltr', 'style' => 'text-align: right;']);
    }

    /**
     * استخراج بيانات الاعتماد بناءً على ما إذا كان المدخل بريداً إلكترونياً أو رقم هاتف
     */
    protected function getCredentialsFromFormData(array $data): array
    {
        $login = trim($data['email']);
        $isEmail = filter_var($login, FILTER_VALIDATE_EMAIL);

        if ($isEmail) {
            return [
                'email'    => $login,
                'password' => $data['password'],
            ];
        }

        // تنظيف ومطابقة رقم الهاتف
        $cleanPhone = preg_replace('/[\s\-\+\(\)]/', '', $login);

        $matchedUser = \App\Models\User::where('phone', $login)
            ->orWhere('phone', $cleanPhone)
            ->when(strlen($cleanPhone) >= 7, function ($query) use ($cleanPhone) {
                $trimmed = ltrim($cleanPhone, '0');
                $query->orWhere('phone', 'like', "%{$trimmed}");
            })
            ->first();

        if ($matchedUser && !empty($matchedUser->email)) {
            return [
                'email'    => $matchedUser->email,
                'password' => $data['password'],
            ];
        }

        return [
            'phone'    => $login,
            'password' => $data['password'],
        ];
    }
}
