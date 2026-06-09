@extends('layouts.app')

@section('title', 'Priorités Scientifiques — UMMISCO')

@section('content')
<div class="container" style="padding-top: 2rem;">
    {{-- Page Header --}}
    <div style="background: linear-gradient(135deg, rgba(30, 58, 138, 0.03) 0%, rgba(37, 99, 235, 0.05) 100%); border-bottom: 2px solid var(--color-border); padding: 3rem 2rem; border-radius: var(--radius); margin-bottom: 3rem; text-align: center;">
        <h1 style="font-size: 2.25rem; font-weight: 800; margin-bottom: 1rem; color: var(--color-primary);">
            Priorités Scientifiques
        </h1>
        <p style="font-size: 1.1rem; color: var(--color-text-muted); max-width: 800px; margin: 0 auto;">
            Axes de recherche majeurs et contributions scientifiques d'UMMISCO dans la modélisation des systèmes complexes
        </p>
    </div>

    {{-- Grid des Priorités --}}
    <div style="display: flex; flex-direction: column; gap: 3rem; margin-bottom: 4rem;">
        
        {{-- Priorité 1 : Modélisation Épidémiologique --}}
        <div class="card" style="display: grid; grid-template-columns: 80px 1fr; gap: 1.5rem; border-left: 5px solid var(--color-primary-light);">
            <div style="font-size: 3rem; text-align: center; display: flex; align-items: flex-start; justify-content: center; padding-top: 0.5rem;">
                🦠
            </div>
            <div>
                <h2 style="font-size: 1.4rem; font-weight: 700; margin-bottom: 0.75rem; color: var(--color-primary);">
                    Modélisation en Santé & Épidémiologie
                </h2>
                <p style="color: var(--color-text); margin-bottom: 1rem; font-weight: 500;">
                    L'un des piliers historiques d'UMMISCO réside dans la modélisation des maladies infectieuses émergentes et endémiques (Paludisme, Tuberculose, COVID-19, Fièvre de la Vallée du Rift).
                </p>
                <p style="color: var(--color-text-muted); font-size: 0.925rem; line-height: 1.7; margin-bottom: 1rem;">
                    Nos travaux intègrent des modèles de propagation spatiotemporelle et des outils d'aide à la décision pour les autorités sanitaires. L'objectif est d'analyser l'impact des mesures de contrôle (vaccination, lutte vectorielle, distanciation sociale) sur la dynamique des épidémies.
                </p>
                <div style="display: flex; flex-wrap: wrap; gap: 0.5rem;">
                    <span class="badge badge-primary">Maladies Vectorielles</span>
                    <span class="badge badge-primary">Aide à la Décision</span>
                    <span class="badge badge-primary">Santé Publique</span>
                </div>
            </div>
        </div>

        {{-- Priorité 2 : Environnement & Ressources Vivantes --}}
        <div class="card" style="display: grid; grid-template-columns: 80px 1fr; gap: 1.5rem; border-left: 5px solid var(--color-accent);">
            <div style="font-size: 3rem; text-align: center; display: flex; align-items: flex-start; justify-content: center; padding-top: 0.5rem;">
                🌊
            </div>
            <div>
                <h2 style="font-size: 1.4rem; font-weight: 700; margin-bottom: 0.75rem; color: var(--color-primary);">
                    Modélisation Environnementale & Ressources Vivantes
                </h2>
                <p style="color: var(--color-text); margin-bottom: 1rem; font-weight: 500;">
                    Analyser la dynamique des écosystèmes et l'exploitation des ressources naturelles face aux perturbations anthropiques et climatiques.
                </p>
                <p style="color: var(--color-text-muted); font-size: 0.925rem; line-height: 1.7; margin-bottom: 1rem;">
                    Nous développons des modèles mathématiques pour la dynamique des populations de poissons dans les zones de pêche exclusives (Sénégal, Afrique de l'Ouest), ainsi que des outils de suivi des dynamiques forestières et agro-écologiques face aux changements climatiques globaux.
                </p>
                <div style="display: flex; flex-wrap: wrap; gap: 0.5rem;">
                    <span class="badge badge-success">Pêcherie durable</span>
                    <span class="badge badge-success">Écologie Numérique</span>
                    <span class="badge badge-success">Changement Climatique</span>
                </div>
            </div>
        </div>

        {{-- Priorité 3 : Systèmes Multi-agents et Simulation --}}
        <div class="card" style="display: grid; grid-template-columns: 80px 1fr; gap: 1.5rem; border-left: 5px solid var(--color-success);">
            <div style="font-size: 3rem; text-align: center; display: flex; align-items: flex-start; justify-content: center; padding-top: 0.5rem;">
                🧬
            </div>
            <div>
                <h2 style="font-size: 1.4rem; font-weight: 700; margin-bottom: 0.75rem; color: var(--color-primary);">
                    Modélisation Multi-agents & Plateformes Complexes
                </h2>
                <p style="color: var(--color-text); margin-bottom: 1rem; font-weight: 500;">
                    Conception et développement de méthodologies de modélisation à base d'agents pour l'intégration de données géographiques et le calcul à haute performance.
                </p>
                <p style="color: var(--color-text-muted); font-size: 0.925rem; line-height: 1.7; margin-bottom: 1rem;">
                    UMMISCO est l'entité centrale derrière la plateforme GAMA (GIS-Agent Modeling & Simulation). Nous cherchons constamment à améliorer le couplage entre modèles agents et données SIG (Systèmes d'Information Géographique), facilitant ainsi l'exploration interactive de modèles.
                </p>
                <div style="display: flex; flex-wrap: wrap; gap: 0.5rem;">
                    <span class="badge badge-warning">Simulation GAMA</span>
                    <span class="badge badge-warning">Systèmes Multi-agents</span>
                    <span class="badge badge-warning">SIG Interactif</span>
                </div>
            </div>
        </div>

    </div>
</div>
@endsection
