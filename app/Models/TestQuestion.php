<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TestQuestion extends Model
{
    use HasFactory;

    protected $fillable = [
        'test_id',
        'question_text',
        'question_type',
        'marks',
        'order',
        'is_required',
    ];

    protected $casts = [
        'marks' => 'integer',
        'order' => 'integer',
        'is_required' => 'boolean',
    ];

    // Relationships
    public function test(): BelongsTo
    {
        return $this->belongsTo(Test::class);
    }

    public function answerChoices(): HasMany
    {
        return $this->hasMany(TestAnswerChoice::class);
    }

    public function responses(): HasMany
    {
        return $this->hasMany(TestResponse::class);
    }

    // Helper methods
    public function isMultipleChoice(): bool
    {
        return $this->question_type === 'multiple_choice';
    }

    public function isTyped(): bool
    {
        return $this->question_type === 'typed';
    }
}
