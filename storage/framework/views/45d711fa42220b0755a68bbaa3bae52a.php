<?php if (isset($component)) { $__componentOriginalb525200bfa976483b4eaa0b7685c6e24 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalb525200bfa976483b4eaa0b7685c6e24 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'filament-widgets::components.widget','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('filament-widgets::widget'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

    <?php
        $data = $this->getIntelligenceData();
    ?>

    <style>
        .gcpi-risk-wrapper {
            margin-top: 6px;
            direction: rtl;
        }
        .gcpi-risk-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 12px;
            padding: 0 4px;
        }
        .gcpi-risk-header h3 {
            font-size: 0.98rem;
            font-weight: 800;
            color: #0f172a;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .gcpi-risk-grid {
            display: grid;
            grid-template-columns: repeat(1, minmax(0, 1fr));
            gap: 14px;
            width: 100%;
        }
        @media (min-width: 768px) {
            .gcpi-risk-grid {
                grid-template-columns: repeat(3, minmax(0, 1fr));
            }
        }
        .gcpi-risk-panel {
            background: #ffffff;
            border-radius: 16px;
            border: 1px solid #e2e8f0;
            padding: 16px 18px;
            box-shadow: 0 4px 20px -2px rgba(0, 0, 0, 0.05);
            transition: all 0.25s ease;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }
        .gcpi-risk-panel:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 24px -2px rgba(0, 0, 0, 0.09);
        }
        .panel-iso {
            border-top: 4px solid #3b82f6;
            background: linear-gradient(180deg, #f8fafc 0%, #ffffff 100%);
        }
        .panel-danger {
            border-top: 4px solid #ef4444;
            background: linear-gradient(180deg, #fff5f5 0%, #ffffff 100%);
        }
        .panel-aging {
            border-top: 4px solid #8b5cf6;
            background: linear-gradient(180deg, #faf5ff 0%, #ffffff 100%);
        }
        .gcpi-progress-bar {
            width: 100%;
            height: 7px;
            background-color: #e2e8f0;
            border-radius: 9999px;
            overflow: hidden;
            margin: 8px 0;
        }
        .gcpi-progress-fill {
            height: 100%;
            border-radius: 9999px;
            transition: width 0.5s ease;
        }
    </style>

    <div class="gcpi-risk-wrapper">
        <div class="gcpi-risk-header">
            <h3>
                <span>🛡️</span>
                <span>مركز الذكاء الرقابي ومؤشرات الامتثال التشغيلي (سنة <?php echo e($data['currentYear']); ?>)</span>
            </h3>
            <div class="flex items-center gap-2">
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800 border border-emerald-300">
                    <span>مؤشر السلامة العام:</span>
                    <span class="font-mono text-sm"><?php echo e($data['healthScore']); ?>%</span>
                </span>
            </div>
        </div>

        <div class="gcpi-risk-grid">
            <!-- 1. مؤشر جودة البيانات وتدقيق ISO 6346 -->
            <div class="gcpi-risk-panel panel-iso">
                <div>
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-xs font-bold text-blue-900">مؤشر مطابقة معيار ISO 6346</span>
                        <span class="p-1.5 rounded-lg bg-blue-100 text-blue-700">🔍</span>
                    </div>
                    <div class="flex items-baseline justify-between mb-1">
                        <span class="text-2xl font-black text-blue-700 font-mono"><?php echo e($data['isoStats']['compliance_rate']); ?>%</span>
                        <span class="text-xs text-gray-500 font-semibold"><?php echo e(number_format($data['isoStats']['valid_count'])); ?> مطابقة من أصل <?php echo e(number_format($data['isoStats']['total_examined'])); ?></span>
                    </div>

                    <div class="gcpi-progress-bar">
                        <div class="gcpi-progress-fill bg-blue-600" style="width: <?php echo e($data['isoStats']['compliance_rate']); ?>%"></div>
                    </div>
                </div>

                <div class="pt-3 border-t border-slate-100 flex items-center justify-between text-xs font-semibold">
                    <span class="text-amber-700">⚠️ ملاحظات تدقيق / نواقص: <?php echo e(number_format($data['isoStats']['invalid_count'])); ?></span>
                    <a href="<?php echo e(url('/admin/container-items')); ?>" class="text-blue-600 hover:text-blue-800 underline">فحص القيود ←</a>
                </div>
            </div>

            <!-- 2. كثافة الخطورة (Dangerous Density) -->
            <div class="gcpi-risk-panel panel-danger">
                <div>
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-xs font-bold text-rose-900">كثافة المواد والحاويات الخطرة</span>
                        <span class="p-1.5 rounded-lg bg-rose-100 text-rose-700">⚠️</span>
                    </div>
                    <div class="flex items-baseline justify-between mb-1">
                        <span class="text-2xl font-black text-rose-700 font-mono"><?php echo e(number_format($data['dangerousCont'] + $data['dangerousCargo'])); ?></span>
                        <span class="text-xs text-rose-600 font-semibold">رصيد خطر إجمالي</span>
                    </div>

                    <div class="gcpi-progress-bar">
                        <div class="gcpi-progress-fill bg-rose-500" style="width: <?php echo e(min(100, $data['contDangerRate'] * 3)); ?>%"></div>
                    </div>
                </div>

                <div class="pt-3 border-t border-rose-100 flex items-center justify-between text-xs font-semibold text-rose-800">
                    <span>حاويات خطرة: <?php echo e(number_format($data['dangerousCont'])); ?> (<?php echo e($data['contDangerRate']); ?>%)</span>
                    <span>طرود بضائع: <?php echo e(number_format($data['dangerousCargo'])); ?></span>
                </div>
            </div>

            <!-- 3. الأرصدة المعمرة والتقادم (> 3 سنوات) -->
            <div class="gcpi-risk-panel panel-aging">
                <div>
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-xs font-bold text-purple-900">الأرصدة المعمرة والراكدة (أعوام سابقة)</span>
                        <span class="p-1.5 rounded-lg bg-purple-100 text-purple-700">⏳</span>
                    </div>
                    <div class="flex items-baseline justify-between mb-1">
                        <span class="text-2xl font-black text-purple-700 font-mono"><?php echo e(number_format($data['oldContCount'] + $data['oldCargoCount'])); ?></span>
                        <span class="text-xs text-purple-600 font-semibold">متروكة منذ سنوات سابقة</span>
                    </div>

                    <div class="gcpi-progress-bar">
                        <div class="gcpi-progress-fill bg-purple-600" style="width: <?php echo e(min(100, (($data['oldContCount'] + $data['oldCargoCount']) > 0 ? 65 : 10))); ?>%"></div>
                    </div>
                </div>

                <div class="pt-3 border-t border-purple-100 flex items-center justify-between text-xs font-semibold text-purple-800">
                    <span>حاويات قديمة: <?php echo e(number_format($data['oldContCount'])); ?></span>
                    <span>بضائع قديمة: <?php echo e(number_format($data['oldCargoCount'])); ?></span>
                </div>
            </div>
        </div>
    </div>
 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalb525200bfa976483b4eaa0b7685c6e24)): ?>
<?php $attributes = $__attributesOriginalb525200bfa976483b4eaa0b7685c6e24; ?>
<?php unset($__attributesOriginalb525200bfa976483b4eaa0b7685c6e24); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalb525200bfa976483b4eaa0b7685c6e24)): ?>
<?php $component = $__componentOriginalb525200bfa976483b4eaa0b7685c6e24; ?>
<?php unset($__componentOriginalb525200bfa976483b4eaa0b7685c6e24); ?>
<?php endif; ?>
<?php /**PATH D:\Projects\ports-system\resources\views/filament/widgets/regulatory-risk-intelligence-widget.blade.php ENDPATH**/ ?>