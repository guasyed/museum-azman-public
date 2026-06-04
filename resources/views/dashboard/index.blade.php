<x-layout title="Dashboard - Museum Azman">
    <section class="museum-dashboard-compact space-y-6">
        <div>
            <h2 class="museum-page-title">Dashboard</h2>
            <p class="museum-page-subtitle">Executive overview of your art collection</p>
        </div>

        <div class="grid gap-4 md:grid-cols-3 xl:grid-cols-6">
            <div class="museum-stat-card"><p>Total Artworks</p><strong>{{ number_format($stats['total_artworks']) }}</strong></div>
            <div class="museum-stat-card"><p>Total Artists</p><strong>{{ number_format($stats['total_artists']) }}</strong></div>
            <div class="museum-stat-card"><p>Total Locations</p><strong>{{ number_format($stats['total_locations']) }}</strong></div>
            @php
                $collectionValueRaw = (float) ($stats['collection_value'] ?? 0);
                $collectionValueDisplay = number_format($collectionValueRaw, 0);

                if (abs($collectionValueRaw) >= 1000000000) {
                    $collectionValueDisplay = rtrim(rtrim(number_format($collectionValueRaw / 1000000000, 1), '0'), '.').'B';
                } elseif (abs($collectionValueRaw) >= 1000000) {
                    $collectionValueDisplay = rtrim(rtrim(number_format($collectionValueRaw / 1000000, 1), '0'), '.').'M';
                } elseif (abs($collectionValueRaw) >= 1000) {
                    $collectionValueDisplay = rtrim(rtrim(number_format($collectionValueRaw / 1000, 1), '0'), '.').'K';
                }
            @endphp
            <div class="museum-stat-card"><p>Collection Value</p><strong title="{{ \App\Support\Currency::symbol() }}{{ number_format($collectionValueRaw, 0) }}">{{ \App\Support\Currency::symbol() }}{{ $collectionValueDisplay }}</strong></div>
            <div class="museum-stat-card"><p>In Stage</p><strong class="text-amber-600">{{ number_format($stats['in_stage']) }}</strong></div>
            <div class="museum-stat-card"><p>On Loan</p><strong class="text-violet-700">{{ number_format($stats['on_loan']) }}</strong></div>
        </div>

        <div class="grid gap-3 xl:grid-cols-[1.05fr_1fr]">
            <article class="museum-panel p-6">
                <h3 class="museum-section-title">Geographic Distribution</h3>
                <p class="text-zinc-600">Collection by origin</p>

            <div id="geo-distribution-chart" class="dashboard-geo-chart mt-4 mb-2 w-full" style="max-width: 580px;"></div>
            </article>

            <article class="museum-panel p-6">
                <div class="mb-4 flex items-start justify-between">
                    <div>
                        <h3 class="museum-section-title">Recent Movements</h3>
                        <p class="text-zinc-600">Latest movement logs</p>
                    </div>
                    <a href="{{ route('movements.index', [], false) }}" class="text-3xl leading-none" aria-label="View all movements">→</a>
                </div>

                <div class="space-y-2" data-recent-movements-list>
                    @forelse($recentMovements as $move)
                        @php
                            $movementStatusClass = match($move->status) {
                                'On Display' => 'bg-emerald-100 text-emerald-700',
                                'In Stage', 'In Storage', 'In Residence', 'In Office' => 'bg-amber-100 text-amber-700',
                                'On Loan', 'Loaned Out' => 'bg-violet-100 text-violet-700',
                                'Under Restoration' => 'bg-sky-100 text-sky-700',
                                'Sold or Left' => 'bg-rose-100 text-rose-700',
                                default => 'bg-zinc-100 text-zinc-700',
                            };
                        @endphp
                        <div class="border-b border-zinc-200 py-2.5 last:border-b-0 {{ $loop->iteration > 3 ? 'hidden' : '' }}" data-recent-movement-item>
                            <div class="flex items-center justify-between gap-2">
                                <div>
                                    <strong class="text-base leading-tight">{{ $move->artwork?->title ?? 'Unknown Artwork' }}</strong>
                                    <p class="text-xs text-zinc-500">{{ $move->artwork?->artist?->name ?? 'Unknown Artist' }}</p>
                                </div>
                                <span class="shrink-0 rounded-lg px-2.5 py-0.5 text-xs font-semibold {{ $movementStatusClass }}">{{ $move->status }}</span>
                            </div>
                            <p class="mt-1.5 text-sm text-zinc-600">{{ $move->from_location ?: 'Unknown Location' }} → {{ $move->to_location ?: 'Unknown Location' }}</p>
                            <div class="mt-1.5 grid gap-1 text-xs text-zinc-500 sm:grid-cols-2">
                                <p>{{ \App\Support\DateFormat::display($move->date_out) }}</p>
                                <p>{{ $move->responsible_handler ?: 'No handler assigned' }}</p>
                                <p>{{ $move->reason ?: 'No reason recorded' }}</p>
                                <p>Expected: {{ \App\Support\DateFormat::display($move->expected_return_date) }}</p>
                            </div>
                        </div>
                    @empty
                        <p class="text-zinc-500">No movement records yet.</p>
                    @endforelse
                </div>

                @if($recentMovements->count() > 3)
                    <div class="mt-4 flex items-center justify-between gap-3 border-t border-zinc-200 pt-4">
                        <button type="button" class="museum-btn-secondary px-3 py-1.5 text-sm disabled:cursor-not-allowed disabled:opacity-50" data-recent-movements-back>
                            Back
                        </button>
                        <p class="text-sm text-zinc-500" data-recent-movements-page></p>
                        <button type="button" class="museum-btn-secondary px-3 py-1.5 text-sm disabled:cursor-not-allowed disabled:opacity-50" data-recent-movements-next>
                            Next
                        </button>
                    </div>
                @endif
            </article>
        </div>

        <article class="museum-panel p-6">
            <div class="mb-4 flex items-start justify-between">
                <div>
                    <h3 class="museum-section-title">Featured Artworks</h3>
                    <p class="text-zinc-600">Random selection, prioritizing artworks with images</p>
                </div>
                @if(auth()->check() && auth()->user()->isAdmin())
                    <a href="{{ route('artworks.index', [], false) }}" class="museum-btn-secondary">Manage Collection</a>
                @else
                    <span class="text-3xl leading-none">→</span>
                @endif
            </div>

            <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                @forelse($recentArtworks as $artwork)
                    @php
                        $artworkStatusClass = match($artwork->status) {
                            'On Display' => 'bg-emerald-100 text-emerald-700',
                            'In Stage', 'In Storage', 'In Residence', 'In Office' => 'bg-amber-100 text-amber-700',
                            'On Loan', 'Loaned Out' => 'bg-violet-100 text-violet-700',
                            'Under Restoration' => 'bg-sky-100 text-sky-700',
                            'Under Evaluation' => 'bg-zinc-100 text-zinc-700',
                            'Sold or Left' => 'bg-rose-100 text-rose-700',
                            default => 'bg-zinc-100 text-zinc-700',
                        };
                    @endphp
                    <article id="artwork-{{ $artwork->id }}" class="overflow-hidden rounded-xl border border-zinc-200 bg-white">
                        <a href="{{ route('artworks.show', ['artwork' => $artwork, 'from' => 'dashboard', 'return' => request()->getRequestUri().'#artwork-'.$artwork->id], false) }}">
                            @if($artwork->primary_image_url)
                                <img src="{{ $artwork->primary_image_url }}" alt="{{ $artwork->title }}" class="h-64 w-full object-cover">
                            @else
                                <div class="flex h-64 items-center justify-center bg-zinc-100 text-zinc-500">No Image</div>
                            @endif
                        </a>
                        <div class="p-3">
                            <h4 class="museum-card-title">{{ $artwork->title }}</h4>
                            <p class="text-zinc-600">{{ $artwork->artist?->name ?? 'Unknown Artist' }}{{ $artwork->year ? ', '.$artwork->year : '' }}</p>
                            <div class="mt-3 flex items-center justify-between gap-2">
                                <span class="text-sm font-semibold text-zinc-500">{{ $artwork->display_inventory_code }}</span>
                                <span class="shrink-0 rounded-lg px-2.5 py-0.5 text-xs font-semibold {{ $artworkStatusClass }}">{{ $artwork->status ?: 'Unknown' }}</span>
                            </div>

                            @if(auth()->check() && auth()->user()->isAdmin())
                                <div class="mt-3">
                                    <a href="{{ route('artworks.edit', ['artwork' => $artwork, 'from' => 'dashboard', 'return' => request()->getRequestUri().'#artwork-'.$artwork->id], false) }}" class="museum-btn-secondary w-full justify-center">Edit Artwork</a>
                                </div>
                            @endif
                        </div>
                    </article>
                @empty
                    <p class="col-span-full text-zinc-500">No artworks yet.</p>
                @endforelse
            </div>
        </article>
    </section>

    <script src="/vendor/highcharts/highcharts.js"></script>
    <script>
        const ensureHighcharts = () => {
            if (typeof Highcharts !== 'undefined') {
                return Promise.resolve();
            }

            return new Promise((resolve, reject) => {
                const existing = document.querySelector('script[data-highcharts-fallback="1"]');
                if (existing) {
                    existing.addEventListener('load', () => resolve(), { once: true });
                    existing.addEventListener('error', () => reject(new Error('Highcharts fallback failed to load.')), { once: true });
                    return;
                }

                const script = document.createElement('script');
                script.src = 'https://cdn.jsdelivr.net/npm/highcharts@12.1.2/highcharts.js';
                script.async = true;
                script.dataset.highchartsFallback = '1';
                script.onload = () => resolve();
                script.onerror = () => reject(new Error('Highcharts fallback failed to load.'));
                document.head.appendChild(script);
            });
        };

        const renderGeoDistributionChart = () => {
            const chartContainer = document.getElementById('geo-distribution-chart');

            if (!chartContainer || typeof Highcharts === 'undefined') {
                return;
            }

            if (chartContainer.dataset.chartReady === '1') {
                return;
            }

            const isMobile = window.matchMedia('(max-width: 640px)').matches;

            if (!chartContainer.style.minHeight) {
                chartContainer.style.minHeight = isMobile ? '300px' : '360px';
            }

            const geoSeriesData = @json(
                collect($geoByCountry)->map(
                    fn ($count, $country) => ['name' => $country, 'y' => (int) $count]
                )->values()
            );

            Highcharts.chart('geo-distribution-chart', {
                chart: {
                    type: 'pie',
                    backgroundColor: 'transparent',
                    height: isMobile ? 300 : 360,
                    spacing: isMobile ? [10, 8, 10, 8] : [24, 40, 24, 40],
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
                        dataLabels: {
                            enabled: true,
                            format: '{point.name}: {point.y}',
                            distance: isMobile ? 10 : 18,
                            connectorPadding: isMobile ? 2 : 4,
                            connectorWidth: isMobile ? 1 : 1.5,
                            softConnector: true,
                            crookDistance: '70%',
                            overflow: 'allow',
                            crop: false,
                            allowOverlap: false,
                            filter: {
                                property: 'percentage',
                                operator: '>',
                                value: isMobile ? 2.5 : 0
                            },
                            style: {
                                fontSize: isMobile ? '12px' : '16px',
                                fontWeight: '600',
                                color: '#52525b',
                                textOutline: 'none'
                            }
                        },
                        showInLegend: false
                    }
                },
                series: [{
                    name: 'Collection',
                    type: 'pie',
                    size: isMobile ? '82%' : '88%',
                    innerSize: '58%',
                    center: isMobile ? ['56%', '55%'] : ['58%', '52%'],
                    data: geoSeriesData
                }]
            });

            chartContainer.dataset.chartReady = '1';
        };

        const renderGeoDistributionWhenReady = () => {
            ensureHighcharts()
                .then(renderGeoDistributionChart)
                .catch(() => {
                    const chartContainer = document.getElementById('geo-distribution-chart');
                    if (chartContainer) {
                        chartContainer.innerHTML = '<p class="text-sm text-zinc-500">Chart failed to load.</p>';
                    }
                });
        };

        const renderRecentMovementsPager = () => {
            const movementItems = Array.from(document.querySelectorAll('[data-recent-movement-item]'));
            const movementBackButton = document.querySelector('[data-recent-movements-back]');
            const movementNextButton = document.querySelector('[data-recent-movements-next]');
            const movementPageLabel = document.querySelector('[data-recent-movements-page]');
            const movementsPerPage = 3;
            let movementPage = 0;

            const renderPage = () => {
                if (!movementItems.length) {
                    return;
                }

                const totalPages = Math.ceil(movementItems.length / movementsPerPage);
                const start = movementPage * movementsPerPage;
                const end = start + movementsPerPage;

                movementItems.forEach((item, index) => {
                    item.classList.toggle('hidden', index < start || index >= end);
                });

                if (movementBackButton) {
                    movementBackButton.disabled = movementPage === 0;
                }

                if (movementNextButton) {
                    movementNextButton.disabled = movementPage >= totalPages - 1;
                }

                if (movementPageLabel) {
                    movementPageLabel.textContent = `Page ${movementPage + 1} of ${totalPages}`;
                }
            };

            movementBackButton?.addEventListener('click', () => {
                movementPage = Math.max(0, movementPage - 1);
                renderPage();
            });

            movementNextButton?.addEventListener('click', () => {
                const totalPages = Math.ceil(movementItems.length / movementsPerPage);
                movementPage = Math.min(totalPages - 1, movementPage + 1);
                renderPage();
            });

            renderPage();
        };

        document.addEventListener('DOMContentLoaded', renderGeoDistributionWhenReady);
        document.addEventListener('DOMContentLoaded', renderRecentMovementsPager);
        window.addEventListener('load', renderGeoDistributionWhenReady);
    </script>
</x-layout>
