@extends('layouts.app')

@section('title', 'Publications — UMMISCO')
@section('description', 'Catalogue des publications scientifiques du laboratoire UMMISCO. Recherchez par DOI, ORCID ou mots-clés.')

@section('styles')
<style>
/* ── Page Header ──────────────────────────────────────────────── */
.pub-page-header {
    position: relative;
    padding: 5rem 0 4rem;
    overflow: hidden;
    border-bottom: 1px solid var(--border);
    background: var(--bg-secondary);
}

.pub-page-header::before {
    content: '';
    position: absolute;
    inset: 0;
    background-image: url('{{ asset('images/pub_library.jpg') }}');
    background-size: cover;
    background-position: center 30%;
    opacity: 0.18;
    transition: opacity 0.35s ease;
}

[data-theme="dark"] .pub-page-header::before {
    opacity: 0.12;
    filter: brightness(0.7) saturate(0.6);
}

.pub-page-header::after {
    content: '';
    position: absolute;
    inset: 0;
    background:
        linear-gradient(to bottom, var(--bg-secondary) 0%, transparent 30%, transparent 70%, var(--bg-secondary) 100%),
        radial-gradient(ellipse 60% 50% at 50% 100%, var(--primary-glow) 0%, transparent 70%);
    pointer-events: none;
}


/* ── Search Bar ───────────────────────────────────────────────── */
.search-bar-wrapper {
    position: relative;
    max-width: 720px;
    margin: 0 auto 1.5rem;
}

.search-bar {
    width: 100%;
    height: 60px;
    padding: 0 5rem 0 3.5rem;
    background: var(--surface);
    border: 1.5px solid var(--border-strong);
    border-radius: var(--radius-lg);
    font-size: 1rem;
    font-family: inherit;
    font-weight: 400;
    color: var(--text);
    outline: none;
    transition: border-color 0.2s ease, box-shadow 0.2s ease;
    box-shadow: var(--shadow);
}

.search-bar:focus {
    border-color: var(--primary-light);
    box-shadow: 0 0 0 5px var(--primary-glow), var(--shadow-md);
}

.search-bar::placeholder { color: var(--text-subtle); }

.search-icon-left {
    position: absolute;
    left: 1.25rem;
    top: 50%;
    transform: translateY(-50%);
    color: var(--text-subtle);
    pointer-events: none;
    transition: color 0.2s;
}

.search-bar:focus ~ .search-icon-left,
.search-bar-wrapper:focus-within .search-icon-left {
    color: var(--primary-light);
}

.search-btn {
    position: absolute;
    right: 0.5rem;
    top: 50%;
    transform: translateY(-50%);
    height: 44px;
    padding: 0 1.25rem;
    background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%);
    color: #fff;
    border: none;
    border-radius: var(--radius);
    font-size: 0.875rem;
    font-weight: 700;
    cursor: pointer;
    transition: var(--transition);
    font-family: inherit;
}

.search-btn:hover {
    filter: brightness(1.1);
    transform: translateY(-50%) translateY(-1px);
}

.search-hints {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 1.5rem;
    flex-wrap: wrap;
}

.search-hint-chip {
    display: inline-flex;
    align-items: center;
    gap: 0.375rem;
    font-size: 0.72rem;
    font-weight: 600;
    color: var(--text-subtle);
    background: var(--bg-tertiary);
    border: 1px solid var(--border);
    border-radius: 9999px;
    padding: 0.25rem 0.75rem;
    cursor: pointer;
    transition: var(--transition);
    user-select: none;
}

.search-hint-chip:hover {
    color: var(--primary-light);
    border-color: var(--primary-light);
    background: var(--primary-glow);
}

/* ── Filters Strip ────────────────────────────────────────────── */
.filters-strip {
    background: var(--bg-secondary);
    border-bottom: 1px solid var(--border);
    padding: 0.875rem 0;
    position: sticky;
    top: 70px;
    z-index: 100;
    backdrop-filter: saturate(180%) blur(16px);
    -webkit-backdrop-filter: saturate(180%) blur(16px);
}

.filters-inner {
    display: flex;
    align-items: center;
    gap: 1rem;
    flex-wrap: wrap;
}

.filter-select {
    height: 36px;
    padding: 0 2rem 0 0.875rem;
    background: var(--surface);
    border: 1.5px solid var(--border-strong);
    border-radius: var(--radius-sm);
    font-size: 0.82rem;
    font-family: inherit;
    font-weight: 500;
    color: var(--text);
    cursor: pointer;
    outline: none;
    transition: var(--transition);
    appearance: none;
    background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'%3e%3cpath fill='none' stroke='%2394a3b8' stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='m2 5 6 6 6-6'/%3e%3c/svg%3e");
    background-repeat: no-repeat;
    background-position: right 0.625rem center;
    background-size: 12px 8px;
}

.filter-select:focus {
    border-color: var(--primary-light);
    box-shadow: 0 0 0 3px var(--primary-glow);
}

.filter-reset {
    height: 36px;
    padding: 0 0.875rem;
    background: transparent;
    border: 1.5px solid var(--border-strong);
    border-radius: var(--radius-sm);
    font-size: 0.8rem;
    font-weight: 600;
    color: var(--danger);
    cursor: pointer;
    outline: none;
    transition: var(--transition);
    font-family: inherit;
    display: flex; align-items: center; gap: 0.25rem;
}

.filter-reset:hover {
    background: rgba(225,29,72,0.08);
    border-color: var(--danger);
}

.results-count {
    margin-left: auto;
    font-size: 0.8rem;
    font-weight: 600;
    color: var(--text-subtle);
    white-space: nowrap;
}

/* ── Skeleton ─────────────────────────────────────────────────── */
.skeleton-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 1.5rem;
}

.skeleton-card {
    background: var(--card-bg);
    border-radius: var(--radius);
    border: 1px solid var(--border);
    padding: 1.75rem;
}

.skeleton-line {
    height: 12px;
    border-radius: 6px;
    margin-bottom: 0.75rem;
}

/* ── Loading state ─────────────────────────────────────────────── */
#publications-container {
    transition: opacity 0.25s ease;
}
#publications-container.is-loading { opacity: 0.4; pointer-events: none; }

</style>
@endsection

@section('content')

{{-- Page Header --}}
<div class="pub-page-header">
    <div class="container" style="position:relative;z-index:1;text-align:center;">
        <div class="section-label" style="justify-content:center; margin-bottom:1rem;">
            Bibliothèque scientifique
        </div>
        <h1 style="font-size:clamp(2rem,5vw,3rem); font-weight:900; letter-spacing:-0.03em; color:var(--text); margin-bottom:1rem;">
            Publications du Laboratoire
        </h1>
        <p style="font-size:1rem; color:var(--text-muted); max-width:620px; margin:0 auto 2.5rem; line-height:1.65;">
            Recherchez par <strong style="color:var(--text);">mots-clés</strong>, par <strong style="color:var(--text);">DOI</strong>
            ou par <strong style="color:var(--text);">ORCID</strong> de chercheur. Les résultats intègrent notre base locale et les archives académiques.
        </p>

        {{-- Search Bar --}}
        <form method="GET" action="{{ route('publications') }}" id="searchForm">
            <div class="search-bar-wrapper">
                <svg class="search-icon-left" viewBox="0 0 24 24" width="20" height="20" stroke="currentColor" stroke-width="2" fill="none">
                    <circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/>
                </svg>
                <input
                    type="text"
                    name="q"
                    id="searchInput"
                    class="search-bar"
                    placeholder="Titre, mot-clé, DOI (10.xxxx/...) ou ORCID (0000-0000-0000-0000)"
                    value="{{ request('q') }}"
                    autocomplete="off"
                >
                <button type="submit" class="search-btn">Rechercher</button>
            </div>

            <div class="search-hints">
                <span class="search-hint-chip" data-fill="intelligence artificielle">
                    <svg viewBox="0 0 24 24" width="11" height="11" stroke="currentColor" stroke-width="2.5" fill="none"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
                    intelligence artificielle
                </span>
                <span class="search-hint-chip" data-fill="modélisation agents">
                    <svg viewBox="0 0 24 24" width="11" height="11" stroke="currentColor" stroke-width="2.5" fill="none"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
                    modélisation agents
                </span>
                <span class="search-hint-chip" data-fill-prefix="DOI :" data-example="10.1038/s41586-023-06919-z">
                    <svg viewBox="0 0 24 24" width="11" height="11" stroke="currentColor" stroke-width="2.5" fill="none"><path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"/><path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"/></svg>
                    DOI
                </span>
                <span class="search-hint-chip" data-example="0000-0002-1825-0097">
                    <svg viewBox="0 0 24 24" width="11" height="11" stroke="currentColor" stroke-width="2.5" fill="none"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                    ORCID
                </span>
            </div>

            {{-- Hidden filters (updated by the sticky bar) --}}
            <input type="hidden" name="type" id="hiddenType" value="{{ request('type') }}">
            <input type="hidden" name="axe"  id="hiddenAxe"  value="{{ request('axe') }}">
        </form>
    </div>
</div>

{{-- Filters Strip --}}
<div class="filters-strip">
    <div class="container">
        <div class="filters-inner">
            <svg viewBox="0 0 24 24" width="16" height="16" stroke="currentColor" stroke-width="2" fill="none" style="color:var(--text-subtle);flex-shrink:0;">
                <polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"/>
            </svg>

            <select class="filter-select" id="filterType" title="Filtrer par type">
                <option value="">Tous les types</option>
                @foreach($types as $key => $label)
                    <option value="{{ $key }}" {{ request('type') === $key ? 'selected' : '' }}>{{ $label }}</option>
                @endforeach
            </select>

            <select class="filter-select" id="filterAxe" title="Filtrer par axe">
                <option value="">Tous les axes</option>
                @foreach($axes as $axe)
                    <option value="{{ $axe->id }}" {{ request('axe') === $axe->id ? 'selected' : '' }}>
                        {{ $axe->code }} — {{ Str::limit($axe->nom_fr, 35) }}
                    </option>
                @endforeach
            </select>

            @if(request()->hasAny(['q', 'type', 'axe']))
                <a href="{{ route('publications') }}" class="filter-reset">
                    <svg viewBox="0 0 24 24" width="12" height="12" stroke="currentColor" stroke-width="3" fill="none"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                    Réinitialiser
                </a>
            @endif

            <span class="results-count" id="resultsCount" style="display:none;"></span>
        </div>
    </div>
</div>

{{-- Results --}}
<div class="container" style="padding-top: 2.5rem; padding-bottom: 5rem;">
    <div id="publications-container">
        @include('public.partials.publications_list')
    </div>
</div>

@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const container   = document.getElementById('publications-container');
    const searchInput = document.getElementById('searchInput');
    const filterType  = document.getElementById('filterType');
    const filterAxe   = document.getElementById('filterAxe');
    const hiddenType  = document.getElementById('hiddenType');
    const hiddenAxe   = document.getElementById('hiddenAxe');
    const baseUrl     = '{{ route('publications') }}';
    let debounceTimer = null;

    // Hint chips
    document.querySelectorAll('.search-hint-chip').forEach(function(chip) {
        chip.addEventListener('click', function() {
            const fill    = chip.getAttribute('data-fill');
            const example = chip.getAttribute('data-example');
            if (fill)    { searchInput.value = fill; }
            if (example) { searchInput.value = example; }
            fetchPublications(true);
        });
    });

    function buildParams() {
        const params = new URLSearchParams();
        const q = searchInput.value.trim();
        if (q) params.set('q', q);
        if (filterType.value) params.set('type', filterType.value);
        if (filterAxe.value)  params.set('axe', filterAxe.value);
        return params;
    }

    function fetchPublications(immediate) {
        clearTimeout(debounceTimer);
        const delay = immediate ? 0 : 320;
        debounceTimer = setTimeout(function() {
            const params = buildParams();
            // Sync hidden fields
            hiddenType.value = filterType.value;
            hiddenAxe.value  = filterAxe.value;

            const url = params.toString() ? `${baseUrl}?${params}` : baseUrl;
            container.classList.add('is-loading');

            fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                .then(r => r.text())
                .then(html => {
                    container.innerHTML = html;
                    container.classList.remove('is-loading');
                    window.history.pushState({}, '', url);
                })
                .catch(() => container.classList.remove('is-loading'));
        }, delay);
    }

    // Live search
    searchInput.addEventListener('input', () => fetchPublications(false));
    filterType.addEventListener('change', () => fetchPublications(true));
    filterAxe.addEventListener('change',  () => fetchPublications(true));

    // Form submit
    document.getElementById('searchForm').addEventListener('submit', function(e) {
        e.preventDefault();
        fetchPublications(true);
    });

    // Pagination intercept
    container.addEventListener('click', function(e) {
        const link = e.target.closest('.pagination a');
        if (!link) return;
        e.preventDefault();
        container.classList.add('is-loading');
        fetch(link.href, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
            .then(r => r.text())
            .then(html => {
                container.innerHTML = html;
                container.classList.remove('is-loading');
                window.history.pushState({}, '', link.href);
                window.scrollTo({ top: document.querySelector('.filters-strip').offsetTop - 80, behavior: 'smooth' });
            })
            .catch(() => container.classList.remove('is-loading'));
    });
});
</script>
@endsection
