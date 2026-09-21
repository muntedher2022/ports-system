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
        $stats = $this->getStats();
    ?>

    <style>
        .gcpi-cargo-widget-wrapper {
            margin-top: 4px;
            direction: rtl;
        }
        .gcpi-cargo-section-title {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 10px;
            padding: 0 4px;
        }
        .gcpi-cargo-section-title h3 {
            font-size: 0.95rem;
            font-weight: 800;
            color: #1e293b;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .gcpi-cargo-stats-row {
            display: grid;
            grid-template-columns: repeat(1, minmax(0, 1fr));
            gap: 12px;
            width: 100%;
        }
        @media (min-width: 640px) {
            .gcpi-cargo-stats-row {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }
        @media (min-width: 1024px) {
            .gcpi-cargo-stats-row {
                grid-template-columns: repeat(4, minmax(0, 1fr));
            }
        }
        .gcpi-cargo-card {
            background: #ffffff;
            border-radius: 14px;
            border: 1px solid #e2e8f0;
            padding: 14px 16px;
            box-shadow: 0 4px 16px -2px rgba(0, 0, 0, 0.04);
            transition: all 0.2s ease;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            text-decoration: none;
        }
        .gcpi-cargo-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px -2px rgba(0, 0, 0, 0.08);
        }
        .cargo-card-amber {
            border-top: 4px solid #f59e0b;
            background: linear-gradient(180deg, #fffbeb 0%, #ffffff 100%);
        }
        .cargo-card-rose {
            border-top: 4px solid #f43f5e;
            background: linear-gradient(180deg, #fff1f2 0%, #ffffff 100%);
        }
        .cargo-card-cyan {
            border-top: 4px solid #0891b2;
            background: linear-gradient(180deg, #ecfeff 0%, #ffffff 100%);
        }
        .cargo-card-emerald {
            border-top: 4px solid #059669;
            background: linear-gradient(180deg, #ecfdf5 0%, #ffffff 100%);
        }
    </style>

    <div class="gcpi-cargo-widget-wrapper">
        <div class="gcpi-cargo-section-title">
            <h3>
                <span>📦</span>
                <span>موقف المواد والبضائع المتخلفة والخطرة (سنة <?php echo e($stats['currentYear']); ?>)</span>
            </h3>
            <a href="<?php echo e(url('/admin/cargo-status-matrix')); ?>" class="text-xs font-bold text-blue-600 hover:text-blue-800 flex items-center gap-1">
                <span>استعراض مصفوفة المواد والبضائع الشاملة</span>
                <span dir="ltr">←</span>
            </a>
        </div>

        <div class="gcpi-cargo-stats-row">
            <!-- 1. المواد والبضائع المتخلفة -->
            <a href="<?php echo e(url('/admin/cargo-status-matrix?type=abandoned')); ?>" class="gcpi-cargo-card cargo-card-amber">
                <div class="flex items-center justify-between mb-1.5">
                    <span class="text-xs font-bold text-amber-900">إجمالي المواد والطرود المتخلفة</span>
                    <span class="p-1.5 rounded-lg bg-amber-100 text-amber-700">📦</span>
                </div>
                <div class="text-lg font-extrabold text-gray-900 mb-1 font-mono">
                    <?php echo e(number_format($stats['abandonedTotal'])); ?> <span class="text-xs font-normal text-gray-500">طرد/مادة</span>
                </div>
                <div class="text-[0.72rem] text-gray-600 font-semibold flex items-center justify-between">
                    <span>🏛️ حكومي: <?php echo e(number_format($stats['abandonedGov'])); ?></span>
                    <span>🏢 خاص: <?php echo e(number_format($stats['abandonedPrivate'])); ?></span>
                </div>
            </a>

            <!-- 2. المواد والبضائع الخطرة -->
            <a href="<?php echo e(url('/admin/cargo-status-matrix?type=dangerous')); ?>" class="gcpi-cargo-card cargo-card-rose">
                <div class="flex items-center justify-between mb-1.5">
                    <span class="text-xs font-bold text-rose-900">إجمالي المواد والطرود الخطرة</span>
                    <span class="p-1.5 rounded-lg bg-rose-100 text-rose-700">⚠️</span>
                </div>
                <div class="text-lg font-extrabold text-rose-700 mb-1 font-mono">
                    <?php echo e(number_format($stats['dangerousTotal'])); ?> <span class="text-xs font-normal text-rose-500">طرد/مادة</span>
                </div>
                <div class="text-[0.72rem] text-rose-600 font-semibold">
                    <span>🔴 رصيد مواد كيميائية وخطرة بالموانئ</span>
                </div>
            </a>

            <!-- 3. الميناء الأكثر تسجيلاً -->
            <div class="gcpi-cargo-card cargo-card-cyan">
                <div class="flex items-center justify-between mb-1.5">
                    <span class="text-xs font-bold text-cyan-900">أعلى ميناء في رصيد المواد</span>
                    <span class="p-1.5 rounded-lg bg-cyan-100 text-cyan-700">⚓</span>
                </div>
                <div class="text-base font-extrabold text-cyan-950 mb-1 truncate">
                    <?php echo e($stats['topPortName']); ?>

                </div>
                <div class="text-[0.72rem] text-cyan-600 font-semibold">
                    <span>إجمالي الرصيد: <?php echo e(number_format($stats['topPortCount'])); ?> طرد/مادة</span>
                </div>
            </div>

            <!-- 4. عدد الجهات والوزارات المسجلة -->
            <a href="<?php echo e(url('/admin/cargo-entities')); ?>" class="gcpi-cargo-card cargo-card-emerald">
                <div class="flex items-center justify-between mb-1.5">
                    <span class="text-xs font-bold text-emerald-900">الجهات والوزارات العائدة للمواد</span>
                    <span class="p-1.5 rounded-lg bg-emerald-100 text-emerald-700">🏛️</span>
                </div>
                <div class="text-lg font-extrabold text-emerald-900 mb-1 font-mono">
                    <?php echo e(number_format($stats['entitiesCount'])); ?> <span class="text-xs font-normal text-emerald-600">جهة / قطاع</span>
                </div>
                <div class="text-[0.72rem] text-emerald-600 font-semibold">
                    <span>اضغط لإدارة وتحديث بيانات جهات المواد</span>
                </div>
            </a>
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
<?php /**PATH D:\Projects\ports-system\resources\views/filament/widgets/cargo-stats-overview-widget.blade.php ENDPATH**/ ?>