<div class="w-full" style="min-height:100vh; display:flex; align-items:center; justify-content:center; background: linear-gradient(135deg, #1e1b4b 0%, #312e81 50%, #4338ca 100%); direction: rtl;">
    <div style="width:100%; max-width:420px; margin: 0 auto; padding: 16px;">
        <div style="background:#fff; border-radius:24px; box-shadow: 0 25px 50px rgba(0,0,0,0.25); overflow:hidden;">

            {{-- Header --}}
            <div style="background: linear-gradient(135deg,#1e1b4b,#312e81); padding: 36px 32px; text-align:center;">
                <div style="width:64px;height:64px;background:rgba(165,180,252,0.2);border-radius:16px;display:flex;align-items:center;justify-content:center;margin:0 auto 16px;">
                    <svg style="width:36px;height:36px;color:#a5b4fc;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                    </svg>
                </div>
                <h1 style="font-size:1.2rem; font-weight:800; color:#a5b4fc; margin:0 0 6px;">التحقق الثنائي</h1>
                <p style="font-size:0.85rem; color:#c7d2fe; margin:0;">
                    افتح تطبيق المصادقة واحصل على الرمز
                </p>
            </div>

            {{-- Content --}}
            <div style="padding: 32px;">
                <p style="text-align:center; color:#4b5563; font-size:0.875rem; line-height:1.7; margin-bottom:24px;">
                    أدخل الرمز المكون من <strong style="color:#4338ca;">6 أرقام</strong> من
                    تطبيق <strong>Google Authenticator</strong> أو أي تطبيق TOTP
                </p>

                <form wire:submit.prevent="verify">
                    <div style="margin-bottom: 16px; text-align:right;">
                        {{ $this->form }}
                    </div>

                    <button
                        type="submit"
                        wire:loading.attr="disabled"
                        style="width:100%; background: linear-gradient(135deg,#1e1b4b,#4338ca); color:#fff; border:none; border-radius:12px; padding:14px; font-size:1rem; font-weight:700; cursor:pointer; margin-top:8px;"
                    >
                        <span wire:loading.remove wire:target="verify">🛡️ تحقق والدخول</span>
                        <span wire:loading wire:target="verify">جارٍ التحقق...</span>
                    </button>
                </form>

                <div style="margin-top:24px; text-align:center;">
                    <p style="font-size:0.75rem; color:#9ca3af; margin-bottom:6px;">
                        🔄 الرمز يتجدد كل 30 ثانية تلقائياً
                    </p>
                    <a href="{{ filament()->getPanel('admin')->getLoginUrl() }}"
                       style="font-size:0.8rem; color:#4338ca; text-decoration:none; font-weight:600;"
                       onmouseover="this.style.textDecoration='underline'"
                       onmouseout="this.style.textDecoration='none'"
                    >
                        ← تسجيل الخروج والعودة لتسجيل الدخول
                    </a>
                </div>
            </div>

        </div>
    </div>
</div>
