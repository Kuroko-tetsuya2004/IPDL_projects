<?php

namespace App\Modules\Admin\Models;

use App\Modules\User\Models\User;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DocumentAdministratif extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'documents_administratifs';

    protected $fillable = [
        'user_id',
        'type_document',
        'reference',
        'donnees',
        'file_path',
    ];

    protected $casts = [
        'donnees' => 'array',
    ];

    /**
     * Obtenir l'utilisateur qui a généré le document.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
