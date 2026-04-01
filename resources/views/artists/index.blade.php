<x-layout title="Artists - Museum Azman">
    @php
        $artistModalData = $artists->map(function ($artist) {
            return [
                'id' => (int) $artist->id,
                'name' => (string) $artist->name,
                'country' => (string) ($artist->country ?: 'Unknown'),
                'birth_year' => $artist->birth_year ? (int) $artist->birth_year : null,
                'style' => (string) ($artist->style_label ?: 'Mixed Media'),
                'biography' => (string) (trim((string) $artist->biography) ?: 'No biography available for this artist yet.'),
                'works_owned' => (int) $artist->artworks_count,
                'portfolio_value' => (float) $artist->portfolio_value,
                'avg_value_per_work' => (float) $artist->avg_value_per_work,
                'artworks' => $artist->artworks->map(function ($artwork) {
                    $sizeText = ($artwork->size_from_cm && $artwork->size_to_cm)
                        ? number_format((float) $artwork->size_from_cm, 0).' × '.number_format((float) $artwork->size_to_cm, 0).' cm'
                        : ($artwork->size_from_cm ? number_format((float) $artwork->size_from_cm, 0).' cm' : '-');

                    return [
                        'title' => (string) $artwork->title,
                        'year' => $artwork->year ? (int) $artwork->year : null,
                        'medium' => (string) ($artwork->medium ?: '-'),
                        'size_text' => $sizeText,
                        'current_valuation' => (float) $artwork->current_valuation,
                        'status' => (string) ($artwork->status ?: 'Unknown'),
                        'location' => (string) ($artwork->location?->name ?: 'Unknown Location'),
                        'image_url' => $artwork->primary_image_url,
                        'edit_url' => route('artworks.edit', [
                            'artwork' => $artwork,
                            'from' => 'collection',
                            'return' => request()->fullUrl(),
                        ]),
                    ];
                })->values(),
            ];
        })->values();
    @endphp

    <section class="space-y-6">
        <div class="flex items-start justify-between gap-4">
            <div>
                <h2 class="museum-page-title">Artists</h2>
                <p class="museum-page-subtitle">Artist directory and portfolio overview</p>
            </div>
            @if(auth()->check() && auth()->user()->isAdmin())
                <button type="button" id="open-add-artist-modal" class="museum-btn shrink-0">+ Add Artist</button>
            @endif
        </div>

        <div class="grid gap-4 md:grid-cols-3">
            <article class="museum-stat-card">
                <p>Total Artists</p>
                <strong>{{ number_format($stats['total_artists']) }}</strong>
            </article>
            <article class="museum-stat-card">
                <p>Total Artworks</p>
                <strong>{{ number_format($stats['total_artworks']) }}</strong>
            </article>
            <article class="museum-stat-card">
                <p>Total Portfolio Value</p>
                <strong>{{ \App\Support\Currency::short($stats['total_portfolio_value']) }}</strong>
            </article>
        </div>

        <div class="flex flex-col gap-3 lg:flex-row lg:items-center">
            <form method="GET" class="flex-1" id="artist-search-form">
                <div class="relative">
                    <svg class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-zinc-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="11" cy="11" r="8"></circle>
                        <path d="m21 21-4.3-4.3"></path>
                    </svg>
                    <input
                        type="text"
                        name="q"
                        id="artist-search-input"
                        value="{{ $q }}"
                        placeholder="Search by name, nationality, or style..."
                        class="w-full rounded-xl border border-zinc-300 bg-white py-2.5 pl-10 pr-3"
                        autocomplete="off"
                        aria-autocomplete="list"
                        aria-expanded="false"
                        aria-controls="artist-search-suggestions"
                    >
                    <div
                        id="artist-search-suggestions"
                        class="absolute left-0 right-0 z-30 mt-2 hidden max-h-72 overflow-y-auto rounded-xl border border-zinc-200 bg-white p-1 shadow-xl"
                        role="listbox"
                    ></div>
                    <input type="hidden" name="sort" value="{{ $sort }}">
                </div>
            </form>

            @php
                $activeSortButtonStyle = 'background: var(--museum-accent); color: #fff; border-color: var(--museum-accent);';
            @endphp
            <div class="flex items-center gap-2">
                <a href="{{ route('artists.index', array_merge(request()->query(), ['sort' => 'value'])) }}" class="museum-btn-secondary" style="{{ $sort === 'value' ? $activeSortButtonStyle : '' }}">Sort by Value</a>
                <a href="{{ route('artists.index', array_merge(request()->query(), ['sort' => 'works'])) }}" class="museum-btn-secondary" style="{{ $sort === 'works' ? $activeSortButtonStyle : '' }}">Sort by Works</a>
                <a href="{{ route('artists.index', array_merge(request()->query(), ['sort' => 'name'])) }}" class="museum-btn-secondary" style="{{ $sort === 'name' ? $activeSortButtonStyle : '' }}">Sort by Name</a>
                <a href="{{ route('artists.index') }}" class="museum-btn-secondary">Reset</a>
            </div>
        </div>

        <article class="museum-panel">
            <div class="mb-4">
                <h3 class="museum-section-title">Artist Directory</h3>
                <p class="mt-1 text-zinc-600">{{ number_format($artists->count()) }} artists in collection</p>
            </div>

            <div class="overflow-x-auto rounded-2xl border border-zinc-200 bg-white">
                <table class="w-full min-w-[1080px] text-sm">
                    <thead class="sticky top-0 z-10 bg-white/90 backdrop-blur supports-[backdrop-filter]:bg-white/70" style="color: color-mix(in srgb, var(--museum-accent) 78%, #374151);">
                        <tr class="border-b border-zinc-200 text-left text-xs font-semibold uppercase tracking-wide">
                            <th class="px-4 py-3">Artist</th>
                            <th class="px-4 py-3">Nationality</th>
                            <th class="px-4 py-3">Birth</th>
                            <th class="px-4 py-3">Style</th>
                            <th class="px-4 py-3 text-center">Works</th>
                            <th class="px-4 py-3 text-right">Portfolio</th>
                            <th class="px-4 py-3 text-right">Action</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-zinc-100">
                        @forelse($artists as $artist)
                            <tr
                                class="group bg-white transition hover:bg-zinc-50"
                            >
                                {{-- Artist --}}
                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-3">
                                       {{--  <div class="flex h-9 w-9 items-center justify-center rounded-xl border border-zinc-200 bg-zinc-50 text-xs font-semibold text-zinc-700">
                                            {{ strtoupper(mb_substr($artist->name, 0, 2)) }}
                                        </div> --}}

                                        <div class="min-w-0">
                                            <p class="truncate font-semibold text-zinc-900" title="{{ $artist->name }}">
                                                {{ \Illuminate\Support\Str::limit($artist->name, 30) }}
                                            </p>
                                            <p class="mt-0.5 text-xs text-zinc-500">
                                                ID #{{ $artist->id }}
                                            </p>
                                        </div>
                                    </div>
                                </td>

                                {{-- Nationality --}}
                                <td class="px-4 py-3">
                                    <span
                                        class="inline-flex items-center rounded-lg px-2 py-1 text-xs font-semibold"
                                        style="background: color-mix(in srgb, var(--museum-accent) 14%, white); color: var(--museum-accent); border: 1px solid color-mix(in srgb, var(--museum-accent) 35%, white);"
                                    >
                                        {{ $artist->country ?: 'Unknown' }}
                                    </span>
                                </td>

                                {{-- Birth Year --}}
                                <td class="px-4 py-3 text-zinc-700">
                                    {{ $artist->birth_year ?: '—' }}
                                </td>

                                {{-- Style --}}
                                <td class="px-4 py-3 text-zinc-700">
                                    <span class="block max-w-[260px] truncate" title="{{ $artist->style_label ?: '' }}">
                                        {{-- {{ $artist->style_label ?: 'Mixed Media' }} --}}
                                        {{ \Illuminate\Support\Str::limit($artist->style_label, 30) ?: 'Mixed Media'}}
                                    </span>
                                </td>

                                {{-- Works --}}
                                <td class="px-4 py-3 text-center font-semibold text-zinc-900 tabular-nums">
                                    {{ number_format($artist->artworks_count) }}
                                </td>

                                {{-- Portfolio Value --}}
                                <td class="px-4 py-3 text-right font-semibold text-zinc-900 tabular-nums">
                                    {{ \App\Support\Currency::short((float) $artist->portfolio_value) }}
                                    <div class="mt-0.5 text-xs font-normal text-zinc-500">
                                        Avg {{ \App\Support\Currency::short((float) $artist->avg_value_per_work) }}/work
                                    </div>
                                </td>

                                {{-- Action --}}
                                <td class="px-4 py-3 text-right">
                                    <div class="inline-flex items-center gap-2">
                                        <button
                                            type="button"
                                            class="inline-flex items-center gap-2 rounded-xl border bg-white px-3 py-2 text-xs font-semibold shadow-sm transition group-hover:shadow js-open-artist-modal"
                                            style="border-color: color-mix(in srgb, var(--museum-accent) 38%, white); color: var(--museum-accent);"
                                            data-artist-id="{{ $artist->id }}"
                                            aria-label="View {{ $artist->name }}"
                                        >
                                            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round">
                                                <path d="M2.062 12.348a1 1 0 0 1 0-.696 10.75 10.75 0 0 1 19.876 0 1 1 0 0 1 0 .696 10.75 10.75 0 0 1-19.876 0"></path>
                                                <circle cx="12" cy="12" r="3"></circle>
                                            </svg>
                                            View
                                        </button>
                                        @if(auth()->check() && auth()->user()->isAdmin())
                                            <button
                                                type="button"
                                                class="inline-flex items-center gap-1.5 rounded-xl border border-zinc-300 bg-white px-3 py-2 text-xs font-semibold text-zinc-700 shadow-sm transition hover:bg-zinc-50 js-open-edit-artist"
                                                data-artist-id="{{ $artist->id }}"
                                                data-artist-name="{{ $artist->name }}"
                                                data-artist-country="{{ $artist->country }}"
                                                data-artist-birth="{{ $artist->birth_year }}"
                                                data-artist-bio="{{ $artist->biography }}"
                                                aria-label="Edit {{ $artist->name }}"
                                            >
                                                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round"><path d="M17 3a2.85 2.83 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z"/></svg>
                                                Edit
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-4 py-10 text-center text-zinc-500">
                                    No artists found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </article>
    </section>

    {{-- Create / Edit Artist Modal --}}
    @if(auth()->check() && auth()->user()->isAdmin())
    <div class="museum-modal-overlay" id="artist-form-modal">
        <div class="museum-modal" role="dialog" aria-modal="true" aria-labelledby="artist-form-title" style="max-width:36rem;">
            <button type="button" class="museum-modal-close" id="artist-form-close" aria-label="Close">&times;</button>

            <h3 id="artist-form-title" class="museum-section-title text-xl! mb-4"></h3>

            <form id="artist-form" method="POST" class="space-y-4">
                @csrf
                <span id="artist-form-method-field"></span>

                <label class="museum-field block">
                    <span>Name <span class="text-rose-500">*</span></span>
                    <input type="text" name="name" id="af-name" required maxlength="255">
                </label>

                <label class="museum-field block">
                    <span>Nationality / Country</span>
                    <input type="text" name="country" id="af-country" maxlength="255" placeholder="e.g. Malaysia">
                </label>

                <label class="museum-field block">
                    <span>Birth Year</span>
                    <input type="number" name="birth_year" id="af-birth" min="1000" max="{{ date('Y') }}" placeholder="e.g. 1975">
                </label>

                <label class="museum-field block">
                    <span>Biography</span>
                    <textarea name="biography" id="af-bio" rows="4" maxlength="5000" class="resize-y"></textarea>
                </label>

                <div class="flex items-center justify-between gap-3 pt-2">
                    <button type="button" id="af-delete-btn" class="hidden rounded-xl border border-rose-200 bg-rose-50 px-4 py-2 text-sm font-semibold text-rose-700 transition hover:bg-rose-100">Delete Artist</button>
                    <div class="ml-auto flex gap-2">
                        <button type="button" id="artist-form-cancel" class="museum-btn-secondary">Cancel</button>
                        <button type="submit" class="museum-btn" id="af-submit-btn">Save</button>
                    </div>
                </div>
            </form>

            {{-- Hidden delete form --}}
            <form id="artist-delete-form" method="POST" class="hidden">
                @csrf
                @method('DELETE')
            </form>
        </div>
    </div>
    @endif

    <div class="museum-modal-overlay" id="artist-view-modal">
        <div class="museum-modal" role="dialog" aria-modal="true" aria-labelledby="artist-view-title">
            <button type="button" class="museum-modal-close" id="artist-view-close" aria-label="Close">&times;</button>

            <div class="space-y-4">
                <div>
                    <h3 id="artist-view-title" class="museum-page-title text-[2rem]! leading-tight"></h3>
                    <p id="artist-view-meta" class="mt-2 text-lg text-zinc-600"></p>
                </div>

                <div>
                    <p class="text-sm font-semibold text-zinc-700">Biography</p>
                    <p id="artist-view-biography" class="mt-1 text-zinc-700"></p>
                </div>

                <div class="grid gap-3 md:grid-cols-3">
                    <div class="rounded-xl border border-zinc-200 bg-zinc-50 p-4">
                        <p class="text-xs font-semibold uppercase tracking-wide text-zinc-500">Works Owned</p>
                        <p id="artist-view-works" class="mt-3 text-2xl font-semibold text-zinc-900"></p>
                    </div>
                    <div class="rounded-xl border border-zinc-200 bg-zinc-50 p-4">
                        <p class="text-xs font-semibold uppercase tracking-wide text-zinc-500">Total Portfolio Value</p>
                        <p id="artist-view-value" class="mt-3 text-2xl font-semibold text-zinc-900"></p>
                    </div>
                    <div class="rounded-xl border border-zinc-200 bg-zinc-50 p-4">
                        <p class="text-xs font-semibold uppercase tracking-wide text-zinc-500">Avg. Value per Work</p>
                        <p id="artist-view-avg" class="mt-3 text-2xl font-semibold text-zinc-900"></p>
                    </div>
                </div>

                <div>
                    <div class="flex items-center justify-between gap-3">
                        <p class="text-xl font-semibold text-zinc-900">Artworks in Collection</p>
                        @if(auth()->check() && auth()->user()->isAdmin())
                            <a
                                id="artist-view-add-artwork"
                                href="{{ route('artworks.create') }}"
                                class="inline-flex items-center rounded-xl border border-zinc-300 bg-white px-3 py-1.5 text-sm font-semibold text-zinc-800 transition hover:bg-zinc-50"
                            >
                                Add New Artwork
                            </a>
                        @endif
                    </div>
                    <div id="artist-view-artworks" class="mt-3 space-y-2"></div>
                </div>
            </div>
        </div>
    </div>

    <script>
document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('artist-search-form');
    const input = document.getElementById('artist-search-input');
    const list = document.getElementById('artist-search-suggestions');

    if (!form || !input || !list) {
        return;
    }

    const suggestions = @json($artistSuggestions);
    let items = [];
    let activeIndex = -1;

    const hide = () => {
        list.innerHTML = '';
        list.classList.add('hidden');
        input.setAttribute('aria-expanded', 'false');
        items = [];
        activeIndex = -1;
    };

    const escapeHtml = (value) => String(value ?? '')
        .replaceAll('&', '&amp;')
        .replaceAll('<', '&lt;')
        .replaceAll('>', '&gt;')
        .replaceAll('"', '&quot;')
        .replaceAll("'", '&#39;');

    const render = () => {
        list.innerHTML = '';
        if (!items.length) {
            hide();
            return;
        }

        const keyword = input.value.trim().toLowerCase();

        items.forEach((item, index) => {
            const option = document.createElement('button');
            option.type = 'button';
            option.className = [
                'w-full rounded-lg px-3 py-2 text-left transition',
                index === activeIndex ? 'bg-zinc-100' : 'hover:bg-zinc-50',
            ].join(' ');
            option.setAttribute('role', 'option');
            option.setAttribute('aria-selected', index === activeIndex ? 'true' : 'false');

            const label = escapeHtml(item.label);
            const text = keyword
                ? label.replace(new RegExp(`(${keyword.replace(/[.*+?^${}()|[\]\\]/g, '\\$&')})`, 'ig'), '<strong class="font-semibold text-zinc-900">$1</strong>')
                : label;

            option.innerHTML = `
                <p class="truncate text-sm text-zinc-800">${text}</p>
                <p class="truncate text-xs text-zinc-500">${escapeHtml(item.meta)}</p>
            `;

            option.addEventListener('mousedown', (event) => {
                event.preventDefault();
                input.value = item.query;
                hide();
                form.requestSubmit();
            });

            list.appendChild(option);
        });

        list.classList.remove('hidden');
        input.setAttribute('aria-expanded', 'true');
    };

    const update = () => {
        const q = input.value.trim().toLowerCase();
        if (!q) {
            hide();
            return;
        }

        items = suggestions
            .filter((item) => item && typeof item === 'object')
            .filter((item) => String(item.query || '').toLowerCase().includes(q))
            .slice(0, 8);
        activeIndex = -1;
        render();
    };

    input.addEventListener('input', update);
    input.addEventListener('focus', update);

    input.addEventListener('keydown', (event) => {
        if (!items.length) {
            return;
        }

        if (event.key === 'ArrowDown') {
            event.preventDefault();
            activeIndex = (activeIndex + 1) % items.length;
            render();
            return;
        }

        if (event.key === 'ArrowUp') {
            event.preventDefault();
            activeIndex = activeIndex <= 0 ? items.length - 1 : activeIndex - 1;
            render();
            return;
        }

        if (event.key === 'Enter' && activeIndex >= 0) {
            event.preventDefault();
            input.value = items[activeIndex].query;
            hide();
            form.requestSubmit();
            return;
        }

        if (event.key === 'Escape') {
            hide();
        }
    });

    document.addEventListener('click', (event) => {
        if (!form.contains(event.target)) {
            hide();
        }
    });

    const artistData = @json($artistModalData);
    const canAdminEdit = @json(auth()->check() && auth()->user()->isAdmin());
    const artistMap = new Map(artistData.map((artist) => [String(artist.id), artist]));
    const modal = document.getElementById('artist-view-modal');
    const modalClose = document.getElementById('artist-view-close');
    const titleEl = document.getElementById('artist-view-title');
    const metaEl = document.getElementById('artist-view-meta');
    const bioEl = document.getElementById('artist-view-biography');
    const worksEl = document.getElementById('artist-view-works');
    const valueEl = document.getElementById('artist-view-value');
    const avgEl = document.getElementById('artist-view-avg');
    const artworksEl = document.getElementById('artist-view-artworks');
    const addArtworkEl = document.getElementById('artist-view-add-artwork');

    const currencySymbol = @json(\App\Support\Currency::symbol());
    const compactNumberFormatter = new Intl.NumberFormat(undefined, {
        notation: 'compact',
        maximumFractionDigits: 2,
    });
    const formatMoney = (value) => `${currencySymbol}${Number(value || 0).toLocaleString()}`;
    const formatMoneyShort = (value) => {
        const amount = Number(value || 0);
        if (!Number.isFinite(amount)) {
            return `${currencySymbol}0`;
        }
        if (Math.abs(amount) < 1000000) {
            return formatMoney(amount);
        }

        return `${currencySymbol}${compactNumberFormatter.format(amount)}`;
    };
    const statusClass = (status) => {
        const key = String(status || '').toLowerCase();
        if (key === 'on display') return 'bg-zinc-900 text-white';
        if (key === 'in stage') return 'bg-blue-100 text-blue-700';
        if (key === 'on loan') return 'bg-violet-100 text-violet-700';
        if (key === 'under restoration') return 'bg-amber-100 text-amber-700';
        if (key === 'in storage') return 'bg-blue-100 text-blue-700';
        if (key === 'in transit') return 'bg-amber-100 text-amber-700';
        return 'bg-zinc-100 text-zinc-700';
    };

    const openArtistModal = (artistId) => {
        const artist = artistMap.get(String(artistId));
        if (!artist || !modal) {
            return;
        }

        titleEl.textContent = artist.name;
        metaEl.textContent = `${artist.country} • Born ${artist.birth_year ?? '-'} • ${artist.style}`;
        bioEl.textContent = artist.biography;
        worksEl.textContent = Number(artist.works_owned || 0).toLocaleString();
        valueEl.textContent = formatMoneyShort(artist.portfolio_value);
        avgEl.textContent = formatMoneyShort(artist.avg_value_per_work);

        if (canAdminEdit && addArtworkEl) {
            const createUrl = new URL(@json(route('artworks.create')), window.location.origin);
            createUrl.searchParams.set('artist_name', artist.name || '');
            createUrl.searchParams.set('artist_country', artist.country || '');
            if (artist.birth_year) {
                createUrl.searchParams.set('artist_birth_year', String(artist.birth_year));
            }
            addArtworkEl.href = createUrl.toString();
        }

        artworksEl.innerHTML = '';
        const artworks = Array.isArray(artist.artworks) ? artist.artworks : [];

        if (!artworks.length) {
            artworksEl.innerHTML = '<p class="text-zinc-500">No artworks found for this artist.</p>';
        } else {
            artworks.forEach((artwork) => {
                const card = document.createElement('article');
                card.className = 'rounded-xl border border-zinc-200 bg-white p-3';
                const imageHtml = artwork.image_url
                    ? `<img src="${escapeHtml(artwork.image_url)}" alt="${escapeHtml(artwork.title)}" class="h-20 w-28 rounded-lg object-cover">`
                    : `<div class="flex h-20 w-28 items-center justify-center rounded-lg bg-zinc-100 text-xs text-zinc-500">No Image</div>`;
                card.innerHTML = `
                    <div class="flex items-start justify-between gap-3">
                        <div class="flex min-w-0 items-start gap-3">
                            ${imageHtml}
                            <div class="min-w-0">
                                <p class="line-clamp-2 text-xl font-semibold text-zinc-900">${escapeHtml(artwork.title)}</p>
                                <p class="mt-1 text-zinc-600">${artwork.year ?? '-'} • ${escapeHtml(artwork.medium)} • ${escapeHtml(artwork.size_text)}</p>
                                <div class="mt-2 flex flex-wrap items-center gap-2">
                                    <span class="inline-flex rounded-lg px-2.5 py-1 text-xs font-semibold ${statusClass(artwork.status)}">${escapeHtml(artwork.status)}</span>
                                    <span class="text-sm text-zinc-600">${escapeHtml(artwork.location)}</span>
                                </div>
                            </div>
                        </div>
                        <div class="text-right">
                            <p class="text-xs uppercase tracking-wide text-zinc-500">Current Value</p>
                            <p class="mt-1 font-semibold text-zinc-900">${formatMoneyShort(artwork.current_valuation)}</p>
                            ${canAdminEdit ? `
                                <a href="${escapeHtml(artwork.edit_url)}" class="mt-2 inline-flex items-center justify-center rounded-lg border border-zinc-300 px-2.5 py-1 text-xs font-semibold text-zinc-700 hover:bg-zinc-100">
                                    Edit Artwork
                                </a>
                            ` : ''}
                        </div>
                    </div>
                `;
                artworksEl.appendChild(card);
            });
        }

        modal.classList.add('is-open');
    };

    document.querySelectorAll('.js-open-artist-modal').forEach((button) => {
        button.addEventListener('click', () => openArtistModal(button.dataset.artistId));
    });

    const closeArtistModal = () => {
        if (!modal) {
            return;
        }
        modal.classList.remove('is-open');
    };

    modalClose?.addEventListener('click', closeArtistModal);
    modal?.addEventListener('click', (event) => {
        if (event.target === modal) {
            closeArtistModal();
        }
    });

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') {
            closeArtistModal();
        }
    });

    // ── Create / Edit Artist modal ──────────────────────────────────
    const formModal      = document.getElementById('artist-form-modal');
    const formEl         = document.getElementById('artist-form');
    const formTitle      = document.getElementById('artist-form-title');
    const formMethodEl   = document.getElementById('artist-form-method-field');
    const afName         = document.getElementById('af-name');
    const afCountry      = document.getElementById('af-country');
    const afBirth        = document.getElementById('af-birth');
    const afBio          = document.getElementById('af-bio');
    const afSubmitBtn    = document.getElementById('af-submit-btn');
    const afDeleteBtn    = document.getElementById('af-delete-btn');
    const deleteForm     = document.getElementById('artist-delete-form');
    const openAddBtn     = document.getElementById('open-add-artist-modal');

    const createRoute    = @json(route('artists.store'));
    const updateRouteBase= @json(route('artists.update', ['artist' => '__ID__']));
    const deleteRouteBase= @json(route('artists.destroy', ['artist' => '__ID__']));

    const closeFormModal = () => formModal?.classList.remove('is-open');

    const openFormModal = ({ id = null, name = '', country = '', birth_year = '', biography = '' } = {}) => {
        if (!formModal || !formEl) return;

        const isEdit = !!id;
        formTitle.textContent = isEdit ? 'Edit Artist' : 'Add Artist';
        afSubmitBtn.textContent = isEdit ? 'Save Changes' : 'Add Artist';

        formEl.action = isEdit ? updateRouteBase.replace('__ID__', id) : createRoute;
        formMethodEl.innerHTML = isEdit ? '<input type="hidden" name="_method" value="PUT">' : '';

        afName.value    = name;
        afCountry.value = country;
        afBirth.value   = birth_year ?? '';
        afBio.value     = biography ?? '';

        if (isEdit && deleteForm && afDeleteBtn) {
            deleteForm.action = deleteRouteBase.replace('__ID__', id);
            afDeleteBtn.classList.remove('hidden');
        } else {
            afDeleteBtn?.classList.add('hidden');
        }

        formModal.classList.add('is-open');
        setTimeout(() => afName.focus(), 60);
    };

    openAddBtn?.addEventListener('click', () => openFormModal());

    document.querySelectorAll('.js-open-edit-artist').forEach((btn) => {
        btn.addEventListener('click', () => openFormModal({
            id:         btn.dataset.artistId,
            name:       btn.dataset.artistName,
            country:    btn.dataset.artistCountry,
            birth_year: btn.dataset.artistBirth,
            biography:  btn.dataset.artistBio,
        }));
    });

    document.getElementById('artist-form-close')?.addEventListener('click', closeFormModal);
    document.getElementById('artist-form-cancel')?.addEventListener('click', closeFormModal);
    formModal?.addEventListener('click', (e) => { if (e.target === formModal) closeFormModal(); });

    afDeleteBtn?.addEventListener('click', () => {
        if (confirm('Delete this artist? This cannot be undone.')) {
            deleteForm?.submit();
        }
    });
});
    </script>
</x-layout>
