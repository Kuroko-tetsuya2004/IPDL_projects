@extends('layouts.app')

@section('title', 'Membres de l\'Unité — UMMISCO')

@section('content')
<div class="container" style="padding-top: 2rem;">
    {{-- Page Header --}}
    <div style="background: linear-gradient(135deg, rgba(30, 58, 138, 0.03) 0%, rgba(37, 99, 235, 0.05) 100%); border-bottom: 2px solid var(--border); padding: 3rem 2rem; border-radius: var(--radius); margin-bottom: 3rem; text-align: center;">
        <h1 style="font-size: 2.25rem; font-weight: 800; margin-bottom: 1rem; color: var(--primary);">
            Membres de l'Unité
        </h1>
        <p style="font-size: 1.1rem; color: var(--text-muted); max-width: 800px; margin: 0 auto;">
            Annuaire public des chercheurs, enseignants-chercheurs et doctorants d'UMMISCO
        </p>
    </div>

    {{-- Section : Chercheurs --}}
    <div style="margin-bottom: 4rem;">
        <h2 style="font-size: 1.6rem; font-weight: 700; margin-bottom: 2rem; color: var(--primary); display: flex; align-items: center; gap: 0.5rem;">
            <span>🔬</span> Chercheurs & Enseignants-Chercheurs ({{\count($chercheurs)}})
        </h2>
        
        @if($chercheurs->count() > 0)
            <div class="grid-3">
                @foreach($chercheurs as $c)
                <div class="card" style="display: flex; flex-direction: column; justify-content: space-between; height: 100%;">
                    <div>
                        <div style="display: flex; align-items: center; gap: 1rem; margin-bottom: 1.25rem;">
                            @if($c->photo_url)
                                <img src="{{ $c->photo_url }}" alt="Photo de {{ $c->prenom }} {{ $c->nom }}" style="width: 60px; height: 60px; border-radius: 50%; object-fit: cover; border: 2px solid var(--primary-light);">
                            @else
                                <div style="width: 60px; height: 60px; border-radius: 50%; background: rgba(37, 99, 235, 0.08); color: var(--primary-light); display: flex; align-items: center; justify-content: center; font-size: 1.25rem; font-weight: 700; border: 2px solid rgba(37, 99, 235, 0.15);">
                                    {{ substr($c->prenom, 0, 1) }}{{ substr($c->nom, 0, 1) }}
                                </div>
                            @endif
                            <div>
                                <h3 style="font-size: 1.05rem; font-weight: 700; color: var(--text);">
                                    {{ $c->titre_academique ?? '' }} {{ $c->prenom }} {{ $c->nom }}
                                </h3>
                                @if($c->grade)
                                    <div class="text-muted" style="font-size: 0.8rem; font-weight: 600;">{{ $c->grade }}</div>
                                @endif
                            </div>
                        </div>

                        @if($c->biographie_fr)
                            <p style="font-size: 0.85rem; color: var(--text-muted); line-height: 1.6; margin-bottom: 1rem;">
                                {{ Str::limit($c->biographie_fr, 150) }}
                            </p>
                        @else
                            <p style="font-size: 0.85rem; color: var(--text-muted); font-style: italic; margin-bottom: 1rem;">
                                Chercheur permanent spécialisé dans la modélisation mathématique et informatique des systèmes complexes.
                            </p>
                        @endif
                    </div>

                    {{-- Social links & ORCID --}}
                    <div style="border-top: 1px solid var(--border); padding-top: 0.75rem; margin-top: 0.5rem; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 0.5rem;">
                        <div>
                            @if($c->orcid_id)
                                <a href="https://orcid.org/{{ $c->orcid_id }}" target="_blank" title="Profil ORCID" style="font-size: 0.8rem; font-weight: 600; color: #a6d042; display: flex; align-items: center; gap: 0.25rem; text-decoration: none;">
                                    <span style="font-weight: 800; border: 1px solid #a6d042; border-radius: 4px; padding: 1px 3px; font-size: 0.65rem;">iD</span> {{ $c->orcid_id }}
                                </a>
                            @endif
                        </div>
                        <div style="display: flex; gap: 0.5rem;">
                            @if($c->linkedin_url)
                                <a href="{{ $c->linkedin_url }}" target="_blank" style="color: #0077b5; font-size: 0.85rem; font-weight: 700; text-decoration: none;">LN</a>
                            @endif
                            @if($c->researchgate_url)
                                <a href="{{ $c->researchgate_url }}" target="_blank" style="color: #00ccb1; font-size: 0.85rem; font-weight: 700; text-decoration: none;">RG</a>
                            @endif
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        @else
            <div class="card text-center" style="padding: 3rem 1rem;">
                <p class="text-muted">Aucun chercheur enregistré dans l'annuaire public.</p>
            </div>
        @endif
    </div>

    {{-- Section : Doctorants --}}
    <div style="margin-bottom: 4rem;">
        <h2 style="font-size: 1.6rem; font-weight: 700; margin-bottom: 2rem; color: var(--primary); display: flex; align-items: center; gap: 0.5rem;">
            <span>🎓</span> Doctorants ({{\count($doctorants)}})
        </h2>

        @if($doctorants->count() > 0)
            <div class="grid-3">
                @foreach($doctorants as $d)
                <div class="card" style="display: flex; flex-direction: column; justify-content: space-between; height: 100%;">
                    <div>
                        <div style="display: flex; align-items: center; gap: 1rem; margin-bottom: 1.25rem;">
                            @if($d->photo_url)
                                <img src="{{ $d->photo_url }}" alt="Photo de {{ $d->prenom }} {{ $d->nom }}" style="width: 50px; height: 50px; border-radius: 50%; object-fit: cover; border: 2px solid var(--primary-light);">
                            @else
                                <div style="width: 50px; height: 50px; border-radius: 50%; background: rgba(37, 99, 235, 0.05); color: var(--primary-light); display: flex; align-items: center; justify-content: center; font-size: 1.1rem; font-weight: 700; border: 2px solid rgba(37, 99, 235, 0.1);">
                                    {{ substr($d->prenom, 0, 1) }}{{ substr($d->nom, 0, 1) }}
                                </div>
                            @endif
                            <div>
                                <h3 style="font-size: 1rem; font-weight: 700; color: var(--text);">
                                    {{ $d->prenom }} {{ $d->nom }}
                                </h3>
                                <div class="text-muted" style="font-size: 0.75rem; font-weight: 600;">Doctorant UMMISCO</div>
                            </div>
                        </div>

                        @if($d->biographie_fr)
                            <p style="font-size: 0.85rem; color: var(--text-muted); line-height: 1.6; margin-bottom: 1rem;">
                                {{ Str::limit($d->biographie_fr, 150) }}
                            </p>
                        @else
                            <p style="font-size: 0.85rem; color: var(--text-muted); font-style: italic; margin-bottom: 1rem;">
                                Travaux de recherche doctoraux appliqués à la modélisation informatique ou mathématique de systèmes complexes.
                            </p>
                        @endif
                    </div>

                    {{-- Social links & ORCID --}}
                    <div style="border-top: 1px solid var(--border); padding-top: 0.75rem; margin-top: 0.5rem; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 0.5rem;">
                        <div>
                            @if($d->orcid_id)
                                <a href="https://orcid.org/{{ $d->orcid_id }}" target="_blank" title="Profil ORCID" style="font-size: 0.775rem; font-weight: 600; color: #a6d042; display: flex; align-items: center; gap: 0.25rem; text-decoration: none;">
                                    <span style="font-weight: 800; border: 1px solid #a6d042; border-radius: 4px; padding: 0px 2px; font-size: 0.6rem;">iD</span> {{ $d->orcid_id }}
                                </a>
                            @endif
                        </div>
                        <div style="display: flex; gap: 0.5rem;">
                            @if($d->linkedin_url)
                                <a href="{{ $d->linkedin_url }}" target="_blank" style="color: #0077b5; font-size: 0.8rem; font-weight: 700; text-decoration: none;">LN</a>
                            @endif
                            @if($d->researchgate_url)
                                <a href="{{ $d->researchgate_url }}" target="_blank" style="color: #00ccb1; font-size: 0.8rem; font-weight: 700; text-decoration: none;">RG</a>
                            @endif
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        @else
            <div class="card text-center" style="padding: 3rem 1rem;">
                <p class="text-muted">Aucun doctorant enregistré dans l'annuaire public.</p>
            </div>
        @endif
    </div>
</div>
@endsection
