<?php

namespace App\Modules\PublicPortal\Controllers;

use Illuminate\Routing\Controller;
use Inertia\Inertia;
use Inertia\Response;
use App\Modules\UMMISCO\Models\Centre;

class UmmiscoController extends Controller
{
    public function centres(): Response
    {
        $centres = Centre::orderBy('nom')->get();
        
        return Inertia::render('Public/Centres', [
            'centres' => $centres
        ]);
    }
}
