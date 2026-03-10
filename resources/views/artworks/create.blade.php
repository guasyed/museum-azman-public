<x-layout title="Add Artwork - Museum Azman">
    <section class="space-y-6">
        <div>
            <h2 class="museum-page-title">Add New Artwork</h2>
            <p class="museum-page-subtitle">Create a new artwork record. Uploaded images are compressed and saved locally.</p>
        </div>

        <form method="POST" action="{{ route('artworks.store') }}" enctype="multipart/form-data" class="museum-panel space-y-6">
            @csrf
            @include('artworks.partials.form', ['artwork' => null])
            <div class="flex justify-end">
                <button type="submit" class="museum-btn">Save Artwork</button>
            </div>
        </form>
    </section>
</x-layout>
