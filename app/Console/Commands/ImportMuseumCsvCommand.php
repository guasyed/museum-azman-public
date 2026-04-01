<?php

namespace App\Console\Commands;

use App\Models\Artist;
use App\Models\Artwork;
use App\Models\ArtworkImage;
use App\Models\Country;
use App\Models\Location;
use App\Services\ImageOptimizer;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ImportMuseumCsvCommand extends Command
{
    protected $signature = 'museum:import-csv
                            {path : Absolute or relative path to the CSV file}
                            {--connection= : Laravel database connection name}
                            {--download-images : Download and optimize image URLs into storage}
                            {--skip-images : Skip image downloads (faster for large imports)}
                            {--fresh : Truncate artworks and artwork_images before importing}';

    protected $description = 'Import museum artworks from CSV format';

    public function __construct(private readonly ImageOptimizer $imageOptimizer)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        set_time_limit(0);
        ini_set('memory_limit', '-1');

        $path = (string) $this->argument('path');
        $absolutePath = str_starts_with($path, '/') ? $path : base_path($path);

        if (! is_file($absolutePath)) {
            $this->error("File not found: {$absolutePath}");

            return self::FAILURE;
        }

        $connection = $this->option('connection');
        if (is_string($connection) && $connection !== '' && ! config("database.connections.{$connection}")) {
            $this->error("Unknown database connection: {$connection}");

            return self::FAILURE;
        }

        $originalConnection = DB::getDefaultConnection();
        if (is_string($connection) && $connection !== '') {
            DB::setDefaultConnection($connection);
        }

        try {
            if ($this->option('fresh')) {
                $this->truncateArtworks();
            }
            $skipImages = (bool) $this->option('skip-images');
            $downloadImages = (bool) $this->option('download-images') && ! $skipImages;
            $this->importCsv($absolutePath, $downloadImages);
        } finally {
            DB::setDefaultConnection($originalConnection);
        }

        return self::SUCCESS;
    }

    private function importCsv(string $absolutePath, bool $downloadImages): void
    {
        set_time_limit(0);

        $this->info('Starting CSV import...');
        $reader = new \SplFileObject($absolutePath, 'r');
        $reader->setFlags(
            \SplFileObject::READ_CSV
            | \SplFileObject::SKIP_EMPTY
            | \SplFileObject::DROP_NEW_LINE
        );

        $firstRow = $reader->fgetcsv();
        if (! is_array($firstRow) || count(array_filter($firstRow, static fn ($header) => trim((string) $header) !== '')) === 0) {
            $this->warn('No header row found in CSV file.');

            return;
        }

        $defaultHeaders = [
            'artist',
            'description',
            'year',
            'image_1',
            'image_2',
            'image_3',
            'country',
            'dob',
            'artwork',
            'location',
            'date_of_acquisition',
            'price_of_acquisition',
        ];

        $firstRowNormalized = array_map(fn ($value) => $this->normalizeHeader((string) $value), $firstRow);
        $looksLikeHeader = in_array('artist', $firstRowNormalized, true)
            && (in_array('artwork', $firstRowNormalized, true) || in_array('artwork_', $firstRowNormalized, true));

        $headers = $looksLikeHeader ? $firstRowNormalized : $defaultHeaders;
        $pendingFirstDataRow = $looksLikeHeader ? null : $firstRow;

        $created = 0;
        $skipped = 0;
        $imagesSaved = 0;
        $rowIndex = 0;

        $processRow = function (array $row) use ($headers, $downloadImages, &$created, &$skipped, &$imagesSaved): void {
            $mapped = $this->mapRow($headers, $row);
            if ($mapped === null) {
                $skipped++;
                return;
            }

            $artist = Artist::query()->create([
                'name' => $mapped['artist_name'],
                'country_id' => $this->resolveCountryId($mapped['artist_country']),
                'country' => $mapped['artist_country'],
                'birth_year' => $mapped['artist_birth_year'],
            ]);

            $location = Location::query()->create([
                'name' => $mapped['location_name'] ?: 'Unknown Location',
                'type' => 'Imported',
            ]);

            $payload = array_merge($mapped['artwork_payload'], [
                'artist_id' => $artist->id,
                'location_id' => $location->id,
            ]);

            $artwork = Artwork::query()->create(array_merge($payload, [
                'slug' => $this->uniqueSlug($mapped['title']),
            ]));
            $created++;

            if (! $downloadImages || $mapped['image_urls'] === []) {
                return;
            }

            foreach ($mapped['image_urls'] as $index => $imageUrl) {
                $stored = $this->imageOptimizer->storeFromUrl($imageUrl);
                if ($stored === null) {
                    continue;
                }

                ArtworkImage::query()->create([
                    'artwork_id' => $artwork->id,
                    'path' => $stored['path'],
                    'mime_type' => $stored['mime_type'] ?? null,
                    'size_bytes' => $stored['size_bytes'] ?? null,
                    'position' => $index,
                    'original_name' => $stored['original_name'] ?? null,
                ]);

                if ($index === 0 && $this->shouldReplacePrimaryImage($artwork, $stored['path'])) {
                    $artwork->update(['primary_image_path' => $stored['path']]);
                }

                $imagesSaved++;
            }
        };

        DB::transaction(function () use ($reader, $pendingFirstDataRow, $processRow, &$rowIndex, &$created, &$skipped): void {
            if (is_array($pendingFirstDataRow)) {
                $rowIndex++;
                $processRow($pendingFirstDataRow);
            }

            foreach ($reader as $row) {
                $rowIndex++;

                if ($rowIndex % 50 === 0) {
                    $this->line("Processing row {$rowIndex}... (Inserted: {$created}, Skipped: {$skipped})");
                }

                if (! is_array($row)) {
                    continue;
                }

                $processRow($row);
            }
        });

        $this->info("Import completed. Inserted: {$created}, Skipped: {$skipped}, Images saved: {$imagesSaved}");
    }

    private function truncateArtworks(): void
    {
        $this->warn('--fresh: truncating artwork_images and artworks...');
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        DB::table('artwork_images')->truncate();
        DB::table('artworks')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1');
        $this->info('Tables cleared. Starting fresh import.');
    }

    private function shouldReplacePrimaryImage(Artwork $artwork, string $newPath): bool
    {
        if ($artwork->primary_image_path === null || $artwork->primary_image_path === '') {
            return true;
        }

        if ($artwork->primary_image_path === $newPath) {
            return false;
        }

        return ! Storage::disk('public')->exists($artwork->primary_image_path);
    }

    private function mapRow(array $headers, array $row): ?array
    {
        $data = [];
        foreach ($headers as $i => $header) {
            if ($header === '') {
                continue;
            }

            $data[$header] = $row[$i] ?? null;
        }

        if ($this->isEffectivelyEmptyRow($data)) {
            return null;
        }

        $artistName = $this->firstNonEmpty($data, ['artist', 'artist_name']) ?? 'Unknown Artist';
        $title = $this->firstNonEmpty($data, ['artwork', 'artwork_', 'title', 'artwork_title']) ?? 'Untitled';

        $description = $this->firstNonEmpty($data, ['description', 'details']);
        $locationName = $this->firstNonEmpty($data, ['location', 'location_name']) ?? 'Unknown Location';
        $acquisitionPrice = $this->parseDecimal($this->firstNonEmpty($data, ['price_of_acquisition', 'acquisition_price']));

        $imageUrls = array_values(array_filter([
            $this->firstNonEmpty($data, ['image_1', 'image1']),
            $this->firstNonEmpty($data, ['image_2', 'image2']),
            $this->firstNonEmpty($data, ['image_3', 'image3']),
        ]));

        return [
            'artist_name' => $artistName,
            'artist_country' => $this->firstNonEmpty($data, ['country', 'artist_country']),
            'artist_birth_year' => $this->parseYear($this->firstNonEmpty($data, ['dob', 'birth_year'])),
            'location_name' => $locationName,
            'title' => $title,
            'image_urls' => $imageUrls,
            'artwork_payload' => [
                'title' => $title,
                'year' => $this->parseYear($this->firstNonEmpty($data, ['year'])),
                'description' => $description,
                'medium' => $this->extractMedium($description),
                'size_from_cm' => $this->extractSmallestDimensionCm($description),
                'size_to_cm' => $this->extractLargestDimensionCm($description),
                'acquisition_date' => $this->parseDate($this->firstNonEmpty($data, ['date_of_acquisition', 'acquisition_date'])),
                'acquisition_price' => $acquisitionPrice,
                'current_valuation' => $acquisitionPrice,
                'status' => 'On Display',
                'source_image_url' => $imageUrls[0] ?? null,
            ],
        ];
    }

    private function firstNonEmpty(array $data, array $keys): ?string
    {
        foreach ($keys as $key) {
            $value = $this->nullIfEmpty($data[$key] ?? null);
            if ($value !== null) {
                return $value;
            }
        }

        return null;
    }

    private function isEffectivelyEmptyRow(array $data): bool
    {
        foreach ($data as $value) {
            if ($this->nullIfEmpty($value) !== null) {
                return false;
            }
        }

        return true;
    }

    private function normalizeHeader(string $header): string
    {
        $header = trim($header);
        if ($header === '') {
            return '';
        }

        return Str::of($header)
            ->lower()
            ->replaceMatches('/[^a-z0-9]+/i', '_')
            ->trim('_')
            ->toString();
    }

    private function parseYear(mixed $value): ?int
    {
        $text = $this->nullIfEmpty($value);
        if ($text === null) {
            return null;
        }

        if (! preg_match('/\b(\d{4})\b/', $text, $matches)) {
            return null;
        }

        $year = (int) $matches[1];
        $currentYear = (int) date('Y');

        return ($year >= 1000 && $year <= $currentYear) ? $year : null;
    }

    private function parseDate(mixed $value): ?string
    {
        $text = $this->nullIfEmpty($value);
        if ($text === null) {
            return null;
        }

        try {
            return Carbon::parse($text)->toDateString();
        } catch (\Throwable) {
            return null;
        }
    }

    private function parseDecimal(mixed $value): ?float
    {
        $text = $this->nullIfEmpty($value);
        if ($text === null) {
            return null;
        }

        $normalized = str_replace([',', ' '], '', $text);

        return is_numeric($normalized) ? (float) $normalized : null;
    }

    private function extractMedium(?string $description): ?string
    {
        if ($description === null) {
            return null;
        }

        $lines = preg_split('/\R+/', $description) ?: [];
        foreach ($lines as $line) {
            $clean = trim($line);
            if ($clean === '') {
                continue;
            }

            if (preg_match('/\b\d{4}\b/', $clean) === 1) {
                continue;
            }

            if (preg_match('/\b\d+(?:\.\d+)?\s*cm\b/i', $clean) === 1) {
                continue;
            }

            return $clean;
        }

        return null;
    }

    private function extractSmallestDimensionCm(?string $description): ?float
    {
        $values = $this->extractDimensionValues($description);
        if ($values === []) {
            return null;
        }

        return min($values);
    }

    private function extractLargestDimensionCm(?string $description): ?float
    {
        $values = $this->extractDimensionValues($description);
        if ($values === []) {
            return null;
        }

        return max($values);
    }

    /**
     * @return array<int, float>
     */
    private function extractDimensionValues(?string $description): array
    {
        if ($description === null) {
            return [];
        }

        preg_match_all('/(\d+(?:\.\d+)?)\s*cm\b/i', $description, $matches);

        if (! isset($matches[1]) || ! is_array($matches[1])) {
            return [];
        }

        $values = [];
        foreach ($matches[1] as $match) {
            if (is_numeric($match)) {
                $values[] = (float) $match;
            }
        }

        return $values;
    }

    private function nullIfEmpty(mixed $value): ?string
    {
        $clean = trim((string) $value);

        return $clean === '' ? null : $clean;
    }

    private function uniqueSlug(string $title): string
    {
        $base = Str::slug($title);
        $slug = $base;
        $counter = 2;

        while (Artwork::query()->where('slug', $slug)->exists()) {
            $slug = $base.'-'.$counter;
            $counter++;
        }

        return $slug;
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
}