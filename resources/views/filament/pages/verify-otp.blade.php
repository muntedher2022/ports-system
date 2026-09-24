<div class="w-full max-w-md mx-auto p-4" style="direction: rtl; text-align: center;">
    
    <!-- Shield icon centered -->
    <div style="display: flex; justify-content: center; margin-bottom: 16px;">
        <div style="display: flex; align-items: center; justify-content: center; width: 56px; height: 56px; background-color: rgba(99, 102, 241, 0.1); color: rgb(99, 102, 241); border-radius: 50%;">
            <svg style="width: 28px; height: 28px;" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
            </svg>
        </div>
    </div>

    <!-- Header & Subtitle -->
    <h2 class="text-xl font-bold tracking-tight text-gray-950 dark:text-white" style="margin-top: 6px; font-size: 1.35rem; font-weight: 700;">التحقق الثنائي لتسجيل الدخول</h2>

    @php
        $channel = $this->licenseChannel ?? 'both';
    @endphp

    {{-- تبويبات الاختيار عندما تكون القناة هي "كافة الطرق (all)" --}}
    @if($channel === 'all' && $this->hasTotpSetup)
        <div style="display: flex; gap: 8px; justify-content: center; margin: 18px 0 16px; background: rgba(148, 163, 184, 0.12); padding: 5px; border-radius: 12px; border: 1px solid rgba(148, 163, 184, 0.2);">
            <button 
                type="button" 
                wire:click="setMethod('whatsapp')" 
                style="flex: 1; padding: 8px 12px; border-radius: 8px; font-size: 0.85rem; font-weight: 700; cursor: pointer; border: none; transition: all 0.2s; {{ $this->method === 'whatsapp' ? 'background: #059669; color: #fff; box-shadow: 0 2px 6px rgba(5,150,105,0.3);' : 'background: transparent; color: #64748b;' }}"
            >
                📲✉️ رمز الواتساب / الإيميل
            </button>
            <button 
                type="button" 
                wire:click="setMethod('totp')" 
                style="flex: 1; padding: 8px 12px; border-radius: 8px; font-size: 0.85rem; font-weight: 700; cursor: pointer; border: none; transition: all 0.2s; {{ $this->method === 'totp' ? 'background: rgb(79, 70, 229); color: #fff; box-shadow: 0 2px 6px rgba(79,70,229,0.3);' : 'background: transparent; color: #64748b;' }}"
            >
                📱 تطبيق المصادقة
            </button>
        </div>
    @endif

    {{-- محتوى طريقة تطبيق المصادقة (TOTP) --}}
    @if($this->method === 'totp')
        <div style="margin-top: 10px; margin-bottom: 8px;">
            <span style="display: inline-flex; align-items: center; gap: 6px; background-color: rgba(99, 102, 241, 0.12); color: #4f46e5; border: 1px solid rgba(99, 102, 241, 0.25); padding: 5px 14px; border-radius: 20px; font-size: 0.85rem; font-weight: 700;">
                📱 تطبيق المصادقة (Google / Microsoft Authenticator)
            </span>
        </div>

        @if($this->hasTotpSetup)
            <p class="text-sm text-gray-500 dark:text-gray-400" style="margin-top: 6px; margin-bottom: 20px; line-height: 1.6; font-size: 0.88rem;">
                أدخل الرمز المكون من <strong style="color: #4f46e5;">6 أرقام</strong> الظاهر في تطبيق المصادقة على هاتفك.
            </p>
        @else
            <div style="background: rgba(239, 68, 68, 0.1); border: 1px solid rgba(239, 68, 68, 0.3); padding: 12px; border-radius: 10px; margin: 12px 0 16px; font-size: 0.85rem; color: #dc2626; text-align: center;">
                ⚠️ لم تقم بربط تطبيق المصادقة بحسابك مسبقاً.<br>
                يرجى تسجيل الدخول أولاً برمز الواتساب / البريد الإلكتروني، ثم تفعيل التطبيق بأمان من داخل لوحة التحكم.
            </div>
        @endif

    {{-- محتوى طريقة الواتساب / البريد --}}
    @else
        @if($channel === 'whatsapp')
            <div style="margin-top: 10px; margin-bottom: 8px;">
                <span style="display: inline-flex; align-items: center; gap: 6px; background-color: rgba(16, 185, 129, 0.12); color: #059669; border: 1px solid rgba(16, 185, 129, 0.25); padding: 5px 14px; border-radius: 20px; font-size: 0.85rem; font-weight: 700;">
                    📲 تم إرسال الرمز إلى الواتساب
                </span>
            </div>
            <p class="text-sm text-gray-500 dark:text-gray-400" style="margin-top: 6px; margin-bottom: 20px; line-height: 1.6; font-size: 0.88rem;">
                يرجى إدخال رمز التحقق الثنائي المرسل إلى رقم الواتساب المسجل لتنشيط الجلسة.
            </p>
        @elseif($channel === 'email')
            <div style="margin-top: 10px; margin-bottom: 8px;">
                <span style="display: inline-flex; align-items: center; gap: 6px; background-color: rgba(56, 189, 248, 0.12); color: #0284c7; border: 1px solid rgba(56, 189, 248, 0.25); padding: 5px 14px; border-radius: 20px; font-size: 0.85rem; font-weight: 700;">
                    ✉️ تم إرسال الرمز إلى البريد الإلكتروني
                </span>
            </div>
            <p class="text-sm text-gray-500 dark:text-gray-400" style="margin-top: 6px; margin-bottom: 20px; line-height: 1.6; font-size: 0.88rem;">
                يرجى إدخال رمز التحقق الثنائي المرسل إلى بريدك الإلكتروني لتنشيط الجلسة.
            </p>
        @else
            <div style="margin-top: 10px; margin-bottom: 8px;">
                <span style="display: inline-flex; align-items: center; gap: 6px; background-color: rgba(99, 102, 241, 0.12); color: #4f46e5; border: 1px solid rgba(99, 102, 241, 0.25); padding: 5px 14px; border-radius: 20px; font-size: 0.85rem; font-weight: 700;">
                    📲✉️ تم إرسال الرمز إلى الواتساب والبريد معاً
                </span>
            </div>
            <p class="text-sm text-gray-500 dark:text-gray-400" style="margin-top: 6px; margin-bottom: 20px; line-height: 1.6; font-size: 0.88rem;">
                يرجى إدخال رمز التحقق الثنائي المرسل إلى الواتساب والبريد الإلكتروني لتنشيط الجلسة.
            </p>
        @endif
    @endif

    <!-- Form container -->
    <form wire:submit.prevent="verify" class="space-y-6">
        <div style="text-align: right; width: 100%;">
            {{ $this->form }}
        </div>

        <div style="margin-top: 20px; width: 100%;">
            <x-filament::button type="submit" size="lg" style="width: 100%; display: block; background-color: {{ $this->method === 'totp' ? 'rgb(79, 70, 229)' : '#059669' }}; color: #ffffff; font-weight: 700;">
                تأكيد الرمز والعبور
            </x-filament::button>
        </div>
    </form>

    <!-- Footer with Countdown and Resend (only for WhatsApp / Email) -->
    @if($this->method !== 'totp')
        <div style="margin-top: 28px; border-top: 1px solid rgba(229, 231, 235, 0.5); padding-top: 18px; display: flex; justify-content: space-between; align-items: center; font-size: 0.85rem; direction: rtl;">
            
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
                <svg style="width: 16px; height: 16px; color: #059669; display: inline-block; vertical-align: middle; margin-left: 4px;" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <span style="color: #6b7280;">الصلاحية:</span>
                <span class="font-mono font-bold" style="color: #059669; margin-right: 4px;" x-text="formatTime()"></span>
            </div>

            <!-- Resend button -->
            <button 
                type="button" 
                wire:click="resend" 
                style="color: #059669; font-weight: 700; background: none; border: none; cursor: pointer; padding: 0; outline: none;"
                onmouseover="this.style.textDecoration='underline'" 
                onmouseout="this.style.textDecoration='none'"
                wire:loading.attr="disabled"
            >
                إعادة إرسال الرمز
            </button>
        </div>
    @else
        <div style="margin-top: 24px; border-top: 1px solid rgba(229, 231, 235, 0.5); padding-top: 16px; font-size: 0.8rem; color: #94a3b8; text-align: center;">
            🔄 الرمز يتجدد تلقائياً كل 30 ثانية في تطبيق المصادقة
        </div>
    @endif
</div>
