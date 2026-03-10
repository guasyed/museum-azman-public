<?php
$statuses = collect($statusOptions ?? \App\Models\Status::DEFAULT_NAMES)->values();
$locationOptions = collect($locationOptions ?? []);
$locationTypeOptions = collect($locationTypeOptions ?? []);
$selectedLocationName = (string) old('location_name', $artwork?->location?->name);
$selectedLocationType = (string) old('location_type', $artwork?->location?->type);
$selectedLocationAddress = (string) old('location_address', $artwork?->location?->address);
?>

<div class="grid gap-4 md:grid-cols-2">
    <label class="museum-field">
        <span>Artwork Title *</span>
        <input name="title" value="<?php echo e(old('title', $artwork?->title)); ?>" required>
    </label>

    <label class="museum-field">
        <span>Year</span>
        <input name="year" type="number" value="<?php echo e(old('year', $artwork?->year)); ?>">
    </label>

    <label class="museum-field">
        <span>Artist Name *</span>
        <input name="artist_name" value="<?php echo e(old('artist_name', $artwork?->artist?->name ?? request('artist_name'))); ?>" required>
    </label>

    <label class="museum-field">
        <span>Artist Country</span>
        <input name="artist_country" value="<?php echo e(old('artist_country', $artwork?->artist?->country ?? request('artist_country'))); ?>">
    </label>

    <label class="museum-field">
        <span>Artist Birth Year</span>
        <input name="artist_birth_year" type="number" value="<?php echo e(old('artist_birth_year', $artwork?->artist?->birth_year ?? request('artist_birth_year'))); ?>">
    </label>

    <label class="museum-field">
        <span>Medium</span>
        <input name="medium" value="<?php echo e(old('medium', $artwork?->medium)); ?>" placeholder="e.g., Oil on Canvas">
    </label>

    <label class="museum-field">
        <span>Size From (cm)</span>
        <input name="size_from_cm" type="number" step="0.01" value="<?php echo e(old('size_from_cm', $artwork?->size_from_cm)); ?>">
    </label>

    <label class="museum-field">
        <span>Size To (cm)</span>
        <input name="size_to_cm" type="number" step="0.01" value="<?php echo e(old('size_to_cm', $artwork?->size_to_cm)); ?>">
    </label>

    <label class="museum-field">
        <span>Acquisition Date</span>
        <input name="acquisition_date" type="date" value="<?php echo e(old('acquisition_date', optional($artwork?->acquisition_date)->format('Y-m-d'))); ?>">
    </label>

    <label class="museum-field">
        <span>Acquisition Price</span>
        <input name="acquisition_price" type="number" step="0.01" value="<?php echo e(old('acquisition_price', $artwork?->acquisition_price)); ?>">
    </label>

    <label class="museum-field">
        <span>Current Valuation</span>
        <input name="current_valuation" type="number" step="0.01" value="<?php echo e(old('current_valuation', $artwork?->current_valuation)); ?>">
    </label>

    <label class="museum-field">
        <span>Status *</span>
        <select name="status">
            <?php $__currentLoopData = $statuses; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $status): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <option value="<?php echo e($status); ?>" <?php if(old('status', $artwork?->status ?? \App\Models\Status::DEFAULT_NAMES[0]) === $status): echo 'selected'; endif; ?>><?php echo e($status); ?></option>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </select>
    </label>

    <label class="museum-field">
        <span>Location Name *</span>
        <select id="location-name-select" name="location_name" required>
            <option value="">Select location</option>
            <?php $__currentLoopData = $locationOptions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $locationOption): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <option
                    value="<?php echo e($locationOption->name); ?>"
                    data-type="<?php echo e($locationOption->type ?? ''); ?>"
                    data-address="<?php echo e($locationOption->address ?? ''); ?>"
                    <?php if($selectedLocationName === (string) $locationOption->name): echo 'selected'; endif; ?>
                >
                    <?php echo e($locationOption->name); ?>

                </option>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            <?php if($selectedLocationName !== '' && !$locationOptions->pluck('name')->contains($selectedLocationName)): ?>
                <option value="<?php echo e($selectedLocationName); ?>" selected><?php echo e($selectedLocationName); ?></option>
            <?php endif; ?>
        </select>
    </label>

    <label class="museum-field">
        <span>Location Type</span>
        <select id="location-type-select" name="location_type">
            <option value="">Select type</option>
            <?php $__currentLoopData = $locationTypeOptions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $locationTypeOption): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <option value="<?php echo e($locationTypeOption); ?>" <?php if($selectedLocationType === (string) $locationTypeOption): echo 'selected'; endif; ?>>
                    <?php echo e($locationTypeOption); ?>

                </option>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            <?php if($selectedLocationType !== '' && !$locationTypeOptions->contains($selectedLocationType)): ?>
                <option value="<?php echo e($selectedLocationType); ?>" selected><?php echo e($selectedLocationType); ?></option>
            <?php endif; ?>
        </select>
    </label>
</div>

<label class="museum-field">
    <span>Location Address</span>
    <input id="location-address-input" name="location_address" value="<?php echo e($selectedLocationAddress); ?>">
</label>

<label class="museum-field">
    <span>Description</span>
    <textarea name="description" rows="4"><?php echo e(old('description', $artwork?->description)); ?></textarea>
</label>

<label class="museum-field">
    <span>Provenance</span>
    <textarea name="provenance" rows="3"><?php echo e(old('provenance', $artwork?->provenance)); ?></textarea>
</label>

<div class="grid gap-4 md:grid-cols-2">
    <label class="museum-field">
        <span>Primary Image</span>
        <input name="primary_image" type="file" accept="image/*">
    </label>

    <label class="museum-field">
        <span>Gallery Images</span>
        <input name="gallery_images[]" type="file" accept="image/*" multiple>
    </label>
</div>

<p class="text-xs text-zinc-500">Selected images are automatically compressed before upload.</p>

<div data-upload-progress class="hidden rounded-xl border border-zinc-200 bg-zinc-50 p-3">
    <div class="mb-2 h-2 w-full overflow-hidden rounded-full bg-zinc-200">
        <div data-upload-progress-bar class="h-full w-0 bg-zinc-900 transition-all duration-300"></div>
    </div>
    <p data-upload-progress-text class="text-xs font-medium text-zinc-600">Preparing upload...</p>
</div>

<?php if($artwork && $artwork->images->isNotEmpty()): ?>
    <div>
        <p class="mb-2 text-sm font-semibold">Existing Gallery Images</p>
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <?php $__currentLoopData = $artwork->images; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $img): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div class="overflow-hidden rounded-xl border border-zinc-200 bg-white">
                    <?php if($img->url): ?>
                        <img src="<?php echo e($img->url); ?>" alt="Gallery image" class="h-36 w-full object-cover">
                    <?php else: ?>
                        <div class="flex h-36 w-full items-center justify-center bg-zinc-100 text-xs font-medium text-zinc-500">
                            Image file not found
                        </div>
                    <?php endif; ?>
                    <div class="space-y-2 p-3 text-xs">
                        <label class="flex items-center gap-2">
                            <input
                                type="radio"
                                name="primary_gallery_image_id"
                                value="<?php echo e($img->id); ?>"
                                <?php if((string) old('primary_gallery_image_id', $artwork->images->firstWhere('path', $artwork->primary_image_path)?->id) === (string) $img->id): echo 'checked'; endif; ?>
                            >
                            <span>Set as primary</span>
                        </label>
                        <label class="flex items-center gap-2">
                            <input type="checkbox" name="remove_gallery_image_ids[]" value="<?php echo e($img->id); ?>">
                            <span>Remove image</span>
                        </label>
                    </div>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
    </div>
<?php endif; ?>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const forms = Array.from(document.querySelectorAll('form[enctype="multipart/form-data"]'));
    const locationSelect = document.getElementById('location-name-select');
    const locationTypeSelect = document.getElementById('location-type-select');
    const locationAddressInput = document.getElementById('location-address-input');

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
    };

    locationSelect?.addEventListener('change', () => syncLocationDetails(true));
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
<?php /**PATH /Users/syed/MEGA/WORK/RELIVA/Website/LEBTECH/museum-azman/resources/views/artworks/partials/form.blade.php ENDPATH**/ ?>