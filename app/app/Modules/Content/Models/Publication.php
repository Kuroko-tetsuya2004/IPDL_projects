<?php

namespace App\Modules\Content\Models;

use App\Casts\PgArrayCast;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Modèle Publication — Table centrale `publications` (ummisco_database.sql)
 *
 * Gère tous les types de contenus : article, document, event, dataset, news, thesis, report, presentation
 *
 * @property string   $id
 * @property string   $titre_fr
 * @property string|null $titre_en
 * @property string|null $resume_fr
 * @property string|null $resume_en
 * @property string   $type              ENUM publication_type
 * @property string   $statut            ENUM publication_status
 * @property string   $visibilite        ENUM visibility: public|partners|internal
 * @property string   $langue_principale ENUM: fr|en
 * @property string   $auteur_id         FK → users
 * @property string|null $axe_id         FK → axes_thematiques
 * @property array|null  $mots_cles      TEXT[]
 * @property string|null $image_couverture_url
 * @property int      $nb_vues
 * @property int      $nb_telechargements
 * @property \Carbon\Carbon|null $date_publication
 * @property \Carbon\Carbon|null $date_soumission
 * — fts_fr et fts_en : colonnes TSVECTOR GENERATED (lecture seule, gérées par PostgreSQL)
 */
class Publication extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $table = 'publications';
    protected $primaryKey = 'id';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'titre_fr',
        'titre_en',
        'resume_fr',
        'resume_en',
        'type',
        'statut',
        'visibilite',
        'langue_principale',
        'auteur_id',
        'axe_id',
        'mots_cles',
        'image_couverture_url',
        'nb_vues',
        'nb_telechargements',
        'date_publication',
        'date_soumission',
        'doi',
        'url_externe',
        'pdf_url',
        'auteurs_externes',
    ];

    protected $casts = [
        'mots_cles'            => PgArrayCast::class,    // TEXT[] PostgreSQL ⇆ array PHP
        'auteurs_externes'     => 'array',
        'nb_vues'              => 'integer',
        'nb_telechargements'   => 'integer',
        'date_publication'     => 'datetime',
        'date_soumission'      => 'datetime',
        'created_at'           => 'datetime',
        'updated_at'           => 'datetime',
        'deleted_at'           => 'datetime',
    ];

    // ── Colonnes TSVECTOR générées par PostgreSQL (non fillable) ─────────────
    // fts_fr et fts_en sont GENERATED ALWAYS — Laravel ne doit PAS les écrire
    protected $guarded = ['fts_fr', 'fts_en'];

    // ── Constantes ENUM — publication_type ───────────────────────────────────
    public const TYPE_ARTICLE      = 'article';
    public const TYPE_DOCUMENT     = 'document';
    public const TYPE_EVENT        = 'event';
    public const TYPE_DATASET      = 'dataset';
    public const TYPE_NEWS         = 'news';
    public const TYPE_THESIS       = 'thesis';
    public const TYPE_REPORT       = 'report';
    public const TYPE_PRESENTATION = 'presentation';

    // ── Constantes ENUM — publication_status ─────────────────────────────────
    public const STATUS_DRAFT        = 'draft';
    public const STATUS_SUBMITTED    = 'submitted';
    public const STATUS_UNDER_REVIEW = 'under_review';
    public const STATUS_PUBLISHED    = 'published';
    public const STATUS_ARCHIVED     = 'archived';
    public const STATUS_REJECTED     = 'rejected';

    // ── Constantes ENUM — visibility ─────────────────────────────────────────
    public const VISIBILITY_PUBLIC   = 'public';
    public const VISIBILITY_PARTNERS = 'partners';
    public const VISIBILITY_INTERNAL = 'internal';

    // ── Relations ────────────────────────────────────────────────────────────

    /** Auteur principal */
    public function auteur(): BelongsTo
    {
        return $this->belongsTo(\App\Modules\User\Models\User::class, 'auteur_id');
    }

    /** Axe thématique */
    public function axe(): BelongsTo
    {
        return $this->belongsTo(\App\Modules\User\Models\AxeThematique::class, 'axe_id');
    }

    /** Co-auteurs (N:N via publications_auteurs) */
    public function coAuteurs(): BelongsToMany
    {
        return $this->belongsToMany(
            \App\Modules\User\Models\User::class,
            'publications_auteurs',
            'publication_id',
            'user_id'
        )->withPivot(['ordre', 'auteur_externe', 'nom_externe', 'affiliation_externe']);
    }

    /** Tags associés (N:N via publications_tags) */
    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class, 'publications_tags', 'publication_id', 'tag_id');
    }

    /** Médias associés */
    public function medias(): BelongsToMany
    {
        return $this->belongsToMany(
            \App\Modules\Dataset\Models\Media::class,
            'publications_medias',
            'publication_id',
            'media_id'
        )->withPivot(['role', 'ordre']);
    }

    /** Spécialisation article scientifique */
    public function article(): HasOne
    {
        return $this->hasOne(Article::class, 'publication_id');
    }

    /** Spécialisation document/rapport/thèse */
    public function document(): HasOne
    {
        return $this->hasOne(Document::class, 'publication_id');
    }

    /** Spécialisation événement */
    public function evenement(): HasOne
    {
        return $this->hasOne(Evenement::class, 'publication_id');
    }

    /** Spécialisation actualité */
    public function actualite(): HasOne
    {
        return $this->hasOne(Actualite::class, 'publication_id');
    }

    /** Spécialisation dataset */
    public function dataset(): HasOne
    {
        return $this->hasOne(\App\Modules\Dataset\Models\Dataset::class, 'publication_id');
    }

    /** Historique de workflow (pour les doctorants) */
    public function workflowValidations(): HasMany
    {
        return $this->hasMany(WorkflowValidation::class, 'publication_id');
    }

    /** Dernière validation en cours */
    public function workflowActif(): HasOne
    {
        return $this->hasOne(WorkflowValidation::class, 'publication_id')
                    ->where('statut', 'pending')
                    ->latest();
    }

    // ── Scopes ───────────────────────────────────────────────────────────────

    /** Publications publiées et publiques — utilisées par le portail public */
    public function scopePublished($query)
    {
        return $query->where('statut', self::STATUS_PUBLISHED)
                     ->whereNull('deleted_at');
    }

    /** Publications visibles publiquement */
    public function scopePublic($query)
    {
        return $query->where('visibilite', self::VISIBILITY_PUBLIC);
    }

    /**
     * Filtre les publications visibles pour un utilisateur donné selon son rôle et les règles ACL (controle_acces).
     * Super Admin a un accès complet.
     */
    public function scopeVisibleForUser($query, $userId = null, $userRole = null)
    {
        if ($userRole === 'super_admin' || $userRole === 'axe_admin') {
            return $query;
        }

        return $query->where(function ($q) use ($userId, $userRole) {
            // Toujours visible pour le public : statut publié et visibilité publique
            $q->where(function ($sq) {
                $sq->where('statut', self::STATUS_PUBLISHED)
                   ->where('visibilite', self::VISIBILITY_PUBLIC);
            });

            if ($userId) {
                // Auteur de la publication
                $q->orWhere('auteur_id', $userId);

                // Publications internes ou partenaires publiées
                $q->orWhere(function ($sq) {
                    $sq->where('statut', self::STATUS_PUBLISHED)
                       ->whereIn('visibilite', [self::VISIBILITY_INTERNAL, self::VISIBILITY_PARTNERS]);
                });

                // Si Administrateur d'Axe : accès à toutes les publications de son axe
                if ($userRole === 'axe_admin') {
                    $q->orWhere(function ($sq) use ($userId) {
                        $axeIds = \Illuminate\Support\Facades\DB::table('axes_thematiques')
                            ->where('responsable_id', $userId)
                            ->pluck('id')
                            ->toArray();
                        $sq->whereIn('axe_id', $axeIds);
                    });
                }

                // Droits spécifiques via la table controle_acces (ACL)
                $q->orWhereIn('id', function ($sub) use ($userRole) {
                    $sub->select('ressource_id')
                        ->from('controle_acces')
                        ->where('ressource_type', 'publication')
                        ->where('groupe', $userRole);
                });
            }
        });
    }

    /** Filtrer par axe */
    public function scopeByAxe($query, string $axeId)
    {
        return $query->where('axe_id', $axeId);
    }

    /** Filtrer par auteur */
    public function scopeByAuteur($query, string $userId)
    {
        return $query->where('auteur_id', $userId);
    }

    /** Filtrer par type */
    public function scopeByType($query, string $type)
    {
        return $query->where('type', $type);
    }

    /** Publications en attente de validation */
    public function scopePendingValidation($query)
    {
        return $query->where('statut', self::STATUS_SUBMITTED);
    }

    /**
     * Recherche full-text PostgreSQL via tsvector
     * Utilise les colonnes fts_fr et fts_en générées automatiquement
     */
    public function scopeSearch($query, string $searchTerm, string $lang = 'fr')
    {
        $ftsColumn = $lang === 'en' ? 'fts_en' : 'fts_fr';
        $config    = $lang === 'en' ? 'english' : 'french';

        return $query->whereRaw(
            "{$ftsColumn} @@ plainto_tsquery('{$config}', ?)",
            [$searchTerm]
        )->orderByRaw(
            "ts_rank({$ftsColumn}, plainto_tsquery('{$config}', ?)) DESC",
            [$searchTerm]
        );
    }

    // ── Helpers ──────────────────────────────────────────────────────────────

    /** La publication est-elle publiée ? */
    public function isPublished(): bool
    {
        return $this->statut === self::STATUS_PUBLISHED;
    }

    /** La publication est-elle un brouillon ? */
    public function isDraft(): bool
    {
        return $this->statut === self::STATUS_DRAFT;
    }

    /** La publication est-elle en attente de validation ? */
    public function isSubmitted(): bool
    {
        return $this->statut === self::STATUS_SUBMITTED;
    }
}
