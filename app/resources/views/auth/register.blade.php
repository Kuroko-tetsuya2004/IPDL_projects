@extends('layouts.app')

@section('title', 'Inscription de Recherche — UMMISCO')

@section('content')
<div class="container" style="max-width: 550px; margin-top: 3rem; margin-bottom: 4rem;">
    <div class="card" style="background: white; border: 1px solid var(--color-border); border-radius: var(--radius); padding: 2.5rem; box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.05);">
        
        <div style="text-align: center; margin-bottom: 2rem;">
            <div style="width: 60px; height: 60px; background: var(--color-accent); color: white; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; font-size: 1.75rem; margin-bottom: 1rem; box-shadow: 0 4px 12px rgba(16, 185, 129, 0.2);">
                🔬
            </div>
            <h1 style="font-size: 1.6rem; font-weight: 800; color: var(--color-primary); font-family: 'Outfit', sans-serif; margin: 0;">Rejoindre UMMISCO</h1>
            <p style="font-size: 0.85rem; color: var(--color-text-muted); margin-top: 0.5rem;">
                Création de compte chercheur ou doctorant
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

        <form method="POST" action="{{ route('register.post') }}" style="display: flex; flex-direction: column; gap: 1.25rem;">
            @csrf
            
            <div style="display: grid; grid-template-cols: 1fr 1fr; gap: 1rem; grid-auto-flow: column;">
                <div class="form-group" style="display: flex; flex-direction: column; gap: 0.35rem;">
                    <label for="prenom" style="font-size: 0.75rem; font-weight: 700; color: var(--color-text); text-transform: uppercase; letter-spacing: 0.05em;">Prénom *</label>
                    <input type="text" name="prenom" id="prenom" class="form-control" placeholder="Prénom" value="{{ old('prenom') }}" required style="width: 100%; height: 40px; padding: 0.5rem 0.75rem; border-radius: var(--radius-sm); border: 1px solid var(--color-border); outline: none;">
                </div>
                <div class="form-group" style="display: flex; flex-direction: column; gap: 0.35rem;">
                    <label for="nom" style="font-size: 0.75rem; font-weight: 700; color: var(--color-text); text-transform: uppercase; letter-spacing: 0.05em;">Nom *</label>
                    <input type="text" name="nom" id="nom" class="form-control" placeholder="Nom" value="{{ old('nom') }}" required style="width: 100%; height: 40px; padding: 0.5rem 0.75rem; border-radius: var(--radius-sm); border: 1px solid var(--color-border); outline: none;">
                </div>
            </div>

            <div class="form-group" style="display: flex; flex-direction: column; gap: 0.35rem;">
                <label for="email" style="font-size: 0.75rem; font-weight: 700; color: var(--color-text); text-transform: uppercase; letter-spacing: 0.05em;">Adresse email *</label>
                <input type="email" name="email" id="email" class="form-control" placeholder="exemple@ucad.edu.sn" value="{{ old('email') }}" required style="width: 100%; height: 40px; padding: 0.5rem 0.75rem; border-radius: var(--radius-sm); border: 1px solid var(--color-border); outline: none;">
            </div>

            <div style="display: grid; grid-template-cols: 1fr 1fr; gap: 1rem; grid-auto-flow: column;">
                <div class="form-group" style="display: flex; flex-direction: column; gap: 0.35rem;">
                    <label for="password" style="font-size: 0.75rem; font-weight: 700; color: var(--color-text); text-transform: uppercase; letter-spacing: 0.05em;">Mot de passe *</label>
                    <input type="password" name="password" id="password" class="form-control" placeholder="••••••••" required style="width: 100%; height: 40px; padding: 0.5rem 0.75rem; border-radius: var(--radius-sm); border: 1px solid var(--color-border); outline: none;">
                </div>
                <div class="form-group" style="display: flex; flex-direction: column; gap: 0.35rem;">
                    <label for="password_confirmation" style="font-size: 0.75rem; font-weight: 700; color: var(--color-text); text-transform: uppercase; letter-spacing: 0.05em;">Confirmation *</label>
                    <input type="password" name="password_confirmation" id="password_confirmation" class="form-control" placeholder="••••••••" required style="width: 100%; height: 40px; padding: 0.5rem 0.75rem; border-radius: var(--radius-sm); border: 1px solid var(--color-border); outline: none;">
                </div>
            </div>

            <div class="form-group" style="display: flex; flex-direction: column; gap: 0.35rem;">
                <label for="role" style="font-size: 0.75rem; font-weight: 700; color: var(--color-text); text-transform: uppercase; letter-spacing: 0.05em;">Rôle / Profil *</label>
                <select name="role" id="role" class="form-control" required style="width: 100%; height: 40px; padding: 0.5rem 0.75rem; border-radius: var(--radius-sm); border: 1px solid var(--color-border); outline: none; background: white;">
                    <option value="" disabled selected>— Sélectionner un profil —</option>
                    <option value="researcher" {{ old('role') === 'researcher' ? 'selected' : '' }}>🔬 Chercheur</option>
                    <option value="doctoral_student" {{ old('role') === 'doctoral_student' ? 'selected' : '' }}>🎓 Doctorant</option>
                </select>
            </div>

            {{-- Champs Chercheur --}}
            <div id="researcher-fields" style="display: none; border-left: 3px solid var(--color-primary-light); padding-left: 1rem; margin-top: 0.5rem; flex-direction: column; gap: 1rem;">
                <div class="form-group" style="display: flex; flex-direction: column; gap: 0.35rem;">
                    <label for="specialite" style="font-size: 0.75rem; font-weight: 700; color: var(--color-text); text-transform: uppercase; letter-spacing: 0.05em;">Spécialité *</label>
                    <input type="text" name="specialite" id="specialite" class="form-control" placeholder="Ex: Modélisation épidémiologique, Data Science" value="{{ old('specialite') }}" style="width: 100%; height: 40px; padding: 0.5rem 0.75rem; border-radius: var(--radius-sm); border: 1px solid var(--color-border); outline: none;">
                </div>
            </div>

            {{-- Champs Doctorant --}}
            <div id="doctoral-fields" style="display: none; border-left: 3px solid var(--color-accent); padding-left: 1rem; margin-top: 0.5rem; flex-direction: column; gap: 1rem;">
                <div class="form-group" style="display: flex; flex-direction: column; gap: 0.35rem;">
                    <label for="domaine_expertise" style="font-size: 0.75rem; font-weight: 700; color: var(--color-text); text-transform: uppercase; letter-spacing: 0.05em;">Domaine d'expertise *</label>
                    <input type="text" name="domaine_expertise" id="domaine_expertise" class="form-control" placeholder="Ex: IoT, Réseaux de neurones, Climat" value="{{ old('domaine_expertise') }}" style="width: 100%; height: 40px; padding: 0.5rem 0.75rem; border-radius: var(--radius-sm); border: 1px solid var(--color-border); outline: none;">
                </div>
                <div class="form-group" style="display: flex; flex-direction: column; gap: 0.35rem;">
                    <label for="axe_principal_id" style="font-size: 0.75rem; font-weight: 700; color: var(--color-text); text-transform: uppercase; letter-spacing: 0.05em;">Axe thématique principal *</label>
                    <select name="axe_principal_id" id="axe_principal_id" class="form-control" style="width: 100%; height: 40px; padding: 0.5rem 0.75rem; border-radius: var(--radius-sm); border: 1px solid var(--color-border); outline: none; background: white;">
                        <option value="" disabled selected>— Choisir un axe —</option>
                        @foreach($axes as $axe)
                            <option value="{{ $axe->id }}" {{ old('axe_principal_id') === $axe->id ? 'selected' : '' }}>{{ $axe->nom_fr }} ({{ $axe->code }})</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <button type="submit" class="btn btn-primary" style="width: 100%; justify-content: center; height: 44px; font-weight: 700; font-size: 0.9rem; border-radius: var(--radius-sm); margin-top: 0.5rem; cursor: pointer; background: var(--color-primary);">
                Créer mon compte
            </button>
        </form>

        <div style="margin-top: 2rem; padding-top: 1.5rem; border-top: 1px solid var(--color-border); text-align: center;">
            <p style="font-size: 0.85rem; color: var(--color-text-muted); margin: 0;">
                Déjà inscrit ?
            </p>
            <a href="{{ route('login') }}" style="display: inline-block; margin-top: 0.5rem; font-weight: 700; font-size: 0.875rem; color: var(--color-primary-light); text-decoration: none;">
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
