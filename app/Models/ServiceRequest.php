<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\RequestStatusLog;
use Carbon\Carbon;
use App\Models\ActivityLog;

class ServiceRequest extends Model
{
    public const STATUS_FLOW = [
        'pending' => ['assigned'],
        'assigned' => ['in_progress'],
        'in_progress' => ['completed'],
        'completed' => ['reopened'],
        'reopened' => ['assigned'],
    ];
    protected $fillable = [
        'customer_id',
        'assigned_to',
        'title',
        'description',
        'status',
        'started_at',
        'completed_at',
        'sla_hours', // ✅ ADD THIS
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function customer()
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    public function technician()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function statusLogs()
    {
        return $this->hasMany(RequestStatusLog::class, 'request_id');
    }

    /*
    |--------------------------------------------------------------------------
    | Boot (Optional Safety)
    |--------------------------------------------------------------------------
    */

    protected static function booted()
    {
        static::creating(function ($request) {
            if (!$request->status) {
                $request->status = 'pending';
            }
        });
    }
    public function logs()
    {
        return $this->hasMany(ActivityLog::class);
    }


    public function getSlaDeadline(): ?Carbon
    {
        if (!$this->started_at || $this->sla_hours === null) {
            return null;
        }

        return $this->started_at->copy()->addHours($this->sla_hours);
    }
    public function getRemainingSlaTime(): ?int
    {
        $deadline = $this->getSlaDeadline();

        if (!$deadline) {
            return null;
        }

        return now()->diffInSeconds($deadline, false); // negative if overdue
    }
    public function getDynamicSlaStatus(): ?string
    {
        $remaining = $this->getRemainingSlaTime();

        if ($remaining === null) {
            return null;
        }

        if ($remaining < 0) {
            return 'breached';
        }

        // ⚠️ nearing breach threshold (20% time left)
        $total = $this->sla_hours * 3600;

        if ($remaining <= ($total * 0.2)) {
            return 'nearing_breach';
        }

        return 'on_time';
    }
    public function getFormattedRemainingTime(): ?string
    {
        $seconds = $this->getRemainingSlaTime();

        if ($seconds === null) {
            return null;
        }

        return gmdate('H:i:s', abs($seconds));
    }
    public function isOverdue(): bool
    {
        return $this->getDynamicSlaStatus() === 'breached';
    }
    public function canTransitionTo(string $newStatus): bool
    {
        return in_array($newStatus, self::STATUS_FLOW[$this->status] ?? []);
    }
}