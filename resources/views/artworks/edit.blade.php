<x-layout title="Edit Artwork - Museum Azman">
    @php
        $returnUrl = request()->query('return');
        if (!is_string($returnUrl) || trim($returnUrl) === '') {
            $returnUrl = url()->previous();
        }

        $returnHost = is_string($returnUrl) ? parse_url($returnUrl, PHP_URL_HOST) : null;
        $returnPath = is_string($returnUrl) ? parse_url($returnUrl, PHP_URL_PATH) : null;
        $isSafeReturnUrl = is_string($returnUrl)
            && $returnUrl !== ''
            && is_string($returnPath)
            && str_starts_with($returnPath, '/')
            && ($returnHost === null || $returnHost === request()->getHost());

        $backUrl = $isSafeReturnUrl
            ? $returnUrl
            : route('artworks.show', [
                'artwork' => $artwork,
                'from' => request()->string('from')->toString() === 'dashboard' ? 'dashboard' : 'collection',
            ]);
    @endphp

    <section class="space-y-6">
        <div class="flex items-start justify-between gap-3">
            <div>
                <h2 class="museum-page-title">Edit Artwork</h2>
                <p class="museum-page-subtitle">Update artwork details and images.</p>
            </div>
            <a href="{{ $backUrl }}" class="museum-btn-secondary">Back</a>
        </div>

        <form method="POST" action="{{ route('artworks.update', ['artwork' => $artwork, 'from' => request()->string('from')->toString(), 'return' => $returnUrl]) }}" enctype="multipart/form-data" class="museum-panel space-y-6">
            @csrf
            @method('PUT')
            @include('artworks.partials.form', ['artwork' => $artwork])
            <div class="flex justify-end">
                <a href="{{ $backUrl }}" class="museum-btn-secondary" style='margin-right: 10px;'>Back</a>
                <button type="submit" class="museum-btn">Update Artwork</button>
            </div>
        </form>
    </section>
</x-layout>
