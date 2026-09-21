<?php if (isset($component)) { $__componentOriginal166a02a7c5ef5a9331faf66fa665c256 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal166a02a7c5ef5a9331faf66fa665c256 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'filament-panels::components.page.index','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('filament-panels::page'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

    <style>
        .cs-dashboard { font-family: 'Segoe UI', Tahoma, sans-serif; color: #1e293b; direction: rtl; }

        /* ── Modern Filter Bar ── */
        .cs-filter-bar {
            background: linear-gradient(135deg, #ffffff, #f8fafc);
            border: 1px solid #e2e8f0;
            border-radius: 16px;
            padding: 14px 20px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 12px;
            margin-bottom: 20px;
            box-shadow: 0 2px 12px rgba(0,0,0,0.06);
        }
        .cs-filter-left  { display: flex; align-items: center; gap: 8px; flex-wrap: wrap; }
        .cs-filter-right { display: flex; align-items: center; gap: 10px; flex-wrap: wrap; }

        /* divider between filter sections */
        .cs-filter-divider {
            width: 1px; height: 32px;
            background: #e2e8f0;
            margin: 0 4px;
        }

        /* filter field wrapper */
        .cs-field {
            display: flex; align-items: center; gap: 6px;
            background: #f1f5f9; border: 1px solid #e2e8f0;
            border-radius: 10px; padding: 5px 10px;
            transition: border-color 0.2s;
        }
        .cs-field:focus-within { border-color: #3b82f6; background: #fff; }
        .cs-field-icon { font-size: 0.9rem; }
        .cs-field-label { font-weight: 700; font-size: 0.78rem; color: #64748b; white-space: nowrap; }
        .cs-select {
            background: transparent; border: none; outline: none;
            font-size: 0.85rem; color: #1e293b; cursor: pointer;
            min-width: 120px; max-width: 160px;
        }

        /* Type toggle buttons */
        .cs-type-btn {
            display: inline-flex; align-items: center; gap: 5px;
            padding: 7px 15px; border-radius: 10px; border: 2px solid transparent;
            font-size: 0.82rem; font-weight: 700; cursor: pointer;
            transition: all 0.2s ease; text-decoration: none; white-space: nowrap;
            line-height: 1;
        }
        .cs-type-btn.abandoned { background: #fefce8; border-color: #facc15; color: #92400e; }
        .cs-type-btn.abandoned.active {
            background: linear-gradient(135deg, #facc15, #fbbf24);
            border-color: #ca8a04; color: #1c1917;
            box-shadow: 0 2px 8px rgba(234,179,8,0.35);
        }
        .cs-type-btn.dangerous { background: #fef2f2; border-color: #fca5a5; color: #991b1b; }
        .cs-type-btn.dangerous.active {
            background: linear-gradient(135deg, #ef4444, #dc2626);
            border-color: #b91c1c; color: #fff;
            box-shadow: 0 2px 8px rgba(220,38,38,0.35);
        }

        /* Export button */
        .cs-btn-excel {
            display: inline-flex; align-items: center; gap: 7px;
            padding: 8px 18px; border-radius: 10px;
            background: linear-gradient(135deg, #059669, #047857);
            color: #fff; font-size: 0.85rem; font-weight: 700;
            text-decoration: none; white-space: nowrap;
            box-shadow: 0 2px 8px rgba(5,150,105,0.3);
            transition: all 0.2s ease;
        }
        .cs-btn-excel:hover {
            background: linear-gradient(135deg, #047857, #065f46);
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(5,150,105,0.4);
        }

        /* Stat cards */
        .cs-stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(180px,1fr)); gap: 14px; margin-bottom: 20px; }
        .cs-stat-box {
            background: #fff; border-radius: 12px; border: 1px solid #e2e8f0;
            padding: 14px 18px; box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        }
        .cs-stat-title { font-size: 0.78rem; color: #64748b; font-weight: 600; margin-bottom: 6px; }
        .cs-stat-value { font-size: 1.5rem; font-weight: 900; }

        /* Table */
        .cs-card {
            background: #fff; border-radius: 16px; border: 1px solid #e2e8f0;
            box-shadow: 0 4px 16px rgba(0,0,0,0.05); overflow: hidden; margin-bottom: 20px;
        }
        .cs-card-header {
            background: linear-gradient(135deg, #0f172a, #1e3a8a);
            color: #fff; padding: 16px 24px; display: flex; justify-content: space-between;
            align-items: center; gap: 12px; flex-wrap: wrap;
        }
        .cs-card-header h3 { margin: 0; font-size: 1.1rem; font-weight: 800; }
        .cs-table-wrap { overflow-x: auto; }
        .cs-table {
            width: 100%; border-collapse: collapse; font-size: 0.82rem;
            direction: rtl; min-width: 900px;
        }
        .cs-table th {
            background: #facc15; color: #1c1917; font-weight: 800;
            padding: 10px 8px; text-align: center; border: 1px solid #d4a500;
            white-space: nowrap; font-size: 0.8rem;
        }
        .cs-table th:first-child { text-align: right; min-width: 160px; background: #fde68a; }
        .cs-table td {
            padding: 8px 8px; border: 1px solid #e2e8f0; text-align: center;
            color: #1e293b; font-variant-numeric: tabular-nums;
        }
        .cs-table td:first-child { text-align: right; font-weight: 700; white-space: nowrap; }
        .cs-table tbody tr:nth-child(even) { background: #f8fafc; }
        .cs-table tbody tr:hover { background: #fefce8; }

        /* Government / Private rows */
        .cs-row-gov td:first-child { background: #bfdbfe; color: #1e40af; }
        .cs-row-private td:first-child { background: #bbf7d0; color: #065f46; }

        /* Total rows */
        .cs-row-gov-total td { background: #eff6ff; font-weight: 700; color: #1e40af; }
        .cs-row-private-total td { background: #f0fdf4; font-weight: 700; color: #065f46; }
        .cs-row-grand-total td {
            background: #0f172a; color: #ffffff; font-weight: 900; font-size: 0.9rem;
            border-color: #334155;
        }
        .cs-row-grand-total td:first-child { background: #1e293b; }

        .cs-badge-gov {
            display: inline-block; padding: 2px 8px; border-radius: 6px;
            font-size: 0.72rem; font-weight: 700; background: #bfdbfe; color: #1e40af;
        }
        .cs-badge-private {
            display: inline-block; padding: 2px 8px; border-radius: 6px;
            font-size: 0.72rem; font-weight: 700; background: #bbf7d0; color: #065f46;
        }
        .cs-no-data {
            text-align: center; padding: 60px 20px; color: #94a3b8;
            font-size: 1rem; font-weight: 600;
        }
    </style>

    <?php
        $d          = $this->matrixData;
        $months     = $this->months;
        $fiscalYears = $this->fiscalYears;
        $years      = $d['years'];
        $entities   = $d['entities'];
        $totalByYear = $d['totalByYear'];
        $typeLabel  = $this->selectedContainerType === 'abandoned' ? 'الحاويات المتخلفة' : 'الحاويات الخطرة';
        $currMonth  = $months->firstWhere('id', $this->selectedMonthId);
        $currYear   = $fiscalYears->firstWhere('id', $this->selectedFiscalYearId);
        $monthLabel = $currMonth ? "{$currMonth->month_number} - {$currMonth->name_ar}" : '';
        $yearLabel  = $currYear ? $currYear->year : '';
    ?>

    <div class="cs-dashboard">

        
        <div class="cs-filter-bar">

            
            <div class="cs-filter-left">

                
                <button wire:click="$set('selectedContainerType','abandoned')"
                    class="cs-type-btn abandoned <?php echo e($this->selectedContainerType === 'abandoned' ? 'active' : ''); ?>">
                    📦 متخلفة
                </button>
                <button wire:click="$set('selectedContainerType','dangerous')"
                    class="cs-type-btn dangerous <?php echo e($this->selectedContainerType === 'dangerous' ? 'active' : ''); ?>">
                    ⚠️ خطرة
                </button>

                <div class="cs-filter-divider"></div>

                
                <div class="cs-field">
                    <span class="cs-field-icon">🏗️</span>
                    <span class="cs-field-label">الميناء:</span>
                    <select wire:model.live="selectedPortId" class="cs-select">
                        <option value="">جميع الموانئ</option>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $this->ports; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $port): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                            <option value="<?php echo e($port->id); ?>"><?php echo e($port->name_ar); ?></option>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                    </select>
                </div>

                
                <div class="cs-field">
                    <span class="cs-field-icon">📆</span>
                    <span class="cs-field-label">السنة:</span>
                    <select wire:model.live="selectedFiscalYearId" class="cs-select">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $this->fiscalYears; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $fy): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                            <option value="<?php echo e($fy->id); ?>"><?php echo e($fy->year); ?> <?php echo e($fy->is_current ? '(الحالية)' : ''); ?></option>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                    </select>
                </div>

                
                <div class="cs-field">
                    <span class="cs-field-icon">📅</span>
                    <span class="cs-field-label">الشهر:</span>
                    <select wire:model.live="selectedMonthId" class="cs-select">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $this->months; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $m): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                            <option value="<?php echo e($m->id); ?>"><?php echo e($m->month_number); ?> - <?php echo e($m->name_ar); ?></option>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                    </select>
                </div>
            </div>

            
            <div class="cs-filter-right">
                <a href="<?php echo e(route('admin.containers.export-excel', [
                        'type'           => $this->selectedContainerType,
                        'fiscal_year_id' => $this->selectedFiscalYearId,
                        'month_id'       => $this->selectedMonthId,
                        'port'           => $this->selectedPortId,
                    ])); ?>"
                    target="_blank"
                    class="cs-btn-excel"
                    title="تصدير إلى Excel">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                    تصدير Excel
                </a>
            </div>
        </div>

        
        <div class="cs-stats-grid">
            <div class="cs-stat-box">
                <div class="cs-stat-title">📦 إجمالي <?php echo e($typeLabel); ?></div>
                <div class="cs-stat-value" style="color:#1e3a8a;">
                    <?php echo e(number_format($d['grandTotal'])); ?>

                </div>
            </div>
            <div class="cs-stat-box">
                <div class="cs-stat-title">🏛️ القطاع الحكومي</div>
                <div class="cs-stat-value" style="color:#1d4ed8;">
                    <?php echo e(number_format($d['govTotal'])); ?>

                </div>
            </div>
            <div class="cs-stat-box">
                <div class="cs-stat-title">🏢 القطاع الخاص</div>
                <div class="cs-stat-value" style="color:#065f46;">
                    <?php echo e(number_format($d['privateTotal'])); ?>

                </div>
            </div>
            <div class="cs-stat-box">
                <div class="cs-stat-title">📅 التقرير</div>
                <div class="cs-stat-value" style="color:#92400e; font-size:1.1rem;">
                    <?php echo e($monthLabel); ?> (<?php echo e($yearLabel); ?>)
                </div>
            </div>
        </div>

        
        <div class="cs-card">
            <div class="cs-card-header">
                <h3>📋 موقف <?php echo e($typeLabel); ?> لغاية شهر <?php echo e($monthLabel); ?> لسنة <?php echo e($yearLabel); ?></h3>
            </div>

            <div class="cs-table-wrap">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(empty($years)): ?>
                    <div class="cs-no-data">
                        ⚠️ لا توجد بيانات لهذا الشهر والسنة — يرجى إدخال البيانات من صفحة سجلات الحاويات
                    </div>
                <?php else: ?>
                <table class="cs-table">
                    <thead>
                        <tr>
                            <th>ت — عائدية الحاويات</th>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $years; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $y): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                <th><?php echo e(is_numeric($y) ? "خلال عام {$y}" : $y); ?></th>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                            <th style="background:#0f172a;color:#fff;">المجموع</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                            $govRowCount = 1;
                            $govTotalByYear = array_fill_keys($years, 0);
                            $govGrandTotal = 0;
                            $privRowCount = 1;
                            $privTotalByYear = array_fill_keys($years, 0);
                            $privGrandTotal = 0;
                        ?>

                        
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $entities; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $eid => $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($row['entity']->entity_type === 'government'): ?>
                                <tr class="cs-row-gov">
                                    <td>
                                        <span class="cs-badge-gov"><?php echo e($govRowCount++); ?></span>
                                        <?php echo e($row['entity']->name_ar); ?>

                                    </td>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $years; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $y): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                        <?php $val = $row['years'][$y] ?? 0; $govTotalByYear[$y] += $val; $govGrandTotal += $val; ?>
                                        <td style="<?php echo e($val > 0 ? 'color:#991b1b;font-weight:800;' : 'color:#cbd5e1;'); ?>">
                                            <?php echo e($val > 0 ? number_format($val) : '0'); ?>

                                        </td>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                                    <td style="font-weight:800;color:#991b1b;background:#fef08a;"><?php echo e($row['row_total'] > 0 ? number_format($row['row_total']) : '0'); ?></td>
                                </tr>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>

                        
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $entities; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $eid => $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($row['entity']->entity_type === 'private'): ?>
                                <tr class="cs-row-private">
                                    <td>
                                        <span class="cs-badge-private">خاص</span>
                                        <?php echo e($row['entity']->name_ar); ?>

                                    </td>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $years; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $y): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                        <?php $val = $row['years'][$y] ?? 0; $privTotalByYear[$y] += $val; $privGrandTotal += $val; ?>
                                        <td style="<?php echo e($val > 0 ? 'color:#991b1b;font-weight:800;' : 'color:#cbd5e1;'); ?>">
                                            <?php echo e($val > 0 ? number_format($val) : '0'); ?>

                                        </td>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                                    <td style="font-weight:800;color:#991b1b;background:#fef08a;"><?php echo e($row['row_total'] > 0 ? number_format($row['row_total']) : '0'); ?></td>
                                </tr>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                    </tbody>
                    <tfoot>
                        
                        <tr class="cs-row-grand-total" style="background:#fde047;color:#1c1917;">
                            <td style="background:#facc15;color:#1c1917;font-weight:900;">المجموع</td>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $years; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $y): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                <?php $tot = $totalByYear[$y] ?? 0; ?>
                                <td style="font-weight:900;<?php echo e($tot > 0 ? 'color:#991b1b;' : 'color:#94a3b8;'); ?>">
                                    <?php echo e(number_format($tot)); ?>

                                </td>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                            <td style="font-weight:900;color:#991b1b;font-size:1rem;"><?php echo e(number_format($d['grandTotal'])); ?></td>
                        </tr>
                    </tfoot>
                </table>

                
                <div style="display:flex;gap:20px;padding:16px 24px;background:#f8fafc;border-top:1px solid #e2e8f0;">
                    <div style="background:#eff6ff;border-radius:10px;padding:10px 18px;border:1px solid #bfdbfe;">
                        <span style="font-size:0.8rem;color:#64748b;font-weight:600;">🏛️ قطاع حكومي</span>
                        <div style="font-size:1.2rem;font-weight:900;color:#1e40af;"><?php echo e(number_format($d['govTotal'])); ?></div>
                    </div>
                    <div style="background:#f0fdf4;border-radius:10px;padding:10px 18px;border:1px solid #bbf7d0;">
                        <span style="font-size:0.8rem;color:#64748b;font-weight:600;">🏢 قطاع خاص</span>
                        <div style="font-size:1.2rem;font-weight:900;color:#065f46;"><?php echo e(number_format($d['privateTotal'])); ?></div>
                    </div>
                    <div style="background:#fef9c3;border-radius:10px;padding:10px 18px;border:1px solid #fde047;">
                        <span style="font-size:0.8rem;color:#64748b;font-weight:600;">📊 الكلي</span>
                        <div style="font-size:1.2rem;font-weight:900;color:#92400e;"><?php echo e(number_format($d['grandTotal'])); ?></div>
                    </div>
                </div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
        </div>

    </div>
 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal166a02a7c5ef5a9331faf66fa665c256)): ?>
<?php $attributes = $__attributesOriginal166a02a7c5ef5a9331faf66fa665c256; ?>
<?php unset($__attributesOriginal166a02a7c5ef5a9331faf66fa665c256); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal166a02a7c5ef5a9331faf66fa665c256)): ?>
<?php $component = $__componentOriginal166a02a7c5ef5a9331faf66fa665c256; ?>
<?php unset($__componentOriginal166a02a7c5ef5a9331faf66fa665c256); ?>
<?php endif; ?>
<?php /**PATH D:\Projects\ports-system\resources\views/filament/pages/container-status-matrix.blade.php ENDPATH**/ ?>