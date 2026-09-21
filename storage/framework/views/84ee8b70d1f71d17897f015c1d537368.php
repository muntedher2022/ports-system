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

    <?php
        $d = $this->data;
        $companyTable = $d['tables']['company'] ?? null;
    ?>

    <style>
        .gcpi-dashboard {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            color: #1e293b;
            direction: rtl;
        }
        .gcpi-card {
            background: #ffffff;
            border-radius: 16px;
            border: 1px solid #e2e8f0;
            box-shadow: 0 4px 20px -2px rgba(0, 0, 0, 0.05);
            margin-bottom: 28px;
            overflow: hidden;
        }
        .gcpi-card-header {
            background: linear-gradient(135deg, #064e3b 0%, #0f766e 100%);
            color: #ffffff;
            padding: 20px 24px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .gcpi-card-header h3 {
            margin: 0;
            font-size: 1.2rem;
            font-weight: 800;
        }
        .gcpi-filter-bar {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 14px;
            padding: 16px 20px;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 16px;
            margin-bottom: 24px;
        }
        .gcpi-filter-item {
            display: flex;
            flex-direction: column;
            gap: 6px;
        }
        .gcpi-filter-label {
            font-weight: 700;
            font-size: 0.85rem;
            color: #475569;
        }
        .gcpi-select {
            background-color: #ffffff;
            border: 1.5px solid #cbd5e1;
            border-radius: 10px;
            padding: 10px 14px;
            font-size: 0.95rem;
            font-weight: 700;
            color: #0f172a;
            outline: none;
            cursor: pointer;
            width: 100%;
            transition: all 0.2s;
        }
        .gcpi-select:focus {
            border-color: #059669;
            box-shadow: 0 0 0 3px rgba(5, 150, 105, 0.15);
        }
        .gcpi-table-container {
            width: 100%;
            overflow-x: auto;
        }
        .gcpi-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.9rem;
            text-align: right;
            white-space: nowrap;
        }
        .gcpi-table thead tr {
            background: #f1f5f9;
            color: #334155;
            border-bottom: 2px solid #cbd5e1;
        }
        .gcpi-table th {
            padding: 14px 16px;
            font-weight: 800;
            font-size: 0.85rem;
            text-align: center;
        }
        .gcpi-table th:first-child, .gcpi-table td:first-child {
            text-align: right;
            font-weight: 800;
        }
        .gcpi-table td {
            padding: 12px 16px;
            border-bottom: 1px solid #e2e8f0;
            color: #1e293b;
            font-variant-numeric: tabular-nums;
            text-align: center;
        }
        .gcpi-table tbody tr:nth-child(even) {
            background-color: #f8fafc;
        }
        .gcpi-table tbody tr:hover {
            background-color: #f0fdf4;
        }
        .gcpi-table tfoot tr {
            background: #064e3b;
            color: #ffffff;
            font-weight: 800;
            font-size: 0.95rem;
        }
        .gcpi-table tfoot td {
            padding: 16px;
            border: none;
            color: #ffffff;
            font-variant-numeric: tabular-nums;
        }
        .gcpi-badge-pill {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 9999px;
            font-weight: 800;
            font-size: 0.8rem;
            font-variant-numeric: tabular-nums;
        }
        .gcpi-badge-positive {
            background-color: #d1fae5;
            color: #065f46;
        }
        .gcpi-badge-negative {
            background-color: #fee2e2;
            color: #991b1b;
        }
        .gcpi-centers-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(460px, 1fr));
            gap: 20px;
        }
        .gcpi-btn-pdf {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            background: linear-gradient(135deg, #dc2626 0%, #991b1b 100%);
            color: #ffffff !important;
            font-weight: 700;
            font-size: 0.9rem;
            padding: 10px 20px;
            border-radius: 10px;
            text-decoration: none;
            box-shadow: 0 2px 8px rgba(220, 38, 38, 0.25);
            transition: all 0.2s ease;
            height: 44px;
        }
        .gcpi-btn-pdf:hover {
            background: linear-gradient(135deg, #b91c1c 0%, #7f1d1d 100%);
            box-shadow: 0 4px 14px rgba(220, 38, 38, 0.35);
            transform: translateY(-1px);
        }
    </style>

    <div class="gcpi-dashboard">
        <!-- شريط الفلاتر والاختيار والطباعة -->
        <div class="gcpi-filter-bar" style="grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));">
            <div class="gcpi-filter-item">
                <label class="gcpi-filter-label">📅 سنة المقارنة (الحالية):</label>
                <select wire:model.live="currFiscalYearId" class="gcpi-select">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $d['fiscalYears']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $fy): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                        <option value="<?php echo e($fy->id); ?>"><?php echo e($fy->year); ?> <?php echo e($fy->is_current ? '(الحالية)' : ''); ?></option>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                </select>
            </div>

            <div class="gcpi-filter-item">
                <label class="gcpi-filter-label">📅 سنة الأساس (السابقة):</label>
                <select wire:model.live="prevFiscalYearId" class="gcpi-select">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $d['fiscalYears']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $fy): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                        <option value="<?php echo e($fy->id); ?>"><?php echo e($fy->year); ?></option>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                </select>
            </div>

            <div class="gcpi-filter-item" style="justify-content: flex-end;">
                <label class="gcpi-filter-label" style="visibility: hidden;">طباعة</label>
                <a href="<?php echo e(route('admin.reports.revenue-comparison.pdf', ['prev_year_id' => $this->prevFiscalYearId, 'curr_year_id' => $this->currFiscalYearId])); ?>" target="_blank" class="gcpi-btn-pdf">
                    📄 تصدير تقرير PDF
                </a>
            </div>
        </div>

        <!-- 1. جدول إجمالي الشركة ككل (الموانئ السبعة والمقر) -->
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($companyTable): ?>
            <div class="gcpi-card" style="border: 2px solid #059669;">
                <div class="gcpi-card-header">
                    <div>
                        <h3>🏢 <?php echo e($companyTable['center_name']); ?> (مقارنة <?php echo e($d['prevYear']); ?> مقابل <?php echo e($d['currYear']); ?>)</h3>
                        <div style="font-size: 0.85rem; opacity: 0.9; margin-top: 4px;">
                            المقارنة الشهرية والتراكمية للإيراد الكلي بالدينار العراقي (د.ع)
                        </div>
                    </div>
                    <div>
                        <span style="background: #ffffff; color: #064e3b; padding: 6px 14px; border-radius: 9999px; font-weight: 800; font-size: 0.82rem;">
                            المجموع الكلي للإيراد
                        </span>
                    </div>
                </div>

                <div class="gcpi-table-container">
                    <table class="gcpi-table">
                        <thead>
                            <tr>
                                <th style="text-align: right; min-width: 140px;">الشهر</th>
                                <th>سنة <?php echo e($d['prevYear']); ?> (د.ع)</th>
                                <th>سنة <?php echo e($d['currYear']); ?> (د.ع)</th>
                                <th>الفارق (د.ع)</th>
                                <th>نسبة التغير %</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $companyTable['rows']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                <tr>
                                    <td style="font-weight: 800; color: #0f172a;"><?php echo e($row['month_name']); ?></td>
                                    <td style="font-weight: 600; color: #475569;">
                                        <?php echo e($row['prev_val'] > 0 ? number_format($row['prev_val'], 0) : '—'); ?>

                                    </td>
                                    <td style="font-weight: 800; color: #047857; font-size: 0.98rem;">
                                        <?php echo e($row['curr_val'] > 0 ? number_format($row['curr_val'], 0) : '—'); ?>

                                    </td>
                                    <td>
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($row['diff'] != 0): ?>
                                            <span class="gcpi-badge-pill <?php echo e($row['diff'] >= 0 ? 'gcpi-badge-positive' : 'gcpi-badge-negative'); ?>">
                                                <?php echo e($row['diff'] > 0 ? '+' : ''); ?><?php echo e(number_format($row['diff'], 0)); ?>

                                            </span>
                                        <?php else: ?>
                                            <span style="color: #94a3b8;">—</span>
                                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($row['prev_val'] > 0 && $row['curr_val'] > 0): ?>
                                            <span class="gcpi-badge-pill <?php echo e($row['percent'] >= 0 ? 'gcpi-badge-positive' : 'gcpi-badge-negative'); ?>">
                                                <?php echo e($row['percent'] > 0 ? '+' : ''); ?><?php echo e($row['percent']); ?>%
                                            </span>
                                        <?php else: ?>
                                            <span style="color: #94a3b8;">—</span>
                                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    </td>
                                </tr>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                        </tbody>
                        <tfoot>
                            <tr>
                                <td>المجموع السنوي</td>
                                <td><?php echo e(number_format($companyTable['total_prev'], 0)); ?></td>
                                <td style="background: #047857; color: #a7f3d0;"><?php echo e(number_format($companyTable['total_curr'], 0)); ?></td>
                                <td>
                                    <?php echo e(($companyTable['total_diff'] > 0 ? '+' : '') . number_format($companyTable['total_diff'], 0)); ?>

                                </td>
                                <td>
                                    <span style="background: rgba(255,255,255,0.2); padding: 4px 10px; border-radius: 6px;">
                                        <?php echo e(($companyTable['total_pct'] > 0 ? '+' : '') . $companyTable['total_pct']); ?>%
                                    </span>
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

        <!-- 2. جداول كل مركز على حدة في شبكة كروت أنيقة -->
        <h3 style="font-size: 1.15rem; font-weight: 800; color: #0f172a; margin-bottom: 16px;">
            📊 مقارنة الإيراد لكل مركز وتشكيل على حدة
        </h3>

        <div class="gcpi-centers-grid">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $d['tables']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $table): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($key !== 'company'): ?>
                    <div class="gcpi-card">
                        <div class="gcpi-card-header" style="background: #1e293b; padding: 14px 20px;">
                            <h3 style="font-size: 0.95rem;">📊 <?php echo e($table['center_name']); ?></h3>
                            <span style="font-size: 0.78rem; opacity: 0.85;"><?php echo e($d['prevYear']); ?> مقابل <?php echo e($d['currYear']); ?></span>
                        </div>

                        <div class="gcpi-table-container">
                            <table class="gcpi-table" style="font-size: 0.82rem;">
                                <thead>
                                    <tr>
                                        <th style="text-align: right;">الشهر</th>
                                        <th><?php echo e($d['prevYear']); ?></th>
                                        <th><?php echo e($d['currYear']); ?></th>
                                        <th>الفارق</th>
                                        <th>التغير</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $table['rows']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                        <tr>
                                            <td style="font-weight: 700;"><?php echo e($row['month_name']); ?></td>
                                            <td><?php echo e($row['prev_val'] > 0 ? number_format($row['prev_val'], 0) : '—'); ?></td>
                                            <td style="font-weight: 800; color: #047857;"><?php echo e($row['curr_val'] > 0 ? number_format($row['curr_val'], 0) : '—'); ?></td>
                                            <td>
                                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($row['diff'] != 0): ?>
                                                    <span class="gcpi-badge-pill <?php echo e($row['diff'] >= 0 ? 'gcpi-badge-positive' : 'gcpi-badge-negative'); ?>" style="font-size: 0.75rem; padding: 2px 6px;">
                                                        <?php echo e($row['diff'] > 0 ? '+' : ''); ?><?php echo e(number_format($row['diff'], 0)); ?>

                                                    </span>
                                                <?php else: ?>
                                                    <span style="color: #94a3b8;">—</span>
                                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                            </td>
                                            <td>
                                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($row['prev_val'] > 0 && $row['curr_val'] > 0): ?>
                                                    <span style="font-weight: 700; font-size: 0.75rem; color: <?php echo e($row['percent'] >= 0 ? '#059669' : '#dc2626'); ?>;">
                                                        <?php echo e($row['percent'] > 0 ? '+' : ''); ?><?php echo e($row['percent']); ?>%
                                                    </span>
                                                <?php else: ?>
                                                    <span style="color: #94a3b8;">—</span>
                                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                                </tbody>
                                <tfoot>
                                    <tr style="background: #1e293b; color: #ffffff; font-weight: 800; font-size: 0.85rem;">
                                        <td>المجموع</td>
                                        <td><?php echo e(number_format($table['total_prev'], 0)); ?></td>
                                        <td style="color: #6ee7b7;"><?php echo e(number_format($table['total_curr'], 0)); ?></td>
                                        <td><?php echo e(($table['total_diff'] > 0 ? '+' : '') . number_format($table['total_diff'], 0)); ?></td>
                                        <td><?php echo e(($table['total_pct'] > 0 ? '+' : '') . $table['total_pct']); ?>%</td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
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
<?php /**PATH D:\Projects\ports-system\resources\views/filament/pages/revenue-comparison.blade.php ENDPATH**/ ?>