<?php

namespace App\Services;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Factory as HttpFactory;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ImageOptimizer
{
    public function __construct(private readonly HttpFactory $http)
    {
    }

    public function storeUploaded(UploadedFile $file, string $directory = 'artworks'): ?array
    {
        $binary = file_get_contents($file->getRealPath());

        if ($binary === false) {
            return null;
        }

        return $this->storeFromBinary(
            $binary,
            $file->getClientOriginalName(),
            $directory,
            $file->getMimeType() ?: null
        );
    }

    public function storeFromUrl(string $url, string $directory = 'artworks'): ?array
    {
        $trimmedUrl = trim($url);

        if (! filter_var($trimmedUrl, FILTER_VALIDATE_URL)) {
            return null;
        }

        $scheme = strtolower((string) parse_url($trimmedUrl, PHP_URL_SCHEME));
        if (! in_array($scheme, ['http', 'https'], true)) {
            return null;
        }

        try {
            $response = $this->http->timeout(60)->get($trimmedUrl);
        } catch (ConnectionException) {
            return null;
        }

        if (! $response->successful()) {
            return null;
        }

        $contentType = (string) $response->header('Content-Type', '');

        if (! Str::startsWith($contentType, 'image/')) {
            return null;
        }

        $fileName = basename(parse_url($trimmedUrl, PHP_URL_PATH) ?: 'imported-image');

        return $this->storeFromBinary($response->body(), $fileName, $directory, $contentType);
    }

    private function storeFromBinary(string $binary, string $fileName, string $directory, ?string $sourceMime = null): ?array
    {
        $image = @imagecreatefromstring($binary);

        if (! $image) {
            return $this->storeOriginalBinary($binary, $fileName, $directory, $sourceMime);
        }

        $sourceWidth = imagesx($image);
        $sourceHeight = imagesy($image);

        $maxSide = 1800;
        $ratio = min($maxSide / max($sourceWidth, 1), $maxSide / max($sourceHeight, 1), 1);
        $targetWidth = max((int) round($sourceWidth * $ratio), 1);
        $targetHeight = max((int) round($sourceHeight * $ratio), 1);

        $canvas = imagecreatetruecolor($targetWidth, $targetHeight);
        imagecopyresampled($canvas, $image, 0, 0, 0, 0, $targetWidth, $targetHeight, $sourceWidth, $sourceHeight);

        $webpSupported = function_exists('imagewebp');
        $extension = $webpSupported ? 'webp' : 'jpg';
        $mime = $webpSupported ? 'image/webp' : 'image/jpeg';

        ob_start();
        if ($webpSupported) {
            imagewebp($canvas, null, 78);
        } else {
            imagejpeg($canvas, null, 80);
        }
        $optimized = ob_get_clean() ?: '';

        imagedestroy($canvas);
        imagedestroy($image);

        if ($optimized === '') {
            return null;
        }

        $path = trim($directory, '/').'/'.Str::uuid().'.'.$extension;
        Storage::disk('public')->put($path, $optimized);

        return [
            'path' => $path,
            'mime_type' => $mime,
            'size_bytes' => strlen($optimized),
            'original_name' => $fileName,
        ];
    }

    private function storeOriginalBinary(string $binary, string $fileName, string $directory, ?string $sourceMime = null): ?array
    {
        $mime = $sourceMime ?: 'image/jpeg';

        if (! Str::startsWith($mime, 'image/')) {
            return null;
        }

        $extension = match ($mime) {
            'image/webp' => 'webp',
            'image/png' => 'png',
            'image/gif' => 'gif',
            'image/bmp', 'image/x-ms-bmp' => 'bmp',
            'image/svg+xml' => 'svg',
            default => strtolower(pathinfo($fileName, PATHINFO_EXTENSION)) ?: 'jpg',
        };

        $path = trim($directory, '/').'/'.Str::uuid().'.'.$extension;
        Storage::disk('public')->put($path, $binary);

        return [
            'path' => $path,
            'mime_type' => $mime,
            'size_bytes' => strlen($binary),
            'original_name' => $fileName,
        ];
    }
}
