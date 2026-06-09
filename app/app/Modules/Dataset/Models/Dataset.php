<?php

namespace App\Modules\Dataset\Models;

use App\Casts\PgArrayCast;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Modèle Dataset — Table `datasets`
 *
 * Spécialisation de Publication pour les jeux de données scientifiques.
 * RG-007 : Une licence DOIT être définie avant toute publication.
 *
 * @property string   $publication_id   PK = FK → publications
 * @property string   $licence          ENUM dataset_licence
 * @property string   $format_principal ex: 'CSV', 'JSON', 'NetCDF', 'HDF5'
 * @property array|null $formats_disponibles TEXT[]
 * @property float|null  $taille_totale_mo
 * @property string   $version          default '1.0' (format semver simplifié)
 * @property string|null $doi
 * @property array|null  $metadonnees   JSONB libre
 * @property \Carbon\Carbon|null $periode_collecte_debut
 * @property \Carbon\Carbon|null $periode_collecte_fin
 * @property string|null $zone_geographique
 * @property string|null $methodologie
 * @property string|null $conditions_acces
 * @property string|null $lien_externe
 */
class Dataset extends Model
{
    use HasUuids;

    protected $table = 'datasets';
    protected $primaryKey = 'publication_id';
    protected $keyType = 'string';
    public $incrementing = false;

    // Les timestamps sont gérés via la table publications (pas de updated_at ici)
    public $timestamps = false;

    protected $fillable = [
        'publication_id',
        'licence',
        'format_principal',
        'formats_disponibles',
        'taille_totale_mo',
        'version',
        'doi',
        'metadonnees',
        'periode_collecte_debut',
        'periode_collecte_fin',
        'zone_geographique',
        'methodologie',
        'conditions_acces',
        'lien_externe',
    ];

    protected $casts = [
        'formats_disponibles'   => PgArrayCast::class,   // TEXT[] PostgreSQL
        'metadonnees'           => 'array',   // JSONB PostgreSQL
        'taille_totale_mo'      => 'float',
        'periode_collecte_debut'=> 'date',
        'periode_collecte_fin'  => 'date',
    ];

    // ── Constantes ENUM — dataset_licence ────────────────────────────────────
    public const LICENCE_CC_BY        = 'cc_by';
    public const LICENCE_CC_BY_NC     = 'cc_by_nc';
    public const LICENCE_CC_BY_SA     = 'cc_by_sa';
    public const LICENCE_CC_BY_NC_SA  = 'cc_by_nc_sa';
    public const LICENCE_CC0          = 'cc0';
    public const LICENCE_ODC          = 'open_data_commons';
    public const LICENCE_PROPRIETARY  = 'proprietary';
    public const LICENCE_RESTRICTED   = 'restricted';

    public static function licences(): array
    {
        return [
            self::LICENCE_CC_BY       => 'Creative Commons BY',
            self::LICENCE_CC_BY_NC    => 'CC BY-NC',
            self::LICENCE_CC_BY_SA    => 'CC BY-SA',
            self::LICENCE_CC_BY_NC_SA => 'CC BY-NC-SA',
            self::LICENCE_CC0         => 'CC0 (Domaine public)',
            self::LICENCE_ODC         => 'Open Data Commons',
            self::LICENCE_PROPRIETARY => 'Propriétaire',
            self::LICENCE_RESTRICTED  => 'Accès restreint',
        ];
    }

    // ── Relations ────────────────────────────────────────────────────────────

    /** Publication parente */
    public function publication(): BelongsTo
    {
        return $this->belongsTo(
            \App\Modules\Content\Models\Publication::class,
            'publication_id'
        );
    }

    /** Fichiers du dataset (table datasets_fichiers) */
    public function fichiers(): HasMany
    {
        return $this->hasMany(DatasetFichier::class, 'dataset_id', 'publication_id');
    }

    /** Historique des versions */
    public function versions(): HasMany
    {
        return $this->hasMany(DatasetVersion::class, 'dataset_id', 'publication_id')
                    ->orderBy('created_at', 'desc');
    }

    // ── Scopes ───────────────────────────────────────────────────────────────

    public function scopeOpenAccess($query)
    {
        return $query->whereIn('licence', [
            self::LICENCE_CC_BY,
            self::LICENCE_CC_BY_NC,
            self::LICENCE_CC_BY_SA,
            self::LICENCE_CC_BY_NC_SA,
            self::LICENCE_CC0,
            self::LICENCE_ODC,
        ]);
    }

    // ── Helpers ──────────────────────────────────────────────────────────────

    /** Le dataset est-il librement accessible ? */
    public function isOpenAccess(): bool
    {
        return !in_array($this->licence, [
            self::LICENCE_PROPRIETARY,
            self::LICENCE_RESTRICTED,
        ]);
    }

    /** Taille formatée pour l'affichage */
    public function getTailleFormatteeAttribute(): string
    {
        if (!$this->taille_totale_mo) return 'N/A';
        if ($this->taille_totale_mo < 1) return round($this->taille_totale_mo * 1024, 0) . ' Ko';
        if ($this->taille_totale_mo >= 1024) return round($this->taille_totale_mo / 1024, 1) . ' Go';
        return round($this->taille_totale_mo, 1) . ' Mo';
    }
}
