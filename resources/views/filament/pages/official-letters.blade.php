<x-filament-panels::page>
    <style>
        .letters-container {
            font-family: 'Tajawal', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            direction: rtl;
        }

        .letters-hero {
            background: linear-gradient(135deg, #1e3a8a 0%, #0f172a 100%);
            color: #ffffff;
            border-radius: 16px;
            padding: 24px 28px;
            margin-bottom: 24px;
            box-shadow: 0 10px 25px -5px rgba(30, 58, 138, 0.2);
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 16px;
        }

        .letters-hero h1 {
            font-size: 1.5rem;
            font-weight: 800;
            margin: 0 0 6px 0;
            letter-spacing: -0.01em;
        }

        .letters-hero p {
            margin: 0;
            color: #cbd5e1;
            font-size: 0.95rem;
            max-width: 650px;
            line-height: 1.5;
        }

        .letters-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(460px, 1fr));
            gap: 24px;
        }

        @media (max-width: 768px) {
            .letters-grid {
                grid-template-columns: 1fr;
            }
        }

        .letter-card {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 16px;
            box-shadow: 0 4px 15px -3px rgba(0, 0, 0, 0.05);
            overflow: hidden;
            display: flex;
            flex-direction: column;
            transition: all 0.25s ease;
        }

        .letter-card:hover {
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1);
            border-color: #cbd5e1;
        }

        .dark .letter-card {
            background: #1e293b;
            border-color: #334155;
        }

        .card-top {
            padding: 20px 24px;
            border-bottom: 1px solid #f1f5f9;
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 12px;
        }

        .dark .card-top {
            border-bottom-color: #334155;
        }

        .card-title-group h3 {
            margin: 0 0 4px 0;
            font-size: 1.15rem;
            font-weight: 800;
            color: #0f172a;
        }

        .dark .card-title-group h3 {
            color: #f8fafc;
        }

        .card-title-group p {
            margin: 0;
            font-size: 0.85rem;
            color: #64748b;
        }

        .dark .card-title-group p {
            color: #94a3b8;
        }

        .tag-badge {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 4px 10px;
            border-radius: 9999px;
            font-size: 0.75rem;
            font-weight: 700;
            white-space: nowrap;
        }

        .tag-internal {
            background: #e0f2fe;
            color: #0369a1;
            border: 1px solid #bae6fd;
        }

        .dark .tag-internal {
            background: #082f49;
            color: #7dd3fc;
            border-color: #0369a1;
        }

        .tag-central {
            background: #fef3c7;
            color: #b45309;
            border: 1px solid #fde68a;
        }

        .dark .tag-central {
            background: #451a03;
            color: #fde68a;
            border-color: #78350f;
        }

        .card-body-section {
            padding: 20px 24px;
            flex: 1;
            display: flex;
            flex-direction: column;
            gap: 16px;
        }

        .form-grid-2 {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 14px;
        }

        @media (max-width: 480px) {
            .form-grid-2 {
                grid-template-columns: 1fr;
            }
        }

        .field-group {
            display: flex;
            flex-direction: column;
            gap: 6px;
        }

        .field-label {
            font-size: 0.82rem;
            font-weight: 700;
            color: #475569;
        }

        .dark .field-label {
            color: #cbd5e1;
        }

        .field-input, .field-select {
            width: 100%;
            padding: 8px 12px;
            border-radius: 8px;
            border: 1.5px solid #cbd5e1;
            background-color: #ffffff;
            color: #1e293b;
            font-size: 0.9rem;
            font-family: inherit;
            outline: none;
            transition: border-color 0.15s ease;
        }

        .dark .field-input, .dark .field-select {
            background-color: #0f172a;
            border-color: #475569;
            color: #f8fafc;
        }

        .field-input:focus, .field-select:focus {
            border-color: #2563eb;
            box-shadow: 0 0 0 2px rgba(37, 99, 235, 0.15);
        }

        .info-notice {
            background: #f8fafc;
            border: 1px dashed #cbd5e1;
            border-radius: 10px;
            padding: 12px 14px;
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 0.85rem;
            color: #64748b;
        }

        .dark .info-notice {
            background: #0f172a;
            border-color: #334155;
            color: #94a3b8;
        }

        .card-actions {
            padding: 16px 24px;
            background: #f8fafc;
            border-top: 1px solid #f1f5f9;
            display: flex;
            align-items: center;
            justify-content: flex-end;
            gap: 10px;
            flex-wrap: wrap;
        }

        .dark .card-actions {
            background: #0f172a;
            border-top-color: #334155;
        }

        .action-btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 9px 16px;
            border-radius: 8px;
            font-size: 0.85rem;
            font-weight: 700;
            cursor: pointer;
            text-decoration: none;
            transition: all 0.2s ease;
            border: none;
        }

        .btn-preview {
            background: #1e40af;
            color: #ffffff;
        }

        .btn-preview:hover {
            background: #1d4ed8;
            color: #ffffff;
        }

        .btn-word {
            background: #0284c7;
            color: #ffffff;
        }

        .btn-word:hover {
            background: #0369a1;
            color: #ffffff;
        }
    </style>

    <div class="letters-container">
        <!-- ترويسة الصفحة -->
        <div class="letters-hero">
            <div>
                <h1>قوالب الكتب والمذكرات الرسمية</h1>
                <p>توليد وتغذية مذكرات الإيراد والطاقة الإنتاجية ومواقف الحاويات والبضائع طبقاً لصيغ وفورمات الشركة المعتمدة لدى وزارة النقل مع التغذية الفورية بالبيانات الموثقة من النظام.</p>
            </div>
            <div>
                <span class="tag-badge tag-internal" style="font-size: 0.85rem; padding: 6px 14px;">
                    4 نماذج رسمية معتمدة
                </span>
            </div>
        </div>

        <div class="letters-grid">
            <!-- 1. مذكرة الإيراد الكلي والصافي (نموذج 87) -->
            <div class="letter-card">
                <div class="card-top">
                    <div class="card-title-group">
                        <h3>1. مذكرة الإيراد الكلي والصافي</h3>
                        <p>نموذج م.ت 87 - موجهة إلى السيد المدير العام</p>
                    </div>
                    <span class="tag-badge tag-internal">مذكرة داخلية</span>
                </div>

                <div class="card-body-section">
                    <div class="form-grid-2">
                        <div class="field-group">
                            <label class="field-label">السنة المالية</label>
                            <select wire:model.live="revenue_fiscal_year_id" class="field-select">
                                @foreach($this->fiscalYears as $fy)
                                    <option value="{{ $fy->id }}">{{ $fy->year }} {{ $fy->is_current ? '(الحالية)' : '' }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="field-group">
                            <label class="field-label">الشهر</label>
                            <select wire:model.live="revenue_month_id" class="field-select">
                                @foreach($this->months as $m)
                                    <option value="{{ $m->id }}">{{ $m->name_ar }} ({{ $m->month_number }})</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="form-grid-2">
                        <div class="field-group">
                            <label class="field-label">رقم المذكرة (مطلوب)</label>
                            <input type="text" wire:model.live="revenue_memo_number" class="field-input" placeholder="مثال: 87">
                        </div>
                        <div class="field-group">
                            <label class="field-label">تاريخ المذكرة (مطلوب)</label>
                            <input type="text" wire:model.live="revenue_memo_date" class="field-input" placeholder="مثال: 9 / 9 / 2026">
                        </div>
                    </div>

                    <div class="info-notice">
                        <svg width="20" height="20" fill="none" stroke="#2563eb" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        <span>تتضمن المذكرة إحصائيات الإيراد الكلي والصافي للمراكز السبعة والمقارنة مع السنة السابقة.</span>
                    </div>
                </div>

                <div class="card-actions">
                    <a href="{{ route('admin.official-letters.preview', ['type' => 'revenue_memo', 'fiscal_year_id' => $revenue_fiscal_year_id, 'month_id' => $revenue_month_id, 'memo_number' => $revenue_memo_number, 'memo_date' => $revenue_memo_date]) }}" target="_blank" class="action-btn btn-preview">
                        <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                        معاينة وطباعة (A4 / PDF)
                    </a>
                    <a href="{{ route('admin.official-letters.docx', ['type' => 'revenue_memo', 'fiscal_year_id' => $revenue_fiscal_year_id, 'month_id' => $revenue_month_id, 'memo_number' => $revenue_memo_number, 'memo_date' => $revenue_memo_date]) }}" class="action-btn btn-word">
                        <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                        تحميل Word (.docx)
                    </a>
                </div>
            </div>

            <!-- 2. مذكرة الطاقة الإنتاجية (نموذج 88) -->
            <div class="letter-card">
                <div class="card-top">
                    <div class="card-title-group">
                        <h3>2. مذكرة الطاقة الإنتاجية للموانئ</h3>
                        <p>نموذج م.ت 88 - موجهة إلى السيد المدير العام</p>
                    </div>
                    <span class="tag-badge tag-internal">مذكرة داخلية</span>
                </div>

                <div class="card-body-section">
                    <div class="form-grid-2">
                        <div class="field-group">
                            <label class="field-label">السنة المالية</label>
                            <select wire:model.live="capacity_fiscal_year_id" class="field-select">
                                @foreach($this->fiscalYears as $fy)
                                    <option value="{{ $fy->id }}">{{ $fy->year }} {{ $fy->is_current ? '(الحالية)' : '' }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="field-group">
                            <label class="field-label">الشهر</label>
                            <select wire:model.live="capacity_month_id" class="field-select">
                                @foreach($this->months as $m)
                                    <option value="{{ $m->id }}">{{ $m->name_ar }} ({{ $m->month_number }})</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="form-grid-2">
                        <div class="field-group">
                            <label class="field-label">رقم المذكرة (مطلوب)</label>
                            <input type="text" wire:model.live="capacity_memo_number" class="field-input" placeholder="مثال: 88">
                        </div>
                        <div class="field-group">
                            <label class="field-label">تاريخ المذكرة (مطلوب)</label>
                            <input type="text" wire:model.live="capacity_memo_date" class="field-input" placeholder="مثال: 9 / 9 / 2026">
                        </div>
                    </div>

                    <div class="info-notice">
                        <svg width="20" height="20" fill="none" stroke="#2563eb" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        <span>تغطي الموانئ الأربعة (أم قصر الشمالي والجنوبي، خور الزبير، أبو فلوس) وإجمالي الشركة.</span>
                    </div>
                </div>

                <div class="card-actions">
                    <a href="{{ route('admin.official-letters.preview', ['type' => 'capacity_memo', 'fiscal_year_id' => $capacity_fiscal_year_id, 'month_id' => $capacity_month_id, 'memo_number' => $capacity_memo_number, 'memo_date' => $capacity_memo_date]) }}" target="_blank" class="action-btn btn-preview">
                        <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                        معاينة وطباعة (A4 / PDF)
                    </a>
                    <a href="{{ route('admin.official-letters.docx', ['type' => 'capacity_memo', 'fiscal_year_id' => $capacity_fiscal_year_id, 'month_id' => $capacity_month_id, 'memo_number' => $capacity_memo_number, 'memo_date' => $capacity_memo_date]) }}" class="action-btn btn-word">
                        <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                        تحميل Word (.docx)
                    </a>
                </div>
            </div>

            <!-- 3. كتاب موقف الحاويات المتخلفة والخطرة (صادر مركزي) -->
            <div class="letter-card">
                <div class="card-top">
                    <div class="card-title-group">
                        <h3>3. موقف الحاويات المتخلفة والخطرة</h3>
                        <p>موجه إلى وزارة النقل / الدائرة الفنية</p>
                    </div>
                    <span class="tag-badge tag-central">صادر مركزي</span>
                </div>

                <div class="card-body-section">
                    <div class="form-grid-2">
                        <div class="field-group">
                            <label class="field-label">السنة المالية</label>
                            <select wire:model.live="containers_fiscal_year_id" class="field-select">
                                @foreach($this->fiscalYears as $fy)
                                    <option value="{{ $fy->id }}">{{ $fy->year }} {{ $fy->is_current ? '(الحالية)' : '' }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="field-group">
                            <label class="field-label">الشهر</label>
                            <select wire:model.live="containers_month_id" class="field-select">
                                @foreach($this->months as $m)
                                    <option value="{{ $m->id }}">{{ $m->name_ar }} ({{ $m->month_number }})</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="info-notice" style="background: #fffbeb; border-color: #fde68a; color: #92400e;">
                        <svg width="22" height="22" fill="none" stroke="#d97706" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                        <span><strong>صادر مركزي يسحب فارغاً:</strong> لا يُطلب رقم أو تاريخ المذكرة لهذا الكتاب؛ حيث يُطبع بحقول فارغة للختم والقيد المركزي.</span>
                    </div>
                </div>

                <div class="card-actions">
                    <a href="{{ route('admin.official-letters.preview', ['type' => 'containers_letter', 'fiscal_year_id' => $containers_fiscal_year_id, 'month_id' => $containers_month_id]) }}" target="_blank" class="action-btn btn-preview">
                        <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                        معاينة وطباعة (A4 / PDF)
                    </a>
                    <a href="{{ route('admin.official-letters.docx', ['type' => 'containers_letter', 'fiscal_year_id' => $containers_fiscal_year_id, 'month_id' => $containers_month_id]) }}" class="action-btn btn-word">
                        <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                        تحميل Word (.docx)
                    </a>
                </div>
            </div>

            <!-- 4. كتاب موقف المواد والبضائع المتخلفة (صادر مركزي) -->
            <div class="letter-card">
                <div class="card-top">
                    <div class="card-title-group">
                        <h3>4. موقف المواد والبضائع المتخلفة</h3>
                        <p>موجه إلى وزارة النقل / الدائرة الفنية</p>
                    </div>
                    <span class="tag-badge tag-central">صادر مركزي</span>
                </div>

                <div class="card-body-section">
                    <div class="form-grid-2">
                        <div class="field-group">
                            <label class="field-label">السنة المالية</label>
                            <select wire:model.live="cargo_fiscal_year_id" class="field-select">
                                @foreach($this->fiscalYears as $fy)
                                    <option value="{{ $fy->id }}">{{ $fy->year }} {{ $fy->is_current ? '(الحالية)' : '' }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="field-group">
                            <label class="field-label">الشهر</label>
                            <select wire:model.live="cargo_month_id" class="field-select">
                                @foreach($this->months as $m)
                                    <option value="{{ $m->id }}">{{ $m->name_ar }} ({{ $m->month_number }})</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="info-notice" style="background: #fffbeb; border-color: #fde68a; color: #92400e;">
                        <svg width="22" height="22" fill="none" stroke="#d97706" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                        <span><strong>صادر مركزي يسحب فارغاً:</strong> لا يُطلب رقم أو تاريخ المذكرة لهذا الكتاب؛ حيث يُطبع بحقول فارغة للختم والقيد المركزي.</span>
                    </div>
                </div>

                <div class="card-actions">
                    <a href="{{ route('admin.official-letters.preview', ['type' => 'cargo_letter', 'fiscal_year_id' => $cargo_fiscal_year_id, 'month_id' => $cargo_month_id]) }}" target="_blank" class="action-btn btn-preview">
                        <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                        معاينة وطباعة (A4 / PDF)
                    </a>
                    <a href="{{ route('admin.official-letters.docx', ['type' => 'cargo_letter', 'fiscal_year_id' => $cargo_fiscal_year_id, 'month_id' => $cargo_month_id]) }}" class="action-btn btn-word">
                        <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                        تحميل Word (.docx)
                    </a>
                </div>
            </div>
        </div>
    </div>
</x-filament-panels::page>
