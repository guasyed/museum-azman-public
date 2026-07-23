@php
$statuses = collect($statusOptions ?? [])->values();
$locationOptions = collect($locationOptions ?? []);
$locationTypeOptions = collect($locationTypeOptions ?? []);
$selectedLocationName = (string) old('location_name', $artwork?->location?->name);
$selectedLocationType = (string) old('location_type', $artwork?->location?->type);
$selectedLocationAddress = (string) old('location_address', $artwork?->location?->address);
$externalStatusOptions = collect(['Under Restoration', 'Loaned Out', 'Under Evaluation']);
$selectedStatus = (string) old('status', $artwork?->status);
$normalizedLocationName = strtolower(trim($selectedLocationName));
$automaticStatus = match ($normalizedLocationName) {
    'store' => 'In Storage',
    'sold or left' => 'Sold or Left',
    'external' => $externalStatusOptions->contains($selectedStatus) ? $selectedStatus : 'Under Restoration',
    default => 'On Display',
};

$descriptionText = trim((string) old('description', $artwork?->description));
$derivedYear = null;
$derivedMedium = null;
$derivedSizeFrom = null;
$derivedSizeTo = null;

if ($descriptionText !== '' && preg_match('/\b(1[89]\d{2}|20\d{2}|21\d{2})\b/u', $descriptionText, $yearMatch) === 1) {
    $derivedYear = $yearMatch[1];
}

if ($descriptionText !== '' && preg_match('/(\d+(?:\.\d+)?)\s*(?:cm)?\s*[x×]\s*(\d+(?:\.\d+)?)\s*(?:cm)?/iu', $descriptionText, $dimensionMatch) === 1) {
    $derivedSizeFrom = $dimensionMatch[1];
    $derivedSizeTo = $dimensionMatch[2];
}

if ($descriptionText !== '') {
    $mediumCandidate = preg_replace('/\b(1[89]\d{2}|20\d{2}|21\d{2})\b/u', '', $descriptionText, 1);
    $mediumCandidate = preg_replace('/\b(\d{4}-\d{1,2}-\d{1,2}|\d{1,2}[\/\-]\d{1,2}[\/\-]\d{2,4})\b/u', '', (string) $mediumCandidate, 1);
    $mediumCandidate = preg_replace('/(\d+(?:\.\d+)?)\s*(?:cm)?\s*[x×]\s*(\d+(?:\.\d+)?)\s*(?:cm)?/iu', '', (string) $mediumCandidate, 1);
    $mediumCandidate = preg_replace('/\s{2,}/u', ' ', (string) $mediumCandidate);
    $mediumCandidate = trim((string) $mediumCandidate, " ,.;:-\t\n\r\0\x0B");

    if ($mediumCandidate !== '') {
        $derivedMedium = $mediumCandidate;
    }
}

$yearDefault = $artwork?->year ?: $derivedYear;
$mediumDefault = $artwork?->medium ?: $derivedMedium;
$sizeFromDefault = $artwork?->size_from_cm ?: $derivedSizeFrom;
$sizeToDefault = $artwork?->size_to_cm ?: $derivedSizeTo;
@endphp

<div class="grid gap-4 md:grid-cols-2">
    <label class="museum-field">
        <span>Artwork Title *</span>
        <input name="title" value="{{ old('title', $artwork?->title) }}" required>
    </label>

    <label class="museum-field">
        <span>Year</span>
        <input name="year" type="number" value="{{ old('year', $yearDefault) }}">
    </label>

    <label class="museum-field">
        <span>Artist Name *</span>
        <input name="artist_name" value="{{ old('artist_name', $artwork?->artist?->name ?? request('artist_name')) }}" required>
    </label>

    <label class="museum-field">
        <span>Artist Country</span>
        <input name="artist_country" value="{{ old('artist_country', $artwork?->artist?->country ?? request('artist_country')) }}">
    </label>

    <label class="museum-field">
        <span>Artist Birth Year</span>
        <input name="artist_birth_year" type="number" value="{{ old('artist_birth_year', $artwork?->artist?->birth_year ?? request('artist_birth_year')) }}">
    </label>

    <label class="museum-field">
        <span>Medium</span>
        <input name="medium" value="{{ old('medium', $mediumDefault) }}" placeholder="e.g., Oil on Canvas">
    </label>

    <label class="museum-field">
        <span>Size From (cm)</span>
        <input name="size_from_cm" type="number" step="0.01" value="{{ old('size_from_cm', $sizeFromDefault) }}">
    </label>

    <label class="museum-field">
        <span>Size To (cm)</span>
        <input name="size_to_cm" type="number" step="0.01" value="{{ old('size_to_cm', $sizeToDefault) }}">
    </label>

    <label class="museum-field">
        <span>Acquisition Date</span>
        <input name="acquisition_date" type="date" value="{{ old('acquisition_date', optional($artwork?->acquisition_date)->format('Y-m-d')) }}">
    </label>

    <label class="museum-field">
        <span>Acquisition Price</span>
        <input name="acquisition_price" type="number" step="0.01" value="{{ old('acquisition_price', $artwork?->acquisition_price) }}">
    </label>

    <label class="museum-field">
        <span>Current Valuation</span>
        <input name="current_valuation" type="number" step="0.01" value="{{ old('current_valuation', $artwork?->current_valuation) }}">
    </label>

    <label class="museum-field">
        <span>Status (Automated)</span>
        <input id="artwork-status-display" value="{{ $automaticStatus }}" readonly aria-readonly="true">
        <input id="artwork-status-value" type="hidden" name="status" value="{{ $automaticStatus }}">
        <select id="artwork-external-status" class="hidden" aria-label="External artwork status">
            @foreach($externalStatusOptions as $status)
                <option value="{{ $status }}" @selected($automaticStatus === $status)>{{ $status }}</option>
            @endforeach
        </select>
    </label>

    <label class="museum-field">
        <span>Location Name *</span>
        <select id="location-name-select" name="location_name" required>
            <option value="">Select location</option>
            @foreach($locationOptions as $locationOption)
                <option
                    value="{{ $locationOption->name }}"
                    data-type="{{ $locationOption->type ?? '' }}"
                    data-address="{{ $locationOption->address ?? '' }}"
                    @selected($selectedLocationName === (string) $locationOption->name)
                >
                    {{ $locationOption->name }}
                </option>
            @endforeach
            @if($selectedLocationName !== '' && !$locationOptions->pluck('name')->contains($selectedLocationName))
                <option value="{{ $selectedLocationName }}" selected>{{ $selectedLocationName }}</option>
            @endif
        </select>
    </label>

    <label class="museum-field">
        <span>Remarks <span class="font-normal text-zinc-400">(Optional)</span></span>
        <textarea name="remarks" rows="2" maxlength="5000" placeholder="Add any internal notes or reference details">{{ old('remarks', $artwork?->remarks) }}</textarea>
    </label>

    <input id="location-type-select" type="hidden" name="location_type" value="{{ $selectedLocationType }}">
</div>

<label class="museum-field">
    <span>Location Address</span>
    <input id="location-address-input" name="location_address" value="{{ $selectedLocationAddress }}">
</label>

<label class="museum-field">
    <span>Description</span>
    <textarea name="description" rows="4">{{ old('description', $artwork?->description) }}</textarea>
</label>

<label class="museum-field">
    <span>Provenance</span>
    <textarea name="provenance" rows="3">{{ old('provenance', $artwork?->provenance) }}</textarea>
</label>

<label class="museum-field">
    <span>Gallery Images</span>
    <input name="gallery_images[]" type="file" accept="image/*" multiple>
</label>

<p class="text-xs text-zinc-500">Selected images are automatically compressed before upload.</p>

<div data-upload-progress class="hidden rounded-xl border border-zinc-200 bg-zinc-50 p-3">
    <div class="mb-2 h-2 w-full overflow-hidden rounded-full bg-zinc-200">
        <div data-upload-progress-bar class="h-full w-0 bg-zinc-900 transition-all duration-300"></div>
    </div>
    <p data-upload-progress-text class="text-xs font-medium text-zinc-600">Preparing upload...</p>
</div>

@if($artwork && $artwork->images->isNotEmpty())
    <div>
        <p class="mb-2 text-sm font-semibold">Existing Gallery Images</p>
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            @foreach($artwork->images as $img)
                <div class="overflow-hidden rounded-xl border border-zinc-200 bg-white">
                    @if($img->url)
                        <img src="{{ $img->url }}" alt="Gallery image" class="h-36 w-full object-cover">
                    @else
                        <div class="flex h-36 w-full items-center justify-center bg-zinc-100 text-xs font-medium text-zinc-500">
                            Image file not found
                        </div>
                    @endif
                    <div class="space-y-2 p-3 text-xs">
                        <label class="flex items-center gap-2">
                            <input
                                type="radio"
                                name="primary_gallery_image_id"
                                value="{{ $img->id }}"
                                @checked((string) old('primary_gallery_image_id', $artwork->images->firstWhere('path', $artwork->primary_image_path)?->id) === (string) $img->id)
                            >
                            <span>Set as primary</span>
                        </label>
                        <label class="flex items-center gap-2">
                            <input type="checkbox" name="remove_gallery_image_ids[]" value="{{ $img->id }}">
                            <span>Remove image</span>
                        </label>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
@endif

<script>
document.addEventListener('DOMContentLoaded', () => {
    const forms = Array.from(document.querySelectorAll('form[enctype="multipart/form-data"]'));
    const locationSelect = document.getElementById('location-name-select');
    const locationTypeSelect = document.getElementById('location-type-select');
    const locationAddressInput = document.getElementById('location-address-input');
    const statusDisplay = document.getElementById('artwork-status-display');
    const statusValue = document.getElementById('artwork-status-value');
    const externalStatusSelect = document.getElementById('artwork-external-status');

    const statusForLocation = (locationName) => {
        const normalizedName = String(locationName || '').trim().toLowerCase();

        if (normalizedName === 'store') return 'In Storage';
        if (normalizedName === 'sold or left') return 'Sold or Left';
        if (normalizedName === 'external') return externalStatusSelect?.value || 'Under Restoration';

        return 'On Display';
    };

    const syncAutomatedStatus = () => {
        const locationName = locationSelect?.value || '';
        const isExternal = locationName.trim().toLowerCase() === 'external';
        const status = statusForLocation(locationName);

        statusDisplay?.classList.toggle('hidden', isExternal);
        externalStatusSelect?.classList.toggle('hidden', !isExternal);

        if (statusDisplay) statusDisplay.value = status;
        if (statusValue) statusValue.value = status;
    };

    const syncLocationDetails = (force = false) => {
        if (!locationSelect) {
            return;
        }

        const selectedOption = locationSelect.options[locationSelect.selectedIndex];
        if (!selectedOption) {
            return;
        }

        const type = selectedOption.dataset.type || '';
        const address = selectedOption.dataset.address || '';

        if (locationTypeSelect && (force || locationTypeSelect.value.trim() === '')) {
            locationTypeSelect.value = type;
        }

        if (locationAddressInput && (force || locationAddressInput.value.trim() === '')) {
            locationAddressInput.value = address;
        }

        syncAutomatedStatus();
    };

    locationSelect?.addEventListener('change', () => syncLocationDetails(true));
    externalStatusSelect?.addEventListener('change', syncAutomatedStatus);
    syncLocationDetails(false);

    const maxSide = 2200;
    const quality = 0.82;
    const minCompressSizeBytes = 900 * 1024;

    const makeImageBitmap = async (file) => {
        if (typeof createImageBitmap === 'function') {
            return createImageBitmap(file);
        }

        return new Promise((resolve, reject) => {
            const image = new Image();
            const objectUrl = URL.createObjectURL(file);
            image.onload = () => {
                URL.revokeObjectURL(objectUrl);
                resolve(image);
            };
            image.onerror = () => {
                URL.revokeObjectURL(objectUrl);
                reject(new Error('Unable to load image for compression.'));
            };
            image.src = objectUrl;
        });
    };

    const drawCompressedBlob = async (file) => {
        if (!file.type.startsWith('image/')) return null;
        if (file.type === 'image/gif') return null; // Preserve animated GIFs.
        if (file.size < minCompressSizeBytes) return null;

        const bitmap = await makeImageBitmap(file);
        const width = bitmap.width || bitmap.naturalWidth || 1;
        const height = bitmap.height || bitmap.naturalHeight || 1;
        const ratio = Math.min(maxSide / width, maxSide / height, 1);
        const targetWidth = Math.max(1, Math.round(width * ratio));
        const targetHeight = Math.max(1, Math.round(height * ratio));

        const canvas = document.createElement('canvas');
        canvas.width = targetWidth;
        canvas.height = targetHeight;

        const context = canvas.getContext('2d');
        if (!context) return null;
        context.drawImage(bitmap, 0, 0, targetWidth, targetHeight);

        if (typeof bitmap.close === 'function') {
            bitmap.close();
        }

        return new Promise((resolve) => {
            canvas.toBlob((blob) => resolve(blob), 'image/webp', quality);
        });
    };

    const fileWithType = (file, blob) => {
        if (!blob || blob.size <= 0 || blob.size >= file.size) {
            return file;
        }

        const base = file.name.replace(/\.[^/.]+$/, '');
        return new File([blob], `${base}.webp`, {
            type: 'image/webp',
            lastModified: Date.now(),
        });
    };

    const compressInputFiles = async (input) => {
        if (!input || !input.files || !input.files.length) return;

        const files = Array.from(input.files);
        const compressed = await Promise.all(files.map(async (file) => {
            try {
                const blob = await drawCompressedBlob(file);
                return fileWithType(file, blob);
            } catch (error) {
                return file;
            }
        }));

        const transfer = new DataTransfer();
        compressed.forEach((file) => transfer.items.add(file));
        input.files = transfer.files;
    };

    const getProgressParts = (form) => {
        const wrap = form.querySelector('[data-upload-progress]');
        const bar = wrap?.querySelector('[data-upload-progress-bar]');
        const text = wrap?.querySelector('[data-upload-progress-text]');
        return { wrap, bar, text };
    };

    const setProgress = (parts, percent, message) => {
        if (!parts.wrap || !parts.bar || !parts.text) return;
        parts.wrap.classList.remove('hidden');
        parts.bar.style.width = `${Math.max(0, Math.min(100, percent))}%`;
        if (message) {
            parts.text.textContent = message;
        }
    };

    const setValidationErrors = (parts, payload) => {
        const errors = payload && payload.errors && typeof payload.errors === 'object'
            ? Object.values(payload.errors).flat().filter((msg) => typeof msg === 'string')
            : [];
        const firstError = errors.length ? errors[0] : 'Upload failed. Please check your files and try again.';
        setProgress(parts, 100, firstError);
        if (parts.bar) {
            parts.bar.classList.remove('bg-zinc-900');
            parts.bar.classList.add('bg-rose-500');
        }
        if (parts.text) {
            parts.text.classList.remove('text-zinc-600');
            parts.text.classList.add('text-rose-600');
        }
    };

    forms.forEach((form) => {
        const primaryInput = form.querySelector('input[name="primary_image"]');
        const galleryInput = form.querySelector('input[name="gallery_images[]"]');

        if (!primaryInput && !galleryInput) return;

        form.addEventListener('submit', async (event) => {
            if (form.dataset.uploadInFlight === '1') {
                return;
            }

            event.preventDefault();

            const submitter = event.submitter || null;
            const controls = Array.from(form.querySelectorAll('button, input[type="submit"], a.museum-btn-secondary'));
            controls.forEach((control) => {
                if (control instanceof HTMLButtonElement || control instanceof HTMLInputElement) {
                    control.disabled = true;
                }
                control.classList?.add('pointer-events-none', 'opacity-70');
            });

            const progress = getProgressParts(form);
            setProgress(progress, 5, 'Compressing images...');

            try {
                await compressInputFiles(primaryInput);
                await compressInputFiles(galleryInput);

                setProgress(progress, 15, 'Uploading...');
                form.dataset.uploadInFlight = '1';

                const xhr = new XMLHttpRequest();
                xhr.open((form.method || 'POST').toUpperCase(), form.action, true);
                xhr.setRequestHeader('Accept', 'application/json');

                xhr.upload.addEventListener('progress', (uploadEvent) => {
                    if (!uploadEvent.lengthComputable) {
                        return;
                    }
                    const percent = 15 + Math.round((uploadEvent.loaded / uploadEvent.total) * 75);
                    setProgress(progress, percent, `Uploading... ${percent}%`);
                });

                xhr.addEventListener('load', () => {
                    const isJson = (xhr.getResponseHeader('Content-Type') || '').includes('application/json');
                    const payload = isJson && xhr.responseText ? JSON.parse(xhr.responseText) : null;

                    if (xhr.status >= 200 && xhr.status < 300) {
                        setProgress(progress, 100, 'Upload complete. Redirecting...');
                        const redirectUrl = payload && typeof payload.redirect_url === 'string'
                            ? payload.redirect_url
                            : xhr.responseURL;

                        if (redirectUrl) {
                            window.location.assign(redirectUrl);
                            return;
                        }

                        window.location.reload();
                        return;
                    }

                    if (xhr.status === 422) {
                        setValidationErrors(progress, payload);
                    } else {
                        setProgress(progress, 100, 'Upload failed. Please try again.');
                    }

                    form.dataset.uploadInFlight = '0';
                    controls.forEach((control) => {
                        if (control instanceof HTMLButtonElement || control instanceof HTMLInputElement) {
                            control.disabled = false;
                        }
                        control.classList?.remove('pointer-events-none', 'opacity-70');
                    });
                });

                xhr.addEventListener('error', () => {
                    setProgress(progress, 100, 'Network error during upload. Please try again.');
                    form.dataset.uploadInFlight = '0';
                    controls.forEach((control) => {
                        if (control instanceof HTMLButtonElement || control instanceof HTMLInputElement) {
                            control.disabled = false;
                        }
                        control.classList?.remove('pointer-events-none', 'opacity-70');
                    });
                });

                xhr.send(new FormData(form));
            } catch (error) {
                setProgress(progress, 100, 'Could not process selected images. Please try different files.');
                form.dataset.uploadInFlight = '0';
                controls.forEach((control) => {
                    if (control instanceof HTMLButtonElement || control instanceof HTMLInputElement) {
                        control.disabled = false;
                    }
                    control.classList?.remove('pointer-events-none', 'opacity-70');
                });
            }
        });
    });
});
</script>
