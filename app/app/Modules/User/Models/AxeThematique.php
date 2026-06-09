<?php

namespace App\Modules\User\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Modèle AxeThematique — Mappé sur la table `axes_thematiques`
 *
 * @property string   $id
 * @property string   $code              ex: 'epidemio', 'climat', 'fablab', 'iot', 'methodes'
 * @property string   $nom_fr
 * @property string|null $nom_en
 * @property string|null $description_fr
 * @property string|null $description_en
 * @property string|null $logo_url
 * @property string|null $couleur_hex    ex: '#1F4E79'
 * @property int      $ordre_affichage
 * @property bool     $actif
 * @property string|null $responsable_id UUID → users
 */
class AxeThematique extends Model
{
    use HasUuids;

    protected $table = 'axes_thematiques';
    protected $primaryKey = 'id';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'code',
        'nom_fr',
        'nom_en',
        'description_fr',
        'description_en',
        'logo_url',
        'couleur_hex',
        'ordre_affichage',
        'actif',
        'responsable_id',
    ];

    protected $casts = [
        'actif'           => 'boolean',
        'ordre_affichage' => 'integer',
    ];

    // ── Relations ────────────────────────────────────────────────────────────

    /** Responsable de l'axe */
    public function responsable(): BelongsTo
    {
        return $this->belongsTo(User::class, 'responsable_id');
    }

    /** Membres de l'axe (N:N via users_axes) */
    public function membres(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'users_axes', 'axe_id', 'user_id')
                    ->withPivot(['role_dans_axe', 'depuis']);
    }

    /** Publications de cet axe */
    public function publications(): HasMany
    {
        return $this->hasMany(
            \App\Modules\Content\Models\Publication::class,
            'axe_id'
        );
    }

    /** Outils doctoraux rattachés à cet axe */
    public function outilsDoctoraux(): HasMany
    {
        return $this->hasMany(
            \App\Modules\Integration\Models\OutilDoctoral::class,
            'axe_id'
        );
    }

    // ── Scopes ───────────────────────────────────────────────────────────────

    /** Axes actifs uniquement */
    public function scopeActif($query)
    {
        return $query->where('actif', true)->orderBy('ordre_affichage');
    }

    /** Trouver par code */
    public function scopeByCode($query, string $code)
    {
        return $query->where('code', $code);
    }
}
