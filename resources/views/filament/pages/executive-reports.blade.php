<x-filament-panels::page>
    <div style="display: flex; flex-direction: column; gap: 1.25rem;">
        <!-- نموذج معايير التقرير -->
        <div>
            {{ $this->form }}
        </div>

        <!-- شريط الجاهزية والطباعة الرسمية -->
        @php
            $query = $this->getFilteredActivitiesQuery();
            $total = (clone $query)->count();
            $completed = (clone $query)->where('status', 'completed')->count();
            $urgent = (clone $query)->where('importance', 'urgent')->count();
            $visits = (clone $query)->where('category', 'زيارة ميدانية')->count();
            $meetings = (clone $query)->where('category', 'اجتماع رسمي')->count();
        @endphp

        <x-filament::section>
            <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 1rem;">
                <div style="display: flex; align-items: center; gap: 0.85rem;">
                    <div style="padding: 0.6rem; background-color: rgba(16, 185, 129, 0.12); border-radius: 0.6rem; display: flex; align-items: center; justify-content: center;">
                        <x-filament::icon
                            icon="heroicon-o-document-check"
                            style="width: 28px; height: 28px; color: #10b981; display: block;"
                        />
                    </div>
                    <div>
                        <h3 style="font-weight: 700; font-size: 1.05rem; margin: 0; color: inherit;">
                            جاهزية التقرير الدوري للطباعة والتصدير
                        </h3>
                        <p style="font-size: 0.85rem; color: #64748b; margin: 0.25rem 0 0 0;">
                            تمت مطابقة <strong style="color: #10b981; font-size: 0.95rem;">{{ $total }}</strong> نشاط وفق المعايير المحددة أعلاه جاهزة للإدراج في وثيقة الـ PDF الرسمية.
                        </p>
                    </div>
                </div>

                <div>
                    <x-filament::button
                        type="button"
                        wire:click="generatePdf"
                        size="lg"
                        color="success"
                        icon="heroicon-o-printer"
                    >
                        تصدير وطباعة تقرير PDF فوري (رسمي)
                    </x-filament::button>
                </div>
            </div>
        </x-filament::section>

        <!-- بطاقات المؤشرات الإحصائية السريعة بتنسيق الثيم الأصلي -->
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(170px, 1fr)); gap: 12px;">
            <x-filament::section>
                <div style="text-align: center; padding: 0.25rem;">
                    <div style="font-size: 0.8rem; font-weight: 600; color: #64748b;">إجمالي النشاطات</div>
                    <div style="font-size: 1.85rem; font-weight: 900; margin-top: 0.25rem; color: inherit;">{{ $total }}</div>
                </div>
            </x-filament::section>

            <x-filament::section>
                <div style="text-align: center; padding: 0.25rem;">
                    <div style="font-size: 0.8rem; font-weight: 600; color: #0284c7;">اجتماعات رسمية</div>
                    <div style="font-size: 1.85rem; font-weight: 900; color: #0284c7; margin-top: 0.25rem;">{{ $meetings }}</div>
                </div>
            </x-filament::section>

            <x-filament::section>
                <div style="text-align: center; padding: 0.25rem;">
                    <div style="font-size: 0.8rem; font-weight: 600; color: #10b981;">جولات وزيارات ميدانية</div>
                    <div style="font-size: 1.85rem; font-weight: 900; color: #10b981; margin-top: 0.25rem;">{{ $visits }}</div>
                </div>
            </x-filament::section>

            <x-filament::section>
                <div style="text-align: center; padding: 0.25rem;">
                    <div style="font-size: 0.8rem; font-weight: 600; color: #e11d48;">أنشطة عاجلة وهامة</div>
                    <div style="font-size: 1.85rem; font-weight: 900; color: #e11d48; margin-top: 0.25rem;">{{ $urgent }}</div>
                </div>
            </x-filament::section>

            <x-filament::section>
                <div style="text-align: center; padding: 0.25rem;">
                    <div style="font-size: 0.8rem; font-weight: 600; color: #059669;">منجزة بالكامل</div>
                    <div style="font-size: 1.85rem; font-weight: 900; color: #059669; margin-top: 0.25rem;">{{ $completed }}</div>
                </div>
            </x-filament::section>
        </div>

        <!-- جدول الأنشطة المعتمد بنظام Filament الأصلي الكامل -->
        <div>
            {{ $this->table }}
        </div>
    </div>
</x-filament-panels::page>
