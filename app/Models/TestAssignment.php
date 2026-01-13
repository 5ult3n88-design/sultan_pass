<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class TestAssignment extends Model
{
    use HasFactory;

    protected $fillable = [
        'test_id',
        'participant_id',
        'assigned_by',
        'assigned_at',
        'due_date',
        'status',
    ];

    protected $casts = [
        'assigned_at' => 'datetime',
        'due_date' => 'datetime',
    ];

    // Relationships
    public function test(): BelongsTo
    {
        return $this->belongsTo(Test::class);
    }

    public function participant(): BelongsTo
    {
        return $this->belongsTo(User::class, 'participant_id');
    }

    public function assignedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }

    public function responses(): HasMany
    {
        return $this->hasMany(TestResponse::class);
    }

    public function result(): HasOne
    {
        return $this->hasOne(TestResult::class);
    }

    // Helper methods
    public function isCompleted(): bool
    {
        return $this->status === 'submitted' || $this->status === 'graded';
    }

    public function isGraded(): bool
    {
        return $this->status === 'graded';
    }

    public function needsGrading(): bool
    {
        return $this->status === 'submitted';
    }
}
