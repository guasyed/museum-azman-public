<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Reports & Analytics - Museum Azman</title>
    <style>
        @page {
            size: A4;
            margin: 10mm;
        }
        :root {
            --ink: #18181b;
            --muted: #52525b;
            --line: #e4e4e7;
            --panel: #ffffff;
            --bg: #f7f7f8;
        }
        * { box-sizing: border-box; }
        body {
            margin: 0;
            font-family: Inter, Arial, sans-serif;
            color: var(--ink);
            background: var(--bg);
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
            text-rendering: optimizeLegibility;
        }
        .page {
            width: 100%;
            margin: 0 auto;
            padding: 16px 16px 20px;
        }
        .top {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 14px;
            margin-bottom: 14px;
        }
        h1 {
            margin: 0;
            font-size: 30px;
            line-height: 1;
            letter-spacing: -0.02em;
        }
        .subtitle {
            margin-top: 6px;
            font-size: 13px;
            color: var(--muted);
        }
        .stamp {
            font-size: 11px;
            color: var(--muted);
            margin-top: 8px;
            text-align: right;
        }
        .grid-4 {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 12px;
        }
        .card {
            background: var(--panel);
            border: 1px solid var(--line);
            border-radius: 12px;
            padding: 14px 16px;
        }
        .card p {
            margin: 0;
            color: var(--muted);
            font-size: 11px;
        }
        .card strong {
            display: block;
            margin-top: 10px;
            font-size: 22px;
            line-height: 1.1;
        }
        .card small {
            display: block;
            margin-top: 4px;
            color: var(--muted);
            font-size: 10px;
        }
        .section {
            background: var(--panel);
            border: 1px solid var(--line);
            border-radius: 12px;
            padding: 14px;
            margin-top: 12px;
            page-break-inside: avoid;
            break-inside: avoid;
        }
        .section.allow-break {
            page-break-inside: auto;
            break-inside: auto;
        }
        .section h2 {
            margin: 0;
            font-size: 14px;
            line-height: 1.2;
        }
        .section .desc {
            margin-top: 4px;
            color: var(--muted);
            font-size: 11px;
        }
        .charts-2 {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 12px;
        }
        .chart-box {
            border: 1px solid var(--line);
            border-radius: 12px;
            padding: 12px;
            background: #fff;
        }
        .chart {
            height: 280px;
            width: 100%;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 10px;
            margin-top: 10px;
        }
        thead { display: table-header-group; }
        th, td {
            border-bottom: 1px solid var(--line);
            padding: 7px 6px;
            text-align: left;
            vertical-align: top;
        }
        tr {
            page-break-inside: avoid;
            break-inside: avoid;
        }
        th { color: var(--muted); font-weight: 700; }
        .text-right { text-align: right; }
        .chip {
            display: inline-block;
            border-radius: 999px;
            padding: 2px 7px;
            font-size: 9px;
            font-weight: 700;
        }
        .chip-pos { background: #dcfce7; color: #166534; }
        .chip-neg { background: #ffe4e6; color: #9f1239; }
    </style>
</head>
<body>
    <?php
        $pdfGainAnalysis = $pdfGainAnalysis ?? $gainAnalysis;
        $pdfGeography = $pdfGeography ?? $geography->map(fn ($row) => ['name' => $row['name'], 'y' => $row['count']])->values();
        $pdfMediumDistribution = $pdfMediumDistribution ?? $mediumDistribution;
    ?>
    <div class="page">
        <div class="top">
            <div>
                <h1>Reports & Analytics</h1>
                <div class="subtitle">Comprehensive portfolio insights and performance metrics</div>
            </div>
            <div class="stamp">Generated: <?php echo e($exportedAt->format('Y-m-d H:i:s')); ?></div>
        </div>

        <div class="grid-4">
            <div class="card">
                <p>Total Portfolio Value</p>
                <strong><?php echo e(\App\Support\Currency::short($stats['total_value'])); ?></strong>
            </div>
            <div class="card">
                <p>Unrealized Gain</p>
                <strong style="color: <?php echo e($stats['unrealized_gain'] >= 0 ? '#16a34a' : '#e11d48'); ?>;">
                    <?php echo e($stats['unrealized_gain'] >= 0 ? '+' : '-'); ?><?php echo e(\App\Support\Currency::short(abs($stats['unrealized_gain']))); ?>

                </strong>
                <small><?php echo e(number_format($stats['coverage_ratio'], 1)); ?>% insured ratio</small>
            </div>
            <div class="card">
                <p>Insurance Coverage</p>
                <strong><?php echo e(\App\Support\Currency::short($stats['insured_value'])); ?></strong>
                <small><?php echo e(number_format($stats['coverage_ratio'], 1)); ?>% of current value</small>
            </div>
            <div class="card">
                <p>Works on Loan</p>
                <strong><?php echo e($stats['on_loan']); ?></strong>
                <small><?php echo e(\App\Support\Currency::short($stats['on_loan_insured'])); ?> insured</small>
            </div>
        </div>

        <div class="section no-break">
            <div class="charts-2">
                <div class="chart-box">
                    <h2>Collection Distribution by Geography</h2>
                    <div class="desc">Artworks and value by region</div>
                    <div id="pdf-geo-chart" class="chart"></div>
                </div>
                <div class="chart-box">
                    <h2>Collection Distribution by Medium</h2>
                    <div class="desc">Artworks by medium type</div>
                    <div id="pdf-medium-chart" class="chart"></div>
                </div>
            </div>
        </div>

        <div class="section no-break">
            <h2>Portfolio Value Over Time</h2>
            <div class="desc">Historical portfolio valuation trend</div>
            <div id="pdf-trend-chart" class="chart" style="height: 320px;"></div>
        </div>

        <div class="section allow-break">
            <h2>Unrealized Gain Analysis</h2>
            <div class="desc">Performance by individual artworks</div>
            <table>
                <thead>
                    <tr>
                        <th>Artwork</th>
                        <th class="text-right">Acquisition</th>
                        <th class="text-right">Current Value</th>
                        <th class="text-right">Gain/Loss</th>
                        <th class="text-right">% Change</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $__currentLoopData = $pdfGainAnalysis; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $artwork): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <?php
                            $acq = (float) $artwork->acquisition_price;
                            $cur = (float) $artwork->current_valuation;
                            $gain = $cur - $acq;
                            $change = $acq > 0 ? ($gain / $acq) * 100 : 0;
                        ?>
                        <tr>
                            <td>
                                <strong style="font-size:13px;"><?php echo e($artwork->title); ?></strong><br>
                                <span style="color:#6b7280;"><?php echo e($artwork->artist?->name ?? '-'); ?></span>
                            </td>
                            <td class="text-right"><?php echo e(\App\Support\Currency::short($acq)); ?></td>
                            <td class="text-right"><strong><?php echo e(\App\Support\Currency::short($cur)); ?></strong></td>
                            <td class="text-right" style="color: <?php echo e($gain >= 0 ? '#16a34a' : '#e11d48'); ?>;">
                                <?php echo e($gain >= 0 ? '+' : '-'); ?><?php echo e(\App\Support\Currency::short(abs($gain))); ?>

                            </td>
                            <td class="text-right">
                                <span class="chip <?php echo e($change >= 0 ? 'chip-pos' : 'chip-neg'); ?>">
                                    <?php echo e($change >= 0 ? '+' : '-'); ?><?php echo e(number_format(abs($change), 1)); ?>%
                                </span>
                            </td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </tbody>
            </table>
        </div>

        <div class="section allow-break">
            <h2>Works on Loan Summary</h2>
            <div class="desc">Artworks currently on loan to institutions</div>
            <table>
                <thead>
                    <tr>
                        <th>Artwork</th>
                        <th>Location</th>
                        <th class="text-right">Insurance Value</th>
                        <th class="text-right">Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $__currentLoopData = $worksOnLoan; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $artwork): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <tr>
                            <td><?php echo e($artwork->title); ?></td>
                            <td><?php echo e($artwork->location?->name ?? 'Unknown Location'); ?></td>
                            <td class="text-right"><?php echo e(\App\Support\Currency::short((float) $artwork->current_valuation)); ?></td>
                            <td class="text-right">On Loan</td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </tbody>
            </table>
        </div>

        <div class="section allow-break">
            <h2>Insurance Coverage Report</h2>
            <div class="desc">Comprehensive insurance coverage overview</div>
            <table>
                <thead>
                    <tr>
                        <th>Region</th>
                        <th class="text-right">Artworks</th>
                        <th class="text-right">Insured Value</th>
                        <th class="text-right">Market Value</th>
                        <th class="text-right">Coverage Ratio</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $__currentLoopData = $coverageByRegion; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $region): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <tr>
                            <td><?php echo e($region['name']); ?></td>
                            <td class="text-right"><?php echo e(number_format((int) $region['count'])); ?></td>
                            <td class="text-right"><?php echo e(\App\Support\Currency::short((float) $region['insured'])); ?></td>
                            <td class="text-right"><?php echo e(\App\Support\Currency::short((float) $region['market'])); ?></td>
                            <td class="text-right"><?php echo e(number_format((float) $region['ratio'], 1)); ?>%</td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </tbody>
            </table>
        </div>
    </div>

    <script><?php echo $highchartsScript; ?></script>
    <script>
        (function () {
            if (typeof Highcharts === 'undefined') {
                return;
            }

            const geoSeries = <?php echo json_encode($pdfGeography, 15, 512) ?>;
            const mediumSeries = <?php echo json_encode($pdfMediumDistribution, 15, 512) ?>;
            const trendYears = <?php echo json_encode($portfolioTrend->pluck('year')->values(), 15, 512) ?>;
            const trendValues = <?php echo json_encode($portfolioTrend->pluck('value')->values(), 15, 512) ?>;
            const colors = ['#2563eb', '#f97316', '#14b8a6', '#a855f7', '#e11d48', '#22c55e', '#f59e0b'];

            Highcharts.setOptions({
                chart: { style: { fontFamily: 'Inter, Arial, sans-serif' } },
                credits: { enabled: false },
                plotOptions: {
                    series: {
                        animation: false,
                    },
                },
            });

            Highcharts.chart('pdf-geo-chart', {
                chart: { type: 'pie', backgroundColor: 'transparent', animation: false },
                title: null,
                tooltip: { pointFormat: '<b>{point.y}</b> artworks ({point.percentage:.1f}%)' },
                plotOptions: {
                    pie: {
                        animation: false,
                        innerSize: '58%',
                        dataLabels: { enabled: true, format: '{point.name}: {point.y}', style: { fontSize: '10px' } }
                    }
                },
                colors,
                series: [{ type: 'pie', data: geoSeries }]
            });

            Highcharts.chart('pdf-medium-chart', {
                chart: { type: 'column', backgroundColor: 'transparent', animation: false },
                title: null,
                xAxis: { categories: mediumSeries.map(item => item.name), labels: { rotation: -35, style: { fontSize: '10px' } } },
                yAxis: { title: { text: null }, allowDecimals: false },
                tooltip: { pointFormat: '<b>{point.y}</b> artworks' },
                plotOptions: { column: { animation: false, borderRadius: 6, colorByPoint: true, colors } },
                series: [{ type: 'column', data: mediumSeries.map(item => item.y) }]
            });

            Highcharts.chart('pdf-trend-chart', {
                chart: {
                    type: 'areaspline',
                    animation: false,
                    backgroundColor: {
                        linearGradient: { x1: 0, y1: 0, x2: 0, y2: 1 },
                        stops: [[0, '#ffffff'], [1, '#f4f7ff']]
                    },
                    spacing: [18, 14, 12, 14]
                },
                title: null,
                xAxis: {
                    categories: trendYears,
                    lineColor: '#d4d4d8',
                    tickColor: '#d4d4d8',
                    labels: { style: { color: '#52525b', fontSize: '10px' } }
                },
                yAxis: {
                    title: { text: null },
                    gridLineColor: '#e4e4e7',
                    labels: {
                        style: { color: '#52525b', fontSize: '10px' },
                        formatter: function () { return 'RM' + (this.value / 1000000).toFixed(1) + 'M'; }
                    }
                },
                tooltip: {
                    backgroundColor: '#111827',
                    borderWidth: 0,
                    style: { color: '#f8fafc', fontSize: '11px' },
                    formatter: function () { return '<b>' + this.x + '</b><br>RM' + Highcharts.numberFormat(this.y, 0); }
                },
                plotOptions: {
                    series: { animation: false, marker: { enabled: false }, lineWidth: 2.8 }
                },
                series: [{
                    type: 'areaspline',
                    color: '#7dd3fc',
                    data: trendValues,
                    fillColor: {
                        linearGradient: { x1: 0, y1: 0, x2: 0, y2: 1 },
                        stops: [[0, 'rgba(167, 139, 250, 0.70)'], [1, 'rgba(56, 189, 248, 0.55)']]
                    }
                }]
            });
        })();
    </script>
</body>
</html>
<?php /**PATH /Users/syed/MEGA/WORK/RELIVA/Website/LEBTECH/museum-azman/resources/views/reports/pdf.blade.php ENDPATH**/ ?>