@extends('layouts.app')

@section('title', 'Présentations Scientifiques — UMMISCO')

@section('content')
<div class="container" style="padding-top: 2rem;">
    {{-- Page Header --}}
    <div style="background: linear-gradient(135deg, rgba(30, 58, 138, 0.03) 0%, rgba(37, 99, 235, 0.05) 100%); border-bottom: 2px solid var(--color-border); padding: 3rem 2rem; border-radius: var(--radius); margin-bottom: 3rem; text-align: center;">
        <h1 style="font-size: 2.25rem; font-weight: 800; margin-bottom: 1rem; color: var(--color-primary);">
            Présentations & Séminaires
        </h1>
        <p style="font-size: 1.1rem; color: var(--color-text-muted); max-width: 800px; margin: 0 auto;">
            Supports de conférences, exposés de séminaires et présentations de travaux de recherche
        </p>
    </div>

    {{-- Contenu des Présentations --}}
    <div style="margin-bottom: 4rem;">
        <h2 style="font-size: 1.5rem; margin-bottom: 1.5rem; color: var(--color-primary);">🎤 Séminaires Réguliers UMMISCO</h2>
        <p style="color: var(--color-text); margin-bottom: 2rem;">
            Le laboratoire UMMISCO organise régulièrement des séminaires scientifiques internes et ouverts, permettant aux chercheurs et doctorants de présenter l'avancement de leurs travaux en modélisation mathématique et informatique des systèmes complexes.
        </p>

        <div style="display: flex; flex-direction: column; gap: 1.5rem;">
            
            <div class="card" style="display: flex; flex-direction: column; gap: 0.75rem;">
                <div style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 0.5rem;">
                    <h3 style="font-size: 1.1rem; color: var(--color-primary-light); font-weight: 700;">
                        Séminaire : Plateforme GAMA 1.9 & Outils de Co-Simulation
                    </h3>
                    <span class="badge badge-primary">Séminaire Interne</span>
                </div>
                <p style="font-size: 0.875rem; color: var(--color-text-muted);">
                    Présentation des nouveautés de la plateforme GAMA, y compris le support du multi-threading pour les simulations à large échelle et l'implémentation de la co-simulation FMI.
                </p>
                <div style="font-size: 0.8rem; color: var(--color-text-muted); font-weight: 600;">
                    Présenté par : Équipe de Développement GAMA · Dakar, Sénégal
                </div>
            </div>

            <div class="card" style="display: flex; flex-direction: column; gap: 0.75rem;">
                <div style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 0.5rem;">
                    <h3 style="font-size: 1.1rem; color: var(--color-primary-light); font-weight: 700;">
                        Modèles Épidémiologiques Hétérogènes en Milieux Urbains
                    </h3>
                    <span class="badge badge-success">Conférence Externe</span>
                </div>
                <p style="font-size: 0.875rem; color: var(--color-text-muted);">
                    Conférence internationale sur la modélisation des dynamiques urbaines et propagation de vecteurs de maladies en zone tropicale.
                </p>
                <div style="font-size: 0.8rem; color: var(--color-text-muted); font-weight: 600;">
                    Présenté par : Dr. Seydou Doumbia · Conférence CODESRIA
                </div>
            </div>

        </div>
    </div>
</div>
@endsection
