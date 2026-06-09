<?php

namespace App\Modules\Dataset\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DatasetFichier extends Model
{
    use HasUuids;

    protected $table = 'datasets_fichiers';
    public $timestamps = false;

    protected $fillable = [
        'dataset_id',
        'nom',
        'description',
        'chemin_minio',
        'bucket_minio',
        'taille_octets',
        'format',
        'checksum_sha256',
        'version',
        'est_principal',
    ];

    protected $casts = [
        'taille_octets' => 'integer',
        'est_principal' => 'boolean',
        'created_at'    => 'datetime',
    ];

    public function dataset(): BelongsTo
    {
        return $this->belongsTo(Dataset::class, 'dataset_id', 'publication_id');
    }
}
