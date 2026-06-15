@extends('layouts.app')

@section('title', $publication->titre_fr . ' — UMMISCO')
@section('description', Str::limit($publication->resume_fr, 200))

@section('content')
<div class="container" style="padding-top: 2rem; max-width: 900px; padding-bottom: 4rem;">

    {{-- Breadcrumbs --}}
    <nav style="font-size: 0.85rem; color: var(--color-text-muted); margin-bottom: 2rem; font-weight: 500;">
        <a href="{{ route('home') }}" style="color: var(--color-primary-light); text-decoration: none;">Accueil</a>
        <span style="margin: 0 0.5rem; opacity: 0.5;">&rsaquo;</span>
        <a href="{{ route('publications') }}" style="color: var(--color-primary-light); text-decoration: none;">Publications</a>
        <span style="margin: 0 0.5rem; opacity: 0.5;">&rsaquo;</span>
        <span style="color: var(--color-text);">Fiche publication</span>
    </nav>

    {{-- Fiche Principale --}}
    <div class="card" style="background: white; padding: 2rem; margin-bottom: 3rem;">
        @php
        $typeLabels = [
            'article' => 'Article scientifique',
            'document' => 'Recherche en cours',
            'event' => 'Événement',
            'dataset' => 'Dataset',
            'news' => 'Actualité',
            'thesis' => 'Thèse / Mémoire',
            'report' => 'Rapport de recherche',
            'presentation' => 'Présentation / Poster'
        ];
        @endphp
        {{-- Tags & Type --}}
        <div style="display: flex; gap: 0.5rem; margin-bottom: 1.5rem; align-items: center; flex-wrap: wrap;">
            <span class="badge badge-primary">{{ $typeLabels[$publication->type] ?? ucfirst($publication->type) }}</span>
            @if($publication->axe)
                <span class="badge" style="background: {{ $publication->axe->couleur_hex ?? '#2563eb' }}10; color: {{ $publication->axe->couleur_hex ?? '#2563eb' }}; border: 1px solid {{ $publication->axe->couleur_hex ?? '#2563eb' }}20; font-weight: 700;">
                    {{ $publication->axe->code }} — {{ $publication->axe->nom_fr }}
                </span>
            @endif
        </div>

        {{-- Titres --}}
        <h1 style="font-size: 1.85rem; font-weight: 800; color: var(--color-primary); line-height: 1.3; margin-bottom: 0.75rem; font-family: 'Outfit', sans-serif;">
            {{ $publication->titre_fr }}
        </h1>
        @if($publication->titre_en && $publication->titre_en !== $publication->titre_fr)
            <p style="font-size: 1.1rem; font-style: italic; color: var(--color-text-muted); margin-bottom: 1.5rem; line-height: 1.4;">
                {{ $publication->titre_en }}
            </p>
        @endif

        {{-- Infos Auteurs & Date --}}
        <div style="display: flex; align-items: center; gap: 1rem; padding: 1rem 0; border-top: 1px solid var(--color-border); border-bottom: 1px solid var(--color-border); margin-bottom: 2rem; flex-wrap: wrap;">
            @if($publication->auteur || !empty($publication->auteurs_externes))
            <div>
                <div style="font-weight: 700; font-size: 0.95rem; color: var(--color-text);">
                    👤 
                    @if($publication->auteur)
                        {{ $publication->auteur->titre_academique ? $publication->auteur->titre_academique . ' ' : '' }}{{ $publication->auteur->prenom }} {{ $publication->auteur->nom }}
                    @endif
                    @if(!empty($publication->auteurs_externes))
                        @if($publication->auteur)
                            <span style="color: var(--color-text-muted); font-weight: normal; font-size: 0.85rem;">
                                et {{ count($publication->auteurs_externes) }} co-auteur(s) ({{ implode(', ', array_slice($publication->auteurs_externes, 0, 5)) }}{{ count($publication->auteurs_externes) > 5 ? '...' : '' }})
                            </span>
                        @else
                            {{ implode(', ', array_slice($publication->auteurs_externes, 0, 10)) }}{{ count($publication->auteurs_externes) > 10 ? '...' : '' }}
                        @endif
                    @endif
                </div>
                @if($publication->auteur && $publication->auteur->orcid_id)
                    <a href="https://orcid.org/{{ $publication->auteur->orcid_id }}" target="_blank" rel="noopener"
                        style="font-size: 0.775rem; color: #a6d042; font-weight: 600; text-decoration: none; display: inline-flex; align-items: center; gap: 0.25rem; margin-top: 0.25rem;">
                        <span style="font-weight: 800; border: 1px solid #a6d042; border-radius: 4px; padding: 0px 2px; font-size: 0.6rem;">iD</span> https://orcid.org/{{ $publication->auteur->orcid_id }}
                    </a>
                @endif
                @if($publication->auteur->email)
                    <div style="font-size: 0.8rem; color: var(--color-text-muted); margin-top: 0.35rem; font-weight: 500;">
                        ✉️ <a href="mailto:{{ $publication->auteur->email }}" style="color: var(--color-primary-light); text-decoration: none; font-weight: 600;">{{ $publication->auteur->email }}</a>
                    </div>
                @endif
            </div>
            @endif
            @if($publication->date_publication)
            <div class="text-muted" style="font-size: 0.85rem; margin-left: auto; font-weight: 600;">
                📅 {{ $publication->date_publication->format('d F Y') }}
            </div>
            @endif
        </div>

        {{-- Résumé / Abstract --}}
        @if($publication->resume_fr)
        <div style="margin-bottom: 2rem;">
            <h2 style="font-size: 1.15rem; font-weight: 700; color: var(--color-primary); margin-bottom: 0.75rem; font-family: 'Outfit', sans-serif;">Résumé</h2>
            <p style="line-height: 1.8; color: var(--color-text-muted); font-size: 0.95rem; font-weight: 500; text-align: justify;">
                {{ $publication->resume_fr }}
            </p>
        </div>
        @endif

        @if($publication->resume_en)
        <div style="margin-bottom: 2rem; padding: 1.25rem; background: #f8fafc; border-radius: var(--radius-sm); border-left: 4px solid var(--color-accent);">
            <p style="font-size: 0.8rem; font-weight: 700; color: var(--color-accent); margin-bottom: 0.5rem; text-transform: uppercase; letter-spacing: 0.05em;">Abstract</p>
            <p style="line-height: 1.75; color: var(--color-text-muted); font-size: 0.9rem; font-style: italic; text-align: justify;">
                {{ $publication->resume_en }}
            </p>
        </div>
        @endif

        {{-- Mots-clés --}}
        @if($publication->mots_cles)
        <div style="display: flex; flex-wrap: wrap; gap: 0.4rem; margin-bottom: 2rem; align-items: center;">
            <span style="font-size: 0.85rem; font-weight: 700; color: var(--color-text-muted); margin-right: 0.5rem;">🏷️ Mots-clés :</span>
            @foreach(is_array($publication->mots_cles) ? $publication->mots_cles : explode(',', $publication->mots_cles) as $tag)
                <span class="badge" style="background: #f1f5f9; color: var(--color-text-muted); border: 1px solid var(--color-border); font-weight: 600;">
                    {{ trim($tag) }}
                </span>
            @endforeach
        </div>
        @endif

        {{-- Liens externes --}}
        <div style="display: flex; gap: 0.75rem; flex-wrap: wrap; border-top: 1px solid var(--color-border); padding-top: 1.5rem;">
            @if($publication->doi)
            <a href="https://doi.org/{{ $publication->doi }}" target="_blank" rel="noopener" class="btn btn-primary" style="font-size: 0.8rem; padding: 0.5rem 1rem;">
                🔗 DOI : {{ $publication->doi }}
            </a>
            @endif
            @if($publication->url_externe)
            <a href="{{ $publication->url_externe }}" target="_blank" rel="noopener" class="btn btn-outline" style="font-size: 0.8rem; padding: 0.5rem 1rem;">
                🌐 Lien externe
            </a>
            @endif
        </div>

        {{-- Document Joint --}}
        @if($publication->document)
        <div style="margin-top: 2rem; padding: 1.5rem; background: #f8fafc; border-radius: var(--radius); border: 1px solid var(--color-border); display: flex; align-items: center; justify-content: space-between; gap: 1rem; flex-wrap: wrap;">
            <div>
                <h3 style="font-size: 0.95rem; font-weight: 700; color: var(--color-primary); margin: 0;">Fichier joint</h3>
                <p style="font-size: 0.8rem; color: var(--color-text-muted); margin: 0.25rem 0 0 0; font-weight: 500;">
                    {{ $publication->document->fichier_nom }}
                    @if($publication->document->fichier_taille)
                        · ({{ round($publication->document->fichier_taille / 1024 / 1024, 2) }} Mo)
                    @endif
                </p>
            </div>
            <div style="display: flex; gap: 0.5rem; align-items: center; flex-wrap: wrap;">
                @if($canDownload)
                <button id="toggle-viewer-btn" class="btn btn-outline" style="display: inline-flex; align-items: center; gap: 0.5rem; font-size: 0.85rem; padding: 0.55rem 1.2rem; cursor: pointer; border: 1px solid var(--color-border); background: white;">
                    📖 <span id="toggle-viewer-text">Afficher la liseuse</span>
                </button>
                <a href="{{ route('documents.download', $publication->id) }}" class="btn btn-primary" style="display: inline-flex; align-items: center; gap: 0.5rem; text-decoration: none; padding: 0.55rem 1.2rem; font-size: 0.85rem;">
                    📥 Télécharger
                </a>
                @else
                <span style="font-size: 0.8rem; color: var(--color-text-muted); background: white; padding: 0.5rem 1rem; border-radius: var(--radius-sm); border: 1.5px dashed var(--color-border-hover); font-weight: 600;">
                    🔒 Téléchargement réservé
                </span>
                @endif
            </div>
        </div>

        @if($canDownload)
        <div id="document-viewer-container" style="display: none; margin-top: 1rem; border: 1px solid var(--color-border); border-radius: var(--radius); overflow: hidden; background: #f1f5f9; box-shadow: inset 0 2px 4px rgba(0,0,0,0.06);">
            <iframe src="{{ route('documents.view', $publication->id) }}" style="width: 100%; height: 600px; border: none; display: block;" allowfullscreen></iframe>
        </div>

        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const btn = document.getElementById('toggle-viewer-btn');
                const container = document.getElementById('document-viewer-container');
                const text = document.getElementById('toggle-viewer-text');
                
                if (btn && container && text) {
                    btn.addEventListener('click', function() {
                        if (container.style.display === 'none') {
                            container.style.display = 'block';
                            text.textContent = 'Masquer la liseuse';
                            btn.style.background = 'var(--color-primary)';
                            btn.style.color = 'white';
                        } else {
                            container.style.display = 'none';
                            text.textContent = 'Afficher la liseuse';
                            btn.style.background = 'white';
                            btn.style.color = 'var(--color-text)';
                        }
                    });
                }
            });
        </script>
        @endif
        @elseif($publication->pdf_url)
        <div style="margin-top: 2rem; padding: 1.5rem; background: #f8fafc; border-radius: var(--radius); border: 1px solid var(--color-border); display: flex; align-items: center; justify-content: space-between; gap: 1rem; flex-wrap: wrap;">
            <div>
                <h3 style="font-size: 0.95rem; font-weight: 700; color: #a6d042; margin: 0;">🔓 Version Open Access (Accès Libre)</h3>
                <p style="font-size: 0.8rem; color: var(--color-text-muted); margin: 0.25rem 0 0 0; font-weight: 500;">
                    Le document complet est librement accessible via Unpaywall.
                </p>
            </div>
            <div style="display: flex; gap: 0.5rem; align-items: center; flex-wrap: wrap;">
                <a href="{{ $publication->pdf_url }}" target="_blank" rel="noopener" class="btn btn-primary" style="display: inline-flex; align-items: center; gap: 0.5rem; text-decoration: none; padding: 0.55rem 1.2rem; font-size: 0.85rem; background-color: #a6d042; color: #000; border: none;">
                    📄 Lire le document PDF
                </a>
            </div>
        </div>
        @endif

        {{-- Dataset files --}}
        @if($publication->type === 'dataset' && $publication->dataset && $publication->dataset->fichiers && $publication->dataset->fichiers->count() > 0)
        <div style="margin-top: 2rem; border-top: 1px solid var(--color-border); padding-top: 2rem;">
            <h3 style="font-size: 1.1rem; font-weight: 700; color: var(--color-primary); margin-bottom: 1rem; font-family: 'Outfit', sans-serif;">Fichiers du jeu de données</h3>
            <div style="display: flex; flex-direction: column; gap: 0.75rem;">
                @foreach($publication->dataset->fichiers as $fichier)
                <div style="padding: 1rem; background: #f8fafc; border-radius: var(--radius); border: 1px solid var(--color-border); display: flex; align-items: center; justify-content: space-between; gap: 1rem; flex-wrap: wrap;">
                    <div>
                        <div style="font-weight: 700; font-size: 0.875rem; color: var(--color-text);">{{ $fichier->nom }}</div>
                        @if($fichier->description)
                            <div style="font-size: 0.775rem; color: var(--color-text-muted); margin-top: 0.15rem;">{{ $fichier->description }}</div>
                        @endif
                        <div style="font-size: 0.725rem; color: var(--color-text-muted); margin-top: 0.35rem; font-weight: 500;">
                            Format : <span style="background: white; padding: 0.15rem 0.4rem; border-radius: 4px; border: 1px solid var(--color-border); font-weight: 700; color: var(--color-primary-light);">{{ $fichier->format }}</span>
                            @if($fichier->taille_octets)
                                &bull; Taille : {{ round($fichier->taille_octets / 1024 / 1024, 2) }} Mo
                            @endif
                        </div>
                    </div>
                    @if($canDownload)
                    <a href="{{ route('datasets.files.download', $fichier->id) }}" class="btn btn-outline" style="display: inline-flex; align-items: center; gap: 0.4rem; text-decoration: none; font-size: 0.8rem; padding: 0.45rem 1rem;">
                        📥 Télécharger
                    </a>
                    @else
                    <span style="font-size: 0.75rem; color: var(--color-text-muted); background: white; padding: 0.45rem 1rem; border-radius: var(--radius-sm); border: 1.5px dashed var(--color-border-hover); font-weight: 600;">
                        🔒 Téléchargement réservé
                    </span>
                    @endif
                </div>
                @endforeach
            </div>
        </div>
        @endif
    </div>

    {{-- Publications similaires --}}
    @if(count($similar) > 0)
    <section>
        <h2 style="font-size: 1.4rem; font-weight: 700; margin-bottom: 1.5rem; color: var(--color-primary); font-family: 'Outfit', sans-serif;">📚 Publications du même axe</h2>
        <div class="grid-3">
            @foreach($similar as $pub)
            <a href="{{ route('publications.show', $pub->id) }}" style="text-decoration: none;" class="card">
                <div style="display: flex; gap: 0.5rem; margin-bottom: 0.75rem;">
                    <span class="badge badge-primary" style="font-size: 0.65rem;">{{ $typeLabels[$pub->type] ?? ucfirst($pub->type) }}</span>
                </div>
                <h3 style="font-size: 0.95rem; font-weight: 700; color: var(--color-primary); margin-bottom: 0.5rem; line-height: 1.4;">
                    {{ Str::limit($pub->titre_fr, 75) }}
                </h3>
                <p class="text-muted" style="font-size: 0.775rem; font-weight: 500;">
                    👤 {{ $pub->auteur->prenom ?? '' }} {{ $pub->auteur->nom ?? '' }}
                </p>
            </a>
            @endforeach
        </div>
    </section>
    @endif

</div>
@endsection
