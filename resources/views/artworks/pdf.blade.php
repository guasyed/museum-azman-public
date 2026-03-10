<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Collection Export - Museum Azman</title>
    <style>
        @page { margin: 8mm; }

        :root {
            --ink: #222228;
            --muted: #5a5a66;
            --line: #c9cad3;
            --panel: #efeff2;
            --bg: #e5e5e8;
        }

        * { box-sizing: border-box; }

        body {
            margin: 0;
            font-family: "Helvetica Neue", Helvetica, Arial, sans-serif;
            color: var(--ink);
            background: #ffffff;
            -webkit-font-smoothing: antialiased;
            text-rendering: optimizeLegibility;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
            font-size: 10px;
        }

        .page {
            width: 100%;
            margin: 0 auto;
            padding: 4px;
        }

        .meta-bar {
            margin-bottom: 10px;
            padding: 0 2px;
        }

        .meta-title {
            margin: 0;
            font-size: 14px;
            line-height: 1.2;
            font-weight: 700;
        }

        .meta-time {
            display: block;
            margin-top: 4px;
            font-size: 10px;
            color: var(--muted);
        }

        .grid-wrap {
            font-size: 0;
            width: 100%;
        }

        .grid-item {
            display: inline-block;
            vertical-align: top;
            width: 24%;
            margin-right: 1.3333%;
            margin-bottom: 10px;
            font-size: 10px;
            page-break-inside: avoid;
            break-inside: avoid;
        }

        .grid-item:nth-child(4n) {
            margin-right: 0;
        }

        .card {
            border: 1px solid var(--line);
            border-radius: 12px;
            background: #f8f8f9;
            page-break-inside: avoid;
            break-inside: avoid;
        }

        .thumb {
            width: 100%;
            height: 165px;
            object-fit: cover;
            background: #f4f4f5;
            display: block;
            border-top-left-radius: 12px;
            border-top-right-radius: 12px;
        }

        .no-thumb {
            width: 100%;
            height: 165px;
            color: #71717a;
            font-size: 10px;
            background: #f4f4f5;
            text-align: center;
            line-height: 165px;
            border-top-left-radius: 12px;
            border-top-right-radius: 12px;
        }

        .card-body {
            padding: 10px 12px 0;
        }

        .card-title {
            margin: 0;
            font-size: 11px;
            font-weight: 700;
            line-height: 1.35;
        }

        .card-meta {
            margin-top: 6px;
            color: var(--muted);
            font-size: 9px;
            line-height: 1.35;
        }

        .card-foot {
            margin-top: 8px;
            font-size: 9px;
            border-top: 1px solid #dbdce3;
            padding: 9px 12px 10px;
        }

        .foot-table {
            width: 100%;
            border-collapse: collapse;
        }

        .foot-table td {
            padding: 0;
            vertical-align: middle;
        }

        .foot-right {
            text-align: right;
        }

        .valuation {
            font-weight: 800;
        }

        .badge {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 999px;
            font-size: 8px;
            font-weight: 700;
            white-space: nowrap;
        }

        .on-display { background: #cdebd9; color: #1f7a4f; }
        .in-storage { background: #dbeafe; color: #1d4ed8; }
        .on-loan { background: #ede9fe; color: #6d28d9; }
        .in-transit { background: #fef3c7; color: #92400e; }
        .unknown { background: #f4f4f5; color: #52525b; }

        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 10px;
            margin-top: 2px;
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

        th {
            color: var(--muted);
            font-weight: 700;
        }

        .text-right { text-align: right; }

        .empty-note {
            color: #71717a;
            font-size: 10px;
            margin: 0;
        }
    </style>
</head>
<body>
    @php
        $generatedAt = isset($exportedAt) ? $exportedAt : now();
        $requestedView = $view ?? 'grid';
        $pdfView = in_array($requestedView, ['grid', 'table'], true) ? $requestedView : 'grid';

        $items = method_exists($artworks, 'items') ? $artworks->items() : $artworks;

        $resolvePdfImageSrc = static function ($artwork): ?string {
            $disk = \Illuminate\Support\Facades\Storage::disk('public');

            $paths = array_filter([
                $artwork->primary_image_path,
                optional($artwork->images->sortBy('position')->first())->path,
            ]);

            foreach ($paths as $path) {
                if (! is_string($path) || $path === '') {
                    continue;
                }

                if (! $disk->exists($path)) {
                    continue;
                }

                $absolute = $disk->path($path);

                if (is_file($absolute)) {
                    return 'file://' . $absolute;
                }
            }

            return null;
        };
    @endphp

    <div class="page">
        <div class="meta-bar">
            <p class="meta-title">Collection</p>
            <span class="meta-time">Generated: {{ $generatedAt->format('Y-m-d H:i:s') }}</span>
        </div>

        @if($pdfView === 'grid')
            <div class="grid-wrap">
                @forelse($items as $artwork)
                    @php
                        $statusKey = strtolower(trim((string) ($artwork->status ?? '')));
                        $statusClass = match ($statusKey) {
                            'on display' => 'on-display',
                            'in storage' => 'in-storage',
                            'on loan' => 'on-loan',
                            'in transit' => 'in-transit',
                            default => 'unknown',
                        };

                        $sizeText = ($artwork->size_from_cm && $artwork->size_to_cm)
                            ? number_format((float) $artwork->size_from_cm, 0) . ' × ' . number_format((float) $artwork->size_to_cm, 0) . ' cm'
                            : ($artwork->size_from_cm ? number_format((float) $artwork->size_from_cm, 0) . ' cm' : '-');

                        $imageSrc = $resolvePdfImageSrc($artwork);
                        $valuation = is_numeric($artwork->current_valuation)
                            ? number_format((float) $artwork->current_valuation, 0)
                            : '0';
                    @endphp

                    <div class="grid-item">
                        <article class="card">
                            @if($imageSrc)
                                <img src="{{ $imageSrc }}" alt="{{ $artwork->title }}" class="thumb">
                            @else
                                <div class="no-thumb">No Image</div>
                            @endif

                            <div class="card-body">
                                <p class="card-title">{{ $artwork->title }}</p>
                                <p class="card-meta">
                                    {{ $artwork->artist?->name ?? 'Unknown Artist' }}{{ $artwork->year ? ', '.$artwork->year : '' }}
                                </p>
                                <p class="card-meta">{{ $artwork->medium ?: '-' }}</p>
                                <p class="card-meta">{{ $sizeText }}</p>
                            </div>

                            <div class="card-foot">
                                <table class="foot-table">
                                    <tr>
                                        <td class="valuation">{{ \App\Support\Currency::symbol() }}{{ $valuation }}</td>
                                        <td class="foot-right">
                                            <span class="badge {{ $statusClass }}">{{ $artwork->status ?: 'Unknown' }}</span>
                                        </td>
                                    </tr>
                                </table>
                            </div>
                        </article>
                    </div>
                @empty
                    <p class="empty-note">No artworks found.</p>
                @endforelse
            </div>
        @else
            <table>
                <thead>
                    <tr>
                        <th style="width:62px;">Image</th>
                        <th>Artwork</th>
                        <th>Artist</th>
                        <th>Region</th>
                        <th>Medium</th>
                        <th class="text-right">Value</th>
                        <th class="text-right">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($items as $artwork)
                        @php
                            $statusKey = strtolower(trim((string) ($artwork->status ?? '')));
                            $statusClass = match ($statusKey) {
                                'on display' => 'on-display',
                                'in storage' => 'in-storage',
                                'on loan' => 'on-loan',
                                'in transit' => 'in-transit',
                                default => 'unknown',
                            };

                            $imageSrc = $resolvePdfImageSrc($artwork);
                            $valuation = is_numeric($artwork->current_valuation)
                                ? number_format((float) $artwork->current_valuation, 0)
                                : '0';
                        @endphp
                        <tr>
                            <td>
                                @if($imageSrc)
                                    <img src="{{ $imageSrc }}" alt="{{ $artwork->title }}" style="width:52px;height:52px;object-fit:cover;border-radius:8px;border:1px solid #e4e4e7;display:block;">
                                @else
                                    <div style="width:52px;height:52px;border-radius:8px;border:1px solid #e4e4e7;background:#f4f4f5;text-align:center;line-height:52px;font-size:8px;color:#71717a;">No Image</div>
                                @endif
                            </td>
                            <td><strong>{{ $artwork->title }}</strong></td>
                            <td>{{ $artwork->artist?->name ?? 'Unknown Artist' }}</td>
                            <td>{{ $artwork->artist?->country ?? '-' }}</td>
                            <td>{{ $artwork->medium ?: '-' }}</td>
                            <td class="text-right">{{ \App\Support\Currency::symbol() }}{{ $valuation }}</td>
                            <td class="text-right"><span class="badge {{ $statusClass }}">{{ $artwork->status ?: 'Unknown' }}</span></td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" style="color:#71717a;">No artworks found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        @endif
    </div>
</body>
</html>