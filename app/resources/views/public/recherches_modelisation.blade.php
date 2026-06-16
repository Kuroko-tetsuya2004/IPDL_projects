@extends('layouts.app')

@section('title', 'Modélisation à base d\'agents — UMMISCO')

@section('content')
<div class="container" style="padding-top: 2rem;">
    {{-- Page Header --}}
    <div style="background: linear-gradient(135deg, rgba(30, 58, 138, 0.03) 0%, rgba(37, 99, 235, 0.05) 100%); border-bottom: 2px solid var(--border); padding: 3rem 2rem; border-radius: var(--radius); margin-bottom: 3rem; text-align: center;">
        <h1 style="font-size: 2.25rem; font-weight: 800; margin-bottom: 1rem; color: var(--primary);">
            Modélisation à base d'agents
        </h1>
        <p style="font-size: 1.1rem; color: var(--text-muted); max-width: 800px; margin: 0 auto;">
            Simuler des systèmes complexes par l'interaction d'entités autonomes géoréférencées
        </p>
    </div>

    <div class="grid-2" style="margin-bottom: 4rem; align-items: center;">
        <div>
            <h2 style="font-size: 1.5rem; margin-bottom: 1rem; color: var(--primary);">
                Principes et Méthodologies
            </h2>
            <p style="margin-bottom: 1.25rem; color: var(--text); font-weight: 500;">
                La modélisation à base d'agents (SMA) permet de modéliser des systèmes complexes en simulant les actions et interactions d'individus ou d'entités autonomes afin d'évaluer leurs effets sur le système global.
            </p>
            <p style="color: var(--text-muted); font-size: 0.95rem; line-height: 1.7;">
                Contrairement aux approches macroscopiques basées sur des équations globales, les modèles d'agents se focalisent sur le niveau micro. Chaque individu (agent) suit des règles simples de comportement, communique avec d'autres agents, et évolue dans un environnement partagé (généralement spatialisé avec des cartes réelles). Ces interactions font émerger des structures complexes auto-organisées à l'échelle globale.
            </p>
        </div>
        <div class="card" style="border-left: 5px solid var(--primary-light);">
            <h3 style="font-size: 1.25rem; margin-bottom: 0.75rem; color: var(--primary);">La Plateforme GAMA</h3>
            <p style="font-size: 0.9rem; color: var(--text-muted); line-height: 1.7; margin-bottom: 1.25rem;">
                Développée en grande partie par les chercheurs de l'UMMISCO, la plateforme de simulation <strong>GAMA</strong> (GIS Agent-based Modeling Architecture) est un outil de simulation multi-agents open-source très puissant doté d'une intégration forte des données géographiques (SIG).
            </p>
            <div style="display: flex; gap: 0.75rem;">
                <a href="https://gama-platform.org" target="_blank" class="btn btn-primary" style="font-size: 0.8rem; padding: 0.5rem 1rem;">Accéder au site de GAMA</a>
                <span class="badge badge-primary" style="display: inline-flex; align-items: center;">Open Source</span>
            </div>
        </div>
    </div>

    {{-- Thèmes d'application --}}
    <div style="margin-bottom: 4rem;">
        <h2 style="font-size: 1.5rem; margin-bottom: 2rem; text-align: center; color: var(--primary);">Principales Applications chez UMMISCO</h2>
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 1.5rem;">
            <div class="card">
                <h3 style="font-size: 1.1rem; margin-bottom: 0.5rem; color: var(--primary-light);">🏙️ Gestion des Risques Urbains</h3>
                <p style="font-size: 0.875rem; color: var(--text-muted); line-height: 1.6;">
                    Simulation d'évacuations de populations face à des inondations urbaines, modélisation du trafic routier et de la gestion de crise en temps réel.
                </p>
            </div>
            <div class="card">
                <h3 style="font-size: 1.1rem; margin-bottom: 0.5rem; color: var(--primary-light);">🌲 Agro-écosystèmes & Gestion de l'Eau</h3>
                <p style="font-size: 0.875rem; color: var(--text-muted); line-height: 1.6;">
                    Modélisation des comportements d'agriculteurs face à la rareté de l'eau et aux contraintes climatiques pour évaluer les politiques de répartition.
                </p>
            </div>
            <div class="card">
                <h3 style="font-size: 1.1rem; margin-bottom: 0.5rem; color: var(--primary-light);">🦠 Propagation Épidémique Spatiale</h3>
                <p style="font-size: 0.875rem; color: var(--text-muted); line-height: 1.6;">
                    Couplage de la mobilité individuelle des agents avec des modèles épidémiologiques pour tester l'impact de confinements ciblés ou de campagnes de dépistage.
                </p>
            </div>
        </div>
    </div>
</div>
@endsection
