<?php

namespace App\Modules\Integration\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Modèle ExternalPublication
 * Représente un article récupéré depuis Semantic Scholar, OpenAlex ou arXiv
 */
class ExternalPublication extends Model
{
    use HasUuids;

    protected $table      = 'external_publications';
    protected $primaryKey = 'id';
    protected $keyType    = 'string';
    public    $incrementing = false;

    // Sources disponibles
    public const SOURCE_SEMANTIC_SCHOLAR = 'semantic_scholar';
    public const SOURCE_OPENALEX         = 'openalex';
    public const SOURCE_ARXIV            = 'arxiv';
    public const SOURCE_CROSSREF         = 'crossref';

    // Statuts
    public const STATUT_DISPONIBLE = 'disponible';
    public const STATUT_IMPORTE    = 'importe';
    public const STATUT_IGNORE     = 'ignore';

    protected $fillable = [
        'publication_id',
        'source',
        'external_id',
        'doi',
        'pdf_url',
        'raw_data',
        'titre',
        'resume',
        'auteurs',
        'journal',
        'annee',
        'type_publication',
        'statut',
        'fetched_at',
    ];

    protected $casts = [
        'raw_data'   => 'array',
        'fetched_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // ── Relation vers la publication UMMISCO si importée ────────────────────
    public function publication(): BelongsTo
    {
        return $this->belongsTo(
            \App\Modules\Content\Models\Publication::class,
            'publication_id'
        );
    }

    // ── Scopes ───────────────────────────────────────────────────────────────

    public function scopeFromSource($query, string $source)
    {
        return $query->where('source', $source);
    }

    public function scopeDisponible($query)
    {
        return $query->where('statut', self::STATUT_DISPONIBLE);
    }

    public function scopeImporte($query)
    {
        return $query->where('statut', self::STATUT_IMPORTE);
    }

    // ── Accesseurs ───────────────────────────────────────────────────────────

    /** Auteurs sous forme de tableau */
    public function getAuteursArrayAttribute(): array
    {
        if (!$this->auteurs) {
            return [];
        }
        $decoded = json_decode($this->auteurs, true);
        return is_array($decoded) ? $decoded : [];
    }

    /** Label humain de la source */
    public function getSourceLabelAttribute(): string
    {
        return match ($this->source) {
            self::SOURCE_SEMANTIC_SCHOLAR => 'Semantic Scholar',
            self::SOURCE_OPENALEX         => 'OpenAlex',
            self::SOURCE_ARXIV            => 'arXiv',
            self::SOURCE_CROSSREF         => 'CrossRef',
            default                       => ucfirst($this->source),
        };
    }
}
