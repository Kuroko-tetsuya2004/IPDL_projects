@extends('layouts.app')

@section('title', 'Portail UMMISCO — Accueil')
@section('description', 'Portail web institutionnel du laboratoire UMMISCO — Recherche en modélisation des systèmes complexes, CNRS/IRD/UCAD, Dakar.')

@section('styles')
<style>
/* ── Hero ───────────────────────────────────────────────────────── */
.hero {
    position: relative;
    min-height: calc(100vh - 70px);
    display: flex;
    align-items: center;
    justify-content: center;
    overflow: hidden;
    background: var(--bg);
}

.hero-bg {
    position: absolute;
    inset: 0;
    z-index: 0;
}

/* ── Background Photo Layer ─────────────────────────────────────── */
.hero-photo {
    position: absolute;
    inset: 0;
    background-size: cover;
    background-position: center;
    background-repeat: no-repeat;
    transition: opacity 0.6s ease;
}

/* Light mode photo — photo de recherche africaine (Unsplash) */
.hero-photo-light {
    background-image: url('{{ asset('images/hero_research.jpg') }}');
    opacity: 0.22;
    background-position: center 40%;
}

/* Dark mode photo — réseau de données (Unsplash) */
.hero-photo-dark {
    background-image: url('{{ asset('images/hero_network.jpg') }}');
    opacity: 0;
    background-position: center;
}

[data-theme="dark"] .hero-photo-light { opacity: 0; }
[data-theme="dark"] .hero-photo-dark  { opacity: 0.22; }

/* Overlay gradient for text legibility */
.hero-overlay {
    position: absolute;
    inset: 0;
    background: linear-gradient(
        to bottom,
        var(--bg) 0%,
        rgba(var(--bg-rgb, 245, 247, 250), 0.45) 30%,
        rgba(var(--bg-rgb, 245, 247, 250), 0.45) 70%,
        var(--bg) 100%
    );
    transition: background 0.35s ease;
}

[data-theme="dark"] .hero-overlay {
    background: linear-gradient(
        to bottom,
        rgba(3, 7, 18, 0.92) 0%,
        rgba(3, 7, 18, 0.55) 30%,
        rgba(3, 7, 18, 0.55) 70%,
        rgba(3, 7, 18, 0.95) 100%
    );
}

/* Animated mesh grid on top of photo */
.hero-grid {
    position: absolute;
    inset: 0;
    background-image:
        linear-gradient(var(--border) 1px, transparent 1px),
        linear-gradient(90deg, var(--border) 1px, transparent 1px);
    background-size: 60px 60px;
    mask-image: radial-gradient(ellipse 80% 60% at 50% 50%, black 40%, transparent 100%);
    opacity: 0.35;
}

/* Glow orbs */
.hero-orb {
    position: absolute;
    border-radius: 50%;
    filter: blur(90px);
    pointer-events: none;
    animation: orb-float 8s ease-in-out infinite;
}

.hero-orb-1 {
    width: 600px; height: 600px;
    background: radial-gradient(circle, rgba(37,99,235,0.15) 0%, transparent 70%);
    top: -10%; left: -10%;
    animation-delay: 0s;
}

.hero-orb-2 {
    width: 500px; height: 500px;
    background: radial-gradient(circle, rgba(14,165,233,0.12) 0%, transparent 70%);
    bottom: -5%; right: -5%;
    animation-delay: 3s;
}

.hero-orb-3 {
    width: 300px; height: 300px;
    background: radial-gradient(circle, rgba(124,58,237,0.1) 0%, transparent 70%);
    top: 30%; right: 20%;
    animation-delay: 5s;
}

[data-theme="dark"] .hero-orb-1 { background: radial-gradient(circle, rgba(59,130,246,0.25) 0%, transparent 70%); }
[data-theme="dark"] .hero-orb-2 { background: radial-gradient(circle, rgba(56,189,248,0.2) 0%, transparent 70%); }
[data-theme="dark"] .hero-orb-3 { background: radial-gradient(circle, rgba(167,139,250,0.15) 0%, transparent 70%); }

@keyframes orb-float {
    0%, 100% { transform: translate(0, 0) scale(1); }
    33%       { transform: translate(20px, -20px) scale(1.05); }
    66%       { transform: translate(-15px, 15px) scale(0.97); }
}

.hero-content {
    position: relative;
    z-index: 1;
    text-align: center;
    padding: 3rem 1.5rem;
    max-width: 1000px;
    margin: 0 auto;
}

.hero-eyebrow {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 0.72rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.2em;
    color: var(--primary-light);
    background: var(--primary-glow);
    border: 1px solid rgba(37,99,235,0.2);
    border-radius: 9999px;
    padding: 0.35rem 1rem;
    margin-bottom: 2rem;
}

.hero-eyebrow .dot {
    width: 6px; height: 6px;
    border-radius: 50%;
    background: var(--accent);
    box-shadow: 0 0 8px var(--accent-glow);
    animation: pulse-dot 2s ease-in-out infinite;
}

.hero-title {
    font-family: 'Inter', sans-serif;
    font-size: clamp(3rem, 8vw, 6rem);
    font-weight: 900;
    letter-spacing: -0.04em;
    line-height: 1.05;
    margin-bottom: 1.5rem;
    color: var(--text);
}

.hero-title .accent {
    background: linear-gradient(135deg, var(--primary-light) 0%, var(--accent) 60%, #818cf8 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}

.hero-subtitle {
    font-size: clamp(1rem, 2.5vw, 1.2rem);
    color: var(--text-muted);
    max-width: 700px;
    margin: 0 auto 2.5rem;
    line-height: 1.7;
    font-weight: 400;
}

.hero-tagline {
    font-size: 0.8rem;
    font-weight: 700;
    color: var(--text-subtle);
    text-transform: uppercase;
    letter-spacing: 0.15em;
    margin-bottom: 3rem;
}

.hero-actions {
    display: flex;
    gap: 1rem;
    justify-content: center;
    flex-wrap: wrap;
    margin-bottom: 4rem;
}

.hero-actions .btn-hero-primary {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.875rem 2rem;
    border-radius: var(--radius);
    font-size: 0.95rem;
    font-weight: 700;
    background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%);
    color: #fff;
    text-decoration: none;
    box-shadow: 0 8px 30px var(--primary-glow);
    border: none;
    transition: var(--transition-spring);
    cursor: pointer;
    font-family: inherit;
}

.hero-actions .btn-hero-primary:hover {
    transform: translateY(-2px) scale(1.02);
    box-shadow: 0 12px 40px var(--primary-glow);
}

.hero-actions .btn-hero-secondary {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.875rem 2rem;
    border-radius: var(--radius);
    font-size: 0.95rem;
    font-weight: 600;
    background: var(--surface);
    color: var(--text);
    text-decoration: none;
    border: 1.5px solid var(--border-strong);
    transition: var(--transition);
}

.hero-actions .btn-hero-secondary:hover {
    border-color: var(--primary-light);
    color: var(--primary-light);
    background: var(--primary-glow);
    transform: translateY(-1px);
}

/* ── Stats ──────────────────────────────────────────────────────── */
.stats-bar {
    display: flex;
    justify-content: center;
    gap: 0;
    border: 1px solid var(--border-strong);
    border-radius: var(--radius-lg);
    background: var(--surface);
    backdrop-filter: blur(8px);
    max-width: 800px;
    margin: 0 auto;
    overflow: hidden;
    box-shadow: var(--shadow-md);
}

.stat-item {
    flex: 1;
    padding: 1.5rem 1rem;
    text-align: center;
    position: relative;
    transition: background 0.2s ease;
}

.stat-item:not(:last-child)::after {
    content: '';
    position: absolute;
    right: 0; top: 20%; bottom: 20%;
    width: 1px;
    background: var(--border-strong);
}

.stat-item:hover { background: var(--surface-alt); }

.stat-number {
    font-size: 2rem;
    font-weight: 800;
    letter-spacing: -0.03em;
    line-height: 1;
    margin-bottom: 0.25rem;
}

.stat-label {
    font-size: 0.72rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.1em;
    color: var(--text-subtle);
}

/* ── Section Header ─────────────────────────────────────────────── */
.section-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-end;
    margin-bottom: 2.5rem;
    gap: 1rem;
    flex-wrap: wrap;
}

.section-title {
    font-size: 1.85rem;
    font-weight: 800;
    letter-spacing: -0.025em;
    color: var(--text);
}

.section-title span { color: var(--primary-light); }

/* ── Axes Cards ─────────────────────────────────────────────────── */
.axe-card {
    background: var(--card-bg);
    border-radius: var(--radius);
    border: 1px solid var(--border);
    padding: 1.75rem;
    box-shadow: var(--shadow);
    transition: box-shadow 0.3s ease, transform 0.3s ease, border-color 0.3s ease;
    display: flex;
    flex-direction: column;
    height: 100%;
    position: relative;
    overflow: hidden;
}

.axe-card::before {
    content: '';
    position: absolute;
    top: 0; left: 0; right: 0;
    height: 3px;
    background: var(--axe-color, var(--primary-light));
    border-radius: var(--radius) var(--radius) 0 0;
}

.axe-card::after {
    content: '';
    position: absolute;
    bottom: 0; right: 0;
    width: 100px; height: 100px;
    border-radius: 50%;
    background: var(--axe-color, var(--primary-light));
    opacity: 0.04;
    transform: translate(30%, 30%);
    transition: transform 0.4s ease, opacity 0.4s ease;
}

.axe-card:hover {
    transform: translateY(-4px);
    box-shadow: var(--shadow-lg);
    border-color: var(--border-strong);
}

.axe-card:hover::after {
    transform: translate(20%, 20%) scale(1.3);
    opacity: 0.07;
}

.axe-code-badge {
    display: inline-flex;
    align-items: center;
    padding: 0.3rem 0.75rem;
    border-radius: 9999px;
    font-size: 0.72rem;
    font-weight: 800;
    letter-spacing: 0.08em;
    text-transform: uppercase;
    margin-bottom: 1rem;
    border: 1px solid;
}

/* ── Pub Cards (Home) ───────────────────────────────────────────── */
.pub-card-home {
    background: var(--card-bg);
    border-radius: var(--radius);
    border: 1px solid var(--border);
    padding: 1.5rem;
    box-shadow: var(--shadow);
    transition: var(--transition);
    display: flex;
    flex-direction: column;
    height: 100%;
    cursor: default;
}

.pub-card-home:hover {
    transform: translateY(-3px);
    box-shadow: var(--shadow-md);
    border-color: var(--border-strong);
}

</style>
@endsection

@section('content')

{{-- ═══ HERO ═══ --}}
<section class="hero">
    <div class="hero-bg">
        {{-- Photo backgrounds (dark/light adaptive) --}}
        <div class="hero-photo hero-photo-light"></div>
        <div class="hero-photo hero-photo-dark"></div>
        {{-- Overlay for text legibility --}}
        <div class="hero-overlay"></div>
        {{-- Grid + orbs on top --}}
        <div class="hero-grid"></div>
        <div class="hero-orb hero-orb-1"></div>
        <div class="hero-orb hero-orb-2"></div>
        <div class="hero-orb hero-orb-3"></div>
    </div>

    <div class="hero-content">
        <div class="animate-in">
            <span class="hero-eyebrow">
                <span class="dot"></span>
                Laboratoire de recherche · Dakar, Sénégal
            </span>
        </div>

        <h1 class="hero-title animate-in delay-1">
            La Science des<br>
            <span class="accent">Systèmes Complexes</span>
        </h1>

        <p class="hero-subtitle animate-in delay-2">
            UMMISCO est une unité de recherche internationale pionnière en modélisation mathématique,
            informatique et numérique des systèmes complexes — portée par le CNRS, l'IRD et l'UCAD.
        </p>

        <p class="hero-tagline animate-in delay-2">CNRS · IRD · UCAD</p>

        <div class="hero-actions animate-in delay-3">
            <a href="{{ route('publications') }}" class="btn-hero-primary">
                <svg viewBox="0 0 24 24" width="16" height="16" stroke="currentColor" stroke-width="2.5" fill="none">
                    <path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/>
                </svg>
                Explorer les publications
            </a>
            <a href="{{ route('axes') }}" class="btn-hero-secondary">
                <svg viewBox="0 0 24 24" width="16" height="16" stroke="currentColor" stroke-width="2.5" fill="none">
                    <circle cx="12" cy="12" r="2"/><path d="M12 2v3m0 14v3M2 12h3m14 0h3"/>
                    <path d="m4.93 4.93 2.12 2.12m9.9 9.9 2.12 2.12M4.93 19.07l2.12-2.12m9.9-9.9 2.12-2.12"/>
                </svg>
                Nos axes de recherche
            </a>
        </div>

        {{-- Stats Bar --}}
        @if($stats ?? null)
        <div class="stats-bar animate-in delay-4">
            <div class="stat-item">
                <div class="stat-number text-gradient" data-count="{{ $stats->total_publications ?? 0 }}">0</div>
                <div class="stat-label">Publications</div>
            </div>
            <div class="stat-item">
                <div class="stat-number" style="color: var(--success);" data-count="{{ $stats->total_chercheurs ?? 0 }}">0</div>
                <div class="stat-label">Chercheurs</div>
            </div>
            <div class="stat-item">
                <div class="stat-number" style="color: var(--accent);" data-count="{{ $stats->total_doctorants ?? 0 }}">0</div>
                <div class="stat-label">Doctorants</div>
            </div>
            <div class="stat-item">
                <div class="stat-number" style="color: var(--purple);" data-count="{{ $stats->total_axes ?? 0 }}">0</div>
                <div class="stat-label">Axes</div>
            </div>
            <div class="stat-item">
                <div class="stat-number" style="color: var(--warning);" data-count="{{ $stats->total_datasets ?? 0 }}">0</div>
                <div class="stat-label">Datasets</div>
            </div>
        </div>
        @endif
    </div>
</section>

{{-- ═══ AXES DE RECHERCHE ═══ --}}
<section style="
    padding: 6rem 0;
    border-top: 1px solid var(--border);
    border-bottom: 1px solid var(--border);
    position: relative;
    overflow: hidden;
">
    {{-- Background image with adaptive overlay --}}
    <div style="
        position: absolute; inset: 0;
        background-image: url('{{ asset('images/section_lab.jpg') }}');
        background-size: cover;
        background-position: center;
        opacity: 0.12;
        transition: opacity 0.35s ease;
    " class="section-bg-photo"></div>
    <style>[data-theme='dark'] .section-bg-photo { opacity: 0.08; filter: brightness(0.6) saturate(0.5); }</style>
    <div style="position: absolute; inset: 0; background: var(--bg-secondary); opacity: 0.85; transition: opacity 0.35s;"></div>
    <div class="container" style="position:relative;z-index:1;">
        <div class="section-header">
            <div>
                <div class="section-label">Notre expertise</div>
                <h2 class="section-title">Axes de <span>Recherche</span></h2>
            </div>
            <a href="{{ route('axes') }}" class="btn btn-outline" style="font-size:0.85rem;">
                Voir tous les axes
                <svg viewBox="0 0 24 24" width="14" height="14" stroke="currentColor" stroke-width="2.5" fill="none"><path d="m9 18 6-6-6-6"/></svg>
            </a>
        </div>

        <div class="grid-3">
            @foreach($axes as $axe)
            <div class="axe-card" style="--axe-color: {{ $axe->couleur_hex ?? '#2563eb' }};">
                <span class="axe-code-badge" style="background: {{ ($axe->couleur_hex ?? '#2563eb') }}18; color: {{ $axe->couleur_hex ?? '#2563eb' }}; border-color: {{ ($axe->couleur_hex ?? '#2563eb') }}30;">
                    {{ $axe->code }}
                </span>
                <h3 style="font-size: 1.05rem; font-weight: 700; color: var(--text); margin-bottom: 0.5rem; line-height: 1.35;">
                    {{ $axe->nom_fr }}
                </h3>
                @if($axe->nom_en)
                    <p style="font-size: 0.78rem; color: var(--text-subtle); font-style: italic; margin-bottom: 0.75rem;">{{ $axe->nom_en }}</p>
                @endif
                <p style="font-size: 0.85rem; color: var(--text-muted); line-height: 1.65; flex-grow: 1;">
                    {{ Str::limit($axe->description_fr, 140) }}
                </p>
            </div>
            @endforeach
        </div>
    </div>
</section>

{{-- ═══ PUBLICATIONS RÉCENTES ═══ --}}
@if($recentPublications->count() > 0)
<section style="padding: 6rem 0;">
    <div class="container">
        <div class="section-header">
            <div>
                <div class="section-label">Dernières parutions</div>
                <h2 class="section-title">Publications <span>Récentes</span></h2>
            </div>
            <a href="{{ route('publications') }}" class="btn btn-outline" style="font-size:0.85rem;">
                Catalogue complet
                <svg viewBox="0 0 24 24" width="14" height="14" stroke="currentColor" stroke-width="2.5" fill="none"><path d="m9 18 6-6-6-6"/></svg>
            </a>
        </div>

        <div class="grid-3">
            @foreach($recentPublications as $pub)
            @php
            $typeLabels = [
                'article' => 'Article', 'document' => 'Recherche en cours',
                'event' => 'Événement', 'dataset' => 'Dataset', 'news' => 'Actualité',
                'thesis' => 'Thèse', 'report' => 'Rapport', 'presentation' => 'Présentation'
            ];
            @endphp
            <div class="pub-card-home">
                <div style="display:flex; align-items:center; gap:0.5rem; margin-bottom:0.875rem;">
                    <span class="badge badge-primary">{{ $typeLabels[$pub->type] ?? ucfirst($pub->type) }}</span>
                    @if($pub->axe)
                        <span class="badge" style="background:{{ $pub->axe->couleur_hex ?? '#2563eb' }}14; color:{{ $pub->axe->couleur_hex ?? '#2563eb' }}; border-color:{{ $pub->axe->couleur_hex ?? '#2563eb' }}30; font-size:0.65rem;">
                            {{ $pub->axe->code }}
                        </span>
                    @endif
                </div>
                <h3 style="font-size:0.95rem; font-weight:700; color:var(--text); margin-bottom:0.625rem; line-height:1.4; flex-grow:1;">
                    <a href="{{ route('publications.show', $pub->id) }}" style="text-decoration:none; color:inherit; transition:color 0.2s;">
                        {{ Str::limit($pub->titre_fr, 90) }}
                    </a>
                </h3>
                @if($pub->resume_fr)
                    <p style="font-size:0.82rem; color:var(--text-muted); line-height:1.6; margin-bottom:1rem;">
                        {{ Str::limit($pub->resume_fr, 110) }}
                    </p>
                @endif
                <div style="border-top:1px solid var(--border); padding-top:0.75rem; display:flex; justify-content:space-between; align-items:center; font-size:0.75rem; color:var(--text-subtle); margin-top:auto;">
                    <span style="font-weight:600;">
                        👤 {{ $pub->auteur->prenom ?? '' }} {{ $pub->auteur->nom ?? '' }}
                    </span>
                    @if($pub->date_publication)
                        <span>{{ $pub->date_publication->format('d/m/Y') }}</span>
                    @endif
                </div>
            </div>
            @endforeach
        </div>
    </div>
</section>
@endif

@endsection

@section('scripts')
<script>
// Count-up animation for statistics
document.addEventListener('DOMContentLoaded', function() {
    const counters = document.querySelectorAll('[data-count]');
    if (!counters.length) return;

    const observer = new IntersectionObserver(function(entries) {
        entries.forEach(function(entry) {
            if (entry.isIntersecting) {
                const el = entry.target;
                const target = parseInt(el.getAttribute('data-count'), 10);
                if (isNaN(target) || target === 0) { el.textContent = '0'; return; }
                let start = 0;
                const duration = 1600;
                const step = target / (duration / 16);
                const timer = setInterval(function() {
                    start = Math.min(start + step, target);
                    el.textContent = Math.round(start).toLocaleString('fr-FR');
                    if (start >= target) clearInterval(timer);
                }, 16);
                observer.unobserve(el);
            }
        });
    }, { threshold: 0.5 });

    counters.forEach(function(el) { observer.observe(el); });
});
</script>
@endsection
