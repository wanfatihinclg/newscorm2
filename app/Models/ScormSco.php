<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ScormSco extends Model
{
    protected $table = 'scorm_scoes';

    protected $fillable = [
        'scorm_id',
        'manifest',
        'organization',
        'parent',
        'identifier',
        'launch',
        'scorm_type',
        'title',
        'sort_order',
    ];

    public function scorm(): BelongsTo
    {
        return $this->belongsTo(Scorm::class);
    }

    public function scoData(): HasMany
    {
        return $this->hasMany(ScormScoData::class);
    }

    public function scoValues(): HasMany
    {
        return $this->hasMany(ScormScoValue::class);
    }
}
