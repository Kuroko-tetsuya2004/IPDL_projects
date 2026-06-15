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
                        <span style="font-weight: 600; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 65%;">
                            👤 
                            @if($pub->auteur)
                                {{ $pub->auteur->prenom ?? '' }} {{ $pub->auteur->nom ?? '' }}
                                @if(!empty($pub->auteurs_externes))
                                    <span style="font-weight: 400;">(+{{ count($pub->auteurs_externes) }})</span>
                                @endif
                            @elseif(!empty($pub->auteurs_externes))
                                {{ implode(', ', array_slice($pub->auteurs_externes, 0, 2)) }}
                                @if(count($pub->auteurs_externes) > 2)
                                    <span style="font-weight: 400;">(+{{ count($pub->auteurs_externes) - 2 }})</span>
                                @endif
                            @else
                                Auteur inconnu
                            @endif
                        </span>
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
