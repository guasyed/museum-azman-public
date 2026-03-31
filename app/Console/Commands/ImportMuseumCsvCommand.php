<?php

namespace App\Console\Commands;

use App\Models\Artist;
use App\Models\Artwork;
use App\Models\ArtworkImage;
use App\Models\Location;
use App\Services\ImageOptimizer;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
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
        $updated = 0;
        $skipped = 0;
        $imagesSaved = 0;
        $rowIndex = 0;

        $processRow = function (array $row) use ($headers, $downloadImages, &$created, &$updated, &$skipped, &$imagesSaved): void {
            $mapped = $this->mapRow($headers, $row);
            if ($mapped === null) {
                $skipped++;
                return;
            }

            $artist = Artist::query()->updateOrCreate(
                ['name' => $mapped['artist_name']],
                [
                    'country' => $mapped['artist_country'],
                    'birth_year' => $mapped['artist_birth_year'],
                ]
            );

            $location = Location::query()->firstOrCreate(
                ['name' => $mapped['location_name'] ?: 'Unknown Location'],
                ['type' => 'Imported']
            );

            $artwork = Artwork::query()
                ->where('artist_id', $artist->id)
                ->where('title', $mapped['title'])
                ->where('description', $mapped['artwork_payload']['description'])
                ->where('source_image_url', $mapped['artwork_payload']['source_image_url'])
                ->first();

            $payload = array_merge($mapped['artwork_payload'], [
                'artist_id' => $artist->id,
                'location_id' => $location->id,
            ]);

            if ($artwork) {
                $artwork->fill($payload)->save();
                $updated++;
            } else {
                $artwork = Artwork::query()->create(array_merge($payload, [
                    'slug' => $this->uniqueSlug($mapped['title']),
                ]));
                $created++;
            }

            if (! $downloadImages || $mapped['image_urls'] === []) {
                return;
            }

            foreach ($mapped['image_urls'] as $index => $imageUrl) {
                $stored = $this->imageOptimizer->storeFromUrl($imageUrl);
                if ($stored === null) {
                    continue;
                }

                ArtworkImage::query()->firstOrCreate(
                    [
                        'artwork_id' => $artwork->id,
                        'path' => $stored['path'],
                    ],
                    [
                        'mime_type' => $stored['mime_type'] ?? null,
                        'size_bytes' => $stored['size_bytes'] ?? null,
                        'position' => $index,
                        'original_name' => $stored['original_name'] ?? null,
                    ]
                );

                if ($index === 0 && empty($artwork->primary_image_path)) {
                    $artwork->update(['primary_image_path' => $stored['path']]);
                }

                $imagesSaved++;
            }
        };

        DB::transaction(function () use ($reader, $pendingFirstDataRow, $processRow, &$rowIndex, &$created, &$updated, &$skipped): void {
            if (is_array($pendingFirstDataRow)) {
                $rowIndex++;
                $processRow($pendingFirstDataRow);
            }

            foreach ($reader as $row) {
                $rowIndex++;

                if ($rowIndex % 50 === 0) {
                    $this->line("Processing row {$rowIndex}... (Created: {$created}, Updated: {$updated}, Skipped: {$skipped})");
                }

                if (! is_array($row)) {
                    continue;
                }

                $processRow($row);
            }
        });

        $this->info("Import completed. Created: {$created}, Updated: {$updated}, Skipped: {$skipped}, Images saved: {$imagesSaved}");
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

    private function mapRow(array $headers, array $row): ?array
    {
        $data = [];
        foreach ($headers as $i => $header) {
            if ($header === '') {
                continue;
            }

            $data[$header] = $row[$i] ?? null;
        }

        $artistName = $this->nullIfEmpty($data['artist'] ?? null);
        $title = $this->nullIfEmpty($data['artwork'] ?? null);
        if ($artistName === null || $title === null) {
            return null;
        }

        $description = $this->nullIfEmpty($data['description'] ?? null);
        $locationName = $this->nullIfEmpty($data['location'] ?? null) ?? 'Unknown Location';
        $acquisitionPrice = $this->parseDecimal($data['price_of_acquisition'] ?? null);

        $imageUrls = array_values(array_filter([
            $this->nullIfEmpty($data['image_1'] ?? null),
            $this->nullIfEmpty($data['image_2'] ?? null),
            $this->nullIfEmpty($data['image_3'] ?? null),
        ]));

        return [
            'artist_name' => $artistName,
            'artist_country' => $this->nullIfEmpty($data['country'] ?? null),
            'artist_birth_year' => $this->parseYear($data['dob'] ?? null),
            'location_name' => $locationName,
            'title' => $title,
            'image_urls' => $imageUrls,
            'artwork_payload' => [
                'title' => $title,
                'year' => $this->parseYear($data['year'] ?? null),
                'description' => $description,
                'medium' => $this->extractMedium($description),
                'size_from_cm' => $this->extractSmallestDimensionCm($description),
                'size_to_cm' => $this->extractLargestDimensionCm($description),
                'acquisition_date' => $this->parseDate($data['date_of_acquisition'] ?? null),
                'acquisition_price' => $acquisitionPrice,
                'current_valuation' => $acquisitionPrice,
                'status' => 'On Display',
                'source_image_url' => $imageUrls[0] ?? null,
            ],
        ];
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
}