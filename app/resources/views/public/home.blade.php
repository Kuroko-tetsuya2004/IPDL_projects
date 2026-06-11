@extends('layouts.app')

@section('title', 'Portail UMMISCO — Accueil')
@section('description', 'Portail web institutionnel du laboratoire UMMISCO — Recherche en modélisation des systèmes complexes')

@section('content')
{{-- Hero Section --}}
<section style="background: linear-gradient(135deg, var(--color-primary) 0%, var(--color-primary-light) 100%); color: white; padding: 5rem 0; border-radius: var(--radius); margin: 0 1rem 3rem; box-shadow: 0 10px 30px rgba(30, 58, 138, 0.15);">
    <div class="container" style="text-align: center;">
        <h1 style="font-size: 3rem; font-weight: 800; margin-bottom: 1rem; color: white; letter-spacing: -0.02em;">UMMISCO</h1>
        <p style="font-size: 1.25rem; opacity: 0.95; max-width: 750px; margin: 0 auto 2rem; font-weight: 500; line-height: 1.6;">
            Unité Mixte Internationale de Modélisation Mathématique et Informatique des Systèmes Complexes
        </p>
        <p style="font-size: 0.95rem; opacity: 0.8; font-weight: 600;">CNRS / IRD / UCAD — Dakar, Sénégal</p>
        <div style="margin-top: 2.5rem; display: flex; gap: 1rem; justify-content: center; flex-wrap: wrap;">
            <a href="{{ route('publications') }}" class="btn" style="background: white; color: var(--color-primary-light); font-weight: 700; box-shadow: 0 4px 12px rgba(0,0,0,0.1);">
                📚 Découvrir nos publications
            </a>
            <a href="{{ route('axes') }}" class="btn" style="background: rgba(255,255,255,0.15); color: white; border: 1.5px solid rgba(255,255,255,0.3); font-weight: 600;">
                🔬 Axes de recherche
            </a>
        </div>
    </div>
</section>

{{-- Statistiques --}}
@if($stats)
<section style="padding: 1.5rem 0; margin-bottom: 3rem;">
    <div class="container">
        <div class="card" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 2rem; text-align: center; background: white; border-radius: var(--radius);">
            <div>
                <div style="font-size: 2.25rem; font-weight: 800; color: var(--color-primary-light);">{{ $stats->total_publications ?? 0 }}</div>
                <div class="text-muted" style="font-size: 0.85rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.05em; margin-top: 0.25rem;">Publications</div>
            </div>
            <div>
                <div style="font-size: 2.25rem; font-weight: 800; color: var(--color-success);">{{ $stats->total_datasets ?? 0 }}</div>
                <div class="text-muted" style="font-size: 0.85rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.05em; margin-top: 0.25rem;">Datasets</div>
            </div>
            <div>
                <div style="font-size: 2.25rem; font-weight: 800; color: var(--color-accent);">{{ $stats->total_chercheurs ?? 0 }}</div>
                <div class="text-muted" style="font-size: 0.85rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.05em; margin-top: 0.25rem;">Chercheurs</div>
            </div>
            <div>
                <div style="font-size: 2.25rem; font-weight: 800; color: var(--color-warning);">{{ $stats->total_doctorants ?? 0 }}</div>
                <div class="text-muted" style="font-size: 0.85rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.05em; margin-top: 0.25rem;">Doctorants</div>
            </div>
            <div>
                <div style="font-size: 2.25rem; font-weight: 800; color: #6366f1;">{{ $stats->total_axes ?? 0 }}</div>
                <div class="text-muted" style="font-size: 0.85rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.05em; margin-top: 0.25rem;">Axes thématiques</div>
            </div>
        </div>
    </div>
</section>
@endif

{{-- Axes de recherche --}}
<section style="padding: 2rem 0 3rem;">
    <div class="container">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
            <h2 style="font-size: 1.75rem; font-weight: 800; color: var(--color-primary);">🔬 Axes de recherche</h2>
            <a href="{{ route('axes') }}" class="btn btn-outline" style="font-size: 0.85rem; padding: 0.5rem 1rem;">Voir tous les axes →</a>
        </div>
        <div class="grid-3">
            @foreach($axes as $axe)
            <div class="card" style="border-top: 4px solid {{ $axe->couleur_hex ?? '#1E3A8A' }}; display: flex; flex-direction: column; justify-content: space-between; height: 100%;">
                <div>
                    <h3 style="font-size: 1.15rem; font-weight: 700; color: {{ $axe->couleur_hex ?? 'var(--color-primary)' }}; margin-bottom: 0.25rem;">
                        {{ $axe->nom_fr }}
                    </h3>
                    @if($axe->nom_en)
                        <p class="text-muted" style="font-size: 0.8rem; font-style: italic; margin-bottom: 0.75rem;">{{ $axe->nom_en }}</p>
                    @endif
                    <p style="font-size: 0.875rem; color: var(--color-text-muted); line-height: 1.6;">
                        {{ Str::limit($axe->description_fr, 140) }}
                    </p>
                </div>
                <div style="margin-top: 1.25rem; display: flex; justify-content: space-between; align-items: center;">
                    <span class="badge" style="background: {{ ($axe->couleur_hex ?? '#2563eb') }}10; color: {{ $axe->couleur_hex ?? '#2563eb' }}; font-weight: 700;">{{ $axe->code }}</span>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</section>

{{-- Publications récentes --}}
@if($recentPublications->count() > 0)
<section style="padding: 3rem 0; background: rgba(30, 58, 138, 0.02); border-top: 1px solid var(--color-border); border-bottom: 1px solid var(--color-border);">
    <div class="container">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
            <h2 style="font-size: 1.75rem; font-weight: 800; color: var(--color-primary);">📚 Publications récentes</h2>
            <a href="{{ route('publications') }}" class="btn btn-outline" style="font-size: 0.85rem; padding: 0.5rem 1rem;">Voir toutes les publications →</a>
        </div>
        <div class="grid-3">
            @foreach($recentPublications as $pub)
            <div class="card" style="display: flex; flex-direction: column; justify-content: space-between; height: 100%; background: white;">
                <div>
                    <div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.75rem;">
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
                    <h3 style="font-size: 1.05rem; font-weight: 700; color: var(--color-text); margin-bottom: 0.5rem; line-height: 1.4;">{{ Str::limit($pub->titre_fr, 80) }}</h3>
                    <p class="text-muted" style="font-size: 0.85rem; line-height: 1.6; margin-bottom: 1rem;">
                        {{ Str::limit($pub->resume_fr, 110) }}
                    </p>
                </div>
                <div style="border-top: 1px solid var(--color-border); padding-top: 0.75rem; display: flex; justify-content: space-between; align-items: center; font-size: 0.775rem; color: var(--color-text-muted);">
                    <div style="font-weight: 600;">
                        👤 {{ $pub->auteur->prenom ?? '' }} {{ $pub->auteur->nom ?? '' }}
                    </div>
                    @if($pub->date_publication)
                        <div>{{ $pub->date_publication->format('d/m/Y') }}</div>
                    @endif
                </div>
            </div>
            @endforeach
        </div>
    </div>
</section>
@endif
@endsection
