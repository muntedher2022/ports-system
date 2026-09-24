<x-filament-panels::page>
    <div class="max-w-2xl mx-auto space-y-6" style="direction: rtl;">

        @if($isEnabled)
            {{-- TOTP مفعّل --}}
            <div style="border-radius: 16px; border: 1px solid #bbf7d0; background: #f0fdf4; padding: 24px; text-align: center;">
                <div style="display: flex; align-items: center; justify-content: center; gap: 12px; margin-bottom: 12px;">
                    <svg style="width:36px;height:36px;color:#16a34a" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                    </svg>
                    <h2 style="font-size:1.25rem; font-weight:800; color:#15803d;">المصادقة الثنائية مفعّلة ✅</h2>
                </div>
                <p style="color:#166534; font-size:0.9rem; line-height:1.6;">
                    حسابك محمي بتطبيق المصادقة. ستحتاج إلى إدخال رمز من التطبيق عند كل تسجيل دخول.
                </p>
            </div>

            <div style="border-radius: 16px; border: 1px solid #fee2e2; background: #fff; padding: 24px;">
                <h3 style="font-weight:700; color:#111827; margin-bottom:8px;">تعطيل المصادقة الثنائية</h3>
                <p style="font-size:0.85rem; color:#6b7280; margin-bottom:16px;">
                    إذا أردت تعطيل المصادقة، ستصبح حماية حسابك أقل أماناً.
                </p>
                <button
                    wire:click="disable"
                    wire:loading.attr="disabled"
                    style="background:#dc2626; color:#fff; border:none; border-radius:8px; padding:10px 20px; font-weight:700; cursor:pointer; font-size:0.9rem;"
                >
                    <span wire:loading.remove wire:target="disable">⛔ تعطيل المصادقة الثنائية</span>
                    <span wire:loading wire:target="disable">جارٍ التعطيل...</span>
                </button>
            </div>

        @else
            {{-- إعداد TOTP --}}
            <div style="border-radius: 16px; border: 1px solid #c7d2fe; background: #eef2ff; padding: 24px;">
                <h2 style="font-size:1rem; font-weight:800; color:#3730a3; margin-bottom:10px;">📱 كيفية الإعداد</h2>
                <ol style="font-size:0.875rem; color:#4338ca; line-height:2; padding-right:20px;">
                    <li>حمّل تطبيق <strong>Google Authenticator</strong> أو <strong>Microsoft Authenticator</strong> على هاتفك</li>
                    <li>افتح التطبيق واضغط <strong>"إضافة حساب"</strong> ثم <strong>"مسح رمز QR"</strong></li>
                    <li>امسح رمز QR أدناه بالكاميرا</li>
                    <li>أدخل الرمز المكون من 6 أرقام وتأكد منه</li>
                </ol>
            </div>

            {{-- QR Code --}}
            <div style="border-radius: 16px; border: 1px solid #e5e7eb; background: #fff; padding: 32px; text-align: center;">
                <h3 style="font-weight:700; color:#374151; font-size:1rem; margin-bottom:16px;">امسح رمز QR بتطبيق المصادقة</h3>

                <div style="display: flex; justify-content: center; margin-bottom: 16px;">
                    <img
                        src="https://api.qrserver.com/v1/create-qr-code/?size=220x220&data={{ urlencode($qrCodeUrl) }}"
                        alt="QR Code"
                        style="width:220px; height:220px; border-radius:12px; border:2px solid #c7d2fe;"
                    />
                </div>

                <div style="background:#f9fafb; border:1px solid #e5e7eb; border-radius:10px; padding:12px; margin-bottom:8px;">
                    <p style="font-size:0.75rem; color:#6b7280; margin-bottom:4px;">أو أدخل المفتاح يدوياً في التطبيق:</p>
                    <code style="font-size:0.95rem; font-weight:800; letter-spacing:4px; color:#4338ca; user-select:all;">{{ $this->getSecretKeyProperty() }}</code>
                </div>
                <p style="font-size:0.75rem; color:#9ca3af;">🔐 هذا المفتاح السري لحسابك فقط - لا تشاركه مع أحد</p>
            </div>

            {{-- نموذج التأكيد --}}
            <div style="border-radius: 16px; border: 1px solid #e5e7eb; background: #fff; padding: 24px;">
                <h3 style="font-weight:700; color:#111827; margin-bottom:8px;">تأكيد الإعداد</h3>
                <p style="font-size:0.875rem; color:#6b7280; margin-bottom:20px;">
                    أدخل الرمز المكون من 6 أرقام الذي يعرضه تطبيق المصادقة الآن:
                </p>

                <form wire:submit.prevent="enable">
                    <div style="margin-bottom: 16px; text-align: right;">
                        {{ $this->form }}
                    </div>
                    <button
                        type="submit"
                        wire:loading.attr="disabled"
                        style="width:100%; background: linear-gradient(135deg,#1e1b4b,#4338ca); color:#fff; border:none; border-radius:10px; padding:14px; font-size:1rem; font-weight:700; cursor:pointer; letter-spacing:0.5px;"
                    >
                        <span wire:loading.remove wire:target="enable">🔐 تفعيل المصادقة الثنائية</span>
                        <span wire:loading wire:target="enable">جارٍ التحقق...</span>
                    </button>
                </form>
            </div>
        @endif

    </div>
</x-filament-panels::page>
