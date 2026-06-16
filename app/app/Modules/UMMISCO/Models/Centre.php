<?php

namespace App\Modules\UMMISCO\Models;

use Illuminate\Database\Eloquent\Model;

class Centre extends Model
{
    protected $table = 'centres';
    protected $fillable = ['nom', 'url', 'description_courte'];

    public function membres()
    {
        return $this->hasMany(MembreUmmisco::class, 'centre_id');
    }
}
