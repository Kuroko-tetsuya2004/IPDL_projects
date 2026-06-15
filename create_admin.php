<?php
$user = \App\Modules\User\Models\User::firstOrNew(['email' => 'directeur@ucad.edu.sn']);
$user->keycloak_id = 'init-superadmin-'.uniqid();
$user->nom = 'Directeur';
$user->prenom = 'Directeur';
$user->password = \Illuminate\Support\Facades\Hash::make('admin2026');
$user->role = 'super_admin';
$user->statut = 'active';
$user->langue_preference = 'fr';
$user->email_notifications = true;
$user->save();
echo "Utilisateur administrateur (directeur@ucad.edu.sn) cree ou mis a jour avec succes.\n";
