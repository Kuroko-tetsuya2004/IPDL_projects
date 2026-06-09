<?php

namespace App\Modules\AxeThematique\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Modèle AxeThematique — Table `axes_thematiques`
 * INC-07 corrigé : déplacé de User/Models → AxeThematique/Models (bon module)
 *
 * Axes initiaux (données seed SQL) :
 *   - epidemio  : Épidémiologie Numérique    (#1F4E79)
 *   - climat    : Modélisation Climatique     (#0F6E56)
 *   - fablab    : FabLab & Makers             (#993C1D)
 *   - iot       : IoT & Capteurs              (#854F0B)
 *   - methodes  : Méthodes & Algorithmes      (#534AB7)
 *
 * @property string   $id
 * @property string   $code
 * @property string   $nom_fr
 * @property string|null $nom_en
 * @property string|null $description_fr
 * @property string|null $description_en
 * @property string|null $logo_url
 * @property string|null $couleur_hex
 * @property int      $ordre_affichage
 * @property bool     $actif
 * @property string|null $responsable_id   FK → users
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

    public function responsable(): BelongsTo
    {
        return $this->belongsTo(\App\Modules\User\Models\User::class, 'responsable_id');
    }

    public function membres(): BelongsToMany
    {
        return $this->belongsToMany(
            \App\Modules\User\Models\User::class,
            'users_axes', 'axe_id', 'user_id'
        )->withPivot(['role_dans_axe', 'depuis']);
    }

    public function publications(): HasMany
    {
        return $this->hasMany(\App\Modules\Content\Models\Publication::class, 'axe_id');
    }

    public function outilsDoctoraux(): HasMany
    {
        return $this->hasMany(\App\Modules\Integration\Models\OutilDoctoral::class, 'axe_id');
    }

    // ── Scopes ───────────────────────────────────────────────────────────────

    public function scopeActif($query)
    {
        return $query->where('actif', true)->orderBy('ordre_affichage');
    }

    public function scopeByCode($query, string $code)
    {
        return $query->where('code', $code);
    }

    // ── Helpers ──────────────────────────────────────────────────────────────

    /** Nom localisé selon la langue active */
    public function getNomAttribute(): string
    {
        return current_locale() === 'en' && $this->nom_en
            ? $this->nom_en
            : $this->nom_fr;
    }
}
