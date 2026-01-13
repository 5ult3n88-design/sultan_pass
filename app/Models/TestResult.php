<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TestResult extends Model
{
    use HasFactory;

    protected $fillable = [
        'test_assignment_id',
        'total_marks_obtained',
        'percentage',
        'result_status',
        'dominant_category_id',
        'category_scores',
        'completed_at',
    ];

    protected $casts = [
        'total_marks_obtained' => 'integer',
        'percentage' => 'decimal:2',
        'category_scores' => 'array',
        'completed_at' => 'datetime',
    ];

    // Relationships
    public function assignment(): BelongsTo
    {
        return $this->belongsTo(TestAssignment::class, 'test_assignment_id');
    }

    public function dominantCategory(): BelongsTo
    {
        return $this->belongsTo(TestCategory::class, 'dominant_category_id');
    }

    // Helper methods
    public function hasPassed(): bool
    {
        return $this->result_status === 'pass';
    }

    public function hasFailed(): bool
    {
        return $this->result_status === 'fail';
    }

    public function isPending(): bool
    {
        return $this->result_status === 'pending';
    }
}
