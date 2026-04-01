<?php if (isset($component)) { $__componentOriginal23a33f287873b564aaf305a1526eada4 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal23a33f287873b564aaf305a1526eada4 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.layout','data' => ['title' => 'Dashboard - Museum Azman']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('layout'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Dashboard - Museum Azman']); ?>
    <section class="museum-dashboard-compact space-y-6">
        <div>
            <h2 class="museum-page-title">Dashboard</h2>
            <p class="museum-page-subtitle">Executive overview of your art collection</p>
        </div>

        <div class="grid gap-4 md:grid-cols-3 xl:grid-cols-6">
            <div class="museum-stat-card"><p>Total Artworks</p><strong><?php echo e(number_format($stats['total_artworks'])); ?></strong></div>
            <div class="museum-stat-card"><p>Total Artists</p><strong><?php echo e(number_format($stats['total_artists'])); ?></strong></div>
            <div class="museum-stat-card"><p>Total Locations</p><strong><?php echo e(number_format($stats['total_locations'])); ?></strong></div>
            <?php
                $collectionValueRaw = (float) ($stats['collection_value'] ?? 0);
                $collectionValueDisplay = number_format($collectionValueRaw, 0);

                if (abs($collectionValueRaw) >= 1000000000) {
                    $collectionValueDisplay = rtrim(rtrim(number_format($collectionValueRaw / 1000000000, 1), '0'), '.').'B';
                } elseif (abs($collectionValueRaw) >= 1000000) {
                    $collectionValueDisplay = rtrim(rtrim(number_format($collectionValueRaw / 1000000, 1), '0'), '.').'M';
                } elseif (abs($collectionValueRaw) >= 1000) {
                    $collectionValueDisplay = rtrim(rtrim(number_format($collectionValueRaw / 1000, 1), '0'), '.').'K';
                }
            ?>
            <div class="museum-stat-card"><p>Collection Value</p><strong title="<?php echo e(\App\Support\Currency::symbol()); ?><?php echo e(number_format($collectionValueRaw, 0)); ?>"><?php echo e(\App\Support\Currency::symbol()); ?><?php echo e($collectionValueDisplay); ?></strong></div>
            <div class="museum-stat-card"><p>In Stage</p><strong class="text-amber-600"><?php echo e(number_format($stats['in_stage'])); ?></strong></div>
            <div class="museum-stat-card"><p>On Loan</p><strong class="text-violet-700"><?php echo e(number_format($stats['on_loan'])); ?></strong></div>
        </div>

        <div class="grid gap-3 xl:grid-cols-[1.05fr_1fr]">
            <article class="museum-panel p-6">
                <h3 class="museum-section-title">Geographic Distribution</h3>
                <p class="text-zinc-600">Collection by origin</p>

            <div id="geo-distribution-chart" class="dashboard-geo-chart mx-auto mt-4 mb-4 w-full max-w-95"></div>

                <div class="mt-6 flex flex-nowrap justify-between gap-2 border-t border-zinc-200 pt-4 text-center sm:gap-3">
                    <?php $__currentLoopData = array_slice($geoByCountry, 0, 8, true); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $country => $count): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <div>
                            <p class="font-bold leading-tight text-zinc-900" style="font-size:20px"><?php echo e(number_format($count)); ?></p>
                            <p class="leading-tight text-zinc-600" style="font-size:10px"><?php echo e($country); ?></p>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            </article>

            <article class="museum-panel p-6">
                <div class="mb-4 flex items-start justify-between">
                    <div>
                        <h3 class="museum-section-title">Recent Movements</h3>
                        <p class="text-zinc-600">Active and scheduled</p>
                    </div>
                    <span class="text-3xl leading-none">→</span>
                </div>

                <div class="space-y-2">
                    <?php $__empty_1 = true; $__currentLoopData = $recentMovements; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $move): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <div class="border-b border-zinc-200 py-3 last:border-b-0">
                            <div class="flex items-center justify-between gap-2">
                                <strong class="text-xl leading-tight"><?php echo e($move->title); ?></strong>
                                <span class="rounded-lg bg-amber-100 px-3 py-1 text-sm font-semibold text-amber-700"><?php echo e($move->status); ?></span>
                            </div>
                            <p class="mt-1 text-zinc-600">Storage Facility → <?php echo e($move->location?->name ?? 'Unknown Location'); ?></p>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <p class="text-zinc-500">No active movements.</p>
                    <?php endif; ?>
                </div>
            </article>
        </div>

        <article class="museum-panel p-6">
            <div class="mb-4 flex items-start justify-between">
                <div>
                    <h3 class="museum-section-title">Recently Acquired</h3>
                    <p class="text-zinc-600">Latest additions to collection</p>
                </div>
                <?php if(auth()->check() && auth()->user()->isAdmin()): ?>
                    <a href="<?php echo e(route('artworks.index')); ?>" class="museum-btn-secondary">Manage Collection</a>
                <?php else: ?>
                    <span class="text-3xl leading-none">→</span>
                <?php endif; ?>
            </div>

            <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                <?php $__empty_1 = true; $__currentLoopData = $recentArtworks; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $artwork): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <article id="artwork-<?php echo e($artwork->id); ?>" class="overflow-hidden rounded-xl border border-zinc-200 bg-white">
                        <a href="<?php echo e(route('artworks.show', ['artwork' => $artwork, 'from' => 'dashboard', 'return' => request()->fullUrl().'#artwork-'.$artwork->id])); ?>">
                            <?php if($artwork->primary_image_url): ?>
                                <img src="<?php echo e($artwork->primary_image_url); ?>" alt="<?php echo e($artwork->title); ?>" class="h-64 w-full object-cover">
                            <?php else: ?>
                                <div class="flex h-64 items-center justify-center bg-zinc-100 text-zinc-500">No Image</div>
                            <?php endif; ?>
                        </a>
                        <div class="p-3">
                            <h4 class="museum-card-title"><?php echo e($artwork->title); ?></h4>
                            <p class="text-zinc-600"><?php echo e($artwork->artist?->name ?? 'Unknown Artist'); ?><?php echo e($artwork->year ? ', '.$artwork->year : ''); ?></p>
                            <p class="mt-2 text-lg"><?php echo e(\App\Support\Currency::symbol()); ?><?php echo e(number_format((float) $artwork->current_valuation, 0)); ?></p>

                            <?php if(auth()->check() && auth()->user()->isAdmin()): ?>
                                <div class="mt-3">
                                    <a href="<?php echo e(route('artworks.edit', ['artwork' => $artwork, 'from' => 'dashboard', 'return' => request()->fullUrl().'#artwork-'.$artwork->id])); ?>" class="museum-btn-secondary w-full justify-center">Edit Artwork</a>
                                </div>
                            <?php endif; ?>
                        </div>
                    </article>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <p class="col-span-full text-zinc-500">No artworks yet.</p>
                <?php endif; ?>
            </div>
        </article>
    </section>

    <script src="<?php echo e(asset('vendor/highcharts/highcharts.js')); ?>"></script>
    <script>
        const renderGeoDistributionChart = () => {
            const chartContainer = document.getElementById('geo-distribution-chart');

            if (!chartContainer || typeof Highcharts === 'undefined') {
                return;
            }

            if (chartContainer.dataset.chartReady === '1') {
                return;
            }

            if (!chartContainer.style.minHeight) {
                chartContainer.style.minHeight = '240px';
            }

            const geoSeriesData = <?php echo json_encode(
                collect($geoByCountry)->map(
                    fn ($count, $country) => ['name' => $country, 'y' => (int) $count]
                )->values()) ?>;

            const totalGeo = geoSeriesData.reduce((sum, item) => sum + item.y, 0);

            Highcharts.chart('geo-distribution-chart', {
                chart: {
                    type: 'pie',
                    custom: {},
                    events: {
                        render() {
                            const chart = this;
                            const series = chart.series[0];
                            let customLabel = chart.options.chart.custom.label;

                            if (!series || !series.center) {
                                return;
                            }

                            if (!customLabel) {
                                customLabel = chart.options.chart.custom.label = chart.renderer
                                    .label(
                                        'Total<br/><strong>' + Highcharts.numberFormat(totalGeo, 0) + '</strong>'
                                    )
                                    .css({
                                        color: '#18181b',
                                        textAnchor: 'middle'
                                    })
                                    .add();
                            }

                            const x = series.center[0] + chart.plotLeft;
                            const y = series.center[1] + chart.plotTop - (customLabel.attr('height') / 2);

                            customLabel.attr({ x, y });
                            customLabel.css({
                                fontSize: `${series.center[2] / 12}px`
                            });
                        }
                    },
                    backgroundColor: 'transparent',
                    spacing: [0, 0, 0, 0],
                    style: {
                        fontFamily: 'Inter, sans-serif'
                    }
                },
                title: null,
                accessibility: {
                    point: {
                        valueSuffix: '%'
                    }
                },
                credits: { enabled: false },
                tooltip: {
                    pointFormat: '<b>{point.name}</b><br/>Count: <b>{point.y}</b><br/>Share: <b>{point.percentage:.0f}%</b>'
                },
                legend: { enabled: false },
                plotOptions: {
                    series: {
                        allowPointSelect: true,
                        cursor: 'pointer',
                        borderRadius: 8,
                        dataLabels: [{
                            enabled: false
                        }],
                        showInLegend: false
                    }
                },
                series: [{
                    name: 'Collection',
                    type: 'pie',
                    innerSize: '62%',
                    center: ['50%', '50%'],
                    data: geoSeriesData
                }]
            });

            chartContainer.dataset.chartReady = '1';
        };

        document.addEventListener('DOMContentLoaded', renderGeoDistributionChart);
        window.addEventListener('load', renderGeoDistributionChart);
    </script>
 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal23a33f287873b564aaf305a1526eada4)): ?>
<?php $attributes = $__attributesOriginal23a33f287873b564aaf305a1526eada4; ?>
<?php unset($__attributesOriginal23a33f287873b564aaf305a1526eada4); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal23a33f287873b564aaf305a1526eada4)): ?>
<?php $component = $__componentOriginal23a33f287873b564aaf305a1526eada4; ?>
<?php unset($__componentOriginal23a33f287873b564aaf305a1526eada4); ?>
<?php endif; ?>
<?php /**PATH /Users/syed/MEGA/WORK/RELIVA/Website/LEBTECH/museum-azman/resources/views/dashboard/index.blade.php ENDPATH**/ ?>