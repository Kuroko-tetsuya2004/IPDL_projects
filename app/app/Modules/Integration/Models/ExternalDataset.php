<?php

namespace App\Modules\Integration\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

/**
 * ExternalDataset
 *
 * Représente un dataset scientifique récupéré depuis DataCite (ou autre source).
 * Équivalent de ExternalPublication pour les datasets.
 *
 * @property string      $id
 * @property string      $source         'datacite' | 'zenodo' | 'dryad' | 'figshare'
 * @property string|null $external_id    ID interne à la source
 * @property string|null $doi            DOI — clé de déduplication principale
 * @property string|null $titre
 * @property string|null $resume
 * @property string|null $auteurs        JSON encodé
 * @property string|null $annee
 * @property string|null $type_dataset
 * @property string|null $licence
 * @property int|null    $taille_octets
 * @property array|null  $formats
 * @property string|null $lien_acces
 * @property string|null $editeur
 * @property string|null $version
 * @property array|null  $raw_data
 * @property string      $statut
 * @property \Carbon\Carbon|null $fetched_at
 */
class ExternalDataset extends Model
{
    use HasUuids;

    protected $table = 'external_datasets';

    public const STATUT_DISPONIBLE = 'disponible';
    public const STATUT_ARCHIVE    = 'archive';
    public const STATUT_SUPPRIME   = 'supprime';

    protected $fillable = [
        'source',
        'external_id',
        'doi',
        'titre',
        'resume',
        'auteurs',
        'annee',
        'type_dataset',
        'licence',
        'taille_octets',
        'formats',
        'lien_acces',
        'editeur',
        'version',
        'raw_data',
        'statut',
        'fetched_at',
    ];

    protected $casts = [
        'formats'     => 'array',
        'raw_data'    => 'array',
        'fetched_at'  => 'datetime',
        'taille_octets' => 'integer',
    ];

    // ── Accessors ────────────────────────────────────────────────────────────

    /** Retourne la liste des auteurs sous forme de tableau PHP */
    public function getAuteursArrayAttribute(): array
    {
        if (!$this->auteurs) return [];
        $decoded = json_decode($this->auteurs, true);
        return is_array($decoded) ? $decoded : [$this->auteurs];
    }

    /** Label lisible de la source */
    public function getSourceLabelAttribute(): string
    {
        return match ($this->source) {
            'datacite' => 'DataCite',
            'zenodo'   => 'Zenodo',
            'dryad'    => 'Dryad',
            'figshare' => 'Figshare',
            'pangaea'  => 'PANGAEA',
            default    => ucfirst($this->source),
        };
    }

    /** URL vers le dataset sur sa plateforme d'origine */
    public function getExternalUrlAttribute(): string
    {
        if ($this->doi) {
            return 'https://doi.org/' . $this->doi;
        }
        return match ($this->source) {
            'zenodo'   => "https://zenodo.org/record/{$this->external_id}",
            'dryad'    => "https://datadryad.org/dataset/{$this->external_id}",
            'figshare' => "https://figshare.com/articles/{$this->external_id}",
            default    => $this->lien_acces ?? '#',
        };
    }

    /** Taille lisible en Ko / Mo / Go */
    public function getTailleFormatteeAttribute(): string
    {
        if (!$this->taille_octets) return 'N/A';
        $mo = $this->taille_octets / 1048576;
        if ($mo < 1)    return round($this->taille_octets / 1024, 1) . ' Ko';
        if ($mo >= 1024) return round($mo / 1024, 2) . ' Go';
        return round($mo, 2) . ' Mo';
    }

    // ── Scopes ───────────────────────────────────────────────────────────────

    public function scopeDisponible($query)
    {
        return $query->where('statut', self::STATUT_DISPONIBLE);
    }
}
