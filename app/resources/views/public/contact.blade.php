@extends('layouts.app')

@section('title', 'Contact — UMMISCO')

@section('content')
<div class="container" style="padding-top: 2rem;">
    {{-- Page Header --}}
    <div style="background: linear-gradient(135deg, rgba(30, 58, 138, 0.03) 0%, rgba(37, 99, 235, 0.05) 100%); border-bottom: 2px solid var(--border); padding: 3rem 2rem; border-radius: var(--radius); margin-bottom: 3rem; text-align: center;">
        <h1 style="font-size: 2.25rem; font-weight: 800; margin-bottom: 1rem; color: var(--primary);">
            Contact & Collaboration
        </h1>
        <p style="font-size: 1.1rem; color: var(--text-muted); max-width: 800px; margin: 0 auto;">
            Vous souhaitez collaborer avec l'UMMISCO ou nous adresser une demande ? Contactez-nous via le formulaire ci-dessous
        </p>
    </div>

    <div class="grid-2" style="margin-bottom: 4rem; gap: 2.5rem;">
        
        {{-- Formulaire de Contact --}}
        <div class="card" style="background: white;">
            <h2 style="font-size: 1.35rem; margin-bottom: 1.5rem; color: var(--primary);">Envoyer un Message</h2>
            
            <form method="POST" action="{{ route('contact.submit') }}">
                @csrf
                
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                    <div class="form-group">
                        <label for="nom">Nom complet <span style="color:var(--danger)">*</span></label>
                        <input type="text" name="nom" id="nom" class="form-control" placeholder="Ex: Jean Dupont" required value="{{ old('nom') }}">
                    </div>
                    <div class="form-group">
                        <label for="email">Adresse email <span style="color:var(--danger)">*</span></label>
                        <input type="email" name="email" id="email" class="form-control" placeholder="Ex: jean.dupont@univ.edu" required value="{{ old('email') }}">
                    </div>
                </div>

                <div class="form-group">
                    <label for="organisation">Organisation / Université</label>
                    <input type="text" name="organisation" id="organisation" class="form-control" placeholder="Ex: Université Cheikh Anta Diop" value="{{ old('organisation') }}">
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                    <div class="form-group">
                        <label for="type_demande">Type de demande</label>
                        <select name="type_demande" id="type_demande" class="form-control">
                            <option value="contact">Demande de contact</option>
                            <option value="collaboration">Collaboration scientifique</option>
                            <option value="prestation">Prestation / Expertise</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="axe_concerne_id">Axe de recherche ciblé</label>
                        <select name="axe_concerne_id" id="axe_concerne_id" class="form-control">
                            <option value="">-- Aucun ou Global --</option>
                            @foreach($axes as $axe)
                                <option value="{{ $axe->id }}">{{ $axe->code }} - {{ $axe->nom_fr }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label for="sujet">Sujet du message <span style="color:var(--danger)">*</span></label>
                    <input type="text" name="sujet" id="sujet" class="form-control" placeholder="Ex: Recherche de stage doctoral" required value="{{ old('sujet') }}">
                </div>

                <div class="form-group">
                    <label for="message">Votre message <span style="color:var(--danger)">*</span></label>
                    <textarea name="message" id="message" class="form-control" placeholder="Rédigez votre demande ici..." required>{{ old('message') }}</textarea>
                </div>

                <button type="submit" class="btn btn-primary" style="width: 100%; justify-content: center; padding: 0.8rem;">
                    ✉️ Envoyer le message
                </button>
            </form>
        </div>

        {{-- Informations et Coordonnées --}}
        <div style="display: flex; flex-direction: column; gap: 2rem;">
            <div class="card" style="border-left: 5px solid var(--primary-light);">
                <h3 style="font-size: 1.2rem; margin-bottom: 0.75rem; color: var(--primary);">📍 Localisation Principale</h3>
                <p style="font-size: 0.925rem; color: var(--text); font-weight: 600; margin-bottom: 0.25rem;">
                    Laboratoire UMMISCO (Dakar, Sénégal)
                </p>
                <p style="font-size: 0.875rem; color: var(--text-muted); line-height: 1.6;">
                    Département d'Informatique, Faculté des Sciences et Techniques<br>
                    Université Cheikh Anta Diop (UCAD)<br>
                    BP 5005, Dakar-Fann, Sénégal
                </p>
            </div>

            <div class="card" style="border-left: 5px solid var(--accent);">
                <h3 style="font-size: 1.2rem; margin-bottom: 0.75rem; color: var(--primary);">📧 Secrétariat Scientifique</h3>
                <p style="font-size: 0.875rem; color: var(--text-muted); line-height: 1.6; margin-bottom: 0.5rem;">
                    Pour toute demande administrative, académique ou relative au suivi des publications :
                </p>
                <p style="font-size: 1rem; font-weight: 700; color: var(--primary-light);">
                    admin@ummisco.ucad.sn
                </p>
            </div>

            <div class="card" style="border-left: 5px solid var(--success);">
                <h3 style="font-size: 1.2rem; margin-bottom: 0.75rem; color: var(--primary);">👥 Autres Implantations</h3>
                <p style="font-size: 0.875rem; color: var(--text-muted); line-height: 1.6;">
                    L'UMMISCO dispose également d'équipes et de bureaux de recherche associés en France (Sorbonne Université, Bondy), au Maroc (Université Cadi Ayyad, Marrakech), au Vietnam (IFI, Hanoï) et au Cameroun (Université de Yaoundé 1).
                </p>
            </div>
        </div>

    </div>
</div>
@endsection
