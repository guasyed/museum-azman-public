<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class AdminImportController extends Controller
{
    public function index(): View
    {
        return view('admin.imports.index');
    }

    public function store(Request $request): RedirectResponse
    {
        if (! $request->user()?->isAdmin()) {
            return redirect()
                ->route('dashboard')
                ->withErrors(['import' => 'Only admin users can upload and import data.']);
        }

        $validated = $request->validate([
            'csv_file' => ['required', 'file', 'mimes:csv,txt', 'max:20480'],
            'download_images' => ['nullable', 'boolean'],
            'connection' => ['nullable', 'string', 'max:64'],
        ]);

        $relativePath = $request->file('csv_file')->store('private/imports', 'local');
        $absolutePath = Storage::disk('local')->path($relativePath);

        try {
            $arguments = [
                'path' => $absolutePath,
                '--download-images' => (bool) ($validated['download_images'] ?? false),
            ];

            if (! empty($validated['connection'])) {
                $arguments['--connection'] = (string) $validated['connection'];
            }

            $exitCode = Artisan::call('museum:import-csv', $arguments);
            $output = trim(Artisan::output());
        } finally {
            Storage::disk('local')->delete($relativePath);
        }

        if ($exitCode !== 0) {
            return redirect()
                ->route('admin.imports.csv.index')
                ->withInput()
                ->withErrors([
                    'import' => $output !== ''
                        ? $output
                        : 'CSV import failed. Please check file format and try again.',
                ]);
        }

        return redirect()
            ->route('admin.imports.csv.index')
            ->with('success', 'CSV import completed successfully.')
            ->with('import_output', $output);
    }
}