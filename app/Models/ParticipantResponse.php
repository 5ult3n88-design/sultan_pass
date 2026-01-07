<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class ParticipantResponse extends Model
{
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'assessment_id',
        'participant_id',
        'question_id',
        'selected_answer_ids',
        'written_response_text',
        'written_response_image_path',
        'graded_score',
        'graded_categories',
        'graded_by',
        'graded_at',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'selected_answer_ids' => 'array',
        'graded_categories' => 'array',
        'graded_score' => 'decimal:2',
        'graded_at' => 'datetime',
    ];

    public function assessment(): BelongsTo
    {
        return $this->belongsTo(Assessment::class);
    }

    public function participant(): BelongsTo
    {
        return $this->belongsTo(User::class, 'participant_id');
    }

    public function question(): BelongsTo
    {
        return $this->belongsTo(AssessmentQuestion::class, 'question_id');
    }

    public function grader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'graded_by');
    }

    /**
     * Get the full URL for the written response image
     */
    public function getImageUrlAttribute(): ?string
    {
        if (!$this->written_response_image_path) {
            return null;
        }

        return Storage::url($this->written_response_image_path);
    }

    /**
     * Check if response has been graded
     */
    public function isGraded(): bool
    {
        return $this->graded_at !== null;
    }
}

