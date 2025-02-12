<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Scorm extends Model
{
    protected $fillable = [
        'course_id',
        'name',
        'scorm_type',
        'reference',
        'intro',
        'version',
        'max_grade',
        'grade_method',
        'what_grade',
        'max_attempt',
        'force_completed',
        'force_new_attempt',
        'last_attempt_lock',
        'mastery_override',
        'display_attempt_status',
        'display_course_structure',
        'sha1_hash',
        'md5_hash',
        'revision',
        'launch',
        'skip_view',
        'hide_browse',
        'hide_toc',
        'nav',
        'nav_position_left',
        'nav_position_top',
        'auto',
        'popup',
        'options',
        'width',
        'height',
        'time_open',
        'time_close',
        'auto_commit',
    ];

    protected $casts = [
        'force_completed' => 'boolean',
        'force_new_attempt' => 'boolean',
        'last_attempt_lock' => 'boolean',
        'mastery_override' => 'boolean',
        'display_attempt_status' => 'boolean',
        'display_course_structure' => 'boolean',
        'skip_view' => 'boolean',
        'hide_browse' => 'boolean',
        'hide_toc' => 'boolean',
        'nav' => 'boolean',
        'auto' => 'boolean',
        'popup' => 'boolean',
        'auto_commit' => 'boolean',
        'time_open' => 'datetime',
        'time_close' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();

        static::deleting(function ($scorm) {
            // Delete related records
            $scorm->scoes()->delete();
            $scorm->attempts()->delete();
        });
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function scoes(): HasMany
    {
        return $this->hasMany(ScormSco::class);
    }

    public function attempts(): HasMany
    {
        return $this->hasMany(ScormAttempt::class);
    }
}
