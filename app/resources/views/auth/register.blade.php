@extends('layouts.app')

@section('title', 'Inscription — UMMISCO')

@section('styles')
<style>
    /* Full height centering for registration container */
    .register-container {
        display: flex;
        justify-content: center;
        align-items: center;
        min-height: calc(100vh - 220px);
        position: relative;
        padding: 3rem 1rem;
        z-index: 1;
    }

    /* Animated background elements */
    .register-glow-1 {
        position: absolute;
        width: 400px;
        height: 400px;
        background: radial-gradient(circle, rgba(14, 165, 233, 0.15) 0%, rgba(37, 99, 235, 0.04) 50%, transparent 70%);
        top: -5%;
        left: 10%;
        border-radius: 50%;
        z-index: -1;
        filter: blur(50px);
        animation: floatGlow1 22s infinite alternate ease-in-out;
    }

    .register-glow-2 {
        position: absolute;
        width: 450px;
        height: 450px;
        background: radial-gradient(circle, rgba(37, 99, 235, 0.12) 0%, rgba(56, 189, 248, 0.04) 50%, transparent 70%);
        bottom: -10%;
        right: 5%;
        border-radius: 50%;
        z-index: -1;
        filter: blur(60px);
        animation: floatGlow2 28s infinite alternate ease-in-out;
    }

    @keyframes floatGlow1 {
        0% { transform: translate(0, 0) scale(1); }
        100% { transform: translate(50px, 40px) scale(1.1); }
    }

    @keyframes floatGlow2 {
        0% { transform: translate(0, 0) scale(1); }
        100% { transform: translate(-40px, -50px) scale(1.05); }
    }

    /* Glassmorphic registration card */
    .register-glass-card {
        background: rgba(255, 255, 255, 0.78);
        backdrop-filter: blur(20px);
        -webkit-backdrop-filter: blur(20px);
        border: 1px solid rgba(255, 255, 255, 0.5);
        border-radius: 24px;
        padding: 3rem 2.5rem;
        width: 100%;
        max-width: 580px;
        box-shadow: 
            0 20px 40px -15px rgba(15, 23, 42, 0.08), 
            0 0 0 1px rgba(15, 23, 42, 0.02),
            inset 0 1px 0 rgba(255, 255, 255, 0.7);
        transition: transform 0.3s cubic-bezier(0.34, 1.56, 0.64, 1), box-shadow 0.3s ease;
    }

    .register-glass-card:hover {
        transform: translateY(-3px);
        box-shadow: 
            0 30px 60px -15px rgba(15, 23, 42, 0.12), 
            0 0 0 1px rgba(15, 23, 42, 0.04),
            inset 0 1px 0 rgba(255, 255, 255, 0.9);
    }

    .register-header {
        text-align: center;
        margin-bottom: 2rem;
    }

    .register-icon-wrapper {
        margin-bottom: 1rem;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 68px;
        height: 68px;
        background: linear-gradient(135deg, rgba(14, 165, 233, 0.1) 0%, rgba(37, 99, 235, 0.05) 100%);
        border: 1px solid rgba(37, 99, 235, 0.15);
        border-radius: 20px;
        font-size: 1.75rem;
        box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.8);
    }

    .register-title {
        font-family: 'Outfit', sans-serif;
        font-size: 1.75rem;
        font-weight: 800;
        background: linear-gradient(135deg, var(--color-primary) 0%, var(--color-primary-light) 60%, var(--color-accent) 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        margin: 0;
        letter-spacing: -0.02em;
    }

    .register-subtitle {
        font-size: 0.875rem;
        color: var(--color-text-muted);
        margin-top: 0.5rem;
        font-weight: 500;
        letter-spacing: 0.02em;
    }

    /* Fields layout grid */
    .form-row-custom {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 1.25rem;
    }

    .form-group-custom {
        margin-bottom: 1.25rem;
    }

    .form-label-custom {
        display: block;
        font-size: 0.75rem;
        font-weight: 700;
        color: var(--color-text);
        text-transform: uppercase;
        letter-spacing: 0.08em;
        margin-bottom: 0.4rem;
    }

    .form-input-wrapper {
        position: relative;
        display: flex;
        align-items: center;
    }

    .form-input-custom {
        width: 100%;
        height: 44px;
        padding: 0.75rem 1rem 0.75rem 2.5rem;
        border: 1.5px solid rgba(15, 23, 42, 0.08);
        border-radius: 12px;
        font-size: 0.9rem;
        font-weight: 500;
        background: rgba(255, 255, 255, 0.7);
        color: var(--color-text);
        transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
        outline: none;
    }

    .form-input-custom:focus {
        border-color: var(--color-primary-light);
        background: #ffffff;
        box-shadow: 
            0 0 0 4px rgba(37, 99, 235, 0.08), 
            0 4px 12px -2px rgba(37, 99, 235, 0.05);
    }

    .form-input-icon-custom {
        position: absolute;
        left: 0.85rem;
        color: var(--color-text-muted);
        transition: color 0.25s ease;
        pointer-events: none;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .form-input-custom:focus + .form-input-icon-custom {
        color: var(--color-primary-light);
    }

    /* Select element styling */
    select.form-input-custom {
        appearance: none;
        background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%2364748b' stroke-width='2.5' stroke-linecap='round' stroke-linejoin='round'%3e%3cpolyline points='6 9 12 15 18 9'/%3e%3c/svg%3e");
        background-repeat: no-repeat;
        background-position: right 1rem center;
        background-size: 16px;
        padding-right: 2.5rem;
    }

    /* Conditional section styles */
    .conditional-panel {
        display: none;
        border-left: 3px solid var(--color-primary-light);
        background: rgba(37, 99, 235, 0.02);
        border-radius: 0 12px 12px 0;
        padding: 1.25rem 1.25rem 0.25rem;
        margin-top: 0.5rem;
        margin-bottom: 1.25rem;
        flex-direction: column;
        animation: slideDown 0.25s cubic-bezier(0.16, 1, 0.3, 1);
    }

    @keyframes slideDown {
        from { opacity: 0; transform: translateY(-8px); }
        to { opacity: 1; transform: translateY(0); }
    }

    /* Submit action button */
    .btn-register-custom {
        width: 100%;
        height: 46px;
        background: linear-gradient(135deg, var(--color-primary) 0%, var(--color-primary-light) 100%);
        color: white;
        font-weight: 700;
        font-size: 0.95rem;
        border: none;
        border-radius: 12px;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0.75rem;
        box-shadow: 0 8px 18px -4px rgba(37, 99, 235, 0.25);
        transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
        margin-top: 0.75rem;
    }

    .btn-register-custom:hover {
        transform: translateY(-2px);
        box-shadow: 0 12px 24px -4px rgba(37, 99, 235, 0.35);
        filter: brightness(1.04);
    }

    /* Alert formatting */
    .alert-custom-container {
        background: rgba(254, 242, 242, 0.9);
        border: 1px solid rgba(239, 68, 68, 0.2);
        color: #991b1b;
        border-radius: 12px;
        padding: 1rem 1.25rem;
        margin-bottom: 1.5rem;
        font-size: 0.825rem;
        font-weight: 600;
        display: flex;
        gap: 0.75rem;
        align-items: flex-start;
    }

    .alert-custom-list {
        margin: 0;
        padding-left: 1rem;
    }

    .register-footer-links {
        margin-top: 2.25rem;
        padding-top: 1.5rem;
        border-top: 1px solid rgba(15, 23, 42, 0.06);
        text-align: center;
    }

    .register-footer-text {
        font-size: 0.85rem;
        color: var(--color-text-muted);
        margin: 0;
        font-weight: 500;
    }

    .register-footer-link {
        display: inline-block;
        margin-top: 0.5rem;
        font-weight: 700;
        font-size: 0.875rem;
        color: var(--color-primary-light);
        text-decoration: none;
        transition: color 0.2s ease;
    }

    .register-footer-link:hover {
        color: var(--color-primary);
        text-decoration: underline;
    }
</style>
@endsection

@section('content')
<div class="register-container">
    <div class="register-glow-1"></div>
    <div class="register-glow-2"></div>

    <div class="register-glass-card">
        <div class="register-header">
            <div class="register-icon-wrapper" style="background: none; border: none; box-shadow: none;">
                <img src="{{ asset('images/logo_ummisco.webp') }}" alt="UMMISCO" style="width: 80px; height: auto; filter: drop-shadow(0 8px 16px rgba(37, 99, 235, 0.15));" />
            </div>
            <h1 class="register-title">Rejoindre UMMISCO</h1>
            <p class="register-subtitle">Création de compte de recherche & administration</p>
        </div>

        @if($errors->any())
            <div class="alert-custom-container">
                <svg viewBox="0 0 24 24" width="18" height="18" stroke="currentColor" stroke-width="2.5" fill="none" style="color: #dc2626; margin-top: 0.1rem;"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>
                <ul class="alert-custom-list">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('register.post') }}" style="display: flex; flex-direction: column; gap: 1.1rem;">
            @csrf
            
            <div class="form-row-custom">
                <div class="form-group-custom">
                    <label for="prenom" class="form-label-custom">Prénom *</label>
                    <div class="form-input-wrapper">
                        <input type="text" name="prenom" id="prenom" class="form-input-custom" placeholder="Prénom" value="{{ old('prenom') }}" required>
                        <span class="form-input-icon-custom">
                            <svg viewBox="0 0 24 24" width="16" height="16" stroke="currentColor" stroke-width="2" fill="none"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                        </span>
                    </div>
                </div>
                <div class="form-group-custom">
                    <label for="nom" class="form-label-custom">Nom *</label>
                    <div class="form-input-wrapper">
                        <input type="text" name="nom" id="nom" class="form-input-custom" placeholder="Nom" value="{{ old('nom') }}" required>
                        <span class="form-input-icon-custom">
                            <svg viewBox="0 0 24 24" width="16" height="16" stroke="currentColor" stroke-width="2" fill="none"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                        </span>
                    </div>
                </div>
            </div>

            <div class="form-group-custom">
                <label for="email" class="form-label-custom">Adresse email *</label>
                <div class="form-input-wrapper">
                    <input type="email" name="email" id="email" class="form-input-custom" placeholder="exemple@ucad.edu.sn" value="{{ old('email') }}" required>
                    <span class="form-input-icon-custom">
                        <svg viewBox="0 0 24 24" width="16" height="16" stroke="currentColor" stroke-width="2" fill="none"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
                    </span>
                </div>
            </div>

            <div class="form-row-custom">
                <div class="form-group-custom">
                    <label for="password" class="form-label-custom">Mot de passe *</label>
                    <div class="form-input-wrapper">
                        <input type="password" name="password" id="password" class="form-input-custom" placeholder="••••••••" required>
                        <span class="form-input-icon-custom">
                            <svg viewBox="0 0 24 24" width="16" height="16" stroke="currentColor" stroke-width="2" fill="none"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                        </span>
                    </div>
                </div>
                <div class="form-group-custom">
                    <label for="password_confirmation" class="form-label-custom">Confirmation *</label>
                    <div class="form-input-wrapper">
                        <input type="password" name="password_confirmation" id="password_confirmation" class="form-input-custom" placeholder="••••••••" required>
                        <span class="form-input-icon-custom">
                            <svg viewBox="0 0 24 24" width="16" height="16" stroke="currentColor" stroke-width="2" fill="none"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                        </span>
                    </div>
                </div>
            </div>

            <div class="form-group-custom">
                <label for="orcid_id" class="form-label-custom">ORCID (Optionnel)</label>
                <div class="form-input-wrapper">
                    <input type="text" name="orcid_id" id="orcid_id" class="form-input-custom" placeholder="0000-0000-0000-0000" value="{{ old('orcid_id') }}" pattern="\d{4}-\d{4}-\d{4}-\d{3}[\dX]">
                    <span class="form-input-icon-custom">
                        <svg viewBox="0 0 24 24" width="16" height="16" stroke="currentColor" stroke-width="2" fill="none"><path d="M16 8a6 6 0 0 1 6 6v7h-4v-7a2 2 0 0 0-2-2 2 2 0 0 0-2 2v7h-4v-7a6 6 0 0 1 6-6z"/><rect x="2" y="9" width="4" height="12"/><circle cx="4" cy="4" r="2"/></svg>
                    </span>
                </div>
                <p style="font-size: 0.75rem; color: var(--color-text-muted); margin-top: 0.4rem; font-weight: 500;">
                    Renseignez votre ORCID pour une validation automatique du compte (si affilié à l'UMMISCO).
                </p>
            </div>

            <div class="form-group-custom">
                <label for="role" class="form-label-custom">Rôle / Profil *</label>
                <div class="form-input-wrapper">
                    <select name="role" id="role" class="form-input-custom" required>
                        <option value="" disabled selected>— Sélectionner un profil —</option>
                        <option value="researcher" {{ old('role') === 'researcher' ? 'selected' : '' }}>🔬 Chercheur</option>
                        <option value="doctoral_student" {{ old('role') === 'doctoral_student' ? 'selected' : '' }}>🎓 Doctorant</option>
                    </select>
                    <span class="form-input-icon-custom">
                        <svg viewBox="0 0 24 24" width="16" height="16" stroke="currentColor" stroke-width="2" fill="none"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                    </span>
                </div>
            </div>

            {{-- Champs Chercheur --}}
            <div id="researcher-fields" class="conditional-panel">
                <div class="form-group-custom">
                    <label for="specialite" class="form-label-custom">Spécialité *</label>
                    <div class="form-input-wrapper">
                        <input type="text" name="specialite" id="specialite" class="form-input-custom" placeholder="Ex: Modélisation épidémiologique, Data Science" value="{{ old('specialite') }}">
                        <span class="form-input-icon-custom">
                            <svg viewBox="0 0 24 24" width="16" height="16" stroke="currentColor" stroke-width="2" fill="none"><path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"/></svg>
                        </span>
                    </div>
                </div>
            </div>

            {{-- Champs Doctorant --}}
            <div id="doctoral-fields" class="conditional-panel" style="border-left-color: var(--color-accent); background: rgba(14, 165, 233, 0.02);">
                <div class="form-group-custom">
                    <label for="domaine_expertise" class="form-label-custom">Domaine d'expertise *</label>
                    <div class="form-input-wrapper">
                        <input type="text" name="domaine_expertise" id="domaine_expertise" class="form-input-custom" placeholder="Ex: IoT, Réseaux de neurones, Climat" value="{{ old('domaine_expertise') }}">
                        <span class="form-input-icon-custom">
                            <svg viewBox="0 0 24 24" width="16" height="16" stroke="currentColor" stroke-width="2" fill="none"><path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"/></svg>
                        </span>
                    </div>
                </div>
                <div class="form-group-custom" style="margin-bottom: 0.5rem;">
                    <label for="axe_principal_id" class="form-label-custom">Axe thématique principal *</label>
                    <div class="form-input-wrapper">
                        <select name="axe_principal_id" id="axe_principal_id" class="form-input-custom">
                            <option value="" disabled selected>— Choisir un axe —</option>
                            @foreach($axes as $axe)
                                <option value="{{ $axe->id }}" {{ old('axe_principal_id') === $axe->id ? 'selected' : '' }}>{{ $axe->nom_fr }} ({{ $axe->code }})</option>
                            @endforeach
                        </select>
                        <span class="form-input-icon-custom">
                            <svg viewBox="0 0 24 24" width="16" height="16" stroke="currentColor" stroke-width="2" fill="none"><polygon points="12 2 2 7 12 12 22 7 12 2"/><polyline points="2 17 12 22 22 17"/><polyline points="2 12 12 17 22 12"/></svg>
                        </span>
                    </div>
                </div>
            </div>

            <button type="submit" class="btn-register-custom">
                <span>Créer mon compte</span>
                <svg viewBox="0 0 24 24" width="18" height="18" stroke="currentColor" stroke-width="2.5" fill="none" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="5" y1="12" x2="19" y2="12"></line>
                    <polyline points="12 5 19 12 12 19"></polyline>
                </svg>
            </button>
        </form>

        <div class="register-footer-links">
            <p class="register-footer-text">
                Déjà inscrit ?
            </p>
            <a href="{{ route('login') }}" class="register-footer-link">
                Se connecter
            </a>
        </div>

    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const roleSelect = document.getElementById('role');
        const researcherFields = document.getElementById('researcher-fields');
        const doctoralFields = document.getElementById('doctoral-fields');
        
        const specInput = document.getElementById('specialite');
        const domInput = document.getElementById('domaine_expertise');
        const axeSelect = document.getElementById('axe_principal_id');

        function toggleFields(role) {
            if (role === 'researcher') {
                researcherFields.style.display = 'flex';
                doctoralFields.style.display = 'none';
                
                specInput.setAttribute('required', 'required');
                domInput.removeAttribute('required');
                axeSelect.removeAttribute('required');
            } else if (role === 'doctoral_student') {
                researcherFields.style.display = 'none';
                doctoralFields.style.display = 'flex';
                
                specInput.removeAttribute('required');
                domInput.setAttribute('required', 'required');
                axeSelect.setAttribute('required', 'required');
            } else {
                researcherFields.style.display = 'none';
                doctoralFields.style.display = 'none';
                
                specInput.removeAttribute('required');
                domInput.removeAttribute('required');
                axeSelect.removeAttribute('required');
            }
        }

        roleSelect.addEventListener('change', function() {
            toggleFields(this.value);
        });

        // Trigger on load for old value support
        if (roleSelect.value) {
            toggleFields(roleSelect.value);
        }
    });
</script>
@endsection
