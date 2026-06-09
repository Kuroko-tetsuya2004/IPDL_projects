<?php

namespace App\Modules\Content\Models;

use App\Casts\PgArrayCast;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Document extends Model
{
    protected $table = 'documents';
    protected $primaryKey = 'publication_id';
    protected $keyType = 'string';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'publication_id',
        'fichier_url',
        'fichier_nom',
        'fichier_taille',
        'fichier_mime',
        'nb_pages',
        'these_soutenue',
        'date_soutenance',
        'jury_membres',
    ];

    protected $casts = [
        'fichier_taille'  => 'integer',
        'nb_pages'        => 'integer',
        'these_soutenue'  => 'boolean',
        'date_soutenance' => 'date',
        'jury_membres'    => PgArrayCast::class,
    ];

    public function publication(): BelongsTo
    {
        return $this->belongsTo(Publication::class, 'publication_id');
    }
}
