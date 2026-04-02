<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreArtworkRequest;
use App\Http\Requests\UpdateArtworkRequest;
use App\Models\Artist;
use App\Models\Artwork;
use App\Models\Country;
use App\Models\Location;
use App\Models\Movement;
use App\Models\Status;
use App\Services\ImageOptimizer;
use App\Services\ActivityLogger;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Throwable;

class ArtworkController extends Controller
{
    private ?string $lastPdfError = null;

    public function __construct(private readonly ImageOptimizer $imageOptimizer)
    {
    }

    public function index(Request $request): View
    {
        $filters = $this->extractArtworkFilters($request);

        $regionOptions = Artist::query()
            ->whereNotNull('country')
            ->where('country', '!=', '')
            ->orderBy('country')
            ->pluck('country')
            ->unique()
            ->values();

        $statusOptions = collect(Status::allowedNames());

        $artworks = $this->buildArtworkQuery(
            q: $filters['q'],
            qLower: $filters['qLower'],
            selectedRegion: $filters['selectedRegion'],
            selectedStatus: $filters['selectedStatus'],
            sortColumn: $filters['sortColumn'],
            direction: $filters['direction']
        )
            ->with([
                'artist',
                'location',
                'images:id,artwork_id,path,position',
            ])
            ->paginate(24)
            ->withPath($request->getPathInfo())
            ->withQueryString();

        if ($filters['sortColumn'] === 'created_at') {
            $artworks->setCollection(
                $artworks->getCollection()
                    ->sortBy([
                        fn (Artwork $artwork) => $artwork->primary_image_url ? 0 : 1,
                        fn (Artwork $artwork) => -((int) optional($artwork->created_at)->getTimestamp()),
                        fn (Artwork $artwork) => -((int) optional($artwork->updated_at)->getTimestamp()),
                    ])
                    ->values()
            );
        }

        return view('artworks.index', [
            'artworks' => $artworks,
            'q' => $filters['q'],
            'selectedRegion' => $filters['selectedRegion'],
            'selectedStatus' => $filters['selectedStatus'],
            'regionOptions' => $regionOptions,
            'statusOptions' => $statusOptions,
            'view' => $filters['view'],
            'sortColumn' => $filters['sortColumn'],
            'direction' => $filters['direction'],
        ]);
    }

    public function create(): View
    {
        return view('artworks.create', $this->artworkLocationFormData());
    }

    public function exportPdf(Request $request)
	{
		@ini_set('memory_limit', '512M');
		@set_time_limit(300);

		$filters = $this->extractArtworkFilters($request);
		$page = max(1, (int) $request->integer('page', 1));
		$perPage = 24;

		$artworks = $this->buildArtworkQuery(
			q: $filters['q'],
			qLower: $filters['qLower'],
			selectedRegion: $filters['selectedRegion'],
            selectedStatus: $filters['selectedStatus'],
            sortColumn: $filters['sortColumn'],
            direction: $filters['direction']
		)
			->with([
				'artist',
				'location',
				'images:id,artwork_id,path,position',
			])
			->paginate($perPage, ['*'], 'page', $page)
			->withQueryString();

		$viewData = [
			'artworks' => $artworks,
			'view' => $filters['view'],
			'q' => $filters['q'],
			'selectedRegion' => $filters['selectedRegion'],
			'selectedStatus' => $filters['selectedStatus'],
			'exportedAt' => now(),
		];

		$filename = 'artworks-' . now()->format('Ymd-His') . '.pdf';
		$html = view('artworks.pdf', $viewData)->render();

		$chromePdf = null;

		try {
			$chromePdf = $this->renderPdfWithChrome($html);
        } catch (\Throwable $e) {
            Log::warning('Chrome PDF render failed, falling back to DomPDF', [
				'message' => $e->getMessage(),
			]);
		}

		if ($chromePdf !== null) {
			return response($chromePdf, 200, [
				'Content-Type' => 'application/pdf',
				'Content-Disposition' => 'attachment; filename="' . $filename . '"',
				'Content-Length' => (string) strlen($chromePdf),
			]);
		}

		$pdf = Pdf::loadView('artworks.pdf', $viewData)
			->setPaper('a4', $filters['view'] === 'grid' ? 'landscape' : 'portrait');

		return $pdf->download($filename);
	}

    public function suggestions(Request $request): JsonResponse
    {
        $q = trim((string) $request->string('q'));

        if ($q === '') {
            return response()->json([
                'groups' => [
                    'products' => [],
                    'suggested_searches' => [],
                ],
            ]);
        }

        $needle = Str::lower($q);
        $like = '%' . $needle . '%';

        $titleMatches = Artwork::query()
            ->whereRaw('LOWER(title) LIKE ?', [$like])
            ->count();

        $artistMatches = Artist::query()
            ->whereRaw('LOWER(name) LIKE ?', [$like])
            ->count();

        $mediumMatches = Artwork::query()
            ->whereRaw('LOWER(medium) LIKE ?', [$like])
            ->count();

        $products = collect([
            $titleMatches > 0 ? [
                'label' => $q . ' in Titles',
                'meta' => number_format($titleMatches) . ' matches',
                'value' => $q,
            ] : null,
            $artistMatches > 0 ? [
                'label' => $q . ' in Artists',
                'meta' => number_format($artistMatches) . ' matches',
                'value' => $q,
            ] : null,
            $mediumMatches > 0 ? [
                'label' => $q . ' in Mediums',
                'meta' => number_format($mediumMatches) . ' matches',
                'value' => $q,
            ] : null,
        ])->filter()->values();

        $items = Artwork::query()
            ->with(['artist:id,name'])
            ->whereNotNull('title')
            ->where('title', '!=', '')
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($inner) use ($q) {
                    $like = '%' . Str::lower($q) . '%';
                    $inner->whereRaw('LOWER(title) LIKE ?', [$like])
                        ->orWhereRaw('LOWER(medium) LIKE ?', [$like])
                        ->orWhereHas('artist', fn ($artistQuery) => $artistQuery->whereRaw('LOWER(name) LIKE ?', [$like]));
                });
            })
            ->latest('id')
            ->limit(30)
            ->get()
            ->sortBy([
                fn (Artwork $artwork) => str_starts_with(Str::lower($artwork->title), $needle) ? 0 : 1,
                fn (Artwork $artwork) => str_starts_with(Str::lower((string) $artwork->artist?->name), $needle) ? 0 : 1,
                fn (Artwork $artwork) => str_starts_with(Str::lower((string) $artwork->medium), $needle) ? 0 : 1,
                fn (Artwork $artwork) => mb_strpos(Str::lower($artwork->title), $needle) !== false ? mb_strpos(Str::lower($artwork->title), $needle) : 999,
                fn (Artwork $artwork) => mb_strpos(Str::lower((string) $artwork->artist?->name), $needle) !== false ? mb_strpos(Str::lower((string) $artwork->artist?->name), $needle) : 999,
                fn (Artwork $artwork) => mb_strpos(Str::lower((string) $artwork->medium), $needle) !== false ? mb_strpos(Str::lower((string) $artwork->medium), $needle) : 999,
                fn (Artwork $artwork) => mb_strlen($artwork->title),
                fn (Artwork $artwork) => Str::lower($artwork->title),
            ])
            ->take(10)
            ->values()
            ->map(function (Artwork $artwork) {
                $subtitle = collect([
                    $artwork->artist?->name,
                    $artwork->year ? (string) $artwork->year : null,
                    $artwork->medium,
                ])->filter()->implode(' • ');

                return [
                    'value' => $artwork->title,
                    'label' => $artwork->title,
                    'meta' => $subtitle ?: null,
                    'thumbnail' => $artwork->primary_image_url,
                ];
            });

        return response()->json([
            'groups' => [
                'products' => $products,
                'suggested_searches' => $items,
            ],
        ]);
    }

    public function store(StoreArtworkRequest $request): RedirectResponse|JsonResponse
    {
        $artwork = DB::transaction(function () use ($request) {
            $countryName = $this->cleanCountryName($request->input('artist_country'));

            $artist = Artist::updateOrCreate(
                ['name' => $request->string('artist_name')->trim()->toString()],
                [
                    'country' => $countryName,
                    'country_id' => $this->resolveCountryId($countryName),
                    'birth_year' => $request->input('artist_birth_year'),
                ]
            );

            $location = Location::firstOrCreate(
                ['name' => $request->string('location_name')->trim()->toString()],
                [
                    'type' => $request->input('location_type'),
                    'address' => $request->input('location_address'),
                ]
            );

            $title = $request->string('title')->trim()->toString();

            $artwork = new Artwork($this->payload($request));
            $artwork->artist()->associate($artist);
            $artwork->location()->associate($location);
            $artwork->slug = $this->uniqueSlug($title);

            if ($request->hasFile('primary_image')) {
                $primary = $this->imageOptimizer->storeUploaded($request->file('primary_image'));
                $artwork->primary_image_path = $primary['path'] ?? null;
            }

            $artwork->save();

            $this->storeGalleryImages($artwork, $request->file('gallery_images', []));

            return $artwork;
        });

        ActivityLogger::log('artwork.created', "Artwork created: {$artwork->title}", $artwork);

        $redirectUrl = route('artworks.show', $artwork);

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Artwork created successfully.',
                'redirect_url' => $redirectUrl,
            ]);
        }

        return redirect()
            ->to($redirectUrl)
            ->with('success', 'Artwork created successfully.');
    }

    public function show(Artwork $artwork): View
    {
        $artwork->load(['artist', 'location', 'images', 'movements']);

        $locationOptions = Location::query()
            ->whereNotNull('name')
            ->where('name', '!=', '')
            ->orderBy('name')
            ->pluck('name')
            ->sort()
            ->values();

        $reasonOptions = ['Loan', 'Exhibition', 'Storage', 'Restoration', 'Sale Prep'];
        $statusOptions = ['Scheduled', 'In Transit', 'Completed', 'Overdue'];

        return view('artworks.show', compact(
            'artwork',
            'locationOptions',
            'reasonOptions',
            'statusOptions'
        ));
    }

    public function edit(Artwork $artwork): View
    {
        $artwork->load(['artist', 'location', 'images']);

        return view('artworks.edit', array_merge(
            compact('artwork'),
            $this->artworkLocationFormData()
        ));
    }

    private function artworkLocationFormData(): array
    {
        $locationOptions = Location::query()
            ->select('name', 'type', 'address')
            ->whereNotNull('name')
            ->where('name', '!=', '')
            ->orderBy('name')
            ->get()
            ->unique('name')
            ->values();

        $locationTypeOptions = $locationOptions
            ->pluck('type')
            ->filter(fn ($value) => is_string($value) && trim($value) !== '')
            ->unique()
            ->values();

        $statusOptions = collect(Status::allowedNames());

        return compact('locationOptions', 'locationTypeOptions', 'statusOptions');
    }

    public function update(UpdateArtworkRequest $request, Artwork $artwork): RedirectResponse|JsonResponse
    {
        $origin = $request->string('from')->toString() === 'dashboard' ? 'dashboard' : 'collection';
        $returnUrl = $request->input('return', $request->query('return'));
        $selectedPrimaryGalleryImageId = $request->integer('primary_gallery_image_id');
        $isSafeReturnUrl = $this->isSafeReturnUrl($request, $returnUrl);

        DB::transaction(function () use ($request, $artwork, $selectedPrimaryGalleryImageId) {
            $countryName = $this->cleanCountryName($request->input('artist_country'));

            $artist = Artist::updateOrCreate(
                ['name' => $request->string('artist_name')->trim()->toString()],
                [
                    'country' => $countryName,
                    'country_id' => $this->resolveCountryId($countryName),
                    'birth_year' => $request->input('artist_birth_year'),
                ]
            );

            $location = Location::firstOrCreate(
                ['name' => $request->string('location_name')->trim()->toString()],
                [
                    'type' => $request->input('location_type'),
                    'address' => $request->input('location_address'),
                ]
            );

            $artwork->fill($this->payload($request));
            $artwork->artist()->associate($artist);
            $artwork->location()->associate($location);
            $artwork->slug = $this->uniqueSlug(
                $request->string('title')->trim()->toString(),
                $artwork->id
            );

            if ($request->hasFile('primary_image')) {
                if ($artwork->primary_image_path) {
                    Storage::disk('public')->delete($artwork->primary_image_path);
                }

                $primary = $this->imageOptimizer->storeUploaded($request->file('primary_image'));
                $artwork->primary_image_path = $primary['path'] ?? $artwork->primary_image_path;
            }

            $artwork->save();

            $removeIds = collect($request->input('remove_gallery_image_ids', []))
                ->map(fn ($id) => (int) $id)
                ->filter();

            if ($removeIds->isNotEmpty()) {
                $images = $artwork->images()
                    ->whereIn('id', $removeIds->all())
                    ->get();

                foreach ($images as $image) {
                    Storage::disk('public')->delete($image->path);
                    $image->delete();
                }
            }

            $this->storeGalleryImages($artwork, $request->file('gallery_images', []));

            if ($selectedPrimaryGalleryImageId) {
                $selectedPrimaryImage = $artwork->images()
                    ->whereKey($selectedPrimaryGalleryImageId)
                    ->first();

                if ($selectedPrimaryImage) {
                    $artwork->primary_image_path = $selectedPrimaryImage->path;
                    $artwork->save();
                }
            }
        });

        ActivityLogger::log('artwork.updated', "Artwork updated: {$artwork->title}", $artwork);

        $redirectUrl = $isSafeReturnUrl
            ? (string) $returnUrl
            : route('artworks.show', [
                'artwork' => $artwork,
                'from' => $origin,
            ]);

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Artwork updated successfully.',
                'redirect_url' => $redirectUrl,
            ]);
        }

        return redirect()
            ->to($redirectUrl)
            ->with('success', 'Artwork updated successfully.');
    }

    private function isSafeReturnUrl(Request $request, mixed $returnUrl): bool
    {
        if (! is_string($returnUrl)) {
            return false;
        }

        $returnUrl = trim($returnUrl);
        if ($returnUrl === '') {
            return false;
        }

        $path = parse_url($returnUrl, PHP_URL_PATH);
        if (! is_string($path) || ! str_starts_with($path, '/')) {
            return false;
        }

        $host = parse_url($returnUrl, PHP_URL_HOST);

        // Relative paths are safe. Absolute URLs must match current host.
        if ($host === null) {
            return ! str_starts_with($returnUrl, '//');
        }

        return $host === $request->getHost();
    }

    public function destroy(Artwork $artwork): RedirectResponse
    {
        if ($artwork->primary_image_path) {
            Storage::disk('public')->delete($artwork->primary_image_path);
        }

        foreach ($artwork->images as $image) {
            Storage::disk('public')->delete($image->path);
        }

        $artwork->delete();

        ActivityLogger::log('artwork.deleted', "Artwork deleted: {$artwork->title}", $artwork);

        return redirect()
            ->route('artworks.index')
            ->with('success', 'Artwork deleted.');
    }

    private function extractArtworkFilters(Request $request): array
    {
        $q = trim((string) $request->string('q'));

        return [
            'q' => $q,
            'qLower' => Str::lower($q),
            'selectedRegion' => trim((string) $request->string('region')),
            'selectedStatus' => trim((string) $request->string('status')),
            'view' => in_array((string) $request->string('view'), ['grid', 'table'], true)
                ? (string) $request->string('view')
                : 'grid',
            'sortColumn' => in_array((string) $request->string('sort', 'created_at'), ['created_at', 'title', 'current_valuation'], true)
                ? (string) $request->string('sort', 'created_at')
                : 'created_at',
            'direction' => strtolower((string) $request->string('direction', 'desc')) === 'asc'
                ? 'asc'
                : 'desc',
        ];
    }

    private function buildArtworkQuery(
        string $q,
        string $qLower,
        string $selectedRegion,
        string $selectedStatus,
        string $sortColumn,
        string $direction
    ): Builder {
        return Artwork::query()
            ->when($q !== '', function ($query) use ($qLower) {
                $query->where(function ($inner) use ($qLower) {
                    $like = "%{$qLower}%";

                    $inner->whereRaw('LOWER(title) LIKE ?', [$like])
                        ->orWhereRaw('LOWER(medium) LIKE ?', [$like])
                        ->orWhereHas('artist', fn ($artistQuery) => $artistQuery->whereRaw('LOWER(name) LIKE ?', [$like]));
                });
            })
            ->when(
                $selectedRegion !== '',
                fn ($query) => $query->whereHas('artist', fn ($artistQuery) => $artistQuery->where('country', $selectedRegion))
            )
            ->when(
                $selectedStatus !== '',
                fn ($query) => $query->where('status', $selectedStatus)
            )
            ->when($sortColumn === 'title', fn ($query) => $query->orderBy('title', $direction))
            ->when($sortColumn === 'current_valuation', fn ($query) => $query->orderBy('current_valuation', $direction))
            ->when($sortColumn === 'created_at', function ($query) use ($direction) {
                if ($direction === 'asc') {
                    $query
                        ->withCount('images')
                        ->orderByRaw("CASE WHEN (primary_image_path IS NOT NULL AND primary_image_path <> '') THEN 2 WHEN images_count > 0 THEN 1 ELSE 0 END DESC")
                        ->orderBy('created_at', 'asc')
                        ->orderBy('updated_at', 'asc');

                    return;
                }

                $query
                    ->withCount('images')
                    ->orderByRaw("CASE WHEN (primary_image_path IS NOT NULL AND primary_image_path <> '') THEN 2 WHEN images_count > 0 THEN 1 ELSE 0 END DESC")
                    ->orderByDesc('created_at')
                    ->orderByDesc('updated_at');
            });
    }

    private function payload(StoreArtworkRequest|UpdateArtworkRequest $request): array
    {
        return [
            'title' => $request->string('title')->trim()->toString(),
            'year' => $request->input('year'),
            'description' => $request->input('description'),
            'medium' => $request->input('medium'),
            'size_from_cm' => $request->input('size_from_cm'),
            'size_to_cm' => $request->input('size_to_cm'),
            'acquisition_date' => $request->input('acquisition_date'),
            'acquisition_price' => $request->input('acquisition_price'),
            'current_valuation' => $request->input('current_valuation'),
            'status' => $request->input('status', Status::DEFAULT_NAMES[0]),
            'provenance' => $request->input('provenance'),
        ];
    }

    private function uniqueSlug(string $title, ?int $ignoreId = null): string
    {
        $base = Str::slug($title);
        $slug = $base;
        $i = 2;

        while (
            Artwork::query()
                ->where('slug', $slug)
                ->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))
                ->exists()
        ) {
            $slug = $base . '-' . $i;
            $i++;
        }

        return $slug;
    }

    private function storeGalleryImages(Artwork $artwork, array $files): void
    {
        $nextPosition = (int) $artwork->images()->max('position') + 1;

        foreach ($files as $file) {
            $stored = $this->imageOptimizer->storeUploaded($file);

            if (! $stored) {
                continue;
            }

            $artwork->images()->create([
                'path' => $stored['path'],
                'mime_type' => $stored['mime_type'],
                'size_bytes' => $stored['size_bytes'],
                'original_name' => $stored['original_name'],
                'position' => $nextPosition++,
            ]);

            if (! $artwork->primary_image_path) {
                $artwork->primary_image_path = $stored['path'];
                $artwork->save();
            }
        }
    }

    private function renderPdfWithChrome(string $html): ?string
    {
        $this->lastPdfError = null;

        $binary = $this->detectChromeBinary();
        if (! $binary) {
            $this->lastPdfError = 'Chrome/Chromium binary is not available on this server.';
            return null;
        }

        if (! function_exists('proc_open')) {
            $this->lastPdfError = 'proc_open is disabled on this server.';
            return null;
        }

        $disabledFunctions = array_map('trim', explode(',', (string) ini_get('disable_functions')));
        if (in_array('proc_open', $disabledFunctions, true)) {
            $this->lastPdfError = 'proc_open is disabled by server configuration.';
            return null;
        }

        $tmpDir = storage_path('app/tmp');
        if (! is_dir($tmpDir)) {
            @mkdir($tmpDir, 0775, true);
        }

        $token = str_replace('.', '', uniqid('collection_', true));
        $htmlPath = $tmpDir . '/' . $token . '.html';
        $pdfPath = $tmpDir . '/' . $token . '.pdf';

        @file_put_contents($htmlPath, $html);

        if (! is_file($htmlPath)) {
            $this->lastPdfError = 'Unable to create temporary HTML file.';
            return null;
        }

        $command = implode(' ', [
            escapeshellarg($binary),
            '--headless',
            '--no-sandbox',
            '--disable-gpu',
            '--disable-dev-shm-usage',
            '--disable-background-networking',
            '--disable-component-update',
            '--disable-sync',
            '--no-first-run',
            '--metrics-recording-only',
            '--mute-audio',
            '--no-pdf-header-footer',
            '--print-to-pdf-no-header',
            '--virtual-time-budget=12000',
            '--print-to-pdf=' . escapeshellarg($pdfPath),
            escapeshellarg('file://' . $htmlPath),
        ]);

        try {
            $code = $this->runCommandWithTimeout($command, 25);
        } catch (Throwable $e) {
            $code = 1;
            $this->lastPdfError = 'Chrome PDF renderer failed: ' . $e->getMessage();
        }

        if ($code === 124) {
            $this->lastPdfError = 'PDF generation timed out while rendering in Chrome.';
        } elseif ($code !== 0 && $this->lastPdfError === null) {
            $this->lastPdfError = 'Chrome renderer exited with code ' . $code . '.';
        }

        $pdf = ($code === 0 && is_file($pdfPath))
            ? @file_get_contents($pdfPath)
            : null;

        if ($code === 0 && ! is_file($pdfPath)) {
            $this->lastPdfError = 'Chrome did not produce a PDF file.';
        }

        @unlink($htmlPath);
        @unlink($pdfPath);

        return $pdf ?: null;
    }

    private function runCommandWithTimeout(string $command, int $timeoutSeconds): int
    {
        $spec = [
            0 => ['pipe', 'r'],
            1 => ['pipe', 'w'],
            2 => ['pipe', 'w'],
        ];

        $process = @proc_open($command, $spec, $pipes);

        if (! is_resource($process)) {
            return 1;
        }

        foreach ($pipes as $pipe) {
            if (is_resource($pipe)) {
                stream_set_blocking($pipe, false);
            }
        }

        $startedAt = microtime(true);
        $exitCode = null;

        while (true) {
            $status = proc_get_status($process);

            if (! ($status['running'] ?? false)) {
                $exitCode = (int) ($status['exitcode'] ?? 1);
                break;
            }

            if ((microtime(true) - $startedAt) >= $timeoutSeconds) {
                @proc_terminate($process, 9);
                $exitCode = 124;
                break;
            }

            foreach ($pipes as $pipe) {
                if (is_resource($pipe)) {
                    @stream_get_contents($pipe);
                }
            }

            usleep(100000);
        }

        foreach ($pipes as $pipe) {
            if (is_resource($pipe)) {
                @fclose($pipe);
            }
        }

        @proc_close($process);

        return $exitCode ?? 1;
    }

    protected function detectChromeBinary(): ?string
    {
        $candidates = array_filter([
            env('REPORTS_PDF_CHROME_BINARY'),
            env('PUPPETEER_EXECUTABLE_PATH'),
            '/usr/bin/google-chrome',
            '/usr/bin/google-chrome-stable',
            '/usr/bin/chromium-browser',
            '/usr/bin/chromium',
            '/usr/bin/chrome',
            '/usr/bin/microsoft-edge',
            '/usr/bin/msedge',
            '/usr/bin/brave-browser',
            '/opt/google/chrome/chrome',
        ]);

        foreach ($candidates as $path) {
            if (is_string($path) && $path !== '' && is_file($path) && is_executable($path)) {
                return $path;
            }
        }

        return null;
    }

    private function resolveCountryId(?string $countryName): ?int
    {
        if ($countryName === null || $countryName === '') {
            return null;
        }

        return Country::query()->firstOrCreate([
            'name' => $countryName,
        ])->id;
    }

    private function cleanCountryName(mixed $value): ?string
    {
        $countryName = trim((string) $value);

        return $countryName === '' ? null : $countryName;
    }
}