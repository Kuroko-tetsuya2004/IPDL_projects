@extends('layouts.app')

@section('title', $publication->titre_fr . ' — UMMISCO')
@section('description', Str::limit($publication->resume_fr ?? '', 200))

@section('styles')
<style>
/* ── Publication Detail ───────────────────────────────────────── */
.pub-detail-layout {
    display: grid;
    grid-template-columns: 1fr 300px;
    gap: 2.5rem;
    padding: 3rem 0 5rem;
    align-items: start;
    max-width: 1200px;
    margin: 0 auto;
    padding-left: 1.5rem;
    padding-right: 1.5rem;
}

@media (max-width: 960px) {
    .pub-detail-layout {
        grid-template-columns: 1fr;
    }
    .pub-sidebar { position: static !important; }
}

/* ── Main Content ─────────────────────────────────────────────── */
.pub-main { min-width: 0; }

.pub-header-card {
    background: var(--card-bg);
    border-radius: var(--radius-lg);
    border: 1px solid var(--border);
    overflow: hidden;
    box-shadow: var(--shadow-md);
    margin-bottom: 1.5rem;
}

.pub-header-stripe {
    height: 5px;
    background: linear-gradient(90deg, var(--axe-color, var(--primary-light)), var(--axe-color, var(--accent)));
}

.pub-header-body { padding: 2.5rem; }

.pub-type-badges {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    flex-wrap: wrap;
    margin-bottom: 1.5rem;
}

.pub-h1 {
    font-size: clamp(1.4rem, 3.5vw, 2rem);
    font-weight: 800;
    letter-spacing: -0.025em;
    line-height: 1.25;
    color: var(--text);
    margin-bottom: 0.875rem;
}

.pub-h1-en {
    font-size: 1rem;
    font-style: italic;
    color: var(--text-muted);
    margin-bottom: 1.5rem;
    line-height: 1.45;
}

.pub-authors-strip {
    display: flex;
    align-items: center;
    gap: 1.25rem;
    padding: 1.25rem 0;
    border-top: 1px solid var(--border);
    border-bottom: 1px solid var(--border);
    margin-bottom: 1.5rem;
    flex-wrap: wrap;
}

.pub-author-block { display: flex; flex-direction: column; gap: 0.25rem; }

.pub-author-name {
    font-weight: 700;
    font-size: 0.95rem;
    color: var(--text);
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.pub-orcid-badge {
    display: inline-flex;
    align-items: center;
    gap: 0.25rem;
    font-size: 0.68rem;
    font-weight: 700;
    color: #a6ce39;
    text-decoration: none;
    background: rgba(166, 206, 57, 0.1);
    border: 1px solid rgba(166, 206, 57, 0.25);
    padding: 0.15rem 0.5rem;
    border-radius: 9999px;
    transition: var(--transition);
}

.pub-orcid-badge:hover { background: rgba(166, 206, 57, 0.2); }

/* ── Abstract ─────────────────────────────────────────────────── */
.abstract-section {
    background: var(--card-bg);
    border-radius: var(--radius);
    border: 1px solid var(--border);
    padding: 2rem;
    margin-bottom: 1.5rem;
    box-shadow: var(--shadow-sm);
}

.abstract-lang-tabs {
    display: flex;
    gap: 0.25rem;
    margin-bottom: 1.25rem;
    border-bottom: 1px solid var(--border);
    padding-bottom: 0.75rem;
}

.abstract-tab {
    font-size: 0.75rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.08em;
    padding: 0.35rem 0.875rem;
    border-radius: var(--radius-xs);
    cursor: pointer;
    transition: var(--transition);
    border: none;
    background: none;
    color: var(--text-subtle);
    font-family: inherit;
}

.abstract-tab.active {
    background: var(--primary-glow);
    color: var(--primary-light);
}

.abstract-content { display: none; }
.abstract-content.active { display: block; }

/* ── Keywords ─────────────────────────────────────────────────── */
.keywords-section {
    background: var(--card-bg);
    border-radius: var(--radius);
    border: 1px solid var(--border);
    padding: 1.5rem 2rem;
    margin-bottom: 1.5rem;
}

/* ── Sidebar ──────────────────────────────────────────────────── */
.pub-sidebar {
    position: sticky;
    top: 100px;
}

.sidebar-card {
    background: var(--card-bg);
    border-radius: var(--radius);
    border: 1px solid var(--border);
    overflow: hidden;
    box-shadow: var(--shadow);
    margin-bottom: 1.25rem;
}

.sidebar-card-header {
    padding: 1rem 1.25rem;
    border-bottom: 1px solid var(--border);
    font-size: 0.72rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.1em;
    color: var(--text-subtle);
    background: var(--bg-tertiary);
}

.sidebar-card-body { padding: 1.25rem; }

.sidebar-meta-row {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    padding: 0.625rem 0;
    border-bottom: 1px solid var(--border);
    gap: 0.75rem;
}

.sidebar-meta-row:last-child { border-bottom: none; padding-bottom: 0; }

.sidebar-meta-label {
    font-size: 0.72rem;
    font-weight: 600;
    color: var(--text-subtle);
    text-transform: uppercase;
    letter-spacing: 0.05em;
    flex-shrink: 0;
}

.sidebar-meta-value {
    font-size: 0.82rem;
    font-weight: 600;
    color: var(--text);
    text-align: right;
}

.action-btn-full {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    width: 100%;
    padding: 0.75rem 1rem;
    border-radius: var(--radius-sm);
    font-size: 0.85rem;
    font-weight: 700;
    text-decoration: none;
    border: none;
    cursor: pointer;
    transition: var(--transition);
    font-family: inherit;
    margin-bottom: 0.625rem;
}

.action-btn-full:last-child { margin-bottom: 0; }

.btn-doi {
    background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%);
    color: #fff;
    box-shadow: 0 4px 14px var(--primary-glow);
}

.btn-doi:hover {
    transform: translateY(-1px);
    box-shadow: 0 6px 20px var(--primary-glow);
}

.btn-pdf {
    background: linear-gradient(135deg, #059669 0%, #34d399 100%);
    color: #fff;
    box-shadow: 0 4px 14px rgba(5,150,105,0.25);
}

.btn-pdf:hover {
    transform: translateY(-1px);
    box-shadow: 0 6px 20px rgba(5,150,105,0.35);
}

.btn-download {
    background: var(--surface);
    border: 1.5px solid var(--border-strong);
    color: var(--text);
}

.btn-download:hover {
    border-color: var(--primary-light);
    color: var(--primary-light);
    background: var(--primary-glow);
}

.btn-locked {
    background: var(--bg-tertiary);
    border: 1.5px dashed var(--border-strong);
    color: var(--text-subtle);
    cursor: not-allowed;
    opacity: 0.7;
}

/* ── Document viewer ──────────────────────────────────────────── */
.viewer-container {
    display: none;
    margin-top: 1.25rem;
    border-radius: var(--radius);
    overflow: hidden;
    border: 1px solid var(--border);
    box-shadow: var(--shadow-md);
}

.viewer-container iframe {
    width: 100%; height: 620px; border: none; display: block;
}

/* ── Similar pubs ─────────────────────────────────────────────── */
.similar-section {
    padding: 3rem 0 5rem;
    border-top: 1px solid var(--border);
    background: var(--bg-secondary);
}

</style>
@endsection

@section('content')

@php
$typeLabels = [
    'article' => 'Article scientifique', 'document' => 'Recherche en cours',
    'event' => 'Événement', 'dataset' => 'Dataset', 'news' => 'Actualité',
    'thesis' => 'Thèse / Mémoire', 'report' => 'Rapport de recherche',
    'presentation' => 'Présentation / Poster'
];
$typeColors = [
    'article' => '#2563eb', 'thesis' => '#7c3aed', 'dataset' => '#059669',
    'report' => '#d97706', 'news' => '#0ea5e9', 'event' => '#e11d48',
    'presentation' => '#db2777', 'document' => '#64748b'
];
$axeColor = $publication->axe->couleur_hex ?? ($typeColors[$publication->type] ?? '#2563eb');
@endphp

{{-- Breadcrumb --}}
<div style="background:var(--bg-secondary); border-bottom:1px solid var(--border); padding:0.875rem 0;">
    <div class="container">
        <nav style="display:flex; align-items:center; gap:0.5rem; font-size:0.8rem; color:var(--text-subtle); font-weight:500;">
            <a href="{{ route('home') }}" style="color:var(--primary-light); text-decoration:none; transition:opacity 0.2s;" onmouseenter="this.style.opacity='.7'" onmouseleave="this.style.opacity='1'">Accueil</a>
            <svg viewBox="0 0 24 24" width="12" height="12" stroke="currentColor" stroke-width="2.5" fill="none"><path d="m9 18 6-6-6-6"/></svg>
            <a href="{{ route('publications') }}" style="color:var(--primary-light); text-decoration:none; transition:opacity 0.2s;" onmouseenter="this.style.opacity='.7'" onmouseleave="this.style.opacity='1'">Publications</a>
            <svg viewBox="0 0 24 24" width="12" height="12" stroke="currentColor" stroke-width="2.5" fill="none"><path d="m9 18 6-6-6-6"/></svg>
            <span>{{ Str::limit($publication->titre_fr, 50) }}</span>
        </nav>
    </div>
</div>

<div class="pub-detail-layout" style="--axe-color: {{ $axeColor }};">

    {{-- ═══ MAIN ═══ --}}
    <div class="pub-main">

        {{-- Header Card --}}
        <div class="pub-header-card">
            <div class="pub-header-stripe"></div>
            <div class="pub-header-body">
                {{-- Type + Axe badges --}}
                <div class="pub-type-badges">
                    <span style="display:inline-flex; align-items:center; padding:0.25rem 0.875rem; border-radius:9999px; font-size:0.72rem; font-weight:800; text-transform:uppercase; letter-spacing:0.06em; background:{{ $axeColor }}18; color:{{ $axeColor }}; border:1px solid {{ $axeColor }}30;">
                        {{ $typeLabels[$publication->type] ?? ucfirst($publication->type) }}
                    </span>
                    @if($publication->axe)
                        <span style="display:inline-flex; padding:0.25rem 0.875rem; border-radius:9999px; font-size:0.72rem; font-weight:700; background:{{ $publication->axe->couleur_hex ?? '#2563eb' }}14; color:{{ $publication->axe->couleur_hex ?? '#2563eb' }}; border:1px solid {{ $publication->axe->couleur_hex ?? '#2563eb' }}30;">
                            {{ $publication->axe->code }} — {{ $publication->axe->nom_fr }}
                        </span>
                    @endif
                </div>

                {{-- Title --}}
                <h1 class="pub-h1">{{ $publication->titre_fr }}</h1>
                @if($publication->titre_en && $publication->titre_en !== $publication->titre_fr)
                    <p class="pub-h1-en">{{ $publication->titre_en }}</p>
                @endif

                {{-- Authors --}}
                <div class="pub-authors-strip">
                    <div class="pub-author-block">
                        <span class="pub-author-name">
                            <span style="color:var(--text-subtle);">👤</span>
                            @if($publication->auteur)
                                {{ $publication->auteur->titre_academique ? $publication->auteur->titre_academique . ' ' : '' }}{{ $publication->auteur->prenom }} {{ $publication->auteur->nom }}
                            @elseif(!empty($publication->auteurs_externes))
                                {{ implode(', ', array_slice($publication->auteurs_externes, 0, 3)) }}
                                @if(count($publication->auteurs_externes) > 3)
                                    <span style="color:var(--text-subtle); font-weight:400;">+{{ count($publication->auteurs_externes) - 3 }} auteurs</span>
                                @endif
                            @else
                                <span style="color:var(--text-muted); font-weight:400;">Auteur inconnu</span>
                            @endif
                        </span>

                        @if($publication->auteur && $publication->auteur->orcid_id)
                            <a href="https://orcid.org/{{ $publication->auteur->orcid_id }}" target="_blank" rel="noopener" class="pub-orcid-badge">
                                <span style="font-weight:800; border:1px solid #a6ce39; border-radius:3px; padding:0px 2px; font-size:0.55rem; line-height:1.2;">iD</span>
                                ORCID {{ $publication->auteur->orcid_id }}
                            </a>
                        @endif

                        @if($publication->auteur && $publication->auteur->email)
                            <a href="mailto:{{ $publication->auteur->email }}" style="font-size:0.75rem; color:var(--text-subtle); text-decoration:none; display:flex; align-items:center; gap:0.25rem; font-weight:500; margin-top:0.125rem; transition:color 0.2s;"
                               onmouseenter="this.style.color='var(--primary-light)'" onmouseleave="this.style.color='var(--text-subtle)'">
                                <svg viewBox="0 0 24 24" width="11" height="11" stroke="currentColor" stroke-width="2" fill="none"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
                                {{ $publication->auteur->email }}
                            </a>
                        @endif

                        @if(!empty($publication->auteurs_externes) && $publication->auteur)
                            <span style="font-size:0.75rem; color:var(--text-subtle); margin-top:0.25rem;">
                                Et {{ count($publication->auteurs_externes) }} co-auteur(s) : {{ implode(', ', array_slice($publication->auteurs_externes, 0, 5)) }}{{ count($publication->auteurs_externes) > 5 ? '...' : '' }}
                            </span>
                        @endif
                    </div>

                    @if($publication->date_publication)
                    <div style="margin-left:auto; text-align:right; font-size:0.82rem; flex-shrink:0;">
                        <div style="font-weight:700; color:var(--text);">
                            📅 {{ $publication->date_publication->format('d F Y') }}
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Abstract --}}
        @if($publication->resume_fr || $publication->resume_en)
        <div class="abstract-section">
            <div class="abstract-lang-tabs">
                <button class="abstract-tab {{ $publication->resume_fr ? 'active' : '' }}" onclick="showAbstract('fr', this)">Résumé (FR)</button>
                @if($publication->resume_en)
                    <button class="abstract-tab {{ !$publication->resume_fr ? 'active' : '' }}" onclick="showAbstract('en', this)">Abstract (EN)</button>
                @endif
            </div>

            @if($publication->resume_fr)
            <div class="abstract-content {{ $publication->resume_fr ? 'active' : '' }}" id="abstract-fr">
                <p style="font-size:0.95rem; line-height:1.85; color:var(--text-muted); text-align:justify;">
                    {{ $publication->resume_fr }}
                </p>
            </div>
            @endif

            @if($publication->resume_en)
            <div class="abstract-content {{ !$publication->resume_fr ? 'active' : '' }}" id="abstract-en">
                <p style="font-size:0.93rem; line-height:1.85; color:var(--text-muted); text-align:justify; font-style:italic;">
                    {{ $publication->resume_en }}
                </p>
            </div>
            @endif
        </div>
        @endif

        {{-- Keywords --}}
        @if($publication->mots_cles)
        <div class="keywords-section">
            <div style="font-size:0.72rem; font-weight:700; text-transform:uppercase; letter-spacing:0.1em; color:var(--text-subtle); margin-bottom:0.875rem; display:flex; align-items:center; gap:0.5rem;">
                <svg viewBox="0 0 24 24" width="13" height="13" stroke="currentColor" stroke-width="2.5" fill="none"><path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"/><line x1="7" y1="7" x2="7.01" y2="7"/></svg>
                Mots-clés
            </div>
            <div style="display:flex; flex-wrap:wrap; gap:0.5rem;">
                @foreach(is_array($publication->mots_cles) ? $publication->mots_cles : explode(',', $publication->mots_cles) as $tag)
                    <a href="{{ route('publications') }}?q={{ urlencode(trim($tag)) }}"
                       style="font-size:0.8rem; font-weight:600; padding:0.3rem 0.875rem; background:var(--bg-tertiary); border-radius:9999px; color:var(--text-muted); border:1px solid var(--border); text-decoration:none; transition:var(--transition);"
                       onmouseenter="this.style.background='var(--primary-glow)'; this.style.color='var(--primary-light)'; this.style.borderColor='rgba(37,99,235,0.3)';"
                       onmouseleave="this.style.background='var(--bg-tertiary)'; this.style.color='var(--text-muted)'; this.style.borderColor='var(--border)';">
                        #{{ trim($tag) }}
                    </a>
                @endforeach
            </div>
        </div>
        @endif

        {{-- Document Viewer --}}
        @if($publication->document && $canDownload)
        <div style="background:var(--card-bg); border-radius:var(--radius); border:1px solid var(--border); overflow:hidden; box-shadow:var(--shadow);">
            <div style="padding:1.25rem 1.75rem; border-bottom:1px solid var(--border); display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:1rem;">
                <div>
                    <div style="font-size:0.72rem; font-weight:700; text-transform:uppercase; letter-spacing:0.1em; color:var(--text-subtle); margin-bottom:0.25rem;">Document joint</div>
                    <div style="font-weight:700; color:var(--text); font-size:0.9rem;">{{ $publication->document->fichier_nom }}</div>
                    @if($publication->document->fichier_taille)
                        <div style="font-size:0.75rem; color:var(--text-subtle);">{{ round($publication->document->fichier_taille / 1024 / 1024, 2) }} Mo</div>
                    @endif
                </div>
                <div style="display:flex; gap:0.5rem; flex-wrap:wrap;">
                    <button id="toggleViewerBtn" onclick="toggleViewer()" style="display:inline-flex; align-items:center; gap:0.375rem; font-size:0.82rem; font-weight:700; padding:0.5rem 1rem; border-radius:var(--radius-sm); background:var(--surface); border:1.5px solid var(--border-strong); color:var(--text); cursor:pointer; transition:var(--transition); font-family:inherit;">
                        📖 <span id="viewerBtnText">Ouvrir la liseuse</span>
                    </button>
                    <a href="{{ route('documents.download', $publication->id) }}" style="display:inline-flex; align-items:center; gap:0.375rem; font-size:0.82rem; font-weight:700; padding:0.5rem 1rem; border-radius:var(--radius-sm); background:linear-gradient(135deg, var(--primary), var(--primary-light)); color:#fff; text-decoration:none; transition:var(--transition);">
                        📥 Télécharger
                    </a>
                </div>
            </div>
            <div class="viewer-container" id="viewerContainer">
                <iframe src="{{ route('documents.view', $publication->id) }}" allowfullscreen></iframe>
            </div>
        </div>
        @endif

    </div>

    {{-- ═══ SIDEBAR ═══ --}}
    <aside class="pub-sidebar">

        {{-- Actions card --}}
        <div class="sidebar-card">
            <div class="sidebar-card-header">Accès au document</div>
            <div class="sidebar-card-body">
                @if($publication->doi)
                    <a href="https://doi.org/{{ $publication->doi }}" target="_blank" rel="noopener" class="action-btn-full btn-doi">
                        <svg viewBox="0 0 24 24" width="14" height="14" stroke="currentColor" stroke-width="2.5" fill="none"><path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"/><path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"/></svg>
                        Voir via DOI
                    </a>
                @endif

                @if($publication->pdf_url)
                    <a href="{{ $publication->pdf_url }}" target="_blank" rel="noopener" class="action-btn-full btn-pdf">
                        <svg viewBox="0 0 24 24" width="14" height="14" stroke="currentColor" stroke-width="2.5" fill="none"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                        🔓 Version Open Access
                    </a>
                @endif

                @if($publication->document)
                    @if($canDownload)
                        <a href="{{ route('documents.download', $publication->id) }}" class="action-btn-full btn-download">
                            <svg viewBox="0 0 24 24" width="14" height="14" stroke="currentColor" stroke-width="2.5" fill="none"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                            Télécharger le document
                        </a>
                    @else
                        <span class="action-btn-full btn-locked">
                            🔒 Accès réservé aux membres
                        </span>
                    @endif
                @endif

                @if($publication->url_externe && !$publication->doi)
                    <a href="{{ $publication->url_externe }}" target="_blank" rel="noopener" class="action-btn-full btn-download">
                        <svg viewBox="0 0 24 24" width="14" height="14" stroke="currentColor" stroke-width="2.5" fill="none"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>
                        Lien externe
                    </a>
                @endif

                @if(!$publication->doi && !$publication->pdf_url && !$publication->document && !$publication->url_externe)
                    <p style="font-size:0.82rem; color:var(--text-subtle); text-align:center; padding:0.5rem 0;">
                        Aucun accès externe disponible.
                    </p>
                @endif
            </div>
        </div>

        {{-- Metadata card --}}
        <div class="sidebar-card">
            <div class="sidebar-card-header">Métadonnées</div>
            <div class="sidebar-card-body" style="padding:0.5rem 1.25rem;">
                @if($publication->date_publication)
                <div class="sidebar-meta-row">
                    <span class="sidebar-meta-label">Date</span>
                    <span class="sidebar-meta-value">{{ $publication->date_publication->format('d M Y') }}</span>
                </div>
                @endif
                @if($publication->type)
                <div class="sidebar-meta-row">
                    <span class="sidebar-meta-label">Type</span>
                    <span class="sidebar-meta-value">{{ $typeLabels[$publication->type] ?? ucfirst($publication->type) }}</span>
                </div>
                @endif
                @if($publication->axe)
                <div class="sidebar-meta-row">
                    <span class="sidebar-meta-label">Axe</span>
                    <span class="sidebar-meta-value" style="color:{{ $publication->axe->couleur_hex ?? 'var(--primary-light)' }};">{{ $publication->axe->code }}</span>
                </div>
                @endif
                @if($publication->doi)
                <div class="sidebar-meta-row">
                    <span class="sidebar-meta-label">DOI</span>
                    <span class="sidebar-meta-value" style="font-family:var(--font-mono, monospace); font-size:0.72rem; word-break:break-all; text-align:right;">{{ $publication->doi }}</span>
                </div>
                @endif
                @if(!empty($publication->auteurs_externes))
                <div class="sidebar-meta-row">
                    <span class="sidebar-meta-label">Co-auteurs</span>
                    <span class="sidebar-meta-value">{{ count($publication->auteurs_externes) }}</span>
                </div>
                @endif
            </div>
        </div>

        {{-- Share --}}
        <div class="sidebar-card">
            <div class="sidebar-card-header">Partager</div>
            <div class="sidebar-card-body">
                <div style="display:flex; gap:0.5rem; flex-wrap:wrap;">
                    <button onclick="copyLink()" style="flex:1; display:inline-flex; align-items:center; justify-content:center; gap:0.375rem; font-size:0.78rem; font-weight:700; padding:0.5rem; border-radius:var(--radius-sm); background:var(--bg-tertiary); border:1.5px solid var(--border-strong); color:var(--text-muted); cursor:pointer; transition:var(--transition); font-family:inherit;" id="copyLinkBtn">
                        <svg viewBox="0 0 24 24" width="13" height="13" stroke="currentColor" stroke-width="2.5" fill="none"><rect x="9" y="9" width="13" height="13" rx="2" ry="2"/><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/></svg>
                        Copier le lien
                    </button>
                </div>
            </div>
        </div>

    </aside>

</div>

{{-- Similar Publications --}}
@if(count($similar) > 0)
<div class="similar-section">
    <div class="container">
        <div class="section-label" style="margin-bottom:0.75rem;">Du même axe</div>
        <h2 style="font-size:1.5rem; font-weight:800; letter-spacing:-0.02em; color:var(--text); margin-bottom:2rem;">Publications similaires</h2>
        <div class="grid-3">
            @foreach($similar as $sim)
            <a href="{{ route('publications.show', $sim->id) }}" style="text-decoration:none; display:flex; flex-direction:column; background:var(--card-bg); border-radius:var(--radius); border:1px solid var(--border); padding:1.5rem; box-shadow:var(--shadow); transition:var(--transition);"
               onmouseenter="this.style.transform='translateY(-3px)'; this.style.boxShadow='var(--shadow-md)';"
               onmouseleave="this.style.transform=''; this.style.boxShadow='var(--shadow)';">
                <span style="display:inline-flex; padding:0.2rem 0.65rem; border-radius:9999px; font-size:0.65rem; font-weight:700; background:var(--primary-glow); color:var(--primary-light); margin-bottom:0.75rem; align-self:flex-start;">
                    {{ $typeLabels[$sim->type] ?? ucfirst($sim->type) }}
                </span>
                <h3 style="font-size:0.9rem; font-weight:700; color:var(--text); line-height:1.4; margin-bottom:0.5rem; flex-grow:1;">
                    {{ Str::limit($sim->titre_fr, 80) }}
                </h3>
                <p style="font-size:0.75rem; color:var(--text-subtle); font-weight:500; margin-top:auto;">
                    👤 {{ $sim->auteur->prenom ?? '' }} {{ $sim->auteur->nom ?? '' }}
                </p>
            </a>
            @endforeach
        </div>
    </div>
</div>
@endif

@endsection

@section('scripts')
<script>
function showAbstract(lang, btn) {
    document.querySelectorAll('.abstract-content').forEach(el => el.classList.remove('active'));
    document.querySelectorAll('.abstract-tab').forEach(el => el.classList.remove('active'));
    document.getElementById('abstract-' + lang).classList.add('active');
    btn.classList.add('active');
}

function toggleViewer() {
    const container = document.getElementById('viewerContainer');
    const btnText   = document.getElementById('viewerBtnText');
    const btn       = document.getElementById('toggleViewerBtn');
    if (container.style.display === 'block') {
        container.style.display = 'none';
        btnText.textContent = 'Ouvrir la liseuse';
        btn.style.background = 'var(--surface)';
    } else {
        container.style.display = 'block';
        btnText.textContent = 'Fermer la liseuse';
        btn.style.background = 'var(--primary-glow)';
    }
}

function copyLink() {
    navigator.clipboard.writeText(window.location.href).then(function() {
        const btn = document.getElementById('copyLinkBtn');
        btn.innerHTML = '<svg viewBox="0 0 24 24" width="13" height="13" stroke="currentColor" stroke-width="2.5" fill="none"><polyline points="20 6 9 17 4 12"/></svg> Copié !';
        btn.style.color = 'var(--success)';
        btn.style.borderColor = 'var(--success)';
        setTimeout(function() {
            btn.innerHTML = '<svg viewBox="0 0 24 24" width="13" height="13" stroke="currentColor" stroke-width="2.5" fill="none"><rect x="9" y="9" width="13" height="13" rx="2" ry="2"/><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/></svg> Copier le lien';
            btn.style.color = '';
            btn.style.borderColor = '';
        }, 2500);
    });
}
</script>
@endsection
