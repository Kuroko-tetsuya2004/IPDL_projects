<?php

namespace App\Modules\User\Controllers;

use App\Modules\User\Models\User;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Inertia\Response;

class ProfileController extends Controller
{
    public function show(): Response
    {
        $userId = session('user_id');
        $user   = User::findOrFail($userId);

        return Inertia::render('User/Profile', [
            'user' => [
                'id'                  => $user->id,
                'nom'                 => $user->nom,
                'prenom'              => $user->prenom,
                'email'               => $user->email,
                'role'                => $user->role,
                'titre_academique'    => $user->titre_academique,
                'specialite'          => $user->specialite,
                'orcid_id'            => $user->orcid_id,
                'bio_fr'              => $user->bio_fr,
                'bio_en'              => $user->bio_en,
                'telephone'           => $user->telephone,
                'institution'         => $user->institution,
                'langue_preference'   => $user->langue_preference,
                'email_notifications' => $user->email_notifications,
                'photo_url'           => $user->photo_url,
                'created_at'          => $user->created_at,
                'derniere_connexion'  => $user->derniere_connexion,
            ],
        ]);
    }

    public function update(Request $request)
    {
        $userId = session('user_id');
        $user   = User::findOrFail($userId);

        $validated = $request->validate([
            'nom'                 => 'required|string|max:150',
            'prenom'              => 'required|string|max:150',
            'titre_academique'    => 'nullable|string|max:50',
            'specialite'          => 'nullable|string|max:255',
            'orcid_id'            => 'nullable|string|max:50',
            'bio_fr'              => 'nullable|string|max:2000',
            'bio_en'              => 'nullable|string|max:2000',
            'telephone'           => 'nullable|string|max:30',
            'institution'         => 'nullable|string|max:255',
            'langue_preference'   => 'nullable|in:fr,en',
            'email_notifications' => 'boolean',
        ]);

        $user->update($validated);

        return back()->with('success', 'Profil mis à jour avec succès.');
    }
}
