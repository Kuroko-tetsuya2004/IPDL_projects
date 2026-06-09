@extends('layouts.app')

@section('title', 'Connexion — UMMISCO')

@section('content')
<div class="container" style="max-width: 450px; margin-top: 4rem; margin-bottom: 4rem;">
    <div class="card" style="background: rgba(255, 255, 255, 0.8); backdrop-filter: blur(24px); -webkit-backdrop-filter: blur(24px); border: 1px solid rgba(255, 255, 255, 0.6); border-radius: var(--radius); padding: 2.5rem; box-shadow: 0 20px 40px -15px rgba(15, 23, 42, 0.08), inset 0 1px 0 rgba(255, 255, 255, 0.6);">
        
        <div style="text-align: center; margin-bottom: 2rem;">
            <div style="margin-bottom: 1.25rem; display: inline-flex; align-items: center; justify-content: center;">
                <svg viewBox="0 0 100 100" style="width: 80px; height: 80px; filter: drop-shadow(0 8px 16px rgba(37, 99, 235, 0.15));" fill="none" xmlns="http://www.w3.org/2000/svg">
                  <!-- Outer rings -->
                  <circle cx="50" cy="50" r="45" stroke="url(#logo-grad-login)" stroke-width="2.5" stroke-dasharray="12 8" />
                  <circle cx="50" cy="50" r="38" stroke="url(#logo-grad2-login)" stroke-width="1.5" stroke-opacity="0.3" />
                  
                  <!-- Complexity U shape -->
                  <path d="M30 35 V60 C30 71 39 80 50 80 C61 80 70 71 70 60 V35" stroke="url(#logo-grad-login)" stroke-width="6" stroke-linecap="round" stroke-linejoin="round" />
                  <path d="M40 40 V60 C40 65 44 70 50 70 C56 70 60 65 60 60 V40" stroke="url(#logo-grad2-login)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" stroke-dasharray="4 2" />
                  
                  <!-- Dots -->
                  <circle cx="30" cy="35" r="4" fill="#0ea5e9" />
                  <circle cx="70" cy="35" r="4" fill="#0ea5e9" />
                  <circle cx="50" cy="80" r="5" fill="#1e3a8a" />
                  <circle cx="40" cy="65" r="3" fill="#ffffff" />
                  <circle cx="60" cy="65" r="3" fill="#ffffff" />
                  <circle cx="50" cy="50" r="3.5" fill="#38bdf8" />
                  
                  <!-- Links -->
                  <line x1="30" y1="35" x2="50" y2="50" stroke="#0ea5e9" stroke-width="1.5" stroke-opacity="0.6" />
                  <line x1="70" y1="35" x2="50" y2="50" stroke="#0ea5e9" stroke-width="1.5" stroke-opacity="0.6" />
                  <line x1="50" y1="50" x2="50" y2="80" stroke="#1e3a8a" stroke-width="1.5" stroke-opacity="0.6" />

                  <defs>
                    <linearGradient id="logo-grad-login" x1="0" y1="0" x2="100" y2="100" gradientUnits="userSpaceOnUse">
                      <stop offset="0%" stop-color="#0ea5e9" />
                      <stop offset="100%" stop-color="#1e3a8a" />
                    </linearGradient>
                    <linearGradient id="logo-grad2-login" x1="100" y1="0" x2="0" y2="100" gradientUnits="userSpaceOnUse">
                      <stop offset="0%" stop-color="#38bdf8" />
                      <stop offset="100%" stop-color="#2563eb" />
                    </linearGradient>
                  </defs>
                </svg>
            </div>
            <h1 style="font-size: 1.8rem; font-weight: 800; color: var(--color-primary); font-family: 'Outfit', sans-serif; margin: 0; letter-spacing: -0.02em;">Connexion</h1>
            <p style="font-size: 0.85rem; color: var(--color-text-muted); margin-top: 0.5rem; font-weight: 500;">
                Portail de Recherche UMMISCO
            </p>
        </div>

        @if($errors->any())
            <div style="background: #fef2f2; border: 1px solid #fca5a5; color: #b91c1c; border-radius: var(--radius-sm); padding: 1rem; margin-bottom: 1.5rem; font-size: 0.825rem; font-weight: 500;">
                <ul style="margin: 0; padding-left: 1.25rem;">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @if(session('success'))
            <div style="background: #ecfdf5; border: 1px solid #6ee7b7; color: #065f46; border-radius: var(--radius-sm); padding: 1rem; margin-bottom: 1.5rem; font-size: 0.825rem; font-weight: 500;">
                {{ session('success') }}
            </div>
        @endif

        <form method="POST" action="{{ route('login.post') }}" style="display: flex; flex-direction: column; gap: 1.25rem;">
            @csrf
            
            <div class="form-group" style="display: flex; flex-direction: column; gap: 0.35rem;">
                <label for="email" style="font-size: 0.775rem; font-weight: 700; color: var(--color-text); text-transform: uppercase; letter-spacing: 0.05em;">Adresse email</label>
                <input type="email" name="email" id="email" class="form-control" placeholder="exemple@ucad.edu.sn" value="{{ old('email') }}" required style="width: 100%; height: 42px; padding: 0.75rem 1rem; border-radius: var(--radius-sm); border: 1px solid var(--color-border); outline: none; transition: all 0.2s;">
            </div>

            <div class="form-group" style="display: flex; flex-direction: column; gap: 0.35rem;">
                <label for="password" style="font-size: 0.775rem; font-weight: 700; color: var(--color-text); text-transform: uppercase; letter-spacing: 0.05em;">Mot de passe</label>
                <input type="password" name="password" id="password" class="form-control" placeholder="••••••••" required style="width: 100%; height: 42px; padding: 0.75rem 1rem; border-radius: var(--radius-sm); border: 1px solid var(--color-border); outline: none; transition: all 0.2s;">
            </div>

            <button type="submit" class="btn btn-primary" style="width: 100%; justify-content: center; height: 44px; font-weight: 700; font-size: 0.9rem; border-radius: var(--radius-sm); margin-top: 0.5rem; cursor: pointer;">
                Se connecter
            </button>
        </form>

        <div style="margin-top: 2rem; padding-top: 1.5rem; border-top: 1px solid var(--color-border); text-align: center;">
            <p style="font-size: 0.85rem; color: var(--color-text-muted); margin: 0;">
                Nouveau chercheur ou doctorant ?
            </p>
            <a href="{{ route('register') }}" style="display: inline-block; margin-top: 0.5rem; font-weight: 700; font-size: 0.875rem; color: var(--color-primary-light); text-decoration: none;">
                Créer un compte de recherche
            </a>
        </div>

    </div>
</div>
@endsection
