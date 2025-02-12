<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ScormElement extends Model
{
    protected $fillable = [
        'element',
    ];

    public function values(): HasMany
    {
        return $this->hasMany(ScormScoValue::class, 'element_id');
    }
}
