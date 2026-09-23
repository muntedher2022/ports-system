<div class="w-full max-w-md mx-auto p-4" style="direction: rtl; text-align: center;">
    
    <!-- Shield icon centered -->
    <div style="display: flex; justify-content: center; margin-bottom: 20px;">
        <div style="display: flex; align-items: center; justify-content: center; width: 56px; height: 56px; background-color: rgba(59, 130, 246, 0.1); color: rgb(59, 130, 246); border-radius: 50%;">
            <svg style="width: 28px; height: 28px;" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
            </svg>
        </div>
    </div>

    @php
        $check = \App\Licensing\LicensingService::check();
        $channel = session('admin_otp_channel') ?? ($check['license']['admin_otp_channel'] ?? 'both');
    @endphp

    <!-- Header & Subtitle -->
    <h2 class="text-xl font-bold tracking-tight text-gray-950 dark:text-white" style="margin-top: 10px; font-size: 1.4rem; font-weight: 700; color: #111827;">التحقق الثنائي لتسجيل الدخول</h2>
    
    @if($channel === 'whatsapp')
        <div style="margin-top: 10px; margin-bottom: 8px;">
            <span style="display: inline-flex; align-items: center; gap: 6px; background-color: rgba(16, 185, 129, 0.12); color: #059669; border: 1px solid rgba(16, 185, 129, 0.25); padding: 5px 14px; border-radius: 20px; font-size: 0.85rem; font-weight: 700;">
                📲 تم إرسال الرمز إلى الواتساب
            </span>
        </div>
        <p class="text-sm text-gray-500 dark:text-gray-400" style="margin-top: 6px; margin-bottom: 24px; line-height: 1.6; color: #6b7280; font-size: 0.9rem;">
            يرجى إدخال رمز التحقق الثنائي المرسل إلى رقم الواتساب الخاص بحسابك لتنشيط الجلسة والعبور إلى لوحة النظام.
        </p>
    @elseif($channel === 'email')
        <div style="margin-top: 10px; margin-bottom: 8px;">
            <span style="display: inline-flex; align-items: center; gap: 6px; background-color: rgba(56, 189, 248, 0.12); color: #0284c7; border: 1px solid rgba(56, 189, 248, 0.25); padding: 5px 14px; border-radius: 20px; font-size: 0.85rem; font-weight: 700;">
                ✉️ تم إرسال الرمز إلى البريد الإلكتروني
            </span>
        </div>
        <p class="text-sm text-gray-500 dark:text-gray-400" style="margin-top: 6px; margin-bottom: 24px; line-height: 1.6; color: #6b7280; font-size: 0.9rem;">
            يرجى إدخال رمز التحقق الثنائي المرسل إلى البريد الإلكتروني الخاص بحسابك لتنشيط الجلسة والعبور إلى لوحة النظام.
        </p>
    @else
        <div style="margin-top: 10px; margin-bottom: 8px;">
            <span style="display: inline-flex; align-items: center; gap: 6px; background-color: rgba(59, 130, 246, 0.12); color: #2563eb; border: 1px solid rgba(59, 130, 246, 0.25); padding: 5px 14px; border-radius: 20px; font-size: 0.85rem; font-weight: 700;">
                📲✉️ تم إرسال الرمز إلى الواتساب والبريد معاً
            </span>
        </div>
        <p class="text-sm text-gray-500 dark:text-gray-400" style="margin-top: 6px; margin-bottom: 24px; line-height: 1.6; color: #6b7280; font-size: 0.9rem;">
            يرجى إدخال رمز التحقق الثنائي المرسل إلى الواتساب والبريد الإلكتروني لتنشيط الجلسة والعبور إلى لوحة النظام.
        </p>
    @endif

    <!-- Form container -->
    <form wire:submit.prevent="verify" class="space-y-6">
        <div style="text-align: right; width: 100%;">
            {{ $this->form }}
        </div>

        <div style="margin-top: 24px; width: 100%;">
            <x-filament::button type="submit" size="lg" style="width: 100%; display: block; background-color: rgb(37, 99, 235); color: #ffffff; font-weight: 700;">
                تأكيد الرمز والعبور
            </x-filament::button>
        </div>
    </form>

    <!-- Footer with Countdown and Resend -->
    <div style="margin-top: 32px; border-top: 1px solid rgba(229, 231, 235, 0.5); padding-top: 20px; display: flex; justify-content: space-between; align-items: center; font-size: 0.85rem; direction: rtl;">
        
        <!-- Timer -->
        <div style="display: flex; align-items: center; gap: 6px;" x-data="{
            seconds: @entangle('countdown'),
            formatTime() {
                let m = Math.floor(this.seconds / 60);
                let s = this.seconds % 60;
                return `${m.toString().padStart(2, '0')}:${s.toString().padStart(2, '0')}`;
            },
            init() {
                setInterval(() => {
                    if (this.seconds > 0) this.seconds--;
                }, 1000);
            }
        }">
            <!-- Clock icon -->
            <svg style="width: 16px; height: 16px; color: rgb(37, 99, 235); display: inline-block; vertical-align: middle; margin-left: 4px;" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            <span style="color: #6b7280;">الصلاحية:</span>
            <span class="font-mono font-bold" style="color: rgb(37, 99, 235); margin-right: 4px;" x-text="formatTime()"></span>
        </div>

        <!-- Resend button -->
        <button 
            type="button" 
            wire:click="resend" 
            style="color: rgb(37, 99, 235); font-weight: 700; background: none; border: none; cursor: pointer; padding: 0; outline: none;"
            onmouseover="this.style.textDecoration='underline'" 
            onmouseout="this.style.textDecoration='none'"
            wire:loading.attr="disabled"
        >
            إعادة إرسال الرمز
        </button>
    </div>
</div>
