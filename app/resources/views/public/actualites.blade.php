@extends('layouts.app')

@section('title', 'Actualités & Événements — UMMISCO')

@section('content')
<div class="container" style="padding-top: 2rem;">
    {{-- Page Header --}}
    <div style="background: linear-gradient(135deg, rgba(30, 58, 138, 0.03) 0%, rgba(37, 99, 235, 0.05) 100%); border-bottom: 2px solid var(--color-border); padding: 3rem 2rem; border-radius: var(--radius); margin-bottom: 3rem; text-align: center;">
        <h1 style="font-size: 2.25rem; font-weight: 800; margin-bottom: 1rem; color: var(--color-primary);">
            Actualités & Événements
        </h1>
        <p style="font-size: 1.1rem; color: var(--color-text-muted); max-width: 800px; margin: 0 auto;">
            Retrouvez les dernières annonces, séminaires à venir et actualités marquantes du laboratoire UMMISCO
        </p>
    </div>

    @if($actualites->count() > 0)
        <div class="grid-3" style="margin-bottom: 3rem;">
            @foreach($actualites as $actu)
            <div class="card" style="display: flex; flex-direction: column; justify-content: space-between; height: 100%;">
                <div>
                    {{-- Badges --}}
                    <div style="display: flex; gap: 0.5rem; align-items: center; margin-bottom: 1rem;">
                        @if($actu->type === 'event')
                            <span class="badge badge-warning">Événement</span>
                        @else
                            <span class="badge badge-success">Actualité</span>
                        @endif

                        @if($actu->axe)
                            <span class="badge" style="background: {{ $actu->axe->couleur_hex ?? '#2563eb' }}10; color: {{ $actu->axe->couleur_hex ?? '#2563eb' }}; border: 1px solid {{ $actu->axe->couleur_hex ?? '#2563eb' }}30;">
                                {{ $actu->axe->code }}
                            </span>
                        @endif
                    </div>

                    <h3 style="font-size: 1.1rem; font-weight: 700; margin-bottom: 0.75rem; line-height: 1.4; color: var(--color-text);">
                        {{ $actu->titre_fr }}
                    </h3>

                    <p style="font-size: 0.875rem; color: var(--color-text-muted); line-height: 1.6; margin-bottom: 1rem;">
                        {{ Str::limit($actu->resume_fr ?? $actu->contenu_fr ?? '', 140) }}
                    </p>
                </div>

                {{-- Card Footer --}}
                <div style="border-top: 1px solid var(--color-border); padding-top: 0.75rem; margin-top: 1rem; display: flex; justify-content: space-between; align-items: center; font-size: 0.775rem; color: var(--color-text-muted);">
                    <div>
                        👤 {{ $actu->auteur->prenom ?? '' }} {{ $actu->auteur->nom ?? '' }}
                    </div>
                    @if($actu->date_publication)
                        <div style="font-weight: 600;">
                            📅 {{ $actu->date_publication->format('d/m/Y') }}
                        </div>
                    @endif
                </div>
            </div>
            @endforeach
        </div>

        {{-- Pagination --}}
        <div style="margin-top: 3rem; display: flex; justify-content: center;">
            {{ $actualites->links() }}
        </div>
    @else
        <div class="card text-center" style="padding: 4rem 1rem; margin-bottom: 4rem;">
            <div style="font-size: 3rem; margin-bottom: 1rem;">📰</div>
            <h2 style="font-size: 1.25rem; font-weight: 700; color: var(--color-primary); margin-bottom: 0.5rem;">Aucune actualité disponible</h2>
            <p class="text-muted">Revenez plus tard pour suivre les annonces et événements du laboratoire.</p>
        </div>
    @endif
</div>
@endsection
