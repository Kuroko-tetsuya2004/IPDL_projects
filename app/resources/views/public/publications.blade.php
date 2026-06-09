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
    @if($publications->count() > 0)
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
            <p class="text-muted" style="font-size: 0.9rem; font-weight: 600;">
                {{ $publications->total() }} publication(s) trouvée(s)
            </p>
        </div>

        <div class="grid-3">
            @foreach($publications as $pub)
            <div class="card" style="display: flex; flex-direction: column; justify-content: space-between; height: 100%; position: relative;">
                <div>
                    {{-- Badges --}}
                    <div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 1rem;">
                        @php
                        $singularTypes = [
                            'article' => 'Article',
                            'document' => 'Recherche en cours',
                            'event' => 'Événement',
                            'dataset' => 'Dataset',
                            'news' => 'Actualité',
                            'thesis' => 'Thèse',
                            'report' => 'Rapport',
                            'presentation' => 'Présentation'
                        ];
                        @endphp
                        <span class="badge badge-primary">{{ $singularTypes[$pub->type] ?? ucfirst($pub->type) }}</span>
                        @if($pub->axe)
                            <span class="badge" style="background: {{ $pub->axe->couleur_hex ?? '#2563eb' }}10; color: {{ $pub->axe->couleur_hex ?? '#2563eb' }}; border: 1px solid {{ $pub->axe->couleur_hex ?? '#2563eb' }}20;">
                                {{ $pub->axe->code }}
                            </span>
                        @endif
                    </div>

                    {{-- Title --}}
                    <h3 style="font-size: 1.1rem; font-weight: 700; margin-bottom: 0.75rem; line-height: 1.4;">
                        <a href="{{ route('publications.show', $pub->id) }}" style="text-decoration: none; color: var(--color-primary); transition: var(--transition);">
                            {{ Str::limit($pub->titre_fr, 90) }}
                        </a>
                    </h3>

                    @if($pub->resume_fr)
                        <p class="text-muted" style="font-size: 0.85rem; line-height: 1.6; margin-bottom: 1rem;">
                            {{ Str::limit($pub->resume_fr, 150) }}
                        </p>
                    @endif
                </div>

                <div>
                    {{-- Tags --}}
                    @if($pub->mots_cles)
                        <div style="display: flex; gap: 0.25rem; flex-wrap: wrap; margin-bottom: 1rem;">
                            @foreach(array_slice($pub->mots_cles, 0, 3) as $tag)
                                <span style="font-size: 0.7rem; font-weight: 600; padding: 0.15rem 0.5rem; background: #f1f5f9; border-radius: 9999px; color: var(--color-text-muted);">#{{ $tag }}</span>
                            @endforeach
                        </div>
                    @endif

                    {{-- Footer --}}
                    <div style="border-top: 1px solid var(--color-border); padding-top: 0.75rem; display: flex; justify-content: space-between; align-items: center; font-size: 0.775rem; color: var(--color-text-muted);">
                        <span style="font-weight: 600;">👤 {{ $pub->auteur->prenom ?? '' }} {{ $pub->auteur->nom ?? '' }}</span>
                        <span>
                            @if($pub->date_publication)
                                {{ $pub->date_publication->format('d/m/Y') }}
                            @endif
                        </span>
                    </div>

                    <a href="{{ route('publications.show', $pub->id) }}" class="btn btn-outline" style="width: 100%; font-size: 0.775rem; justify-content: center; padding: 0.4rem; border-radius: 8px; margin-top: 1rem;">
                        Consulter la fiche →
                    </a>
                </div>
            </div>
            @endforeach
        </div>

        {{-- Pagination --}}
        <div style="margin-top: 3rem; display: flex; justify-content: center;">
            {{ $publications->links() }}
        </div>
    @else
        <div class="card text-center" style="padding: 4rem 1rem; margin-bottom: 4rem;">
            <div style="font-size: 3rem; margin-bottom: 1rem;">📭</div>
            <h2 style="font-size: 1.25rem; font-weight: 700; color: var(--color-primary); margin-bottom: 0.5rem;">Aucune publication trouvée</h2>
            <p class="text-muted">Ajustez vos filtres ou effectuez une autre recherche.</p>
        </div>
    @endif
</div>
@endsection
