<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Course extends Model
{
    protected $fillable = [
        'name',
        'description',
    ];

    public function scorms(): HasMany
    {
        return $this->hasMany(Scorm::class);
    }
}
