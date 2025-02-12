<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ScormScoValue extends Model
{
    protected $fillable = [
        'scorm_attempt_id',
        'scorm_sco_id',
        'element',
        'value'
    ];

    public function attempt(): BelongsTo
    {
        return $this->belongsTo(ScormAttempt::class, 'scorm_attempt_id');
    }

    public function sco(): BelongsTo
    {
        return $this->belongsTo(ScormSco::class, 'scorm_sco_id');
    }
}
