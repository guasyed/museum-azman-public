<?php

namespace App\Console\Commands;

use App\Models\Artist;
use App\Models\Artwork;
use App\Models\Country;
use App\Models\Location;
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
