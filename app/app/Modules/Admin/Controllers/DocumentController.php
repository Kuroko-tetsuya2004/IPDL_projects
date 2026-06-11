<?php

namespace App\Modules\Admin\Controllers;

use Illuminate\Routing\Controller;
use Inertia\Inertia;
use Inertia\Response;

/**
 * DocumentController — Gestion des documents administratifs IRD
 * Convention de Stage, Reçu de Prestation de Service, Bon d'Achat
 */
class DocumentController extends Controller
{
    /**
     * Page index listant les 3 types de documents disponibles
     */
    public function index(): Response
    {
        if (session('user_role') !== 'super_admin') {
            abort(403, 'Accès réservé au Super Administrateur.');
        }

        return Inertia::render('Admin/Documents/Index');
    }

    /**
     * Formulaire Convention de Stage
     */
    public function conventionStage(): Response
    {
        if (session('user_role') !== 'super_admin') {
            abort(403, 'Accès réservé au Super Administrateur.');
        }

        return Inertia::render('Admin/Documents/ConventionStage');
    }

    /**
     * Formulaire Reçu de Prestation de Service
     */
    public function prestationService(): Response
    {
        if (session('user_role') !== 'super_admin') {
            abort(403, 'Accès réservé au Super Administrateur.');
        }

        return Inertia::render('Admin/Documents/PrestationService');
    }

    /**
     * Formulaire Bon d'Achat / Demande d'Achat
     */
    public function bonAchat(): Response
    {
        if (session('user_role') !== 'super_admin') {
            abort(403, 'Accès réservé au Super Administrateur.');
        }

        return Inertia::render('Admin/Documents/BonAchat');
    }
}
