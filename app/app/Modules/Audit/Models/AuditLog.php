<?php

namespace App\Modules\Audit\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Modèle AuditLog — Table `audit_logs` (APPEND-ONLY)
 *
 * ⚠️ Cette table est IMMUABLE — aucun UPDATE ni DELETE autorisé (RG-018)
 * Un trigger PostgreSQL fn_audit_logs_immutable() protège cette règle.
 *
 * @property string   $id
 * @property string|null $user_id          FK → users (nullable si action anonyme)
 * @property string|null $user_email       Dénormalisé pour conservation post-suppression
 * @property string|null $user_role        Dénormalisé
 * @property string   $action              ENUM audit_action
 * @property string|null $ressource_type   ex: 'publication', 'dataset', 'user'
 * @property string|null $ressource_id     UUID
 * @property array|null  $details          JSONB — contexte de l'action
 * @property string|null $ip_address       INET
 * @property string|null $user_agent
 * @property string|null $session_id
 * @property bool     $succes
 * @property string|null $message_erreur
 * @property \Carbon\Carbon $created_at    Seul timestamp (pas de updated_at)
 */
class AuditLog extends Model
{
    use HasUuids;

    protected $table = 'audit_logs';
    protected $primaryKey = 'id';
    protected $keyType = 'string';
    public $incrementing = false;

    /** Pas de updated_at — table append-only */
    const UPDATED_AT = null;

    protected $fillable = [
        'user_id',
        'user_email',
        'user_role',
        'action',
        'ressource_type',
        'ressource_id',
        'details',
        'ip_address',
        'user_agent',
        'session_id',
        'succes',
        'message_erreur',
    ];

    protected $casts = [
        'details'    => 'array',   // JSONB PostgreSQL
        'succes'     => 'boolean',
        'created_at' => 'datetime',
    ];

    // ── Constantes ENUM — audit_action ───────────────────────────────────────
    public const ACTION_CREATE         = 'create';
    public const ACTION_UPDATE         = 'update';
    public const ACTION_DELETE         = 'delete';
    public const ACTION_PUBLISH        = 'publish';
    public const ACTION_SUBMIT         = 'submit';
    public const ACTION_APPROVE        = 'approve';
    public const ACTION_REJECT         = 'reject';
    public const ACTION_LOGIN          = 'login';
    public const ACTION_LOGOUT         = 'logout';
    public const ACTION_DOWNLOAD       = 'download';
    public const ACTION_ACL_CHANGE     = 'acl_change';
    public const ACTION_PASSWORD_RESET = 'password_reset';

    // ── Protection UPDATE/DELETE au niveau Eloquent ───────────────────────────

    /**
     * ⚠️ Bloquer UPDATE au niveau Eloquent (en plus du trigger PostgreSQL)
     */
    public static function boot(): void
    {
        parent::boot();

        static::updating(function () {
            throw new \LogicException('AuditLog est append-only — UPDATE interdit (RG-018).');
        });

        static::deleting(function () {
            throw new \LogicException('AuditLog est append-only — DELETE interdit (RG-018). Utiliser fn_purge_audit_logs() pour la purge planifiée.');
        });
    }

    // ── Relations ────────────────────────────────────────────────────────────

    public function user(): BelongsTo
    {
        return $this->belongsTo(\App\Modules\User\Models\User::class, 'user_id');
    }

    // ── Factory method pour créer un log ─────────────────────────────────────

    /**
     * Crée une entrée d'audit (méthode principale à utiliser)
     */
    public static function log(
        string $action,
        ?string $userId = null,
        ?string $ressourceType = null,
        ?string $ressourceId = null,
        array $details = [],
        bool $succes = true,
        ?string $messageErreur = null
    ): self {
        $user = $userId
            ? \App\Modules\User\Models\User::find($userId)
            : null;

        return static::create([
            'user_id'        => $userId,
            'user_email'     => $user?->email,
            'user_role'      => $user?->role,
            'action'         => $action,
            'ressource_type' => $ressourceType,
            'ressource_id'   => $ressourceId,
            'details'        => $details,
            'ip_address'     => request()->ip(),
            'user_agent'     => request()->userAgent(),
            'session_id'     => session()->getId(),
            'succes'         => $succes,
            'message_erreur' => $messageErreur,
        ]);
    }

    // ── Scopes ───────────────────────────────────────────────────────────────

    public function scopeByUser($query, string $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeByAction($query, string $action)
    {
        return $query->where('action', $action);
    }

    public function scopeByRessource($query, string $type, string $id)
    {
        return $query->where('ressource_type', $type)->where('ressource_id', $id);
    }

    public function scopeRecent($query, int $days = 30)
    {
        return $query->where('created_at', '>=', now()->subDays($days))
                     ->orderBy('created_at', 'desc');
    }

    /** Logs à purger (plus de 12 mois) */
    public function scopeExpired($query, int $retentionMonths = 12)
    {
        return $query->where('created_at', '<', now()->subMonths($retentionMonths));
    }
}
