<x-layout title="Collection - Museum Azman">
    <style>
        .collection-pagination .pagination {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: center;
            gap: 0;
            margin: 0;
            padding: 0;
            list-style: none;
        }

        .collection-pagination .page-item {
            margin: 0;
        }

        .collection-pagination .page-link {
            display: inline-flex;
            min-width: 2.5rem;
            height: 2.5rem;
            align-items: center;
            justify-content: center;
            border: 1px solid #334155;
            border-right: 0;
            background: #1e293b;
            color: #e2e8f0;
            font-weight: 600;
            text-decoration: none;
            transition: background-color 0.15s ease;
            padding: 0 0.75rem;
        }

        .collection-pagination .page-item:first-child .page-link {
            border-top-left-radius: 0.5rem;
            border-bottom-left-radius: 0.5rem;
        }

        .collection-pagination .page-item:last-child .page-link {
            border-right: 1px solid #334155;
            border-top-right-radius: 0.5rem;
            border-bottom-right-radius: 0.5rem;
        }

        .collection-pagination .page-item.active .page-link {
            background: #334155;
            color: #fff;
        }

        .collection-pagination .page-item.disabled .page-link {
            opacity: 0.45;
            pointer-events: none;
        }

        .collection-pagination .page-item:not(.active):not(.disabled) .page-link:hover {
            background: #0f172a;
            color: #fff;
        }
    </style>

    @php
        $exportQuery = [
            'q' => $q !== '' ? $q : null,
            'region' => $selectedRegion !== '' ? $selectedRegion : null,
            'status' => $selectedStatus !== '' ? $selectedStatus : null,
            'view' => $view,
            'page' => $artworks->currentPage(),
        ];
        $canManageArtworks = auth()->check() && auth()->user()->isAdmin();
    @endphp

    <section class="space-y-6">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
            <div>
                <h2 class="museum-page-title">Collection</h2>
                <p class="museum-page-subtitle">Master inventory of {{ number_format($artworks->total()) }} artworks</p>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('artworks.export.pdf', $exportQuery) }}" class="museum-btn-secondary">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M12 3v12"></path>
                        <path d="m7 10 5 5 5-5"></path>
                        <path d="M5 21h14"></path>
                    </svg>
                    <span>Export PDF</span>
                </a>
                <a href="{{ route('artworks.create') }}" class="museum-btn">+ Add Artwork</a>
            </div>
        </div>

        <form method="GET" class="rounded-2xl border border-zinc-300 bg-[#f7f7f6] p-4" id="artwork-search-form">
            <div class="flex flex-col gap-3 xl:flex-row xl:items-center">
                <div class="relative xl:flex-1">
                    <svg class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-zinc-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="11" cy="11" r="8"></circle>
                        <path d="m21 21-4.3-4.3"></path>
                    </svg>
                    <input
                        type="text"
                        name="q"
                        id="artwork-search-input"
                        value="{{ $q }}"
                        placeholder="Search by title, artist, or medium..."
                        class="w-full rounded-full border border-zinc-300 bg-white py-3 pl-10 pr-4 text-zinc-800 placeholder:text-zinc-400 focus:border-zinc-500 focus:outline-none"
                        autocomplete="off"
                        spellcheck="false"
                        aria-autocomplete="list"
                        aria-expanded="false"
                        aria-controls="artwork-search-suggestions"
                    >
                    <div
                        id="artwork-search-suggestions"
                        class="absolute left-0 right-0 z-40 mt-2 hidden max-h-[58vh] overflow-y-auto rounded-2xl border border-zinc-200 bg-white p-2 shadow-xl"
                        role="listbox"
                    ></div>
                </div>

                <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
                    <select name="region" class="min-w-46 rounded-xl border border-zinc-300 bg-white px-4 py-2.5">
                        <option value="">All Regions</option>
                        @foreach($regionOptions as $region)
                            <option value="{{ $region }}" @selected($selectedRegion === $region)>{{ $region }}</option>
                        @endforeach
                    </select>

                    <select name="status" class="min-w-40 rounded-xl border border-zinc-300 bg-white px-4 py-2.5">
                        <option value="">All Statuses</option>
                        @foreach($statusOptions as $status)
                            <option value="{{ $status }}" @selected($selectedStatus === $status)>{{ $status }}</option>
                        @endforeach
                    </select>

                    <div class="inline-flex rounded-xl border border-zinc-300 bg-white p-1">
                        <a
                            href="{{ route('artworks.index', array_merge(request()->query(), ['view' => 'grid'])) }}"
                            class="inline-flex items-center gap-1 rounded-lg px-3 py-1.5 text-sm font-semibold {{ $view === 'grid' ? 'bg-zinc-900 text-white' : 'text-zinc-600' }}"
                        >
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round">
                                <rect width="7" height="7" x="3" y="3" rx="1"></rect>
                                <rect width="7" height="7" x="14" y="3" rx="1"></rect>
                                <rect width="7" height="7" x="14" y="14" rx="1"></rect>
                                <rect width="7" height="7" x="3" y="14" rx="1"></rect>
                            </svg>
                            <span>Grid</span>
                        </a>
                        <a
                            href="{{ route('artworks.index', array_merge(request()->query(), ['view' => 'table'])) }}"
                            class="inline-flex items-center gap-1 rounded-lg px-3 py-1.5 text-sm font-semibold {{ $view === 'table' ? 'bg-zinc-900 text-white' : 'text-zinc-600' }}"
                        >
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round">
                                <line x1="8" x2="21" y1="6" y2="6"></line>
                                <line x1="8" x2="21" y1="12" y2="12"></line>
                                <line x1="8" x2="21" y1="18" y2="18"></line>
                                <line x1="3" x2="3.01" y1="6" y2="6"></line>
                                <line x1="3" x2="3.01" y1="12" y2="12"></line>
                                <line x1="3" x2="3.01" y1="18" y2="18"></line>
                            </svg>
                            <span>Table</span>
                        </a>
                    </div>

                    <a
                        href="{{ route('artworks.index', ['view' => $view]) }}"
                        class="inline-flex items-center justify-center rounded-xl border border-zinc-300 bg-white px-4 py-2 text-sm font-semibold text-zinc-700 transition hover:bg-zinc-100"
                    >
                        Reset
                    </a>
                </div>
            </div>

            <input type="hidden" name="view" value="{{ $view }}">
        </form>

        <div id="artwork-results"></div>

        @if($view === 'table')
            <article class="museum-panel p-0! overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full min-w-245 text-sm">
                        <thead>
                            <tr class="border-b border-zinc-200 text-left font-semibold text-zinc-800">
                                <th class="px-4 py-3">Artwork</th>
                                <th class="px-4 py-3">Artist</th>
                                <th class="px-4 py-3">Region</th>
                                <th class="px-4 py-3">Medium</th>
                                <th class="px-4 py-3 text-right">Value</th>
                                <th class="px-4 py-3 text-right">Status</th>
                                @if($canManageArtworks)
                                    <th class="px-4 py-3 text-right">Action</th>
                                @endif
                            </tr>
                        </thead>
                        <tbody id="artwork-results-table-body">
                            @forelse($artworks as $artwork)
                                @php
                                    $statusClass = match (strtolower((string) ($artwork->status ?? ''))) {
                                        'on display' => 'bg-emerald-100 text-emerald-700',
                                        'in storage' => 'bg-blue-100 text-blue-700',
                                        'on loan' => 'bg-violet-100 text-violet-700',
                                        'in transit' => 'bg-amber-100 text-amber-700',
                                        default => 'bg-zinc-100 text-zinc-700',
                                    };
                                @endphp
                                <tr id="artwork-{{ $artwork->id }}" class="border-b border-zinc-200 last:border-b-0">
                                    <td class="px-4 py-3.5 font-semibold text-zinc-900">
                                        <a href="{{ route('artworks.show', ['artwork' => $artwork, 'from' => 'collection', 'return' => request()->fullUrlWithQuery(['scroll_to' => $artwork->id])]) }}" class="hover:underline">{{ $artwork->title }}</a>
                                    </td>
                                    <td class="px-4 py-3.5 text-zinc-600">{{ $artwork->artist?->name ?? 'Unknown Artist' }}</td>
                                    <td class="px-4 py-3.5 text-zinc-600">{{ $artwork->artist?->country ?? '-' }}</td>
                                    <td class="px-4 py-3.5 text-zinc-600">{{ $artwork->medium ?: '-' }}</td>
                                    <td class="px-4 py-3.5 text-right font-semibold">{{ \App\Support\Currency::short((float) $artwork->current_valuation) }}</td>
                                    <td class="px-4 py-3.5 text-right">
                                        <span class="inline-flex rounded-lg px-2.5 py-1 text-xs font-semibold {{ $statusClass }}">{{ $artwork->status ?: 'Unknown' }}</span>
                                    </td>
                                    @if($canManageArtworks)
                                        <td class="px-4 py-3.5 text-right">
                                            <a
                                                href="{{ route('artworks.edit', ['artwork' => $artwork, 'from' => 'collection', 'return' => request()->fullUrlWithQuery(['scroll_to' => $artwork->id])]) }}"
                                                class="museum-btn-secondary"
                                            >
                                                Edit
                                            </a>
                                        </td>
                                    @endif
                                </tr>
                            @empty
                                <tr><td colspan="{{ $canManageArtworks ? 7 : 6 }}" class="px-4 py-5 text-zinc-500">No artworks found for this filter.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </article>
        @else
            <div id="artwork-results-list" class="grid gap-5 md:grid-cols-2 xl:grid-cols-4">
                @forelse($artworks as $artwork)
                    @php
                        $statusClass = match (strtolower((string) ($artwork->status ?? ''))) {
                            'on display' => 'bg-emerald-100 text-emerald-700',
                            'in storage' => 'bg-blue-100 text-blue-700',
                            'on loan' => 'bg-violet-100 text-violet-700',
                            'in transit' => 'bg-amber-100 text-amber-700',
                            default => 'bg-zinc-100 text-zinc-700',
                        };

                        $sizeText = ($artwork->size_from_cm && $artwork->size_to_cm)
                            ? number_format((float) $artwork->size_from_cm, 0).' × '.number_format((float) $artwork->size_to_cm, 0).' cm'
                            : ($artwork->size_from_cm ? number_format((float) $artwork->size_from_cm, 0).' cm' : '-');
                    @endphp

                    <article id="artwork-{{ $artwork->id }}" class="overflow-hidden rounded-2xl border border-zinc-300 bg-white">
                        <a href="{{ route('artworks.show', ['artwork' => $artwork, 'from' => 'collection', 'return' => request()->fullUrlWithQuery(['scroll_to' => $artwork->id])]) }}">
                            @if($artwork->primary_image_url)
                                <img src="{{ $artwork->primary_image_url }}" alt="{{ $artwork->title }}" class="h-95 w-full object-cover">
                            @else
                                <div class="flex h-95 items-center justify-center bg-zinc-100 text-zinc-500">No Image</div>
                            @endif
                        </a>

                        <div class="p-4">
                            <h3 class="museum-card-title leading-snug">
                                <a href="{{ route('artworks.show', ['artwork' => $artwork, 'from' => 'collection', 'return' => request()->fullUrlWithQuery(['scroll_to' => $artwork->id])]) }}">{{ $artwork->title }}</a>
                            </h3>
                            <p class="mt-1 text-sm text-zinc-600">{{ $artwork->artist?->name ?? 'Unknown Artist' }}{{ $artwork->year ? ', '.$artwork->year : '' }}</p>

                            <div class="mt-3 space-y-1 text-sm text-zinc-600">
                                <p>{{ $artwork->medium ?: '-' }}</p>
                                <p>{{ $sizeText }}</p>
                            </div>
                        </div>

                        <div class="flex items-center justify-between border-t border-zinc-200 px-4 py-2.5">
                            <p class="font-semibold text-zinc-800">{{ \App\Support\Currency::short((float) $artwork->current_valuation) }}</p>
                            <div class="flex items-center gap-2">
                                <span class="inline-flex rounded-lg px-2.5 py-1 text-xs font-semibold {{ $statusClass }}">{{ $artwork->status ?: 'Unknown' }}</span>
                                @if($canManageArtworks)
                                    <a
                                        href="{{ route('artworks.edit', ['artwork' => $artwork, 'from' => 'collection', 'return' => request()->fullUrlWithQuery(['scroll_to' => $artwork->id])]) }}"
                                        class="inline-flex items-center rounded-lg border border-zinc-300 px-2.5 py-1 text-xs font-semibold text-zinc-700 hover:bg-zinc-100"
                                    >
                                        Edit
                                    </a>
                                @endif
                            </div>
                        </div>
                    </article>
                @empty
                    <div class="museum-panel md:col-span-2 xl:col-span-4">
                        <p class="text-zinc-500">No artworks found for this filter.</p>
                    </div>
                @endforelse
            </div>
        @endif

        <div id="artwork-pagination" class="collection-pagination sticky bottom-0 z-20 -mx-6 bg-[#f6f5f4]/95 px-6 pb-3 pt-2 backdrop-blur supports-backdrop-filter:bg-[#f6f5f4]/85 lg:-mx-10 lg:px-10">
            {{ $artworks->appends(['scroll_to_results' => 1])->fragment('artwork-results')->links('pagination::bootstrap-5') }}
        </div>

        <div id="artwork-load-more-wrap" class="pb-2 text-center">
            <button id="artwork-load-more-btn" type="button" class="museum-btn-secondary">Load More</button>
        </div>

    </section>

    <script>
document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('artwork-search-form');
    const input = document.getElementById('artwork-search-input');
    const list = document.getElementById('artwork-search-suggestions');
    const scrollFlagKey = 'artworks:auto-scroll-results';
    const currentView = @json($view);

    const scrollToArtworkTarget = () => {
        const params = new URLSearchParams(window.location.search);
        const scrollTo = (params.get('scroll_to') || '').trim();
        const hash = window.location.hash || '';
        const targetId = scrollTo !== ''
            ? `artwork-${scrollTo}`
            : (hash.startsWith('#artwork-') ? hash.slice(1) : '');

        if (!targetId) {
            return;
        }

        const target = document.getElementById(targetId);
        if (!target) {
            return;
        }

        requestAnimationFrame(() => {
            requestAnimationFrame(() => {
                target.scrollIntoView({ behavior: 'smooth', block: 'center' });
                target.classList.add('ring-2', 'ring-zinc-400', 'ring-offset-2', 'ring-offset-[#f6f5f4]');
                window.setTimeout(() => {
                    target.classList.remove('ring-2', 'ring-zinc-400', 'ring-offset-2', 'ring-offset-[#f6f5f4]');
                }, 1800);
            });
        });

        if (scrollTo !== '') {
            params.delete('scroll_to');
            const nextQuery = params.toString();
            const nextUrl = `${window.location.pathname}${nextQuery ? `?${nextQuery}` : ''}#${targetId}`;
            window.history.replaceState({}, '', nextUrl);
        }
    };

    const enhancePaginationAutoScroll = () => {
        const resultsAnchor = document.getElementById('artwork-results');
        if (!resultsAnchor) {
            return;
        }

        const params = new URLSearchParams(window.location.search);
        const hash = window.location.hash || '';
        const shouldAutoScroll = sessionStorage.getItem(scrollFlagKey) === '1'
            || params.get('scroll_to_results') === '1'
            || hash === '#artwork-results';

        const scrollToResults = () => {
            const container = document.querySelector('main.overflow-y-auto');
            if (container) {
                const offset = 16;
                const anchorTop = resultsAnchor.getBoundingClientRect().top;
                const containerTop = container.getBoundingClientRect().top;
                const targetTop = container.scrollTop + (anchorTop - containerTop) - offset;
                container.scrollTo({ top: Math.max(0, targetTop), behavior: 'smooth' });
                return;
            }

            resultsAnchor.scrollIntoView({ behavior: 'smooth', block: 'start' });
        };

        if (shouldAutoScroll && !hash.startsWith('#artwork-')) {
            requestAnimationFrame(scrollToResults);
            window.setTimeout(scrollToResults, 120);
            sessionStorage.removeItem(scrollFlagKey);

            if (params.get('scroll_to_results') === '1') {
                params.delete('scroll_to_results');
                const nextQuery = params.toString();
                const nextUrl = `${window.location.pathname}${nextQuery ? `?${nextQuery}` : ''}${hash || ''}`;
                window.history.replaceState({}, '', nextUrl);
            }
        }

        document.addEventListener('click', (event) => {
            const link = event.target.closest('a[href]');
            if (!link) {
                return;
            }

            try {
                const url = new URL(link.getAttribute('href'), window.location.origin);
                const samePath = url.origin === window.location.origin && url.pathname === window.location.pathname;
                if (samePath && url.searchParams.has('page')) {
                    sessionStorage.setItem(scrollFlagKey, '1');
                }
            } catch (error) {
                // Ignore malformed links.
            }
        });
    };

    const initLoadMore = () => {
        const paginationWrap = document.getElementById('artwork-pagination');
        const loadMoreWrap = document.getElementById('artwork-load-more-wrap');
        const loadMoreButton = document.getElementById('artwork-load-more-btn');

        if (!paginationWrap || !loadMoreWrap || !loadMoreButton) {
            return;
        }

        const getNextUrl = () => paginationWrap.querySelector('a[rel="next"]')?.getAttribute('href') || null;
        let nextUrl = getNextUrl();

        const updateLoadMoreVisibility = () => {
            loadMoreWrap.classList.toggle('hidden', !nextUrl);
            paginationWrap.classList.toggle('hidden', !!nextUrl);
        };

        const appendRows = (doc) => {
            if (currentView === 'table') {
                const targetBody = document.getElementById('artwork-results-table-body');
                const sourceBody = doc.getElementById('artwork-results-table-body');
                if (!targetBody || !sourceBody) {
                    return;
                }

                sourceBody.querySelectorAll(':scope > tr').forEach((row) => {
                    if (row.querySelector('td[colspan]')) {
                        return;
                    }
                    targetBody.appendChild(row);
                });

                return;
            }

            const targetList = document.getElementById('artwork-results-list');
            const sourceList = doc.getElementById('artwork-results-list');
            if (!targetList || !sourceList) {
                return;
            }

            sourceList.querySelectorAll(':scope > *').forEach((item) => {
                targetList.appendChild(item);
            });
        };

        const setLoading = (loading) => {
            loadMoreButton.disabled = loading;
            loadMoreButton.textContent = loading ? 'Loading...' : 'Load More';
        };

        loadMoreButton.addEventListener('click', async () => {
            if (!nextUrl) {
                return;
            }

            setLoading(true);
            try {
                const response = await fetch(nextUrl, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'text/html',
                    },
                });

                if (!response.ok) {
                    throw new Error('Failed to load next page.');
                }

                const html = await response.text();
                const parser = new DOMParser();
                const doc = parser.parseFromString(html, 'text/html');

                appendRows(doc);

                const nextPagination = doc.getElementById('artwork-pagination');
                if (nextPagination) {
                    paginationWrap.innerHTML = nextPagination.innerHTML;
                }

                nextUrl = getNextUrl();
                updateLoadMoreVisibility();
            } catch (error) {
                loadMoreButton.textContent = 'Retry Load More';
            } finally {
                setLoading(false);
            }
        });

        updateLoadMoreVisibility();
    };


    scrollToArtworkTarget();
    enhancePaginationAutoScroll();
    initLoadMore();

    if (!form || !input || !list) {
        return;
    }

    const suggestionsUrl = '{{ route('artworks.suggestions') }}';
    const regionSelect = form.querySelector('select[name="region"]');
    const statusSelect = form.querySelector('select[name="status"]');
    let abortController = null;
    let debounceTimer = null;

    const escapeHtml = (value) => String(value ?? '')
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#39;');

    const submitForm = () => {
        if (typeof form.requestSubmit === 'function') {
            form.requestSubmit();
            return;
        }

        form.submit();
    };

    const hideSuggestions = (force = false) => {
        const hasActiveQuery = input.value.trim() !== '';
        const isInputActive = document.activeElement === input;
        if (!force && isInputActive && hasActiveQuery) {
            return;
        }

        list.classList.add('hidden');
        list.innerHTML = '';
        input.setAttribute('aria-expanded', 'false');
    };

    const showSuggestions = (items) => {
        list.innerHTML = '';

        const heading = document.createElement('div');
        heading.className = 'px-3 py-1.5 text-[0.78rem] font-semibold tracking-wide text-zinc-600 bg-zinc-100';
        heading.textContent = 'SUGGESTED SEARCHES';
        list.appendChild(heading);

        if (!items.length) {
            const empty = document.createElement('div');
            empty.className = 'px-3 py-2.5 text-sm text-zinc-500';
            empty.textContent = 'No suggestions found.';
            list.appendChild(empty);
            input.setAttribute('aria-expanded', 'true');
            list.classList.remove('hidden');
            return;
        }

        items.forEach((item) => {
            const option = document.createElement('button');
            option.type = 'button';
            option.className = 'group w-full rounded-xl px-3 py-2.5 text-left transition hover:bg-zinc-50';

            const meta = item.meta
                ? `<p class="mt-0.5 truncate text-sm text-zinc-500">${escapeHtml(item.meta)}</p>`
                : '';

            option.innerHTML = `
                <span class="block min-w-0">
                    <p class="truncate text-[1.02rem] leading-tight text-zinc-800">${escapeHtml(item.label)}</p>
                    ${meta}
                </span>
            `;

            option.addEventListener('mousedown', (event) => {
                event.preventDefault();
                input.value = item.value;
                hideSuggestions(true);
                submitForm();
            });

            list.appendChild(option);
        });

        input.setAttribute('aria-expanded', 'true');
        list.classList.remove('hidden');
    };

    const fetchSuggestions = async () => {
        const q = input.value.trim();
        if (q === '') {
            hideSuggestions(true);
            return;
        }

        if (abortController) {
            abortController.abort();
        }

        abortController = typeof AbortController !== 'undefined' ? new AbortController() : null;

        try {
            const response = await fetch(`${suggestionsUrl}?q=${encodeURIComponent(q)}`, {
                headers: { Accept: 'application/json' },
                signal: abortController ? abortController.signal : undefined,
            });

            if (!response.ok) {
                return;
            }

            const payload = await response.json();
            const groups = payload.groups && typeof payload.groups === 'object' ? payload.groups : {};
            const products = Array.isArray(groups.products) ? groups.products : [];
            const suggested = Array.isArray(groups.suggested_searches) ? groups.suggested_searches : [];
            const merged = [...products, ...suggested];

            const seen = new Set();
            const items = merged
                .filter((item) => item && typeof item === 'object' && typeof item.label === 'string')
                .map((item) => ({
                    value: typeof item.value === 'string' ? item.value : item.label,
                    label: item.label,
                    meta: typeof item.meta === 'string' ? item.meta : '',
                }))
                .filter((item) => {
                    const key = `${item.value}__${item.label}`.toLowerCase();
                    if (seen.has(key)) {
                        return false;
                    }

                    seen.add(key);
                    return true;
                });

            showSuggestions(items);
        } catch (error) {
            if (error && error.name === 'AbortError') {
                return;
            }
        }
    };

    input.addEventListener('input', () => {
        window.clearTimeout(debounceTimer);
        debounceTimer = window.setTimeout(fetchSuggestions, 180);
    });

    input.addEventListener('focus', () => {
        if (input.value.trim() !== '') {
            fetchSuggestions();
        }
    });

    input.addEventListener('click', () => {
        if (input.value.trim() !== '') {
            fetchSuggestions();
        }
    });

    document.addEventListener('click', (event) => {
        if (!form.contains(event.target)) {
            hideSuggestions(true);
        }
    });

    input.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') {
            hideSuggestions(true);
        }
    });

    form.addEventListener('submit', () => {
        // Keep current behavior simple: normal submit is still allowed.
        // Suggestions are intentionally not forced closed here.
    });

    [regionSelect, statusSelect].forEach((select) => {
        if (!select) {
            return;
        }

        select.addEventListener('change', submitForm);
    });
});
    </script>
</x-layout>
