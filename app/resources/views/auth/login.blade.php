@extends('layouts.app')

@section('title', 'Connexion — UMMISCO')

@section('styles')
<style>
    /* Full height centering for login container */
    .login-container {
        display: flex;
        justify-content: center;
        align-items: center;
        min-height: calc(100vh - 220px);
        position: relative;
        padding: 3rem 1rem;
        z-index: 1;
    }

    /* Animated background elements */
    .login-glow-1 {
        position: absolute;
        width: 350px;
        height: 350px;
        background: radial-gradient(circle, rgba(14, 165, 233, 0.15) 0%, rgba(37, 99, 235, 0.04) 50%, transparent 70%);
        top: 5%;
        left: 15%;
        border-radius: 50%;
        z-index: -1;
        filter: blur(40px);
        animation: floatGlow1 20s infinite alternate ease-in-out;
    }

    .login-glow-2 {
        position: absolute;
        width: 400px;
        height: 400px;
        background: radial-gradient(circle, rgba(37, 99, 235, 0.12) 0%, rgba(56, 189, 248, 0.04) 50%, transparent 70%);
        bottom: 10%;
        right: 10%;
        border-radius: 50%;
        z-index: -1;
        filter: blur(50px);
        animation: floatGlow2 25s infinite alternate ease-in-out;
    }

    @keyframes floatGlow1 {
        0% { transform: translate(0, 0) scale(1); }
        100% { transform: translate(40px, 30px) scale(1.1); }
    }

    @keyframes floatGlow2 {
        0% { transform: translate(0, 0) scale(1); }
        100% { transform: translate(-30px, -40px) scale(1.05); }
    }

    /* Glassmorphic login card */
    .login-glass-card {
        background: rgba(255, 255, 255, 0.75);
        backdrop-filter: blur(20px);
        -webkit-backdrop-filter: blur(20px);
        border: 1px solid rgba(255, 255, 255, 0.5);
        border-radius: 24px;
        padding: 3rem 2.5rem;
        width: 100%;
        max-width: 460px;
        box-shadow: 
            0 20px 40px -15px rgba(15, 23, 42, 0.08), 
            0 0 0 1px rgba(15, 23, 42, 0.02),
            inset 0 1px 0 rgba(255, 255, 255, 0.7);
        transition: transform 0.3s cubic-bezier(0.34, 1.56, 0.64, 1), box-shadow 0.3s ease;
    }

    .login-glass-card:hover {
        transform: translateY(-4px);
        box-shadow: 
            0 30px 60px -15px rgba(15, 23, 42, 0.12), 
            0 0 0 1px rgba(15, 23, 42, 0.04),
            inset 0 1px 0 rgba(255, 255, 255, 0.9);
    }

    /* Visual indicators/elements */
    .login-header {
        text-align: center;
        margin-bottom: 2.5rem;
    }

    .login-logo-wrapper {
        margin-bottom: 1.25rem;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        position: relative;
    }

    .login-logo-wrapper::after {
        content: '';
        position: absolute;
        width: 100px;
        height: 100px;
        background: radial-gradient(circle, rgba(14, 165, 233, 0.2) 0%, transparent 60%);
        z-index: -1;
        animation: pulseLogo 3s infinite alternate ease-in-out;
    }

    @keyframes pulseLogo {
        0% { transform: scale(0.9); opacity: 0.8; }
        100% { transform: scale(1.15); opacity: 1; }
    }

    .login-title {
        font-family: 'Outfit', sans-serif;
        font-size: 1.85rem;
        font-weight: 800;
        background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 60%, var(--accent) 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        margin: 0;
        letter-spacing: -0.02em;
    }

    .login-subtitle {
        font-size: 0.875rem;
        color: var(--text-muted);
        margin-top: 0.5rem;
        font-weight: 500;
        letter-spacing: 0.02em;
    }

    /* Enhanced forms */
    .form-group-custom {
        margin-bottom: 1.5rem;
    }

    .form-label-custom {
        display: block;
        font-size: 0.75rem;
        font-weight: 700;
        color: var(--text);
        text-transform: uppercase;
        letter-spacing: 0.08em;
        margin-bottom: 0.5rem;
        transition: color 0.2s ease;
    }

    .form-input-wrapper {
        position: relative;
        display: flex;
        align-items: center;
    }

    .form-input-custom {
        width: 100%;
        height: 46px;
        padding: 0.75rem 1rem 0.75rem 2.75rem;
        border: 1.5px solid rgba(15, 23, 42, 0.08);
        border-radius: 12px;
        font-size: 0.925rem;
        font-weight: 500;
        background: rgba(255, 255, 255, 0.7);
        color: var(--text);
        transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
        outline: none;
    }

    .form-input-custom:focus {
        border-color: var(--primary-light);
        background: #ffffff;
        box-shadow: 
            0 0 0 4px rgba(37, 99, 235, 0.08), 
            0 4px 12px -2px rgba(37, 99, 235, 0.05);
    }

    .form-input-icon-custom {
        position: absolute;
        left: 1rem;
        color: var(--text-muted);
        transition: color 0.25s ease;
        pointer-events: none;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .form-input-custom:focus + .form-input-icon-custom {
        color: var(--primary-light);
    }

    /* Submitting button with load/glow style */
    .btn-login-custom {
        width: 100%;
        height: 48px;
        background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%);
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
        margin-top: 0.5rem;
    }

    .btn-login-custom:hover {
        transform: translateY(-2px);
        box-shadow: 0 12px 24px -4px rgba(37, 99, 235, 0.35);
        filter: brightness(1.04);
    }

    .btn-login-custom:active {
        transform: translateY(0);
        box-shadow: 0 4px 12px -2px rgba(37, 99, 235, 0.2);
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
        box-shadow: 0 4px 12px rgba(239, 68, 68, 0.03);
    }

    .alert-custom-container svg {
        flex-shrink: 0;
        margin-top: 0.1rem;
    }

    .alert-custom-list {
        margin: 0;
        padding-left: 1rem;
    }

    .login-footer-links {
        margin-top: 2.25rem;
        padding-top: 1.5rem;
        border-top: 1px solid rgba(15, 23, 42, 0.06);
        text-align: center;
    }

    .login-footer-text {
        font-size: 0.85rem;
        color: var(--text-muted);
        margin: 0;
        font-weight: 500;
    }

    .login-footer-link {
        display: inline-block;
        margin-top: 0.5rem;
        font-weight: 700;
        font-size: 0.875rem;
        color: var(--primary-light);
        text-decoration: none;
        transition: color 0.2s ease;
    }

    .login-footer-link:hover {
        color: var(--primary);
        text-decoration: underline;
    }
</style>
@endsection

@section('content')
<div class="login-container">
    <div class="login-glow-1"></div>
    <div class="login-glow-2"></div>

    <div class="login-glass-card">
        <div class="login-header">
            <div class="login-logo-wrapper">
                <img src="{{ asset('images/logo_ummisco.webp') }}" alt="UMMISCO" style="width: 100px; height: auto; filter: drop-shadow(0 8px 16px rgba(37, 99, 235, 0.15));" />
            </div>
            <h1 class="login-title">Connexion</h1>
            <p class="login-subtitle">Portail de Recherche UMMISCO</p>
        </div>

        @if($errors->any())
            <div class="alert-custom-container">
                <svg viewBox="0 0 24 24" width="18" height="18" stroke="currentColor" stroke-width="2.5" fill="none" style="color: #dc2626;"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>
                <ul class="alert-custom-list">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @if(session('success'))
            <div class="alert-custom-container" style="background: rgba(236, 253, 245, 0.9); border-color: rgba(16, 185, 129, 0.2); color: #065f46;">
                <svg viewBox="0 0 24 24" width="18" height="18" stroke="currentColor" stroke-width="2.5" fill="none" style="color: #10b981;"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                <span style="font-weight: 600;">{{ session('success') }}</span>
            </div>
        @endif

        <form method="POST" action="{{ route('login.post') }}" style="display: flex; flex-direction: column; gap: 1.25rem;">
            @csrf
            
            <div class="form-group-custom">
                <label for="email" class="form-label-custom">Identifiant ou Adresse email</label>
                <div class="form-input-wrapper">
                    <input type="text" name="email" id="email" class="form-input-custom" placeholder="directeur ou exemple@ucad.edu.sn" value="{{ old('email') }}" required autocomplete="username" autofocus>
                    <span class="form-input-icon-custom">
                        <svg viewBox="0 0 24 24" width="18" height="18" stroke="currentColor" stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></circle>
                            <circle cx="12" cy="7" r="4"></circle>
                        </svg>
                    </span>
                </div>
            </div>

            <div class="form-group-custom">
                <label for="password" class="form-label-custom">Mot de passe</label>
                <div class="form-input-wrapper">
                    <input type="password" name="password" id="password" class="form-input-custom" placeholder="••••••••" required autocomplete="current-password">
                    <span class="form-input-icon-custom">
                        <svg viewBox="0 0 24 24" width="18" height="18" stroke="currentColor" stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                            <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                        </svg>
                    </span>
                </div>
            </div>

            <button type="submit" class="btn-login-custom">
                <span>Se connecter</span>
                <svg viewBox="0 0 24 24" width="18" height="18" stroke="currentColor" stroke-width="2.5" fill="none" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="5" y1="12" x2="19" y2="12"></line>
                    <polyline points="12 5 19 12 12 19"></polyline>
                </svg>
            </button>
        </form>

        <div class="login-footer-links">
            <p class="login-footer-text">
                Nouveau chercheur ou doctorant ?
            </p>
            <a href="{{ route('register') }}" class="login-footer-link">
                Créer un compte de recherche
            </a>
        </div>

    </div>
</div>
@endsection
