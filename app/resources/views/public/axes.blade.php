@extends('layouts.app')

@section('title', 'Axes de recherche — UMMISCO')

@section('content')
<div class="container" style="padding-top: 2rem;">
    {{-- Page Header --}}
    <div style="background: linear-gradient(135deg, rgba(30, 58, 138, 0.03) 0%, rgba(37, 99, 235, 0.05) 100%); border-bottom: 2px solid var(--color-border); padding: 3rem 2rem; border-radius: var(--radius); margin-bottom: 3rem; text-align: center;">
        <h1 style="font-size: 2.25rem; font-weight: 800; margin-bottom: 1rem; color: var(--color-primary);">
            Axes de Recherche
        </h1>
        <p style="font-size: 1.1rem; color: var(--color-text-muted); max-width: 800px; margin: 0 auto;">
            Découvrez les axes scientifiques thématiques et interdisciplinaires du laboratoire UMMISCO
        </p>
    </div>

    <div class="grid-3" style="margin-bottom: 4rem;">
        @foreach($axes as $axe)
        <div class="card" style="border-top: 4px solid {{ $axe->couleur_hex ?? '#1E3A8A' }}; display: flex; flex-direction: column; justify-content: space-between; height: 100%;">
            <div>
                <h2 style="font-size: 1.25rem; font-weight: 700; color: {{ $axe->couleur_hex ?? 'var(--color-primary)' }}; margin-bottom: 0.25rem; font-family: 'Outfit', sans-serif;">
                    {{ $axe->nom_fr }}
                </h2>
                @if($axe->nom_en)
                    <p class="text-muted" style="font-size: 0.8rem; font-style: italic; margin-bottom: 1rem;">{{ $axe->nom_en }}</p>
                @endif

                <p style="font-size: 0.875rem; color: var(--color-text-muted); line-height: 1.6; margin-bottom: 1.5rem;">
                    {{ $axe->description_fr ?? 'Pas de description disponible pour cet axe.' }}
                </p>
            </div>

            <div>
                {{-- Responsable --}}
                @if($axe->responsable)
                    <div style="display: flex; align-items: center; gap: 0.75rem; margin-bottom: 1.25rem; padding: 0.75rem; background: #f8fafc; border-radius: var(--radius-sm); border: 1px solid var(--color-border);">
                        <div style="width: 36px; height: 36px; border-radius: 50%; background: {{ $axe->couleur_hex ?? '#1E3A8A' }}; color: white; display: flex; align-items: center; justify-content: center; font-size: 0.8rem; font-weight: 800; border: 2px solid white; box-shadow: var(--shadow-sm);">
                            {{ substr($axe->responsable->prenom, 0, 1) }}{{ substr($axe->responsable->nom, 0, 1) }}
                        </div>
                        <div>
                            <div style="font-size: 0.825rem; font-weight: 700; color: var(--color-text);">
                                {{ $axe->responsable->titre_academique }} {{ $axe->responsable->prenom }} {{ $axe->responsable->nom }}
                            </div>
                            <div class="text-muted" style="font-size: 0.725rem; font-weight: 600;">Responsable d'axe</div>
                        </div>
                    </div>
                @endif

                {{-- Statistiques --}}
                <div style="display: flex; justify-content: space-between; align-items: center; font-size: 0.8rem; font-weight: 600; color: var(--color-text-muted); border-top: 1px solid var(--color-border); padding-top: 0.75rem;">
                    <span>📄 {{ $axe->publications_count ?? 0 }} publications</span>
                    <span>👥 {{ $axe->membres_count ?? 0 }} membres</span>
                </div>
            </div>
        </div>
        @endforeach
    </div>
</div>
@endsection
