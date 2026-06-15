@extends('layouts.app')

@section('title', 'Recherche Académique Mondiale — UMMISCO')
@section('description', 'Agrégateur de recherche sur plusieurs bases de données académiques gratuites.')

@section('content')
<div class="container" style="padding-top: 2rem;">
    <div style="background: linear-gradient(135deg, rgba(30, 58, 138, 0.03) 0%, rgba(37, 99, 235, 0.05) 100%); border-bottom: 2px solid var(--color-border); padding: 3rem 2rem; border-radius: var(--radius); margin-bottom: 2rem; text-align: center;">
        <h1 style="font-size: 2.25rem; font-weight: 800; margin-bottom: 1rem; color: var(--color-primary);">
            Recherche Académique Mondiale
        </h1>
        <p style="font-size: 1.1rem; color: var(--color-text-muted); max-width: 800px; margin: 0 auto;">
            Interrogez simultanément OpenAlex, Semantic Scholar, CORE et CrossRef pour trouver des publications en libre accès.
        </p>
    </div>

    <div class="card mb-3" style="background: white; padding: 1.5rem;">
        <form id="academic-search-form" style="display: flex; gap: 1rem; flex-wrap: wrap; align-items: flex-end;">
            <div class="form-group" style="flex: 2; min-width: 250px; margin-bottom: 0;">
                <label for="search-query">Que cherchez-vous ? (Sujet, Auteur, Titre...)</label>
                <input type="text" id="search-query" class="form-control" placeholder="Ex: Agent-based modeling..." required minlength="2">
            </div>
            
            <div style="display: flex; gap: 0.5rem; margin-bottom: 0;">
                <button type="submit" class="btn btn-primary" id="search-btn">
                    Rechercher
                </button>
            </div>
        </form>
    </div>

    <div id="loading-indicator" style="display: none; text-align: center; padding: 3rem 0;">
        <svg style="animation: spin 1s linear infinite; color: var(--color-primary-light);" xmlns="http://www.w3.org/2000/svg" width="40" height="40" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
        </svg>
        <p style="margin-top: 1rem; color: var(--color-text-muted); font-weight: 600;">Interrogation des bases mondiales en cours...</p>
    </div>

    <div id="results-container" class="grid-3" style="margin-top: 2rem;">
        <!-- Les résultats seront injectés ici par JavaScript -->
    </div>
    
    <div id="no-results" class="card text-center" style="display: none; padding: 4rem 1rem; margin-bottom: 4rem; margin-top: 2rem;">
        <div style="font-size: 3rem; margin-bottom: 1rem;">📭</div>
        <h2 style="font-size: 1.25rem; font-weight: 700; color: var(--color-primary); margin-bottom: 0.5rem;">Aucun résultat</h2>
        <p class="text-muted">Essayez d'autres mots-clés ou termes en anglais.</p>
    </div>
</div>

<style>
@keyframes spin { 100% { transform: rotate(360deg); } }
.result-card {
    background: var(--color-surface);
    border-radius: var(--radius);
    border: 1px solid var(--color-border);
    padding: 1.5rem;
    box-shadow: var(--shadow);
    display: flex;
    flex-direction: column;
    justify-content: space-between;
}
.source-badge {
    font-size: 0.75rem;
    font-weight: 700;
    padding: 0.2rem 0.6rem;
    border-radius: 999px;
    background: #f1f5f9;
    color: #475569;
}
.source-badge.openalex { background: #dbeafe; color: #1e40af; }
.source-badge.semanticscholar { background: #fef3c7; color: #92400e; }
.source-badge.core { background: #dcfce7; color: #166534; }
.source-badge.crossref { background: #fee2e2; color: #991b1b; }
</style>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('academic-search-form');
    const input = document.getElementById('search-query');
    const container = document.getElementById('results-container');
    const loading = document.getElementById('loading-indicator');
    const noResults = document.getElementById('no-results');
    const btn = document.getElementById('search-btn');

    form.addEventListener('submit', function(e) {
        e.preventDefault();
        const query = input.value.trim();
        if (query.length < 2) return;

        container.innerHTML = '';
        noResults.style.display = 'none';
        loading.style.display = 'block';
        btn.disabled = true;

        fetch(`/api/search?q=${encodeURIComponent(query)}&limit=30`)
            .then(res => res.json())
            .then(response => {
                loading.style.display = 'none';
                btn.disabled = false;

                if (!response.success || response.data.length === 0) {
                    noResults.style.display = 'block';
                    return;
                }

                response.data.forEach(item => {
                    const div = document.createElement('div');
                    div.className = 'result-card';
                    
                    const sourceClass = item.source.toLowerCase().replace(' ', '');
                    
                    let authorsHtml = '';
                    if (item.authors && item.authors.length > 0) {
                        authorsHtml = `<p style="font-size: 0.85rem; color: #64748b; margin-bottom: 0.5rem; font-weight: 600;">👥 ${item.authors.slice(0, 3).join(', ')}${item.authors.length > 3 ? ' et al.' : ''}</p>`;
                    }

                    let abstractHtml = '';
                    if (item.abstract) {
                        abstractHtml = `<p style="font-size: 0.85rem; color: #475569; margin-bottom: 1rem; line-height: 1.5;">${item.abstract.substring(0, 150)}...</p>`;
                    }

                    let pdfBtn = '';
                    if (item.pdfUrl) {
                        pdfBtn = `<a href="${item.pdfUrl}" target="_blank" class="btn btn-primary" style="font-size: 0.8rem; width: 100%; justify-content: center; padding: 0.5rem;">📄 Ouvrir le PDF</a>`;
                    } else if (item.doi) {
                        pdfBtn = `<a href="https://doi.org/${item.doi}" target="_blank" class="btn btn-outline" style="font-size: 0.8rem; width: 100%; justify-content: center; padding: 0.5rem;">🔗 Lien Éditeur</a>`;
                    } else {
                        pdfBtn = `<span class="btn btn-outline" style="font-size: 0.8rem; width: 100%; justify-content: center; padding: 0.5rem; opacity: 0.5; cursor: not-allowed;">Non disponible</span>`;
                    }

                    div.innerHTML = `
                        <div>
                            <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 0.75rem;">
                                <span class="source-badge ${sourceClass}">${item.source}</span>
                                <span style="font-size: 0.8rem; font-weight: 700; color: #94a3b8;">${item.year || ''}</span>
                            </div>
                            <h3 style="font-size: 1.05rem; font-weight: 700; color: var(--color-primary); margin-bottom: 0.5rem; line-height: 1.3;">
                                ${item.title}
                            </h3>
                            ${authorsHtml}
                            ${abstractHtml}
                        </div>
                        <div style="margin-top: 1rem;">
                            ${pdfBtn}
                        </div>
                    `;
                    container.appendChild(div);
                });
            })
            .catch(error => {
                console.error(error);
                loading.style.display = 'none';
                btn.disabled = false;
                alert("Une erreur est survenue lors de la recherche.");
            });
    });
});
</script>
@endsection
