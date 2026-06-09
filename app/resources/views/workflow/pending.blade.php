@extends('layouts.app')

@section('title', 'Soumissions en attente — Portail UMMISCO')

@section('content')
<div class="container" style="padding-top: 1rem;">
    <h1 style="font-size: 1.75rem; font-weight: 700; margin-bottom: 1.5rem;">📋 Soumissions en attente de validation</h1>

    @if($submissions->count() > 0)
        <div style="display: flex; flex-direction: column; gap: 1rem;">
            @foreach($submissions as $wf)
            <div class="card" style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 1rem;">
                <div style="flex: 1;">
                    <div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.5rem;">
                        <span class="badge badge-primary">{{ $wf->publication->type ?? 'N/A' }}</span>
                        @if($wf->publication->axe ?? null)
                            <span class="badge" style="background: #EEF2FF; color: #3730A3;">{{ $wf->publication->axe->code }}</span>
                        @endif
                        <span class="badge" style="background: #FEF3C7; color: #92400E;">⏳ En attente</span>
                    </div>
                    <h3 style="font-size: 1.05rem; font-weight: 600;">{{ $wf->publication->titre_fr ?? 'Sans titre' }}</h3>
                    <p class="text-muted mt-1" style="font-size: 0.85rem;">
                        Soumis par <strong>{{ $wf->soumetteur->prenom ?? '' }} {{ $wf->soumetteur->nom ?? '' }}</strong>
                        ({{ $wf->soumetteur->email ?? '' }})
                        · {{ $wf->date_soumission?->format('d/m/Y H:i') }}
                    </p>
                    @if($wf->commentaire_auteur)
                        <p class="mt-1" style="font-size: 0.825rem; background: #F8FAFC; padding: 0.5rem 0.75rem; border-radius: var(--radius-sm); border-left: 3px solid var(--color-accent);">
                            💬 {{ $wf->commentaire_auteur }}
                        </p>
                    @endif
                    @if($wf->date_limite)
                        <p class="text-muted mt-1" style="font-size: 0.8rem;">
                            ⏰ Date limite de validation : <strong>{{ $wf->date_limite->format('d/m/Y') }}</strong>
                            @if($wf->date_limite->isPast())
                                <span style="color: var(--color-danger); font-weight: 600;"> — Dépassée !</span>
                            @endif
                        </p>
                    @endif
                </div>

                <div style="display: flex; flex-direction: column; gap: 0.5rem; min-width: 200px;">
                    {{-- Formulaire d'approbation --}}
                    <form method="POST" action="{{ route('workflow.approve', $wf->id) }}" style="margin: 0;">
                        @csrf
                        <input type="text" name="commentaire_admin" placeholder="Commentaire (optionnel)" class="form-control" style="margin-bottom: 0.5rem; font-size: 0.8rem; padding: 0.4rem 0.75rem;">
                        <button type="submit" class="btn btn-success" style="width: 100%; justify-content: center; font-size: 0.8rem;">
                            ✅ Approuver & Publier
                        </button>
                    </form>

                    {{-- Formulaire de rejet --}}
                    <form method="POST" action="{{ route('workflow.reject', $wf->id) }}" style="margin: 0;">
                        @csrf
                        <input type="text" name="commentaire_admin" placeholder="Motif de rejet (obligatoire)" class="form-control" style="margin-bottom: 0.5rem; font-size: 0.8rem; padding: 0.4rem 0.75rem;" required>
                        <button type="submit" class="btn btn-danger" style="width: 100%; justify-content: center; font-size: 0.8rem;">
                            ❌ Rejeter
                        </button>
                    </form>
                </div>
            </div>
            @endforeach
        </div>

        <div class="mt-3" style="display: flex; justify-content: center;">
            {{ $submissions->links() }}
        </div>
    @else
        <div class="card text-center" style="padding: 3rem;">
            <p style="font-size: 1.5rem;">✅</p>
            <p class="text-muted">Aucune soumission en attente de validation.</p>
        </div>
    @endif
</div>
@endsection
