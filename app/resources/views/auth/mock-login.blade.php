@extends('layouts.app')

@section('title', 'Connexion — Mode Développement | UMMISCO')

@section('content')
<div class="container" style="max-width: 500px; margin-top: 3rem;">
    <div class="card" style="text-align: center;">
        <div style="margin-bottom: 1.5rem;">
            <span style="font-size: 2.5rem;">🔐</span>
            <h1 style="font-size: 1.5rem; margin-top: 0.75rem; color: var(--primary);">Connexion — Mode Développement</h1>
            <p class="text-muted mt-1" style="font-size: 0.875rem;">
                Sélectionnez un rôle pour simuler une connexion.<br>
                <strong>Ce mode n'est disponible qu'en environnement de développement.</strong>
            </p>
        </div>

        <form method="POST" action="{{ route('auth.callback') }}">
            @csrf
            <div class="form-group" style="text-align: left;">
                <label for="role">Rôle à simuler</label>
                <select name="role" id="role" class="form-control" required>
                    <option value="" disabled selected>— Choisir un rôle —</option>
                    <option value="visitor">👁️ Visiteur</option>
                    <option value="researcher">🔬 Chercheur</option>
                    <option value="doctoral_student">🎓 Doctorant</option>
                    <option value="partner">🤝 Partenaire</option>
                    <option value="axe_admin">🛡️ Administrateur d'axe</option>
                    <option value="super_admin">⚡ Super Administrateur</option>
                </select>
            </div>

            <button type="submit" class="btn btn-primary" style="width: 100%; justify-content: center; margin-top: 0.5rem;">
                Se connecter
            </button>
        </form>

        <p class="text-muted mt-2" style="font-size: 0.75rem;">
            ℹ️ En production, ce formulaire sera remplacé par la redirection vers Keycloak.
        </p>
    </div>
</div>
@endsection
