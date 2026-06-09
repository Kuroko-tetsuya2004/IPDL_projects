<?php

namespace App\Modules\User\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;

/**
 * Modèle User — Mappé sur la table `users` (ummisco_database.sql)
 *
 * @property string   $id                UUID
 * @property string   $keycloak_id       Identifiant Keycloak (source de vérité auth)
 * @property string   $email
 * @property string   $nom
 * @property string   $prenom
 * @property string   $role              ENUM: visitor|researcher|doctoral_student|partner|axe_admin|super_admin
 * @property string   $statut            ENUM: active|inactive|archived
 * @property string|null $axe_principal_id
 * @property string|null $photo_url
 * @property string|null $biographie_fr
 * @property string|null $biographie_en
 * @property string|null $titre_academique
 * @property string|null $grade
 * @property string|null $orcid_id
 * @property string|null $page_web_url
 * @property string|null $linkedin_url
 * @property string|null $researchgate_url
 * @property string   $langue_preference  ENUM: fr|en
 * @property bool     $email_notifications
 * @property \Carbon\Carbon|null $derniere_connexion
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property \Carbon\Carbon|null $deleted_at
 */
class User extends Authenticatable
{
    use HasFactory, HasUuids, SoftDeletes;

    /** @var string Nom de la table PostgreSQL */
    protected $table = 'users';

    /** @var string Clé primaire UUID */
    protected $primaryKey = 'id';

    /** @var string Type de la clé primaire */
    protected $keyType = 'string';

    /** @var bool UUID ne s'auto-incrémente pas */
    public $incrementing = false;

    protected $fillable = [
        'keycloak_id',
        'email',
        'nom',
        'prenom',
        'role',
        'statut',
        'axe_principal_id',
        'photo_url',
        'biographie_fr',
        'biographie_en',
        'titre_academique',
        'grade',
        'orcid_id',
        'page_web_url',
        'linkedin_url',
        'researchgate_url',
        'langue_preference',
        'email_notifications',
        'derniere_connexion',
    ];

    protected $hidden = [
        'remember_token',
    ];

    protected $casts = [
        'email_notifications' => 'boolean',
        'derniere_connexion'  => 'datetime',
        'created_at'          => 'datetime',
        'updated_at'          => 'datetime',
        'deleted_at'          => 'datetime',
    ];

    // ── Constantes ENUM (mappées sur PostgreSQL user_role) ─────────────────
    public const ROLE_VISITOR         = 'visitor';
    public const ROLE_RESEARCHER      = 'researcher';
    public const ROLE_DOCTORAL_STUDENT = 'doctoral_student';
    public const ROLE_PARTNER         = 'partner';
    public const ROLE_AXE_ADMIN       = 'axe_admin';
    public const ROLE_SUPER_ADMIN     = 'super_admin';

    public const STATUS_ACTIVE   = 'active';
    public const STATUS_INACTIVE = 'inactive';
    public const STATUS_ARCHIVED = 'archived';

    public const LANG_FR = 'fr';
    public const LANG_EN = 'en';

    // ── Relations ────────────────────────────────────────────────────────────

    /** Axe thématique principal de l'utilisateur */
    public function axePrincipal(): BelongsTo
    {
        return $this->belongsTo(AxeThematique::class, 'axe_principal_id');
    }

    /** Publications dont cet utilisateur est l'auteur principal */
    public function publications(): HasMany
    {
        return $this->hasMany(
            \App\Modules\Content\Models\Publication::class,
            'auteur_id'
        );
    }

    /** Axes auxquels appartient l'utilisateur (N:N via users_axes) */
    public function axes(): BelongsToMany
    {
        return $this->belongsToMany(
            AxeThematique::class,
            'users_axes',
            'user_id',
            'axe_id'
        )->withPivot(['role_dans_axe', 'depuis']);
    }

    /** Profil chercheur étendu (table profils_chercheurs) */
    public function profilChercheur(): HasOne
    {
        return $this->hasOne(ProfilChercheur::class, 'user_id');
    }

    /** Profil doctorant étendu (table profils_doctorants) */
    public function profilDoctorant(): HasOne
    {
        return $this->hasOne(ProfilDoctorant::class, 'user_id');
    }

    /** Profil partenaire étendu (table profils_partenaires) */
    public function profilPartenaire(): HasOne
    {
        return $this->hasOne(ProfilPartenaire::class, 'user_id');
    }

    /** Notifications reçues par cet utilisateur */
    public function notifications(): HasMany
    {
        return $this->hasMany(
            \App\Modules\Notification\Models\Notification::class,
            'destinataire_id'
        );
    }

    /** Logs d'audit liés à cet utilisateur */
    public function auditLogs(): HasMany
    {
        return $this->hasMany(
            \App\Modules\Audit\Models\AuditLog::class,
            'user_id'
        );
    }

    // ── Scopes ───────────────────────────────────────────────────────────────

    /** Utilisateurs actifs uniquement */
    public function scopeActive($query)
    {
        return $query->where('statut', self::STATUS_ACTIVE)
                     ->whereNull('deleted_at');
    }

    /** Filtrer par rôle */
    public function scopeByRole($query, string $role)
    {
        return $query->where('role', $role);
    }

    /** Chercheurs uniquement */
    public function scopeResearchers($query)
    {
        return $query->whereIn('role', [self::ROLE_RESEARCHER, self::ROLE_AXE_ADMIN]);
    }

    /** Membres visibles dans l'annuaire (chercheurs + doctorants + admins axe) */
    public function scopeAnnuaire($query)
    {
        return $query->active()
                     ->whereIn('role', [
                         self::ROLE_RESEARCHER,
                         self::ROLE_DOCTORAL_STUDENT,
                         self::ROLE_AXE_ADMIN,
                     ]);
    }

    // ── Helpers ──────────────────────────────────────────────────────────────

    /** Nom complet de l'utilisateur */
    public function getNomCompletAttribute(): string
    {
        return "{$this->prenom} {$this->nom}";
    }

    /** Vérifie si l'utilisateur a un rôle spécifique */
    public function hasRole(string $role): bool
    {
        return $this->role === $role;
    }

    /** Vérifie si l'utilisateur est un administrateur */
    public function isAdmin(): bool
    {
        return in_array($this->role, [self::ROLE_SUPER_ADMIN, self::ROLE_AXE_ADMIN]);
    }

    /** Vérifie si l'utilisateur peut publier directement (RG-012) */
    public function canPublishDirectly(): bool
    {
        return in_array($this->role, [
            self::ROLE_RESEARCHER,
            self::ROLE_AXE_ADMIN,
            self::ROLE_SUPER_ADMIN,
        ]);
    }

    /** Vérifie si l'utilisateur doit passer par le workflow (RG-009) */
    public function requiresWorkflow(): bool
    {
        return $this->role === self::ROLE_DOCTORAL_STUDENT;
    }
}
