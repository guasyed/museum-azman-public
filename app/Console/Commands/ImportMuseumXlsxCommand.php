<?php

namespace App\Console\Commands;

use App\Models\Artist;
use App\Models\Artwork;
use App\Models\Country;
use App\Models\Location;
use App\Models\Movement;
use App\Models\Status;
use App\Services\ImageOptimizer;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use ZipArchive;

class ImportMuseumXlsxCommand extends Command
{
    protected $signature = 'museum:import-xlsx
                            {path : Absolute or relative path to the .xlsx file}
                            {--download-images : Download and optimize the first image URL}';

    protected $description = 'Import museum artworks from the Museum_Azman Excel file format';

    public function __construct(private readonly ImageOptimizer $imageOptimizer)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        $path = $this->argument('path');
        $absolutePath = str_starts_with($path, '/') ? $path : base_path($path);

        if (! is_file($absolutePath)) {
            $this->error("File not found: {$absolutePath}");
            return self::FAILURE;
        }

        $workbook = $this->parseWorkbook($absolutePath);

        if (isset($workbook['Cleaned Inventory'])) {
            return $this->importCleanedWorkbook($workbook, (bool) $this->option('download-images'));
        }

        $rows = $this->parseXlsx($absolutePath);

        if (count($rows) < 2) {
            $this->warn('No data rows found in file.');
            return self::SUCCESS;
        }

        $headers = array_shift($rows);
        $created = 0;
        $updated = 0;
        $downloadImages = (bool) $this->option('download-images');

        DB::transaction(function () use ($rows, $headers, &$created, &$updated, $downloadImages) {
            foreach ($rows as $row) {
                $mapped = $this->mapRow($headers, $row);
                if (! $mapped['artist_name'] || ! $mapped['title']) {
                    continue;
                }

                $artist = Artist::firstOrCreate(
                    ['name' => $mapped['artist_name']],
                    [
                        'country_id' => $this->resolveCountryId($mapped['artist_country']),
                        'country' => $mapped['artist_country'],
                        'birth_year' => $mapped['artist_birth_year'],
                    ]
                );

                $location = Location::firstOrCreate(
                    ['name' => $mapped['location_name'] ?: 'Unknown Location'],
                    ['type' => 'Imported']
                );

                $exists = Artwork::query()
                    ->where('title', $mapped['title'])
                    ->where('artist_id', $artist->id)
                    ->first();

                if ($exists) {
                    $exists->update(array_merge($mapped['artwork_payload'], [
                        'artist_id' => $artist->id,
                        'location_id' => $location->id,
                    ]));
                    $artwork = $exists;
                    $updated++;
                } else {
                    $artwork = Artwork::create(array_merge($mapped['artwork_payload'], [
                        'artist_id' => $artist->id,
                        'location_id' => $location->id,
                        'slug' => $this->uniqueSlug($mapped['title']),
                    ]));
                    $created++;
                }

                if ($downloadImages && $mapped['image_url']) {
                    $stored = $this->imageOptimizer->storeFromUrl($mapped['image_url']);
                    if ($stored) {
                        $artwork->update(['primary_image_path' => $stored['path']]);
                    }
                }
            }
        });

        $this->info("Import completed. Created: {$created}, Updated: {$updated}");

        return self::SUCCESS;
    }

    private function importCleanedWorkbook(array $workbook, bool $downloadImages): int
    {
        $inventoryRows = $this->rowsWithHeaders($workbook['Cleaned Inventory'] ?? []);

        if ($inventoryRows === []) {
            $this->warn('No cleaned inventory rows found in file.');

            return self::SUCCESS;
        }

        $locationRows = $this->rowsWithHeaders($workbook['Location Master'] ?? []);
        $movementRows = $this->rowsWithHeaders($workbook['Movement Log'] ?? []);
        $referenceRows = $this->rowsWithHeaders($workbook['Reference Data'] ?? []);

        $created = 0;
        $updated = 0;
        $movementsCreated = 0;
        $movementsUpdated = 0;
        $downloadedImages = 0;

        DB::transaction(function () use (
            $inventoryRows,
            $locationRows,
            $movementRows,
            $referenceRows,
            $downloadImages,
            &$created,
            &$updated,
            &$movementsCreated,
            &$movementsUpdated,
            &$downloadedImages
        ): void {
            $this->ensureStatusesFromReference($referenceRows, $inventoryRows, $movementRows);

            $locationLookup = $this->importLocationMaster($locationRows);

            foreach ($inventoryRows as $row) {
                $title = $this->firstNonEmpty($row, ['artwork_title', 'artwork', 'title']);
                if ($title === null) {
                    continue;
                }

                $inventoryCode = $this->firstNonEmpty($row, ['artwork_id', 'inventory_code']);
                $artistName = $this->firstNonEmpty($row, ['artist', 'artist_name']) ?? 'Unknown Artist';
                $countryName = $this->nullIfEmpty($this->firstNonEmpty($row, ['country', 'artist_country']));
                $locationName = $this->firstNonEmpty($row, ['current_location_name', 'location_name', 'location'])
                    ?? 'Unknown Location';
                $locationCode = $this->firstNonEmpty($row, ['current_location_id', 'location_id'])
                    ?? ($locationLookup['by_name'][Str::lower($locationName)]['code'] ?? null);
                $locationType = $this->firstNonEmpty($row, ['location_type'])
                    ?? ($locationLookup['by_name'][Str::lower($locationName)]['type'] ?? 'Imported');
                $status = $this->cleanedStatus($this->firstNonEmpty($row, ['current_status', 'status']));
                $dimensions = $this->parseDimensions($this->firstNonEmpty($row, ['dimensions', 'size']));
                $imageUrl = $this->nullIfEmpty($this->firstNonEmpty($row, ['main_image_url', 'image_url', 'image_1']));

                $artist = Artist::updateOrCreate(
                    ['name' => $artistName],
                    [
                        'country_id' => $this->resolveCountryId($countryName),
                        'country' => $countryName,
                        'birth_year' => $this->parseYear($this->firstNonEmpty($row, ['artist_dob', 'dob'])),
                    ]
                );

                $location = $this->resolveLocation($locationName, $locationType, $locationCode);

                $query = Artwork::query();
                if ($inventoryCode !== null) {
                    $query->where('inventory_code', $inventoryCode);
                } else {
                    $query->where('title', $title)->where('artist_id', $artist->id);
                }

                $artwork = $query->first();
                $payload = [
                    'inventory_code' => $inventoryCode,
                    'artist_id' => $artist->id,
                    'location_id' => $location->id,
                    'title' => $title,
                    'year' => $this->parseYear($this->firstNonEmpty($row, ['year_created', 'year'])),
                    'description' => $this->nullIfEmpty($this->firstNonEmpty($row, ['notes', 'description'])),
                    'medium' => $this->nullIfEmpty($this->firstNonEmpty($row, ['medium'])),
                    'size_from_cm' => $dimensions[0],
                    'size_to_cm' => $dimensions[1],
                    'acquisition_date' => $this->parseDate($this->firstNonEmpty($row, ['date_of_acquisition', 'acquisition_date'])),
                    'status' => $status,
                    'source_image_url' => $imageUrl,
                ];

                if ($artwork) {
                    $artwork->update($payload);
                    $updated++;
                } else {
                    $artwork = Artwork::create(array_merge($payload, [
                        'slug' => $this->uniqueSlug($title),
                    ]));
                    $created++;
                }

                if ($downloadImages && $imageUrl) {
                    $stored = $this->imageOptimizer->storeFromUrl($imageUrl);
                    if ($stored) {
                        $artwork->update(['primary_image_path' => $stored['path']]);
                        $downloadedImages++;
                    }
                }
            }

            foreach ($movementRows as $row) {
                $movementId = $this->firstNonEmpty($row, ['movement_id']);
                $artworkCode = $this->firstNonEmpty($row, ['artwork_id', 'inventory_code']);
                $artworkTitle = $this->firstNonEmpty($row, ['artwork_title', 'title']);

                $artwork = Artwork::query()
                    ->when($artworkCode !== null, fn ($query) => $query->where('inventory_code', $artworkCode))
                    ->when($artworkCode === null && $artworkTitle !== null, fn ($query) => $query->where('title', $artworkTitle))
                    ->first();

                if (! $artwork) {
                    continue;
                }

                $fromName = $this->firstNonEmpty($row, ['from_location_name'])
                    ?? $this->locationNameByCode($locationLookup, $this->firstNonEmpty($row, ['from_location_id']))
                    ?? ($artwork->location?->name ?? 'Unknown Location');
                $toName = $this->firstNonEmpty($row, ['to_location_name'])
                    ?? $this->locationNameByCode($locationLookup, $this->firstNonEmpty($row, ['to_location_id']))
                    ?? 'Unknown Location';
                $dateOut = $this->parseDate($this->firstNonEmpty($row, ['movement_timestamp', 'date_out'])) ?? now()->toDateString();
                $completedDate = $this->parseDate($this->firstNonEmpty($row, ['completed_date']));

                $payload = [
                    'external_movement_id' => $movementId,
                    'artwork_id' => $artwork->id,
                    'from_location' => $fromName,
                    'to_location' => $toName,
                    'date_out' => $dateOut,
                    'expected_return_date' => $this->parseDate($this->firstNonEmpty($row, ['expected_return_date'])) ?? $completedDate,
                    'responsible_handler' => $this->firstNonEmpty($row, ['moved_by', 'approved_by']) ?? 'Imported',
                    'reason' => $this->firstNonEmpty($row, ['external_reason', 'movement_type']) ?? 'Internal Transfer',
                    'status' => $this->cleanedStatus($this->firstNonEmpty($row, ['status_after_movement', 'status'])),
                    'notes' => $this->firstNonEmpty($row, ['external_party']),
                ];

                $movement = $movementId
                    ? Movement::query()->where('external_movement_id', $movementId)->first()
                    : Movement::query()
                        ->where('artwork_id', $artwork->id)
                        ->where('date_out', $dateOut)
                        ->where('to_location', $toName)
                        ->first();

                if ($movement) {
                    $movement->update($payload);
                    $movementsUpdated++;
                } else {
                    Movement::create($payload);
                    $movementsCreated++;
                }
            }
        });

        $this->info("Cleaned inventory import completed. Artworks created: {$created}, updated: {$updated}, movements created: {$movementsCreated}, movements updated: {$movementsUpdated}, images saved: {$downloadedImages}");

        return self::SUCCESS;
    }

    private function parseXlsx(string $path): array
    {
        $zip = new ZipArchive();
        if ($zip->open($path) !== true) {
            return [];
        }

        $sharedStrings = [];
        $sharedXml = $zip->getFromName('xl/sharedStrings.xml');
        if ($sharedXml !== false) {
            $shared = simplexml_load_string($sharedXml);
            if ($shared && isset($shared->si)) {
                foreach ($shared->si as $si) {
                    $sharedStrings[] = trim((string) ($si->t ?? ''));
                }
            }
        }

        $sheetXml = $zip->getFromName('xl/worksheets/sheet1.xml');
        if ($sheetXml === false) {
            $zip->close();
            return [];
        }

        $sheet = simplexml_load_string($sheetXml);
        $zip->close();

        if (! $sheet || ! isset($sheet->sheetData->row)) {
            return [];
        }

        $rows = [];
        foreach ($sheet->sheetData->row as $row) {
            $rowData = array_fill(0, 15, '');
            foreach ($row->c as $cell) {
                $ref = (string) $cell['r'];
                $index = $this->columnIndex($ref);
                if ($index < 0 || $index > 14) {
                    continue;
                }

                $value = (string) ($cell->v ?? '');
                if ((string) $cell['t'] === 's' && is_numeric($value)) {
                    $value = $sharedStrings[(int) $value] ?? '';
                }

                $rowData[$index] = trim($value);
            }

            $rows[] = $rowData;
        }

        return $rows;
    }

    private function parseWorkbook(string $path): array
    {
        $zip = new ZipArchive();
        if ($zip->open($path) !== true) {
            return [];
        }

        $workbookXml = $zip->getFromName('xl/workbook.xml');
        $relationshipsXml = $zip->getFromName('xl/_rels/workbook.xml.rels');
        if ($workbookXml === false || $relationshipsXml === false) {
            $zip->close();

            return [];
        }

        $workbook = simplexml_load_string($workbookXml);
        $relationships = simplexml_load_string($relationshipsXml);
        if (! $workbook || ! $relationships) {
            $zip->close();

            return [];
        }

        $relationshipTargets = [];
        foreach ($relationships->Relationship as $relationship) {
            $relationshipTargets[(string) $relationship['Id']] = ltrim((string) $relationship['Target'], '/');
        }

        $sharedStrings = $this->sharedStrings($zip);
        $sheets = [];

        foreach ($workbook->sheets->sheet as $sheet) {
            $name = (string) $sheet['name'];
            $relationshipId = (string) $sheet->attributes('http://schemas.openxmlformats.org/officeDocument/2006/relationships')['id'];
            $target = $relationshipTargets[$relationshipId] ?? null;
            if (! $target) {
                continue;
            }

            $sheetXml = $zip->getFromName($target);
            if ($sheetXml === false) {
                continue;
            }

            $sheetData = simplexml_load_string($sheetXml);
            if (! $sheetData || ! isset($sheetData->sheetData->row)) {
                continue;
            }

            $rows = [];
            $maxColumnIndex = 0;
            foreach ($sheetData->sheetData->row as $row) {
                $rowData = [];
                foreach ($row->c as $cell) {
                    $index = $this->columnIndex((string) $cell['r']);
                    if ($index < 0) {
                        continue;
                    }

                    $maxColumnIndex = max($maxColumnIndex, $index);
                    $rowData[$index] = trim($this->cellValue($cell, $sharedStrings));
                }

                if ($rowData === []) {
                    $rows[] = [];
                    continue;
                }

                ksort($rowData);
                $rows[] = $rowData;
            }

            $sheets[$name] = collect($rows)
                ->map(function (array $row) use ($maxColumnIndex) {
                    $normalized = [];
                    for ($index = 0; $index <= $maxColumnIndex; $index++) {
                        $normalized[] = $row[$index] ?? '';
                    }

                    return $normalized;
                })
                ->all();
        }

        $zip->close();

        return $sheets;
    }

    private function sharedStrings(ZipArchive $zip): array
    {
        $sharedStrings = [];
        $sharedXml = $zip->getFromName('xl/sharedStrings.xml');
        if ($sharedXml === false) {
            return $sharedStrings;
        }

        $shared = simplexml_load_string($sharedXml);
        if (! $shared || ! isset($shared->si)) {
            return $sharedStrings;
        }

        foreach ($shared->si as $si) {
            if (isset($si->t)) {
                $sharedStrings[] = trim((string) $si->t);
                continue;
            }

            $text = '';
            if (isset($si->r)) {
                foreach ($si->r as $run) {
                    $text .= (string) $run->t;
                }
            }

            $sharedStrings[] = trim($text);
        }

        return $sharedStrings;
    }

    private function cellValue(\SimpleXMLElement $cell, array $sharedStrings): string
    {
        $type = (string) $cell['t'];

        if ($type === 's') {
            $value = (string) ($cell->v ?? '');

            return is_numeric($value) ? (string) ($sharedStrings[(int) $value] ?? '') : $value;
        }

        if ($type === 'inlineStr') {
            if (isset($cell->is->t)) {
                return (string) $cell->is->t;
            }

            $text = '';
            if (isset($cell->is->r)) {
                foreach ($cell->is->r as $run) {
                    $text .= (string) $run->t;
                }
            }

            return $text;
        }

        return (string) ($cell->v ?? '');
    }

    private function rowsWithHeaders(array $rows): array
    {
        $headerRowIndex = null;
        $headers = [];

        foreach ($rows as $index => $row) {
            $normalizedHeaders = array_map(fn ($header) => $this->normalizeHeader((string) $header), $row);
            if (count(array_filter($normalizedHeaders)) < 2) {
                continue;
            }

            $headerRowIndex = $index;
            $headers = $normalizedHeaders;
            break;
        }

        if ($headerRowIndex === null) {
            return [];
        }

        $mappedRows = [];
        foreach (array_slice($rows, $headerRowIndex + 1) as $row) {
            $mapped = [];
            foreach ($headers as $index => $header) {
                if ($header === '') {
                    continue;
                }

                $mapped[$header] = $row[$index] ?? null;
            }

            if (! $this->rowHasValue($mapped)) {
                continue;
            }

            $mappedRows[] = $mapped;
        }

        return $mappedRows;
    }

    private function columnIndex(string $ref): int
    {
        $letters = preg_replace('/\d+/', '', $ref);
        if (! $letters) {
            return -1;
        }

        $index = 0;
        foreach (str_split($letters) as $char) {
            $index = $index * 26 + (ord($char) - 64);
        }

        return $index - 1;
    }

    private function mapRow(array $headers, array $row): array
    {
        $data = [];
        foreach ($headers as $i => $header) {
            $key = trim((string) $header);
            $data[$key] = $row[$i] ?? null;
        }

        $title = trim((string) ($data['Artwork'] ?? $data['Artwork '] ?? ''));
        $artistName = trim((string) ($data['Artist'] ?? ''));
        $acqDate = $this->parseDate($data['Date of Acquisition'] ?? null);

        return [
            'artist_name' => $artistName,
            'artist_country' => $this->nullIfEmpty($data['Country'] ?? null),
            'artist_birth_year' => $this->parseYear($data['DOB'] ?? null),
            'location_name' => trim((string) ($data['Location'] ?? 'Unknown Location')),
            'title' => $title,
            'image_url' => $this->nullIfEmpty($data['Image 1'] ?? null),
            'artwork_payload' => [
                'title' => $title,
                'year' => $this->parseYear($data['Year'] ?? null),
                'description' => $this->nullIfEmpty($data['Description'] ?? null),
                'medium' => $this->nullIfEmpty($data['Medium'] ?? null),
                'size_from_cm' => $this->parseFloat($data['Size From (cm)'] ?? null),
                'size_to_cm' => $this->parseFloat($data['Size To (cm)'] ?? null),
                'acquisition_date' => $acqDate,
                'acquisition_price' => $this->parseFloat($data['Price of Acquisition'] ?? null),
                'current_valuation' => $this->parseFloat($data['Price of Acquisition'] ?? null),
                'status' => 'On Display',
                'source_image_url' => $this->nullIfEmpty($data['Image 1'] ?? null),
            ],
        ];
    }

    private function importLocationMaster(array $locationRows): array
    {
        $lookup = [
            'by_code' => [],
            'by_name' => [],
        ];

        foreach ($locationRows as $row) {
            $name = $this->firstNonEmpty($row, ['location_name', 'name']);
            if ($name === null) {
                continue;
            }

            $code = $this->firstNonEmpty($row, ['location_id', 'code']);
            $type = $this->firstNonEmpty($row, ['location_type', 'type']) ?? 'Imported';
            $notes = $this->firstNonEmpty($row, ['notes']);

            $location = $this->resolveLocation($name, $type, $code, $notes);

            $entry = [
                'id' => $location->id,
                'code' => $code,
                'name' => $name,
                'type' => $type,
            ];

            if ($code !== null) {
                $lookup['by_code'][Str::upper($code)] = $entry;
            }

            $lookup['by_name'][Str::lower($name)] = $entry;

            $legacyAlias = $this->firstNonEmpty($row, ['legacy_alias']);
            if ($legacyAlias !== null) {
                $lookup['by_name'][Str::lower($legacyAlias)] = $entry;
            }
        }

        return $lookup;
    }

    private function resolveLocation(string $name, ?string $type = null, ?string $code = null, ?string $notes = null): Location
    {
        $query = Location::query();
        if ($code !== null && $code !== '') {
            $query->where('code', $code);
        } else {
            $query->where('name', $name);
        }

        $location = $query->first();
        $payload = [
            'name' => $name,
            'type' => $type ?: 'Imported',
            'address' => $notes,
        ];

        if ($code !== null && $code !== '') {
            $payload['code'] = $code;
        }

        if ($location) {
            $location->fill($payload);
            $location->save();

            return $location;
        }

        return Location::query()->create($payload);
    }

    private function ensureStatusesFromReference(array $referenceRows, array $inventoryRows, array $movementRows): void
    {
        $statuses = collect(Status::DEFAULT_NAMES);

        foreach ($referenceRows as $row) {
            $statuses->push($this->firstNonEmpty($row, ['status_options', 'status']));
        }

        foreach ($inventoryRows as $row) {
            $statuses->push($this->firstNonEmpty($row, ['current_status', 'status']));
        }

        foreach ($movementRows as $row) {
            $statuses->push($this->firstNonEmpty($row, ['status_after_movement', 'status']));
        }

        $statuses = $statuses
            ->filter(fn ($status) => is_string($status) && trim($status) !== '')
            ->map(fn ($status) => trim((string) $status))
            ->unique()
            ->values();

        if ($statuses->isNotEmpty()) {
            Status::query()->update(['is_active' => false]);
        }

        foreach ($statuses as $index => $status) {
            Status::query()->updateOrCreate(
                ['name' => $status],
                [
                    'sort_order' => $index + 1,
                    'is_active' => true,
                ]
            );
        }
    }

    private function cleanedStatus(?string $status): string
    {
        $status = trim((string) $status);

        return $status !== '' ? $status : Status::DEFAULT_NAMES[0];
    }

    private function locationNameByCode(array $locationLookup, ?string $code): ?string
    {
        if ($code === null || $code === '') {
            return null;
        }

        return $locationLookup['by_code'][Str::upper($code)]['name'] ?? null;
    }

    private function parseDimensions(?string $value): array
    {
        $text = $this->nullIfEmpty($value);
        if ($text === null) {
            return [null, null];
        }

        preg_match_all('/(\d+(?:\.\d+)?)/', $text, $matches);
        $values = collect($matches[1] ?? [])
            ->map(fn ($match) => is_numeric($match) ? (float) $match : null)
            ->filter(fn ($match) => $match !== null)
            ->values();

        if ($values->isEmpty()) {
            return [null, null];
        }

        return [$values->min(), $values->max()];
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

    private function rowHasValue(array $data): bool
    {
        foreach ($data as $value) {
            if ($this->nullIfEmpty($value) !== null) {
                return true;
            }
        }

        return false;
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
        if ($value === null || $value === '') {
            return null;
        }

        $year = (int) $value;
        if ($year < 1000 || $year > (int) date('Y')) {
            return null;
        }

        return $year;
    }

    private function parseDate(mixed $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (is_numeric($value)) {
            $date = Carbon::createFromTimestampUTC(((int) $value - 25569) * 86400);
            return $date->toDateString();
        }

        try {
            return Carbon::parse((string) $value)->toDateString();
        } catch (\Throwable) {
            return null;
        }
    }

    private function parseFloat(mixed $value): ?float
    {
        if ($value === null || $value === '') {
            return null;
        }

        return is_numeric($value) ? (float) $value : null;
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
        $i = 2;

        while (Artwork::where('slug', $slug)->exists()) {
            $slug = $base.'-'.$i;
            $i++;
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
