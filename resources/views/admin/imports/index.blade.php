<x-layout title="CSV Import - Museum Azman">
    <section class="space-y-6">
        <div class="flex items-start justify-between gap-4">
            <div>
                <h2 class="museum-page-title">CSV Import</h2>
                <p class="museum-page-subtitle">Admin-only tool to import artworks, artists, and locations from CSV</p>
            </div>
            <a href="{{ route('settings.index', ['tab' => 'general']) }}" class="museum-btn-secondary">Back to Settings</a>
        </div>

        <article class="museum-panel p-5">
            <h3 class="museum-section-title text-base!">Upload CSV File</h3>
            <form action="{{ route('admin.imports.csv.store') }}" method="POST" enctype="multipart/form-data" class="mt-4 space-y-4">
                @csrf

                <label class="museum-field block">
                    <span>CSV file</span>
                    <input type="file" name="csv_file" accept=".csv,text/csv" required>
                    <small class="text-zinc-500">Supported: .csv files up to 20MB</small>
                </label>

                <label class="museum-field block">
                    <span>Database connection (optional)</span>
                    <input type="text" name="connection" value="{{ old('connection') }}" placeholder="mysql">
                    <small class="text-zinc-500">Leave blank to use default connection from .env</small>
                </label>

                <label class="inline-flex items-center gap-2 text-sm text-zinc-700">
                    <input type="checkbox" name="download_images" value="1" {{ old('download_images') ? 'checked' : '' }}>
                    <span>Download and optimize image URLs during import</span>
                </label>

                <div>
                    <button type="submit" class="museum-btn">Start Import</button>
                </div>
            </form>
        </article>

        @if (session('import_output'))
            <article class="museum-panel p-5">
                <h3 class="museum-section-title text-base!">Import Result</h3>
                <pre class="mt-3 overflow-x-auto rounded-xl border border-zinc-200 bg-zinc-50 p-3 text-xs text-zinc-700">{{ session('import_output') }}</pre>
            </article>
        @endif
    </section>
</x-layout>
