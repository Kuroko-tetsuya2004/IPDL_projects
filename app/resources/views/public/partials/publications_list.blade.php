@php
$singularTypes = [
    'article' => 'Article', 'document' => 'Recherche', 'event' => 'Événement',
    'dataset' => 'Dataset', 'news' => 'Actualité', 'thesis' => 'Thèse',
    'report' => 'Rapport', 'presentation' => 'Présentation'
];
$typeColors = [
    'article' => '#2563eb', 'thesis' => '#7c3aed', 'dataset' => '#059669',
    'report' => '#d97706', 'news' => '#0ea5e9', 'event' => '#e11d48',
    'presentation' => '#db2777', 'document' => '#64748b'
];
@endphp

@if($publications->count() > 0)

    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:2rem; flex-wrap:wrap; gap:0.75rem;">
        <p style="font-size:0.82rem; font-weight:600; color:var(--text-subtle);">
            <span style="color:var(--text); font-weight:800; font-size:1rem;">{{ $publications->total() }}</span>
            résultat{{ $publications->total() > 1 ? 's' : '' }} trouvé{{ $publications->total() > 1 ? 's' : '' }}
        </p>
        <div style="font-size:0.75rem; color:var(--text-subtle); display:flex; align-items:center; gap:0.375rem;">
            <svg viewBox="0 0 24 24" width="12" height="12" stroke="currentColor" stroke-width="2" fill="none"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
            Triés par pertinence puis par date
        </div>
    </div>

    <div style="display:grid; grid-template-columns:repeat(auto-fill, minmax(300px, 1fr)); gap:1.5rem;">
        @foreach($publications as $pub)
        @php
            $color = $typeColors[$pub->type] ?? '#2563eb';
        @endphp
        <article style="
            background: var(--card-bg);
            border-radius: var(--radius);
            border: 1px solid var(--border);
            padding: 0;
            box-shadow: var(--shadow);
            transition: box-shadow 0.3s ease, transform 0.3s ease, border-color 0.3s ease;
            display: flex;
            flex-direction: column;
            height: 100%;
            overflow: hidden;
            position: relative;
        " onmouseenter="this.style.transform='translateY(-4px)'; this.style.boxShadow='var(--shadow-lg)'; this.style.borderColor='var(--border-strong)';"
           onmouseleave="this.style.transform=''; this.style.boxShadow='var(--shadow)'; this.style.borderColor='var(--border)';">

            {{-- Colored top stripe by type --}}
            <div style="height:3px; background: linear-gradient(90deg, {{ $color }}, {{ $color }}88); flex-shrink:0;"></div>

            <div style="padding:1.5rem; display:flex; flex-direction:column; flex-grow:1;">
                {{-- Badges --}}
                <div style="display:flex; align-items:center; gap:0.375rem; margin-bottom:0.875rem; flex-wrap:wrap;">
                    <span style="
                        display:inline-flex; align-items:center;
                        padding:0.2rem 0.65rem; border-radius:9999px;
                        font-size:0.68rem; font-weight:700; text-transform:uppercase; letter-spacing:0.06em;
                        background: {{ $color }}18; color:{{ $color }}; border:1px solid {{ $color }}30;
                    ">{{ $singularTypes[$pub->type] ?? ucfirst($pub->type) }}</span>

                    @if($pub->axe)
                        <span style="
                            display:inline-flex; padding:0.2rem 0.65rem; border-radius:9999px;
                            font-size:0.65rem; font-weight:700;
                            background:{{ $pub->axe->couleur_hex ?? '#2563eb' }}14;
                            color:{{ $pub->axe->couleur_hex ?? '#2563eb' }};
                            border:1px solid {{ $pub->axe->couleur_hex ?? '#2563eb' }}30;
                        ">{{ $pub->axe->code }}</span>
                    @endif

                    @if($pub->doi)
                        <span style="display:inline-flex; align-items:center; gap:0.2rem; font-size:0.62rem; font-weight:600; color:var(--text-subtle); background:var(--bg-tertiary); padding:0.2rem 0.5rem; border-radius:9999px; border:1px solid var(--border);">
                            <svg viewBox="0 0 24 24" width="9" height="9" stroke="currentColor" stroke-width="2.5" fill="none"><path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"/><path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"/></svg>
                            DOI
                        </span>
                    @endif
                </div>

                {{-- Title --}}
                <h3 style="font-size:0.975rem; font-weight:700; color:var(--text); line-height:1.4; margin-bottom:0.625rem; flex-grow:1;">
                    <a href="{{ route('publications.show', $pub->id) }}" style="text-decoration:none; color:inherit; transition:color 0.2s ease;">
                        {{ Str::limit($pub->titre_fr, 100) }}
                    </a>
                </h3>

                @if($pub->resume_fr)
                    <p style="font-size:0.82rem; color:var(--text-muted); line-height:1.62; margin-bottom:1rem;">
                        {{ Str::limit($pub->resume_fr, 130) }}
                    </p>
                @endif

                {{-- Keywords --}}
                @if($pub->mots_cles && count($pub->mots_cles) > 0)
                    <div style="display:flex; gap:0.25rem; flex-wrap:wrap; margin-bottom:1rem;">
                        @foreach(array_slice($pub->mots_cles, 0, 3) as $kw)
                            <span style="font-size:0.68rem; font-weight:600; padding:0.15rem 0.5rem; background:var(--bg-tertiary); border-radius:9999px; color:var(--text-subtle); border:1px solid var(--border);">#{{ $kw }}</span>
                        @endforeach
                    </div>
                @endif

                {{-- Footer --}}
                <div style="border-top:1px solid var(--border); padding-top:0.875rem; display:flex; justify-content:space-between; align-items:center; margin-top:auto;">
                    <div style="font-size:0.75rem; color:var(--text-subtle); font-weight:500; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; max-width:60%;">
                        👤
                        @if($pub->auteur)
                            <span style="font-weight:700; color:var(--text-muted);">{{ trim(($pub->auteur->prenom ?? '') . ' ' . ($pub->auteur->nom ?? '')) }}</span>
                            @if(!empty($pub->auteurs_externes))
                                <span style="color:var(--text-subtle);"> +{{ count($pub->auteurs_externes) }}</span>
                            @endif
                        @elseif(!empty($pub->auteurs_externes))
                            <span style="font-weight:600; color:var(--text-muted);">{{ implode(', ', array_slice($pub->auteurs_externes, 0, 2)) }}</span>
                            @if(count($pub->auteurs_externes) > 2)
                                <span style="color:var(--text-subtle);"> +{{ count($pub->auteurs_externes) - 2 }}</span>
                            @endif
                        @else
                            <span style="color:var(--text-subtle);">Auteur inconnu</span>
                        @endif
                    </div>

                    <div style="display:flex; align-items:center; gap:0.625rem; flex-shrink:0;">
                        @if($pub->date_publication)
                            <span style="font-size:0.72rem; color:var(--text-subtle); font-weight:500;">
                                {{ $pub->date_publication->format('Y') }}
                            </span>
                        @endif
                        <a href="{{ route('publications.show', $pub->id) }}" style="
                            display:inline-flex; align-items:center; gap:0.3rem;
                            font-size:0.75rem; font-weight:700;
                            color:var(--primary-light);
                            background:var(--primary-glow);
                            border:1px solid {{ $color }}30;
                            padding:0.3rem 0.75rem; border-radius:var(--radius-xs);
                            text-decoration:none; transition:var(--transition);
                        " onmouseenter="this.style.background='var(--primary-light)'; this.style.color='#fff';"
                           onmouseleave="this.style.background='var(--primary-glow)'; this.style.color='var(--primary-light)';">
                            Lire
                            <svg viewBox="0 0 24 24" width="10" height="10" stroke="currentColor" stroke-width="3" fill="none"><path d="m9 18 6-6-6-6"/></svg>
                        </a>
                    </div>
                </div>
            </div>
        </article>
        @endforeach
    </div>

    {{-- Pagination --}}
    @if($publications->hasPages())
    <div style="margin-top:3rem; display:flex; justify-content:center;">
        {{ $publications->links() }}
    </div>
    @endif

@else
    <div style="text-align:center; padding:6rem 2rem;">
        <div style="width:72px; height:72px; border-radius:var(--radius); background:var(--primary-glow); display:flex; align-items:center; justify-content:center; margin:0 auto 1.5rem; font-size:2rem;">
            🔭
        </div>
        <h2 style="font-size:1.35rem; font-weight:800; color:var(--text); margin-bottom:0.625rem; letter-spacing:-0.02em;">Aucune publication trouvée</h2>
        <p style="color:var(--text-muted); font-size:0.9rem; max-width:440px; margin:0 auto 2rem; line-height:1.6;">
            Essayez un terme différent, un DOI ou un identifiant ORCID. Si aucun résultat local n'existe, les archives académiques ouvertes seront interrogées.
        </p>
        <a href="{{ route('publications') }}" style="display:inline-flex; align-items:center; gap:0.5rem; font-size:0.85rem; font-weight:700; color:var(--primary-light); text-decoration:none; background:var(--primary-glow); border:1px solid rgba(37,99,235,0.2); padding:0.6rem 1.25rem; border-radius:var(--radius-sm); transition:var(--transition);">
            <svg viewBox="0 0 24 24" width="14" height="14" stroke="currentColor" stroke-width="2.5" fill="none"><path d="M3 12a9 9 0 1 0 9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"/><path d="M3 3v5h5"/></svg>
            Voir toutes les publications
        </a>
    </div>
@endif
