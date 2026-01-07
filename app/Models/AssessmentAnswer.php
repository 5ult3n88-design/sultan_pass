<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\Storage;

class AssessmentAnswer extends Model
{
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'question_id',
        'answer_text',
        'answer_image_path',
        'order',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'order' => 'integer',
    ];

    public function question(): BelongsTo
    {
        return $this->belongsTo(AssessmentQuestion::class, 'question_id');
    }

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(AssessmentCategory::class, 'answer_category_weights', 'answer_id', 'category_id')
            ->withPivot('weight')
            ->withTimestamps();
    }

    public function translations(): HasMany
    {
        return $this->hasMany(AssessmentAnswerTranslation::class, 'answer_id');
    }

    public function score(): HasOne
    {
        return $this->hasOne(AnswerScore::class, 'answer_id');
    }

    /**
     * Get the full URL for the answer image
     */
    public function getImageUrlAttribute(): ?string
    {
        if (!$this->answer_image_path) {
            return null;
        }

        return Storage::url($this->answer_image_path);
    }

    /**
     * Get translated answer text for current language, fallback to default text
     */
    public function getTranslatedTextAttribute(): string
    {
        $language = \App\Models\Language::where('code', app()->getLocale())->first();
        if ($language) {
            $translation = $this->translations()->where('language_id', $language->id)->first();
            if ($translation && $translation->answer_text) {
                return $translation->answer_text;
            }
        }
        return $this->answer_text;
    }
}

