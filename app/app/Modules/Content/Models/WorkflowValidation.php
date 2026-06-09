<?php

namespace App\Modules\Content\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Modèle WorkflowValidation — Table `workflow_validations`
 *
 * Gère le workflow de validation OBLIGATOIRE pour les doctorants (RG-009)
 * Le validateur DOIT être l'admin de l'axe concerné uniquement (RG-011)
 *
 * @property string   $id
 * @property string   $publication_id    FK → publications
 * @property string   $soumetteur_id     FK → users
 * @property string|null $validateur_id  FK → users (admin de l'axe — RG-011)
 * @property string   $statut            ENUM: pending|approved|rejected|revision_required
 * @property string|null $commentaire_admin
 * @property string|null $commentaire_auteur
 * @property int      $version           Numéro de soumission (incrémenté à chaque re-soumission)
 * @property \Carbon\Carbon $date_soumission
 * @property \Carbon\Carbon|null $date_decision
 * @property \Carbon\Carbon|null $date_limite
 */
class WorkflowValidation extends Model
{
    use HasUuids;

    protected $table = 'workflow_validations';
    protected $primaryKey = 'id';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'publication_id',
        'soumetteur_id',
        'validateur_id',
        'statut',
        'commentaire_admin',
        'commentaire_auteur',
        'version',
        'date_soumission',
        'date_decision',
        'date_limite',
    ];

    protected $casts = [
        'version'         => 'integer',
        'date_soumission' => 'datetime',
        'date_decision'   => 'datetime',
        'date_limite'     => 'datetime',
    ];

    // ── Constantes ENUM — workflow_status ────────────────────────────────────
    public const STATUS_PENDING           = 'pending';
    public const STATUS_APPROVED          = 'approved';
    public const STATUS_REJECTED          = 'rejected';
    public const STATUS_REVISION_REQUIRED = 'revision_required';

    // ── Relations ────────────────────────────────────────────────────────────

    public function publication(): BelongsTo
    {
        return $this->belongsTo(Publication::class, 'publication_id');
    }

    public function soumetteur(): BelongsTo
    {
        return $this->belongsTo(\App\Modules\User\Models\User::class, 'soumetteur_id');
    }

    public function validateur(): BelongsTo
    {
        return $this->belongsTo(\App\Modules\User\Models\User::class, 'validateur_id');
    }

    /** Historique des changements d'état */
    public function historique(): HasMany
    {
        return $this->hasMany(WorkflowHistorique::class, 'workflow_id');
    }

    // ── Scopes ───────────────────────────────────────────────────────────────

    public function scopePending($query)
    {
        return $query->where('statut', self::STATUS_PENDING);
    }

    public function scopeByAxe($query, string $axeId)
    {
        return $query->whereHas('publication', fn($q) => $q->where('axe_id', $axeId));
    }

    // ── Helpers ──────────────────────────────────────────────────────────────

    public function isPending(): bool
    {
        return $this->statut === self::STATUS_PENDING;
    }

    public function isApproved(): bool
    {
        return $this->statut === self::STATUS_APPROVED;
    }
}
