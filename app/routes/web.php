<?php

use App\Modules\Admin\Controllers\AdminController;
use App\Modules\Admin\Controllers\DocumentController;
use App\Modules\Auth\Controllers\AuthController;
use App\Modules\Auth\Middleware\KeycloakMiddleware;
use App\Modules\Content\Controllers\WorkflowValidationController;
use App\Modules\Dataset\Controllers\DatasetController;
use App\Modules\Integration\Controllers\ImportController;
use App\Modules\Notification\Controllers\NotificationController;
use App\Modules\PublicPortal\Controllers\PublicPortalController;
use App\Modules\Search\Controllers\SearchController;
use App\Modules\User\Controllers\DashboardController;
use App\Modules\User\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes — Portail UMMISCO
|--------------------------------------------------------------------------
*/

// ── Routes publiques ────────────────────────────────────────────────────────

Route::get('/', [PublicPortalController::class, 'home'])->name('home');
Route::get('/publications', [PublicPortalController::class, 'publications'])->name('publications');
Route::get('/publications/{id}', [PublicPortalController::class, 'show'])->name('publications.show')->whereUuid('id');
Route::get('/axes', [PublicPortalController::class, 'axes'])->name('axes');
Route::get('/projets', [PublicPortalController::class, 'projets'])->name('projets');
Route::get('/search', [SearchController::class, 'index'])->name('search');
Route::get('/recherche', [SearchController::class, 'index'])->name('recherche');
Route::get('/documents/{id}/download', [PublicPortalController::class, 'downloadDocument'])->name('documents.download')->whereUuid('id');
Route::get('/documents/{id}/view', [PublicPortalController::class, 'viewDocument'])->name('documents.view')->whereUuid('id');
Route::get('/datasets/files/{id}/download', [PublicPortalController::class, 'downloadDatasetFile'])->name('datasets.files.download')->whereUuid('id');

// ── Publications externes (lecture seule — Semantic Scholar, OpenAlex, arXiv) ──
Route::get('/publications/externes', [ImportController::class, 'publicIndex'])->name('publications.externes');
Route::get('/publications/externes/{id}', [ImportController::class, 'publicShow'])->name('publications.externes.show');
Route::get('/api/publications/externes/search', [ImportController::class, 'search'])
    ->name('api.publications.externes.search')
    ->middleware('throttle:api-search');

// Agrégateur de recherche académique (API + UI)
Route::get('/api/search', [\App\Modules\Integration\Controllers\ApiSearchController::class, 'search'])
    ->name('api.search')
    ->middleware('throttle:api-search');
Route::get('/recherche-academique', [PublicPortalController::class, 'rechercheAcademique'])->name('recherche.academique');

// Nouveaux modules UMMISCO (sans formations, ENT, ni CoFab)
Route::get('/recherches/modelisation', [PublicPortalController::class, 'modelisation'])->name('recherches.modelisation');
Route::get('/recherches/milieux', [PublicPortalController::class, 'milieux'])->name('recherches.milieux');
Route::get('/actualites', [PublicPortalController::class, 'actualites'])->name('actualites');
Route::get('/contact', [PublicPortalController::class, 'contact'])->name('contact');

// Gestion de la langue
Route::post('/langue/{lang}', function ($lang) {
    if (in_array($lang, ['fr', 'en'])) {
        session(['locale' => $lang]);
        if (session()->has('user_id')) {
            \App\Modules\User\Models\User::where('id', session('user_id'))->update(['langue_preference' => $lang]);
        }
    }
    return response()->json(['success' => true]);
})->name('langue.update');

// Newsletter & Contact
Route::post('/newsletter/subscribe', [PublicPortalController::class, 'subscribeNewsletter'])->name('newsletter.subscribe');
Route::post('/contact', [PublicPortalController::class, 'submitContact'])->name('contact.submit');

// ── Routes d'authentification ───────────────────────────────────────────────

Route::get('/auth/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/auth/login', [AuthController::class, 'login'])->name('login.post');
Route::get('/auth/register', [AuthController::class, 'showRegister'])->name('register');
Route::post('/auth/register', [AuthController::class, 'register'])->name('register.post');
Route::post('/auth/logout', [AuthController::class, 'logout'])->name('logout');

// ── Routes sécurisées (authentification requise) ────────────────────────────

Route::middleware([KeycloakMiddleware::class])->group(function () {
    Route::get('/run-delete', function () {
        $start = \Carbon\Carbon::now()->subWeek()->startOfWeek();
        $end = \Carbon\Carbon::now()->subWeek()->endOfWeek();
        $count = \App\Modules\Content\Models\Publication::whereBetween('created_at', [$start, $end])->count();
        \App\Modules\Content\Models\Publication::whereBetween('created_at', [$start, $end])->delete();
        return "Opération terminée : {$count} publications supprimées de la base de données (semaine dernière).";
    });

    Route::get('/run-import-json', function () {
        \Illuminate\Support\Facades\Artisan::call('publications:import-json');
        return \Illuminate\Support\Facades\Artisan::output() ?: 'Importation terminée. Vérifiez les publications dans l\'application.';
    });

    // ── Dashboard ─────────────────────────────────────────────────────────
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // ── Profil utilisateur ────────────────────────────────────────────────
    Route::get('/profile', [ProfileController::class, 'show'])->name('profile');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');

    // ── Publications (utilisateur connecté) ───────────────────────────────
    Route::get('/mes-publications', [WorkflowValidationController::class, 'mesPublications'])->name('mes-publications');
    Route::get('/publications/soumettre', [WorkflowValidationController::class, 'create'])->name('publications.create');
    Route::post('/publications/submit', [WorkflowValidationController::class, 'submit'])->name('publications.submit');
    Route::post('/publications/sync-orcid', [WorkflowValidationController::class, 'syncOrcid'])->name('publications.sync-orcid');
    Route::get('/publications/{id}/modifier', [WorkflowValidationController::class, 'edit'])->name('publications.edit')->whereUuid('id');
    Route::put('/publications/{id}', [WorkflowValidationController::class, 'update'])->name('publications.update')->whereUuid('id');
    Route::delete('/publications/{id}', [WorkflowValidationController::class, 'destroy'])->name('publications.destroy')->whereUuid('id');

    // ── Workflow de validation ─────────────────────────────────────────────
    Route::get('/soumissions', [WorkflowValidationController::class, 'pending'])->name('soumissions');
    Route::post('/workflow/{id}/approve', [WorkflowValidationController::class, 'approve'])->name('workflow.approve');
    Route::post('/workflow/{id}/reject', [WorkflowValidationController::class, 'reject'])->name('workflow.reject');

    // ── Datasets ──────────────────────────────────────────────────────────
    Route::get('/mes-datasets', [DatasetController::class, 'index'])->name('mes-datasets');
    Route::get('/datasets/nouveau', [DatasetController::class, 'create'])->name('datasets.create');
    Route::post('/datasets', [DatasetController::class, 'store'])->name('datasets.store');

    // ── Notifications ─────────────────────────────────────────────────────
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications');
    Route::post('/notifications/{id}/read', [NotificationController::class, 'markRead'])->name('notifications.read');
    Route::post('/notifications/read-all', [NotificationController::class, 'markAllRead'])->name('notifications.read-all');

    // ── Vote de suppression de publication ────────────────────────────────
    Route::post('/demandes-suppression/{id}/voter', [AdminController::class, 'voterSuppression'])->name('demandes-suppression.voter');

    // ── Admin (axe_admin + super_admin) ───────────────────────────────────
    Route::prefix('admin')->name('admin.')->middleware('role:axe_admin,super_admin')->group(function () {
        Route::get('/', [AdminController::class, 'index'])->name('index');
        Route::get('/membres', [AdminController::class, 'users'])->name('users');
        Route::put('/membres/{id}', [AdminController::class, 'updateUser'])->name('users.update');
        Route::get('/publications', [AdminController::class, 'publications'])->name('publications');
        Route::post('/publications/{id}/proposer-suppression', [AdminController::class, 'proposerSuppression'])->name('publications.propose-delete')->middleware('role:super_admin');
        Route::get('/axes', [AdminController::class, 'axes'])->name('axes');
        Route::post('/axes', [AdminController::class, 'storeAxe'])->name('axes.store')->middleware('role:super_admin');
        Route::put('/axes/{id}', [AdminController::class, 'updateAxe'])->name('axes.update')->middleware('role:super_admin');
        Route::get('/statistiques', [AdminController::class, 'statistiques'])->name('statistiques');
        Route::get('/datasets', [AdminController::class, 'datasets'])->name('datasets');
        Route::delete('/datasets/{id}', [AdminController::class, 'deleteDataset'])->name('datasets.destroy');
        Route::get('/parametres', [AdminController::class, 'parametres'])->name('parametres')->middleware('role:super_admin');
        Route::put('/parametres/{cle}', [AdminController::class, 'updateParametre'])->name('parametres.update')->middleware('role:super_admin');
        Route::get('/acl', [AdminController::class, 'acl'])->name('acl')->middleware('role:super_admin');

        // ── Documents administratifs (super_admin uniquement) ─────────────────
        Route::prefix('documents')->name('documents.')->middleware('role:super_admin')->group(function () {
            Route::get('/', [DocumentController::class, 'index'])->name('index');
            Route::get('/convention-stage', [DocumentController::class, 'conventionStage'])->name('convention-stage');
            Route::get('/prestation-service', [DocumentController::class, 'prestationService'])->name('prestation-service');
            Route::get('/bon-achat', [DocumentController::class, 'bonAchat'])->name('bon-achat');
        });

        // ── Import publications externes — supervision (super_admin) ──────────
        Route::prefix('import')->name('import.')->middleware('role:super_admin')->group(function () {
            Route::get('/', [ImportController::class, 'adminIndex'])->name('index');
            Route::post('/run', [ImportController::class, 'adminRun'])->name('run');
        });
    });

    // ── Publications externes — Actions Utilisateur (chercheurs/doctorants) ──
    Route::post('/publications/externes/fetch-live', [ImportController::class, 'userLiveFetch'])->name('publications.externes.fetch-live');
    Route::post('/publications/externes/{id}/import', [ImportController::class, 'importToProfile'])->name('publications.externes.import');
});
