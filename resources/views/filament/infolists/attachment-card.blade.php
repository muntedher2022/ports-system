@php
    $attachment = $getRecord();
@endphp

<div style="background: var(--fi-bg-card, #ffffff); border: 1px solid #cbd5e1; border-radius: 10px; overflow: hidden; box-shadow: 0 1px 4px rgba(0,0,0,0.06); display: flex; flex-direction: column; height: 100%;">
    <!-- صورة المرفق الفعلية -->
    <div style="position: relative; width: 100%; height: 240px; background-color: #f1f5f9; display: flex; align-items: center; justify-content: center; overflow: hidden; border-bottom: 1px solid #e2e8f0;">
        @if($attachment && $attachment->base64_data_uri)
            <img
                src="{{ $attachment->base64_data_uri }}"
                alt="{{ $attachment->caption ?: 'صورة توثيقية' }}"
                style="width: 100%; height: 100%; object-fit: contain; display: block;"
            />
        @else
            <div style="text-align: center; color: #94a3b8;">
                <x-filament::icon icon="heroicon-o-photo" style="width: 48px; height: 48px; margin: 0 auto; display: block;" />
                <span style="font-size: 0.75rem; margin-top: 4px; display: block;">مرفق غير مصور</span>
            </div>
        @endif

        @if($attachment && $attachment->is_primary)
            <span style="position: absolute; top: 8px; right: 8px; background-color: #f59e0b; color: #ffffff; font-size: 0.75rem; font-weight: 700; padding: 3px 10px; border-radius: 6px; box-shadow: 0 1px 3px rgba(0,0,0,0.25); display: inline-flex; align-items: center; gap: 4px;">
                <x-filament::icon icon="heroicon-s-star" style="width: 14px; height: 14px;" />
                صورة رئيسية
            </span>
        @endif
    </div>

    <!-- التفاصيل والتعليق -->
    <div style="padding: 12px 14px; display: flex; flex-direction: column; justify-content: space-between; flex-grow: 1;">
        <div>
            @if($attachment && filled($attachment->caption))
                <div style="font-weight: 700; font-size: 0.95rem; color: #0f172a; margin-bottom: 6px; line-height: 1.5;">
                    {{ $attachment->caption }}
                </div>
            @else
                <div style="font-weight: 600; font-size: 0.85rem; color: #64748b; margin-bottom: 6px;">
                    صورة توثيقية للنشاط
                </div>
            @endif
        </div>

        <div style="display: flex; align-items: center; justify-content: space-between; margin-top: 8px; padding-top: 8px; border-top: 1px dashed #e2e8f0; font-size: 0.8rem;">
            <span style="color: #64748b; font-weight: 500; display: inline-flex; align-items: center; gap: 5px;">
                <x-filament::icon icon="heroicon-m-circle-stack" style="width: 15px; height: 15px; color: #94a3b8;" />
                {{ $attachment->formatted_size }}
            </span>

            @if($attachment)
                <a
                    href="{{ $attachment->download_url }}"
                    target="_blank"
                    style="color: #0284c7; font-weight: 700; text-decoration: none; display: inline-flex; align-items: center; gap: 5px;"
                >
                    <x-filament::icon icon="heroicon-m-arrow-down-tray" style="width: 15px; height: 15px; color: #0284c7;" />
                    تحميل المرفق
                </a>
            @endif
        </div>
    </div>
</div>
