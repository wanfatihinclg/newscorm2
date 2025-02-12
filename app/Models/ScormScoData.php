<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ScormScoData extends Model
{
    protected $fillable = [
        'sco_id',
        'name',
        'value',
    ];

    public function sco(): BelongsTo
    {
        return $this->belongsTo(ScormSco::class, 'sco_id');
    }
}
