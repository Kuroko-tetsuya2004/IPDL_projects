@extends('layouts.app')

@section('title', 'Publications — UMMISCO')
@section('description', 'Catalogue des publications scientifiques du laboratoire UMMISCO')

@section('content')
<div class="container" style="padding-top: 2rem;">
    {{-- Page Header --}}
    <div style="background: linear-gradient(135deg, rgba(30, 58, 138, 0.03) 0%, rgba(37, 99, 235, 0.05) 100%); border-bottom: 2px solid var(--color-border); padding: 3rem 2rem; border-radius: var(--radius); margin-bottom: 3rem; text-align: center;">
        <h1 style="font-size: 2.25rem; font-weight: 800; margin-bottom: 1rem; color: var(--color-primary);">
            Publications du Laboratoire
        </h1>
        <p style="font-size: 1.1rem; color: var(--color-text-muted); max-width: 800px; margin: 0 auto;">
            Explorez les articles de recherche, datasets, thèses et rapports produits par les chercheurs de l'UMMISCO
        </p>
    </div>

    {{-- Filtres --}}
    <div class="card mb-3" style="background: white; padding: 1.5rem;">
        <form method="GET" action="{{ route('publications') }}" style="display: flex; gap: 1rem; flex-wrap: wrap; align-items: flex-end;">
            <div class="form-group" style="flex: 2; min-width: 250px; margin-bottom: 0;">
                <label for="q">Mots-clés ou Titre</label>
                <input type="text" name="q" id="q" class="form-control" placeholder="Rechercher..." value="{{ request('q') }}">
            </div>
            
            <div class="form-group" style="flex: 1; min-width: 180px; margin-bottom: 0;">
                <label for="type">Type de document</label>
                <select name="type" id="type" class="form-control">
                    <option value="">Tous les types</option>
                    @foreach($types as $key => $label)
                        <option value="{{ $key }}" {{ request('type') === $key ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            
            <div class="form-group" style="flex: 1; min-width: 180px; margin-bottom: 0;">
                <label for="axe">Axe thématique</label>
                <select name="axe" id="axe" class="form-control">
                    <option value="">Tous les axes</option>
                    @foreach($axes as $axe)
                        <option value="{{ $axe->id }}" {{ request('axe') === $axe->id ? 'selected' : '' }}>{{ $axe->code }} - {{ $axe->nom_fr }}</option>
                    @endforeach
                </select>
            </div>

            <div style="display: flex; gap: 0.5rem; margin-bottom: 0;">
                <button type="submit" class="btn btn-primary">Filtrer</button>
                @if(request()->hasAny(['q', 'type', 'axe']))
                    <a href="{{ route('publications') }}" class="btn btn-outline">Réinitialiser</a>
                @endif
            </div>
        </form>
    </div>

    {{-- Résultats --}}
    <div id="publications-container">
        @include('public.partials.publications_list')
    </div>
</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.querySelector('form[action="{{ route('publications') }}"]');
    const inputs = form.querySelectorAll('input, select');
    const container = document.getElementById('publications-container');
    let timeout = null;

    // Prevent default form submission since we use AJAX
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        fetchPublications();
    });

    function fetchPublications() {
        const url = new URL(form.action);
        const formData = new FormData(form);
        const params = new URLSearchParams();
        
        for (const [key, value] of formData.entries()) {
            if (value) params.append(key, value);
        }

        const fetchUrl = `${url.pathname}?${params.toString()}`;

        // Indicate loading (optional but good for UX)
        container.style.opacity = '0.5';

        fetch(fetchUrl, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.text())
        .then(html => {
            container.innerHTML = html;
            container.style.opacity = '1';
            window.history.pushState({}, '', fetchUrl);
        })
        .catch(error => {
            console.error('Error fetching publications:', error);
            container.style.opacity = '1';
        });
    }

    inputs.forEach(input => {
        input.addEventListener('input', () => {
            clearTimeout(timeout);
            timeout = setTimeout(fetchPublications, 300);
        });
    });

    // Intercept pagination clicks
    container.addEventListener('click', function(e) {
        const link = e.target.closest('.pagination a');
        if (link) {
            e.preventDefault();
            container.style.opacity = '0.5';
            fetch(link.href, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.text())
            .then(html => {
                container.innerHTML = html;
                container.style.opacity = '1';
                window.history.pushState({}, '', link.href);
                window.scrollTo({ top: 0, behavior: 'smooth' });
            });
        }
    });
});
</script>
@endsection
