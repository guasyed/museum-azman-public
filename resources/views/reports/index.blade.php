<x-layout title="Reports & Analytics - Museum Azman">
    <style>
        @media print {
            aside,
            .museum-btn,
            .museum-btn-secondary,
            .museum-modal-overlay {
                display: none !important;
            }

            body.museum-shell,
            main {
                background: #fff !important;
            }

            main > div {
                max-width: none !important;
                padding: 0 !important;
            }

            .museum-panel,
            .museum-stat-card {
                box-shadow: none !important;
                break-inside: avoid;
            }
        }
    </style>

    <section class="space-y-6">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
            <div>
                <h2 class="museum-page-title">Reports & Analytics</h2>
                <p class="museum-page-subtitle">Comprehensive portfolio insights and performance metrics</p>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('reports.export.pdf') }}" class="museum-btn-secondary text-xs">↓ Export PDF</a>
                <a href="{{ route('reports.export.excel') }}" class="museum-btn-secondary text-xs">↓ Export Excel</a>
            </div>
        </div>

        <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
            <div class="museum-stat-card">
                <p>Total Portfolio Value</p>
                <strong>{{ \App\Support\Currency::short($stats['total_value']) }}</strong>
            </div>
            <div class="museum-stat-card">
                <p>Unrealized Gain</p>
                <strong class="{{ $stats['unrealized_gain'] >= 0 ? 'text-emerald-600' : 'text-rose-600' }}">
                    {{ $stats['unrealized_gain'] >= 0 ? '+' : '-' }}{{ \App\Support\Currency::short(abs($stats['unrealized_gain'])) }}
                </strong>
                <span class="mt-1 block text-xs text-zinc-500">+{{ number_format(($stats['coverage_ratio']), 1) }}% insured ratio</span>
            </div>
            <div class="museum-stat-card">
                <p>Insurance Coverage</p>
                <strong>{{ \App\Support\Currency::short($stats['insured_value']) }}</strong>
                <span class="mt-1 block text-xs text-zinc-500">{{ number_format($stats['coverage_ratio'], 1) }}% of current value</span>
            </div>
            <div class="museum-stat-card">
                <p>Works on Loan</p>
                <strong>{{ $stats['on_loan'] }}</strong>
                <span class="mt-1 block text-xs text-zinc-500">{{ \App\Support\Currency::short($stats['on_loan_insured']) }} insured</span>
            </div>
        </div>

        <article class="museum-panel">
            <div class="grid gap-4 xl:grid-cols-2">
                <article class="museum-panel p-5">
                    <h3 class="museum-section-title text-base!">Collection Distribution by Geography</h3>
                    <p class="text-sm text-zinc-600">Artworks and value by region</p>
                    <div id="report-geo-chart" class="mt-4 h-70 w-full"></div>
                </article>

                <article class="museum-panel p-5">
                    <h3 class="museum-section-title text-base!">Collection Distribution by Medium</h3>
                    <p class="text-sm text-zinc-600">Artworks by medium type</p>
                    <div id="report-medium-chart" class="mt-4 h-70 w-full"></div>
                </article>
            </div>
        </article>

        <article class="museum-panel p-5">
            <div id="report-trend-chart" class="h-70 w-full overflow-hidden rounded-xl"></div>
        </article>

        <article class="museum-panel p-0! overflow-hidden">
            <div class="border-b border-zinc-200 px-5 py-4">
                <h3 class="museum-section-title text-base!">Unrealized Gain Analysis</h3>
                <p class="text-sm text-zinc-600">Performance by individual artworks</p>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full min-w-245 text-sm">
                    <thead>
                        <tr class="border-b border-zinc-200 text-left text-zinc-600">
                            <th class="px-5 py-3">Artwork</th>
                            <th class="px-5 py-3 text-right">Acquisition</th>
                            <th class="px-5 py-3 text-right">Current Value</th>
                            <th class="px-5 py-3 text-right">Gain/Loss</th>
                            <th class="px-5 py-3 text-right">% Change</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($gainAnalysis as $artwork)
                            @php
                                $acquisition = (float) $artwork->acquisition_price;
                                $current = (float) $artwork->current_valuation;
                                $gain = $current - $acquisition;
                                $change = $acquisition > 0 ? ($gain / $acquisition) * 100 : 0;
                            @endphp
                            <tr class="border-b border-zinc-100">
                                <td class="px-5 py-3">
                                    <p class="font-semibold text-zinc-900">{{ $artwork->title }}</p>
                                    <p class="text-xs text-zinc-500">{{ $artwork->artist?->name ?? '-' }}</p>
                                </td>
                                <td class="px-5 py-3 text-right">{{ \App\Support\Currency::short($acquisition) }}</td>
                                <td class="px-5 py-3 text-right font-semibold">{{ \App\Support\Currency::short($current) }}</td>
                                <td class="px-5 py-3 text-right font-semibold {{ $gain >= 0 ? 'text-emerald-600' : 'text-rose-600' }}">
                                    {{ $gain >= 0 ? '+' : '-' }}{{ \App\Support\Currency::short(abs($gain)) }}
                                </td>
                                <td class="px-5 py-3 text-right">
                                    <span class="inline-flex rounded-md px-2 py-0.5 text-[11px] font-semibold {{ $change >= 0 ? 'bg-emerald-100 text-emerald-700' : 'bg-rose-100 text-rose-700' }}">
                                        {{ $change >= 0 ? '+' : '-' }}{{ number_format(abs($change), 1) }}%
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="px-5 py-4 text-zinc-500">No data available.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </article>

        <article class="museum-panel p-0! overflow-hidden">
            <div class="border-b border-zinc-200 px-5 py-4">
                <h3 class="museum-section-title text-base!">Works on Loan Summary</h3>
                <p class="text-sm text-zinc-600">Artworks currently on loan to institutions</p>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full min-w-245 text-sm">
                    <thead>
                        <tr class="border-b border-zinc-200 text-left text-zinc-600">
                            <th class="px-5 py-3">Artwork</th>
                            <th class="px-5 py-3">Location</th>
                            <th class="px-5 py-3 text-right">Insurance Value</th>
                            <th class="px-5 py-3 text-right">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($worksOnLoan as $artwork)
                            <tr class="border-b border-zinc-100">
                                <td class="px-5 py-3">
                                    <p class="font-semibold text-zinc-900">{{ $artwork->title }}</p>
                                    <p class="text-xs text-zinc-500">{{ $artwork->artist?->name ?? '-' }}</p>
                                </td>
                                <td class="px-5 py-3 text-zinc-700">{{ $artwork->location?->name ?? 'Unknown Location' }}</td>
                                <td class="px-5 py-3 text-right font-semibold">{{ \App\Support\Currency::short((float) $artwork->current_valuation) }}</td>
                                <td class="px-5 py-3 text-right">On Loan</td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="px-5 py-4 text-zinc-500">No artworks currently on loan.</td></tr>
                        @endforelse
                        <tr class="bg-zinc-50 font-semibold text-zinc-900">
                            <td class="px-5 py-3">Total on Loan</td>
                            <td class="px-5 py-3"></td>
                            <td class="px-5 py-3 text-right">{{ \App\Support\Currency::short($stats['on_loan_insured']) }}</td>
                            <td class="px-5 py-3 text-right">{{ $stats['on_loan'] }} works</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </article>

        <article class="museum-panel p-5">
            <h3 class="museum-section-title text-base!">Insurance Coverage Report</h3>
            <p class="text-sm text-zinc-600">Comprehensive insurance coverage overview</p>

            <div class="mt-4 grid gap-3 sm:grid-cols-3">
                <div class="rounded-xl bg-zinc-100 px-4 py-3">
                    <p class="text-xs text-zinc-500">Total Insured Value</p>
                    <p class="font-semibold text-zinc-900">{{ \App\Support\Currency::short($stats['insured_value']) }}</p>
                </div>
                <div class="rounded-xl bg-zinc-100 px-4 py-3">
                    <p class="text-xs text-zinc-500">Current Market Value</p>
                    <p class="font-semibold text-zinc-900">{{ \App\Support\Currency::short($stats['total_value']) }}</p>
                </div>
                <div class="rounded-xl bg-zinc-100 px-4 py-3">
                    <p class="text-xs text-zinc-500">Coverage Ratio</p>
                    <p class="font-semibold text-zinc-900">{{ number_format($stats['coverage_ratio'], 1) }}%</p>
                </div>
            </div>

            <div class="mt-4 divide-y divide-zinc-200 rounded-xl border border-zinc-200">
                @forelse($coverageByRegion as $region)
                    <div class="flex items-center justify-between gap-4 px-4 py-3">
                        <div>
                            <p class="font-semibold text-zinc-900">{{ $region['name'] }}</p>
                            <p class="text-xs text-zinc-500">{{ $region['count'] }} artworks</p>
                        </div>
                        <div class="text-right">
                            <p class="font-semibold text-zinc-900">{{ \App\Support\Currency::short($region['insured']) }}</p>
                            <p class="text-xs text-zinc-500">{{ number_format($region['ratio'], 1) }}% coverage</p>
                        </div>
                    </div>
                @empty
                    <p class="px-4 py-4 text-zinc-500">No regional insurance data available.</p>
                @endforelse
            </div>
        </article>
    </section>

    <script src="{{ asset('vendor/highcharts/highcharts.js') }}"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const geoSeries = @json($geography->map(fn ($row) => ['name' => $row['name'], 'y' => $row['count']])->values());
            const mediumSeries = @json($mediumDistribution->values());
            const trendYears = @json($portfolioTrend->pluck('year')->values());
            const trendValues = @json($portfolioTrend->pluck('value')->values());
            const ink = '#2563eb';
            const zinc600 = '#52525b';
            const grid = '#e4e4e7';
            const palette = ['#2563eb', '#f97316', '#14b8a6', '#a855f7', '#e11d48', '#22c55e', '#f59e0b'];

            const baseChart = {
                chart: {
                    backgroundColor: 'transparent',
                    style: { fontFamily: 'Inter, sans-serif' },
                    spacing: [12, 8, 8, 8]
                },
                title: null,
                credits: { enabled: false },
                exporting: { enabled: false }
            };

            const renderCharts = () => {
            try {
            Highcharts.chart('report-geo-chart', Highcharts.merge(baseChart, {
                chart: { type: 'pie' },
                tooltip: {
                    useHTML: true,
                    backgroundColor: '#111827',
                    borderWidth: 0,
                    borderRadius: 10,
                    shadow: false,
                    style: { color: '#f8fafc', fontSize: '12px' },
                    pointFormatter: function () {
                        return `
                            <div style="min-width:150px">
                                <div style="font-weight:700">${this.name}</div>
                                <div style="margin-top:4px">Artworks: <b>${this.y}</b></div>
                                <div>Share: <b>${Highcharts.numberFormat(this.percentage, 1)}%</b></div>
                            </div>
                        `;
                    }
                },
                plotOptions: {
                    pie: {
                        innerSize: '58%',
                        borderWidth: 2,
                        borderColor: '#ffffff',
                        allowPointSelect: true,
                        cursor: 'pointer',
                        dataLabels: {
                            enabled: true,
                            distance: 16,
                            style: { color: zinc600, fontSize: '11px', textOutline: 'none', fontWeight: '600' },
                            formatter: function () {
                                return `${this.point.name}: ${this.point.y}`;
                            }
                        },
                        states: {
                            hover: {
                                brightness: 0.06,
                                halo: { size: 6, opacity: 0.2 }
                            }
                        }
                    }
                },
                legend: { enabled: false },
                colors: palette,
                series: [{
                    type: 'pie',
                    name: 'Artworks',
                    data: geoSeries
                }]
            }));
            } catch (error) {}

            try {
            Highcharts.chart('report-medium-chart', Highcharts.merge(baseChart, {
                chart: { type: 'column' },
                xAxis: {
                    categories: mediumSeries.map(item => item.name),
                    lineColor: grid,
                    tickColor: grid,
                    labels: { rotation: -42, style: { color: zinc600, fontSize: '11px' } }
                },
                yAxis: {
                    title: { text: null },
                    gridLineColor: grid,
                    allowDecimals: false,
                    labels: { style: { color: zinc600 } }
                },
                legend: { enabled: false },
                tooltip: {
                    useHTML: true,
                    backgroundColor: '#111827',
                    borderWidth: 0,
                    borderRadius: 10,
                    shadow: false,
                    style: { color: '#f8fafc', fontSize: '12px' },
                    formatter: function () {
                        return `
                            <div style="min-width:150px">
                                <div style="font-weight:700">${this.x}</div>
                                <div style="margin-top:4px">Artworks: <b>${this.y}</b></div>
                            </div>
                        `;
                    }
                },
                plotOptions: {
                    column: {
                        borderRadius: 8,
                        borderWidth: 0,
                        colorByPoint: true,
                        colors: palette,
                        groupPadding: 0.12,
                        pointPadding: 0.06,
                        states: {
                            hover: {
                                brightness: -0.1
                            }
                        }
                    }
                },
                series: [{
                    type: 'column',
                    name: 'Artworks',
                    data: mediumSeries.map(item => item.y)
                }]
            }));
            } catch (error) {}

            try {
            Highcharts.chart('report-trend-chart', Highcharts.merge(baseChart, {
                chart: {
                    type: 'area',
                    zoomType: 'x',
                    backgroundColor: {
                        linearGradient: { x1: 0, y1: 0, x2: 0, y2: 1 },
                        stops: [
                            [0, '#ffffff'],
                            [1, '#f4f7ff']
                        ]
                    },
                    style: { fontFamily: 'Inter, sans-serif' },
                    spacing: [22, 20, 16, 20]
                },
                title: {
                    text: 'Portfolio Value Over Time',
                    style: {
                        color: '#111827',
                        fontSize: '18px',
                        fontWeight: '700'
                    }
                },
                subtitle: {
                    text: 'Click and drag in the plot area to zoom in',
                    style: {
                        color: '#6b7280',
                        fontSize: '12px'
                    }
                },
                xAxis: {
                    categories: trendYears,
                    lineColor: '#d4d4d8',
                    tickColor: '#d4d4d8',
                    crosshair: { color: '#9ca3af', dashStyle: 'ShortDot' },
                    labels: {
                        crop: false,
                        overflow: 'justify',
                        style: {
                            color: '#52525b',
                            textOverflow: 'none',
                        },
                        formatter: function () {
                            const year = Number(this.value);
                            if (!Number.isFinite(year)) {
                                return '';
                            }

                            // Always show the final tick (current year).
                            if (this.isLast) {
                                return String(year);
                            }

                            // Keep labels readable on long timelines.
                            return year % 2 === 0 ? String(year) : '';
                        }
                    }
                },
                yAxis: {
                    title: { text: null },
                    gridLineColor: '#e4e4e7',
                    labels: {
                        style: { color: '#52525b' },
                        formatter: function () {
                            return 'RM' + (this.value / 1000000).toFixed(1) + 'M';
                        }
                    }
                },
                legend: { enabled: false },
                exporting: {
                    enabled: true,
                    buttons: {
                        contextButton: {
                            theme: {
                                fill: 'transparent',
                                stroke: 'transparent'
                            },
                            symbolStroke: '#52525b'
                        }
                    }
                },
                tooltip: {
                    useHTML: true,
                    shared: true,
                    backgroundColor: '#111827',
                    borderWidth: 0,
                    borderRadius: 10,
                    shadow: false,
                    style: { color: '#f8fafc', fontSize: '12px' },
                    formatter: function () {
                        const point = this.points && this.points.length ? this.points[0] : null;
                        return `
                            <div style="min-width:170px">
                                <div style="font-weight:700">${this.x}</div>
                                <div style="margin-top:4px">Portfolio Value</div>
                                <div><b>RM${Highcharts.numberFormat(point ? point.y : 0, 0)}</b></div>
                            </div>
                        `;
                    }
                },
                plotOptions: {
                    series: {
                        animation: { duration: 650 },
                        marker: { enabled: false },
                        lineWidth: 3,
                        states: { hover: { lineWidth: 3 } }
                    }
                },
                series: [{
                    type: 'area',
                    name: 'Portfolio Value',
                    color: '#7dd3fc',
                    data: trendValues,
                    fillColor: {
                        linearGradient: { x1: 0, y1: 0, x2: 0, y2: 1 },
                        stops: [
                            [0, 'rgba(167, 139, 250, 0.70)'],
                            [1, 'rgba(56, 189, 248, 0.55)']
                        ]
                    }
                }],
                responsive: {
                    rules: [{
                        condition: { maxWidth: 700 },
                        chartOptions: {
                            xAxis: {
                                labels: { rotation: -30 }
                            },
                            plotOptions: {
                                pie: { dataLabels: { enabled: false } }
                            }
                        }
                    }]
                }
            }));
            } catch (error) {}
            };

            const showChartError = () => {
                ['report-geo-chart', 'report-medium-chart', 'report-trend-chart'].forEach((id) => {
                    const el = document.getElementById(id);
                    if (!el || el.childElementCount > 0) {
                        return;
                    }
                    el.innerHTML = '<p class="text-sm text-zinc-500">Unable to load chart library.</p>';
                });
            };

            const bootCharts = () => {
                if (typeof Highcharts !== 'undefined') {
                    renderCharts();
                    return;
                }

                const fallback = document.createElement('script');
                fallback.src = 'https://cdnjs.cloudflare.com/ajax/libs/highcharts/12.1.2/highcharts.js';
                fallback.onload = () => {
                    if (typeof Highcharts !== 'undefined') {
                        renderCharts();
                    } else {
                        showChartError();
                    }
                };
                fallback.onerror = showChartError;
                document.head.appendChild(fallback);
            };

            bootCharts();
        });
    </script>
</x-layout>
