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
        $m = $this->getAgencyMetrics();
    ?>

    <style>
        .gcpi-agency-container {
            direction: rtl;
            font-family: 'IBM Plex Sans Arabic', sans-serif;
            margin-top: 4px;
            margin-bottom: 12px;
            width: 100%;
        }
        .gcpi-agency-header {
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
            border-radius: 16px 16px 0 0;
            padding: 14px 20px;
            color: #ffffff;
            border: 1px solid #334155;
            border-bottom: none;
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
        }
        .gcpi-agency-pulse-dot {
            width: 8px;
            height: 8px;
            background-color: #10b981;
            border-radius: 50%;
            display: inline-block;
            box-shadow: 0 0 8px #10b981;
            animation: pulse-glow 2s infinite;
        }
        @keyframes pulse-glow {
            0%, 100% { opacity: 1; transform: scale(1); }
            50% { opacity: 0.4; transform: scale(1.3); }
        }
        .gcpi-agency-body {
            background: #ffffff;
            border-radius: 0 0 16px 16px;
            border: 1px solid #e2e8f0;
            border-top: none;
            padding: 16px 18px;
            box-shadow: 0 6px 20px -2px rgba(0, 0, 0, 0.04);
        }
        .gcpi-agency-grid-kpi {
            display: grid;
            grid-template-columns: repeat(1, minmax(0, 1fr));
            gap: 12px;
            margin-bottom: 14px;
        }
        @media (min-width: 640px) {
            .gcpi-agency-grid-kpi {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }
        @media (min-width: 1024px) {
            .gcpi-agency-grid-kpi {
                grid-template-columns: repeat(4, minmax(0, 1fr));
            }
        }
        .gcpi-agency-card {
            background: #f8fafc;
            border: 1.5px solid #e2e8f0;
            border-radius: 12px;
            padding: 13px 15px;
            transition: all 0.2s ease;
            position: relative;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }
        .gcpi-agency-card:hover {
            border-color: #3b82f6;
            background: #ffffff;
            transform: translateY(-2px);
            box-shadow: 0 6px 16px -2px rgba(59, 130, 246, 0.12);
        }
        .gcpi-agency-card::before {
            content: '';
            position: absolute;
            top: 0;
            right: 0;
            left: 0;
            height: 3px;
        }
        .agency-card-cyan::before { background: linear-gradient(90deg, #06b6d4, #3b82f6); }
        .agency-card-emerald::before { background: linear-gradient(90deg, #10b981, #059669); }
        .agency-card-indigo::before { background: linear-gradient(90deg, #6366f1, #8b5cf6); }
        .agency-card-amber::before { background: linear-gradient(90deg, #f59e0b, #d97706); }

        .gcpi-agency-dominance-row {
            display: grid;
            grid-template-columns: repeat(1, minmax(0, 1fr));
            gap: 10px;
            padding-top: 12px;
            border-top: 1px dashed #e2e8f0;
        }
        @media (min-width: 640px) {
            .gcpi-agency-dominance-row {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }
        @media (min-width: 1024px) {
            .gcpi-agency-dominance-row {
                grid-template-columns: repeat(4, minmax(0, 1fr));
            }
        }
        .gcpi-dom-box {
            background: #f8fafc;
            border-radius: 10px;
            padding: 9px 12px;
            display: flex;
            align-items: center;
            gap: 10px;
            border: 1px solid #e2e8f0;
            transition: all 0.2s ease;
        }
        .gcpi-dom-box:hover {
            background: #f1f5f9;
            border-color: #cbd5e1;
        }
    </style>

    <div class="gcpi-agency-container">
        <!-- Agency Header -->
        <div class="gcpi-agency-header">
            <div class="flex items-center gap-3">
                <span class="p-2 rounded-xl bg-slate-800 text-blue-400 border border-slate-700 flex items-center justify-center shadow-inner" style="width: 38px; height: 38px; min-width: 38px;">
                    <svg style="width: 20px; height: 20px; min-width: 20px; max-width: 20px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                    </svg>
                </span>
                <div>
                    <div class="flex items-center gap-2">
                        <h2 class="text-sm font-extrabold tracking-wide text-white">مركز التحليل والاستخبارات اللوجستية للموانئ</h2>
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[0.68rem] font-bold bg-emerald-950 text-emerald-300 border border-emerald-800">
                            <span class="gcpi-agency-pulse-dot"></span>
                            <span>تحليل حي ومباشر</span>
                        </span>
                    </div>
                    <p class="text-[0.72rem] text-slate-400 font-medium">مؤشرات الكفاءة النوعية، التوقعات التراكمية، ومصفوفة قيادة وتصدر الموانئ لسنة <?php echo e($m['currentYear']); ?></p>
                </div>
            </div>

            <!-- Forecast Mini Pill -->
            <div class="flex items-center gap-3 text-xs font-semibold bg-slate-800/90 px-3.5 py-1.5 rounded-xl border border-slate-700">
                <div class="text-right">
                    <span class="text-slate-400 text-[0.68rem] ml-1">التوقع السنوي لكامل العام (Run-Rate):</span>
                    <span class="text-amber-400 font-mono font-bold"><?php echo e(number_format($m['projectedAnnualTons'])); ?> طن</span>
                    <span class="text-slate-500 mx-1">|</span>
                    <span class="text-emerald-400 font-mono font-bold"><?php echo e(number_format($m['projectedAnnualRev'] / 1000000000, 2)); ?> مليار د.ع</span>
                </div>
            </div>
        </div>

        <!-- Agency Body -->
        <div class="gcpi-agency-body">
            <!-- 4 Strategic Agency KPIs -->
            <div class="gcpi-agency-grid-kpi">
                <!-- 1. العائد المالي للطن الواحد -->
                <div class="gcpi-agency-card agency-card-cyan">
                    <div class="flex items-center justify-between mb-1.5">
                        <span class="text-xs font-bold text-slate-700">معدل العائد المالي للطن (Yield)</span>
                        <span class="text-[0.68rem] px-2 py-0.5 rounded bg-cyan-50 text-cyan-700 font-bold border border-cyan-200">د.ع / طن</span>
                    </div>
                    <div class="text-lg font-black text-slate-900 font-mono mb-1">
                        <?php echo e(number_format($m['revPerTon'])); ?> <span class="text-xs font-medium text-slate-500">د.ع/طن</span>
                    </div>
                    <div class="text-[0.72rem] font-semibold flex items-center gap-1.5 <?php echo e($m['revPerTonDiff'] >= 0 ? 'text-emerald-600' : 'text-rose-600'); ?>">
                        <span><?php echo e($m['revPerTonDiff'] >= 0 ? '▲ +' : '▼ '); ?><?php echo e($m['revPerTonDiff']); ?>%</span>
                        <span class="text-slate-500 font-normal">مقارنة مع سنة <?php echo e($m['prevYear']); ?></span>
                    </div>
                </div>

                <!-- 2. إنتاجية وحمولة السفينة الواحدة -->
                <div class="gcpi-agency-card agency-card-emerald">
                    <div class="flex items-center justify-between mb-1.5">
                        <span class="text-xs font-bold text-slate-700">معدل حمولة السفينة (Payload)</span>
                        <span class="text-[0.68rem] px-2 py-0.5 rounded bg-emerald-50 text-emerald-700 font-bold border border-emerald-200">طن / باخرة</span>
                    </div>
                    <div class="text-lg font-black text-slate-900 font-mono mb-1">
                        <?php echo e(number_format($m['payloadPerShip'])); ?> <span class="text-xs font-medium text-slate-500">طن/سفينة</span>
                    </div>
                    <div class="text-[0.72rem] font-semibold flex items-center gap-1.5 <?php echo e($m['payloadDiff'] >= 0 ? 'text-emerald-600' : 'text-rose-600'); ?>">
                        <span><?php echo e($m['payloadDiff'] >= 0 ? '▲ +' : '▼ '); ?><?php echo e($m['payloadDiff']); ?>%</span>
                        <span class="text-slate-500 font-normal">كثافة الشحن البحري</span>
                    </div>
                </div>

                <!-- 3. نسبة التعادل التجاري للحاويات -->
                <div class="gcpi-agency-card agency-card-indigo">
                    <div class="flex items-center justify-between mb-1.5">
                        <span class="text-xs font-bold text-slate-700">دوران التصدير للحاويات (TEU)</span>
                        <span class="text-[0.68rem] px-2 py-0.5 rounded bg-indigo-50 text-indigo-700 font-bold border border-indigo-200">Export Ratio</span>
                    </div>
                    <div class="text-lg font-black text-slate-900 font-mono mb-1">
                        <?php echo e($m['teuExportRatio']); ?>%
                    </div>
                    <div class="text-[0.72rem] text-slate-600 font-semibold flex items-center justify-between">
                        <span>📥 مستورد: <?php echo e(number_format($m['currImpTeu'])); ?></span>
                        <span>📤 مصدر: <?php echo e(number_format($m['currExpTeu'])); ?></span>
                    </div>
                </div>

                <!-- 4. مؤشر ضغط وخطورة الساحات -->
                <div class="gcpi-agency-card agency-card-amber">
                    <div class="flex items-center justify-between mb-1.5">
                        <span class="text-xs font-bold text-slate-700">كثافة الحاويات الخطرة بالساحات</span>
                        <span class="text-[0.68rem] px-2 py-0.5 rounded <?php echo e($m['contRiskPercent'] > 10 ? 'bg-rose-100 text-rose-700 border-rose-300' : 'bg-amber-50 text-amber-700 border-amber-200'); ?> font-bold border">
                            <?php echo e($m['contRiskPercent'] > 10 ? 'تنبيه رقابي ⚠️' : 'مستوى معتدل 🟢'); ?>

                        </span>
                    </div>
                    <div class="text-lg font-black text-slate-900 font-mono mb-1">
                        <?php echo e($m['contRiskPercent']); ?>% <span class="text-xs font-medium text-slate-500">من إجمالي الساحات</span>
                    </div>
                    <div class="text-[0.72rem] text-slate-600 font-semibold truncate">
                        <span><?php echo e(number_format($m['dangerContDet'])); ?> حاوية خطرة من إجمالي <?php echo e(number_format($m['totalContDet'])); ?></span>
                    </div>
                </div>
            </div>

            <!-- Sector Dominance Leaders Banner -->
            <div class="gcpi-agency-dominance-row">
                <div class="gcpi-dom-box">
                    <span class="text-lg">🏆</span>
                    <div class="truncate">
                        <span class="text-[0.66rem] text-slate-500 font-bold block">الميناء الأول بالحاويات (TEU):</span>
                        <span class="text-xs font-black text-slate-800"><?php echo e($m['topContainerHub']['name']); ?></span>
                        <span class="text-[0.7rem] text-blue-600 font-mono font-bold">(<?php echo e(number_format($m['topContainerHub']['val'])); ?> TEU)</span>
                    </div>
                </div>

                <div class="gcpi-dom-box">
                    <span class="text-lg">📦</span>
                    <div class="truncate">
                        <span class="text-[0.66rem] text-slate-500 font-bold block">الميناء الأول بالبضائع العامة:</span>
                        <span class="text-xs font-black text-slate-800"><?php echo e($m['topCargoHub']['name']); ?></span>
                        <span class="text-[0.7rem] text-amber-600 font-mono font-bold">(<?php echo e(number_format($m['topCargoHub']['val'])); ?> طن)</span>
                    </div>
                </div>

                <div class="gcpi-dom-box">
                    <span class="text-lg">🛢️</span>
                    <div class="truncate">
                        <span class="text-[0.66rem] text-slate-500 font-bold block">الميناء الأول بالمشتقات النفطية:</span>
                        <span class="text-xs font-black text-slate-800"><?php echo e($m['topOilHub']['name']); ?></span>
                        <span class="text-[0.7rem] text-purple-600 font-mono font-bold">(<?php echo e(number_format($m['topOilHub']['val'])); ?> طن)</span>
                    </div>
                </div>

                <div class="gcpi-dom-box">
                    <span class="text-lg">💰</span>
                    <div class="truncate">
                        <span class="text-[0.66rem] text-slate-500 font-bold block">الميناء الأعلى إيراداً:</span>
                        <span class="text-xs font-black text-slate-800"><?php echo e($m['topRevenueHub']['name']); ?></span>
                        <span class="text-[0.7rem] text-emerald-600 font-mono font-bold">(<?php echo e(number_format($m['topRevenueHub']['val'] / 1000000000, 2)); ?> مليار د.ع)</span>
                    </div>
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
<?php /**PATH D:\Projects\ports-system\resources\views\filament\widgets\maritime-intelligence-agency-widget.blade.php ENDPATH**/ ?>