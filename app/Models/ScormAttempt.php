<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ScormAttempt extends Model
{
    protected $fillable = [
        'scorm_id',
        'session_id',
        'attempt',
        'started_at',
        'completed_at',
        'last_location',
        'suspend_data',
        'total_time',
        'session_time',
        'score',
        'status',
        'objectives',
        'interactions'
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'objectives' => 'array',
        'interactions' => 'array'
    ];

    public function scorm(): BelongsTo
    {
        return $this->belongsTo(Scorm::class);
    }

    public function values(): HasMany
    {
        return $this->hasMany(ScormScoValue::class, 'scorm_attempt_id');
    }

    public function isComplete(): bool
    {
        return !is_null($this->completed_at);
    }

    public function isInProgress(): bool
    {
        return !is_null($this->started_at) && is_null($this->completed_at);
    }

    public function getScoStatus(int $scoId): ?string
    {
        $value = $this->values()
            ->where('scorm_sco_id', $scoId)
            ->where('element', 'cmi.core.lesson_status')
            ->first();

        return $value ? $value->value : null;
    }

    public function getLessonLocation(int $scoId): ?string
    {
        $value = $this->values()
            ->where('scorm_sco_id', $scoId)
            ->where('element', 'cmi.core.lesson_location')
            ->first();

        return $value ? $value->value : null;
    }

    public function getSuspendData(int $scoId): ?string
    {
        $value = $this->values()
            ->where('scorm_sco_id', $scoId)
            ->where('element', 'cmi.suspend_data')
            ->first();

        return $value ? $value->value : null;
    }

    public function getCompletionStatus(): string
    {
        $totalScos = $this->scorm->scoes()->count();
        if ($totalScos === 0) {
            return 'not attempted';
        }

        $completedScos = 0;
        $passedScos = 0;
        $failedScos = 0;

        foreach ($this->scorm->scoes as $sco) {
            $status = $this->getScoStatus($sco->id);
            if ($status === 'completed' || $status === 'passed') {
                $completedScos++;
                if ($status === 'passed') {
                    $passedScos++;
                }
            } elseif ($status === 'failed') {
                $failedScos++;
                $completedScos++; // Count failed as completed
            }
        }

        if ($completedScos === 0) {
            return 'not attempted';
        } elseif ($completedScos < $totalScos) {
            return 'incomplete';
        } else {
            // All SCOs are completed
            if ($failedScos > 0) {
                return 'failed';
            } elseif ($passedScos === $totalScos) {
                return 'passed';
            } else {
                return 'completed';
            }
        }
    }

    public function getCompletionPercentage(): float
    {
        $totalScos = $this->scorm->scoes()->count();
        if ($totalScos === 0) {
            return 0;
        }

        $completedScos = 0;
        foreach ($this->scorm->scoes as $sco) {
            $status = $this->getScoStatus($sco->id);
            if (in_array($status, ['completed', 'passed', 'failed'])) {
                $completedScos++;
            }
        }

        return ($completedScos / $totalScos) * 100;
    }

    public function getTotalTime(): string
    {
        $value = $this->values()
            ->where('element', 'cmi.core.total_time')
            ->orderBy('id', 'desc')
            ->first();

        return $value ? $value->value : '0:00:00';
    }
}
