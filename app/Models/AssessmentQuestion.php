<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class AssessmentQuestion extends Model
{
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'assessment_id',
        'question_type',
        'question_text',
        'question_image_path',
        'order',
        'max_score',
        'is_required',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'order' => 'integer',
        'max_score' => 'decimal:2',
        'is_required' => 'boolean',
    ];

    public function assessment(): BelongsTo
    {
        return $this->belongsTo(Assessment::class);
    }

    public function answers(): HasMany
    {
        return $this->hasMany(AssessmentAnswer::class, 'question_id');
    }

    public function translations(): HasMany
    {
        return $this->hasMany(AssessmentQuestionTranslation::class, 'question_id');
    }

    public function responses(): HasMany
    {
        return $this->hasMany(ParticipantResponse::class, 'question_id');
    }

    /**
     * Get the full URL for the question image
     */
    public function getImageUrlAttribute(): ?string
    {
        if (!$this->question_image_path) {
            return null;
        }

        return Storage::url($this->question_image_path);
    }

    /**
     * Get translated question text for current language, fallback to default text
     */
    public function getTranslatedTextAttribute(): string
    {
        $language = \App\Models\Language::where('code', app()->getLocale())->first();
        if ($language) {
            $translation = $this->translations()->where('language_id', $language->id)->first();
            if ($translation && $translation->question_text) {
                return $translation->question_text;
            }
        }
        return $this->question_text;
    }
}

