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

            <div id="geo-distribution-chart" class="dashboard-geo-chart mx-auto mt-4 mb-4 w-full max-w-95"></div>

                <div class="flex items-center justify-center gap-3 text-sm">
                    <span class="inline-flex items-center gap-1"><i class="inline-block h-3 w-3 bg-[#2563eb]"></i>Malaysia</span>
                    <span class="inline-flex items-center gap-1"><i class="inline-block h-3 w-3 bg-[#a855f7]"></i>Southeast Asia</span>
                    <span class="inline-flex items-center gap-1"><i class="inline-block h-3 w-3 bg-[#f97316]"></i>Rest of World</span>
                </div>

                <div class="mt-6 grid grid-cols-3 border-t border-zinc-200 pt-5 text-center">
                    <div><p class="museum-stat-value">{{ $geo['malaysia'] }}</p><p class="text-sm text-zinc-600">Malaysia</p></div>
                    <div><p class="museum-stat-value">{{ $geo['southeast_asia'] }}</p><p class="text-sm text-zinc-600">Southeast Asia</p></div>
                    <div><p class="museum-stat-value">{{ $geo['rest_of_world'] }}</p><p class="text-sm text-zinc-600">Rest of World</p></div>
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
                    @forelse($recentMovements as $move)
                        <div class="border-b border-zinc-200 py-3 last:border-b-0">
                            <div class="flex items-center justify-between gap-2">
                                <strong class="text-xl leading-tight">{{ $move->title }}</strong>
                                <span class="rounded-lg bg-amber-100 px-3 py-1 text-sm font-semibold text-amber-700">{{ $move->status }}</span>
                            </div>
                            <p class="mt-1 text-zinc-600">Storage Facility → {{ $move->location?->name ?? 'Unknown Location' }}</p>
                        </div>
                    @empty
                        <p class="text-zinc-500">No active movements.</p>
                    @endforelse
                </div>
            </article>
        </div>

        <article class="museum-panel p-6">
            <div class="mb-4 flex items-start justify-between">
                <div>
                    <h3 class="museum-section-title">Recently Acquired</h3>
                    <p class="text-zinc-600">Latest additions to collection</p>
                </div>
                @if(auth()->check() && auth()->user()->isAdmin())
                    <a href="{{ route('artworks.index') }}" class="museum-btn-secondary">Manage Collection</a>
                @else
                    <span class="text-3xl leading-none">→</span>
                @endif
            </div>

            <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                @forelse($recentArtworks as $artwork)
                    <article id="artwork-{{ $artwork->id }}" class="overflow-hidden rounded-xl border border-zinc-200 bg-white">
                        <a href="{{ route('artworks.show', ['artwork' => $artwork, 'from' => 'dashboard', 'return' => request()->fullUrl().'#artwork-'.$artwork->id]) }}">
                            @if($artwork->primary_image_url)
                                <img src="{{ $artwork->primary_image_url }}" alt="{{ $artwork->title }}" class="h-64 w-full object-cover">
                            @else
                                <div class="flex h-64 items-center justify-center bg-zinc-100 text-zinc-500">No Image</div>
                            @endif
                        </a>
                        <div class="p-3">
                            <h4 class="museum-card-title">{{ $artwork->title }}</h4>
                            <p class="text-zinc-600">{{ $artwork->artist?->name ?? 'Unknown Artist' }}{{ $artwork->year ? ', '.$artwork->year : '' }}</p>
                            <p class="mt-2 text-lg">{{ \App\Support\Currency::symbol() }}{{ number_format((float) $artwork->current_valuation, 0) }}</p>

                            @if(auth()->check() && auth()->user()->isAdmin())
                                <div class="mt-3">
                                    <a href="{{ route('artworks.edit', ['artwork' => $artwork, 'from' => 'dashboard', 'return' => request()->fullUrl().'#artwork-'.$artwork->id]) }}" class="museum-btn-secondary w-full justify-center">Edit Artwork</a>
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

    <script src="{{ asset('vendor/highcharts/highcharts.js') }}"></script>
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

            const geoSeriesData = [
                { name: 'Malaysia', y: {{ (int) ($geo['malaysia'] ?? 0) }} },
                { name: 'Southeast Asia', y: {{ (int) ($geo['southeast_asia'] ?? 0) }} },
                { name: 'Rest of World', y: {{ (int) ($geo['rest_of_world'] ?? 0) }} },
            ];

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
                    pointFormat: '{series.name}: <b>{point.percentage:.0f}%</b>'
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
                colors: ['#2563eb', '#a855f7', '#f97316'],
                series: [{
                    name: 'Collection',
                    type: 'pie',
                    innerSize: '62%',
                    data: geoSeriesData
                }]
            });

            chartContainer.dataset.chartReady = '1';
        };

        document.addEventListener('DOMContentLoaded', renderGeoDistributionChart);
        window.addEventListener('load', renderGeoDistributionChart);
    </script>
</x-layout>
