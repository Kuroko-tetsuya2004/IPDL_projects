@extends('layouts.app')

@section('title', 'Autres Productions Scientifiques — UMMISCO')

@section('content')
<div class="container" style="padding-top: 2rem;">
    {{-- Page Header --}}
    <div style="background: linear-gradient(135deg, rgba(30, 58, 138, 0.03) 0%, rgba(37, 99, 235, 0.05) 100%); border-bottom: 2px solid var(--border); padding: 3rem 2rem; border-radius: var(--radius); margin-bottom: 3rem; text-align: center;">
        <h1 style="font-size: 2.25rem; font-weight: 800; margin-bottom: 1rem; color: var(--primary);">
            Autres Productions du Laboratoire
        </h1>
        <p style="font-size: 1.1rem; color: var(--text-muted); max-width: 800px; margin: 0 auto;">
            Logiciels, rapports techniques d'expertise, brevets, et vulgarisation scientifique
        </p>
    </div>

    <div class="grid-2" style="margin-bottom: 4rem;">
        
        {{-- Logiciels --}}
        <div class="card" style="border-top: 4px solid var(--primary-light);">
            <div style="font-size: 2rem; margin-bottom: 0.75rem;">💾</div>
            <h3 style="font-size: 1.2rem; margin-bottom: 0.75rem; color: var(--primary);">Logiciels & Outils</h3>
            <p style="font-size: 0.9rem; color: var(--text-muted); line-height: 1.6; margin-bottom: 1.25rem;">
                En dehors de la plateforme GAMA, l'UMMISCO développe et distribue de nombreux packages, bibliothèques (R, Python, C++) et utilitaires pour l'analyse des réseaux complexes et l'intégration des flux de données géographiques massifs.
            </p>
            <div style="display: flex; gap: 0.5rem;">
                <span class="badge badge-primary">GitHub</span>
                <span class="badge badge-primary">R-Packages</span>
                <span class="badge badge-primary">Open-Source</span>
            </div>
        </div>

        {{-- Expertise & Rapports --}}
        <div class="card" style="border-top: 4px solid var(--accent);">
            <div style="font-size: 2rem; margin-bottom: 0.75rem;">📊</div>
            <h3 style="font-size: 1.2rem; margin-bottom: 0.75rem; color: var(--primary);">Expertise & Décision</h3>
            <p style="font-size: 0.9rem; color: var(--text-muted); line-height: 1.6; margin-bottom: 1.25rem;">
                Le laboratoire réalise des missions d'expertise pour des organismes nationaux et internationaux (Ministères de la Santé, Agences Environnementales, UNESCO, Banque Mondiale) afin de formuler des recommandations stratégiques.
            </p>
            <div style="display: flex; gap: 0.5rem;">
                <span class="badge badge-success">Rapports d'expertise</span>
                <span class="badge badge-success">Consulting public</span>
            </div>
        </div>

    </div>
</div>
@endsection
