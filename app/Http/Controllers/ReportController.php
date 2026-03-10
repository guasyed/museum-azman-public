<?php

namespace App\Http\Controllers;

use App\Models\Artwork;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Illuminate\View\View;
use Throwable;
use Barryvdh\DomPDF\Facade\Pdf;

class ReportController extends Controller
{
    public function index(): View
    {
        $data = $this->buildReportData();

        return view('reports.index', $data);
    }

    public function exportExcel(): StreamedResponse
    {
        $data = $this->buildReportData();
        $stats = $data['stats'];
        $gainAnalysis = $data['gainAnalysis'];
        $worksOnLoan = $data['worksOnLoan'];
        $coverageByRegion = $data['coverageByRegion'];

        $filename = 'reports-analytics-'.now()->format('Ymd-His').'.csv';

        return response()->streamDownload(function () use ($stats, $gainAnalysis, $worksOnLoan, $coverageByRegion) {
            $out = fopen('php://output', 'w');
            if (! $out) {
                return;
            }

            fputcsv($out, ['Reports & Analytics Export']);
            fputcsv($out, ['Generated At', now()->format('Y-m-d H:i:s')]);
            fputcsv($out, []);

            fputcsv($out, ['Summary']);
            fputcsv($out, ['Total Portfolio Value', number_format((float) $stats['total_value'], 2)]);
            fputcsv($out, ['Unrealized Gain', number_format((float) $stats['unrealized_gain'], 2)]);
            fputcsv($out, ['Insurance Coverage', number_format((float) $stats['insured_value'], 2)]);
            fputcsv($out, ['Works on Loan', (int) $stats['on_loan']]);
            fputcsv($out, ['On Loan Insured', number_format((float) $stats['on_loan_insured'], 2)]);
            fputcsv($out, []);

            fputcsv($out, ['Unrealized Gain Analysis']);
            fputcsv($out, ['Artwork', 'Artist', 'Acquisition', 'Current Value', 'Gain/Loss', '% Change']);
            foreach ($gainAnalysis as $artwork) {
                $acquisition = (float) $artwork->acquisition_price;
                $current = (float) $artwork->current_valuation;
                $gain = $current - $acquisition;
                $change = $acquisition > 0 ? ($gain / $acquisition) * 100 : 0;

                fputcsv($out, [
                    $artwork->title,
                    $artwork->artist?->name ?? '-',
                    number_format($acquisition, 2, '.', ''),
                    number_format($current, 2, '.', ''),
                    number_format($gain, 2, '.', ''),
                    number_format($change, 2, '.', ''),
                ]);
            }
            fputcsv($out, []);

            fputcsv($out, ['Works on Loan']);
            fputcsv($out, ['Artwork', 'Artist', 'Location', 'Insurance Value', 'Status']);
            foreach ($worksOnLoan as $artwork) {
                fputcsv($out, [
                    $artwork->title,
                    $artwork->artist?->name ?? '-',
                    $artwork->location?->name ?? 'Unknown Location',
                    number_format((float) $artwork->current_valuation, 2, '.', ''),
                    'On Loan',
                ]);
            }
            fputcsv($out, []);

            fputcsv($out, ['Coverage by Region']);
            fputcsv($out, ['Region', 'Artworks', 'Insured', 'Market', 'Coverage Ratio']);
            foreach ($coverageByRegion as $region) {
                fputcsv($out, [
                    $region['name'],
                    $region['count'],
                    number_format((float) $region['insured'], 2, '.', ''),
                    number_format((float) $region['market'], 2, '.', ''),
                    number_format((float) $region['ratio'], 2, '.', '').'%',
                ]);
            }

            fclose($out);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    /* public function exportPdf(): Response
    {
        $data = $this->buildReportData();
        $pdfArtworks = Artwork::query()->with(['artist', 'location'])->get();

        $data['pdfGainAnalysis'] = $pdfArtworks
            ->sortByDesc(fn (Artwork $artwork) => (float) $artwork->current_valuation - (float) $artwork->acquisition_price)
            ->values();

        $data['pdfGeography'] = $pdfArtworks
            ->groupBy(function (Artwork $artwork) {
                return trim((string) ($artwork->artist?->country ?? '')) ?: 'Unknown';
            })
            ->map(function (Collection $group, string $country) {
                return [
                    'name' => $country,
                    'y' => $group->count(),
                ];
            })
            ->sortByDesc('y')
            ->values();

        $data['pdfMediumDistribution'] = $pdfArtworks
            ->groupBy(function (Artwork $artwork) {
                return trim((string) ($artwork->medium ?? '')) ?: 'Unknown';
            })
            ->map(fn (Collection $group, string $medium) => [
                'name' => $medium,
                'y' => $group->count(),
            ])
            ->sortByDesc('y')
            ->values();

        $data['exportedAt'] = now();
        $data['highchartsScript'] = (string) @file_get_contents(public_path('vendor/highcharts/highcharts.js'));
        $html = view('reports.pdf', $data)->render();
        $pdfContent = $this->renderPdfWithChrome($html);
        $filename = 'reports-analytics-'.now()->format('Ymd-His').'.pdf';

        if ($pdfContent === null) {
            return response('PDF generation failed. Chrome binary is unavailable.', 500);
        }

        return response($pdfContent, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
            'Content-Length' => (string) strlen($pdfContent),
        ]);
    } */
    public function exportPdf()
    {
        @ini_set('memory_limit', '512M');
        @set_time_limit(300);

        $data = $this->buildReportData();
        $pdfArtworks = Artwork::query()->with(['artist', 'location'])->get();

        $data['pdfGainAnalysis'] = $pdfArtworks
            ->sortByDesc(fn (Artwork $artwork) => (float) $artwork->current_valuation - (float) $artwork->acquisition_price)
            ->values();

        $data['pdfGeography'] = $pdfArtworks
            ->groupBy(function (Artwork $artwork) {
                return trim((string) ($artwork->artist?->country ?? '')) ?: 'Unknown';
            })
            ->map(function (Collection $group, string $country) {
                return [
                    'name' => $country,
                    'y' => $group->count(),
                ];
            })
            ->sortByDesc('y')
            ->values();

        $data['pdfMediumDistribution'] = $pdfArtworks
            ->groupBy(function (Artwork $artwork) {
                return trim((string) ($artwork->medium ?? '')) ?: 'Unknown';
            })
            ->map(fn (Collection $group, string $medium) => [
                'name' => $medium,
                'y' => $group->count(),
            ])
            ->sortByDesc('y')
            ->values();

        $data['exportedAt'] = now();
        $data['highchartsScript'] = (string) @file_get_contents(public_path('vendor/highcharts/highcharts.js'));

        $filename = 'reports-analytics-'.now()->format('Ymd-His').'.pdf';
        $html = view('reports.pdf', $data)->render();
        $chromePdf = $this->renderPdfWithChrome($html);

        if ($chromePdf !== null) {
            return response($chromePdf, 200, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'attachment; filename="'.$filename.'"',
                'Content-Length' => (string) strlen($chromePdf),
            ]);
        }

        $pdf = Pdf::loadView('reports.pdf', $data)->setPaper('a4', 'portrait');
        return $pdf->download($filename);
    }
    private function buildReportData(): array
    {
        $artworks = Artwork::query()
            ->with(['artist'])
            ->get();

        $totalValue = (float) $artworks->sum(fn (Artwork $artwork) => (float) $artwork->current_valuation);
        $insuredValue = (float) $artworks->sum(fn (Artwork $artwork) => (float) $artwork->acquisition_price);
        $onLoan = $artworks->where('status', 'On Loan');
        $onLoanInsured = (float) $onLoan->sum(fn (Artwork $artwork) => (float) $artwork->current_valuation);
        $unrealizedGain = $totalValue - $insuredValue;
        $coverageRatio = $totalValue > 0 ? ($insuredValue / $totalValue) * 100 : 0;

        $stats = [
            'total_value' => $totalValue,
            'insured_value' => $insuredValue,
            'on_loan' => $onLoan->count(),
            'on_loan_insured' => $onLoanInsured,
            'unrealized_gain' => $unrealizedGain,
            'coverage_ratio' => $coverageRatio,
        ];

        $gainAnalysis = $artworks
            ->sortByDesc(fn (Artwork $artwork) => (float) $artwork->current_valuation - (float) $artwork->acquisition_price)
            ->take(10)
            ->values();

        $worksOnLoan = $onLoan
            ->sortByDesc(fn (Artwork $artwork) => (float) $artwork->current_valuation)
            ->values();

        $geography = $artworks
            ->groupBy(function (Artwork $artwork) {
                return trim((string) ($artwork->artist?->country ?? '')) ?: 'Unknown';
            })
            ->map(function (Collection $group, string $country) {
                return [
                    'name' => $country,
                    'count' => $group->count(),
                    'value' => (float) $group->sum(fn (Artwork $artwork) => (float) $artwork->current_valuation),
                ];
            })
            ->sortByDesc('count')
            ->values();

        $mediumDistribution = $artworks
            ->groupBy(function (Artwork $artwork) {
                return trim((string) ($artwork->medium ?? '')) ?: 'Unknown';
            })
            ->map(fn (Collection $group, string $medium) => [
                'name' => $medium,
                'y' => $group->count(),
            ])
            ->sortByDesc('y')
            ->take(6)
            ->values();

        $yearValues = $artworks
            ->map(function (Artwork $artwork) {
                $year = (int) ($artwork->year ?? 0);
                $currentYear = (int) now()->format('Y');

                if ($year >= 1000 && $year <= $currentYear + 1) {
                    $resolvedYear = $year;
                } elseif ($artwork->acquisition_date) {
                    $resolvedYear = (int) Carbon::parse($artwork->acquisition_date)->format('Y');
                } else {
                    $resolvedYear = (int) Carbon::parse($artwork->created_at)->format('Y');
                }

                return [
                    'year' => $resolvedYear,
                    'value' => (float) $artwork->current_valuation,
                ];
            });

        $currentYear = (int) now()->format('Y');
        $startYear = $yearValues->isNotEmpty()
            ? min((int) $yearValues->min('year'), $currentYear)
            : $currentYear;

        $portfolioTrend = collect(range($startYear, $currentYear))
            ->map(function (int $year) use ($yearValues) {
                return [
                    'year' => $year,
                    'value' => (float) $yearValues
                        ->filter(fn (array $row) => (int) $row['year'] <= $year)
                        ->sum('value'),
                ];
            });

        $coverageByRegion = $artworks
            ->groupBy(function (Artwork $artwork) {
                return trim((string) ($artwork->artist?->country ?? '')) ?: 'Unknown';
            })
            ->map(function (Collection $group, string $region) {
                $insured = (float) $group->sum(fn (Artwork $artwork) => (float) $artwork->acquisition_price);
                $market = (float) $group->sum(fn (Artwork $artwork) => (float) $artwork->current_valuation);

                return [
                    'name' => $region,
                    'count' => $group->count(),
                    'insured' => $insured,
                    'market' => $market,
                    'ratio' => $market > 0 ? ($insured / $market) * 100 : 0,
                ];
            })
            ->sortByDesc('market')
            ->values();

        return compact(
            'stats',
            'gainAnalysis',
            'worksOnLoan',
            'geography',
            'mediumDistribution',
            'portfolioTrend',
            'coverageByRegion'
        );
    }

    private function renderPdfWithChrome(string $html): ?string
    {
        $tmpDir = storage_path('app/tmp');
        if (! is_dir($tmpDir)) {
            @mkdir($tmpDir, 0775, true);
        }

        $token = (string) str_replace('.', '', uniqid('report_', true));
        $htmlPath = $tmpDir.'/'.$token.'.html';
        $pdfPath = $tmpDir.'/'.$token.'.pdf';

        @file_put_contents($htmlPath, $html);
        if (! is_file($htmlPath)) {
            return null;
        }

        $binary = $this->detectChromeBinary();
        if (! $binary) {
            @unlink($htmlPath);
            return null;
        }

        $command = implode(' ', [
            escapeshellarg($binary),
            '--headless',
            '--no-sandbox',
            '--disable-gpu',
            '--disable-dev-shm-usage',
            '--no-pdf-header-footer',
            '--print-to-pdf-no-header',
            '--virtual-time-budget=7000',
            '--print-to-pdf='.escapeshellarg($pdfPath),
            escapeshellarg('file://'.$htmlPath),
        ]);

        try {
            @exec($command, $output, $code);
        } catch (Throwable) {
            $code = 1;
        }

        $pdf = ($code === 0 && is_file($pdfPath))
            ? @file_get_contents($pdfPath)
            : null;

        @unlink($htmlPath);
        @unlink($pdfPath);

        return $pdf ?: null;
    }

    private function detectChromeBinary(): ?string
    {
        $candidates = array_filter([
            env('REPORTS_PDF_CHROME_BINARY'),
            '/Applications/Google Chrome.app/Contents/MacOS/Google Chrome',
            '/usr/bin/google-chrome',
            '/usr/bin/chromium-browser',
            '/usr/bin/chromium',
        ]);

        foreach ($candidates as $candidate) {
            if (is_string($candidate) && is_file($candidate) && is_executable($candidate)) {
                return $candidate;
            }
        }

        return null;
    }

}
