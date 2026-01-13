<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TestResponse extends Model
{
    use HasFactory;

    protected $fillable = [
        'test_assignment_id',
        'test_question_id',
        'selected_choice_id',
        'typed_answer',
        'is_graded',
        'is_correct',
        'assigned_category_id',
        'marks_awarded',
        'assessor_feedback',
        'graded_by',
        'graded_at',
    ];

    protected $casts = [
        'is_graded' => 'boolean',
        'is_correct' => 'boolean',
        'marks_awarded' => 'integer',
        'graded_at' => 'datetime',
    ];

    // Relationships
    public function assignment(): BelongsTo
    {
        return $this->belongsTo(TestAssignment::class, 'test_assignment_id');
    }

    public function question(): BelongsTo
    {
        return $this->belongsTo(TestQuestion::class, 'test_question_id');
    }

    public function selectedChoice(): BelongsTo
    {
        return $this->belongsTo(TestAnswerChoice::class, 'selected_choice_id');
    }

    public function assignedCategory(): BelongsTo
    {
        return $this->belongsTo(TestCategory::class, 'assigned_category_id');
    }

    public function gradedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'graded_by');
    }

    // Helper methods
    public function needsGrading(): bool
    {
        return !$this->is_graded && $this->typed_answer !== null;
    }
}
