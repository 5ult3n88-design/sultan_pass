<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AssessmentCategory extends Model
{
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'assessment_id',
        'name',
        'description',
        'color',
        'order',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'order' => 'integer',
    ];

    public function assessment(): BelongsTo
    {
        return $this->belongsTo(Assessment::class);
    }

    public function translations(): HasMany
    {
        return $this->hasMany(AssessmentCategoryTranslation::class, 'category_id');
    }

    public function answers(): BelongsToMany
    {
        return $this->belongsToMany(AssessmentAnswer::class, 'answer_category_weights', 'category_id', 'answer_id')
            ->withPivot('weight')
            ->withTimestamps();
    }

    /**
     * Get translated name for current language, fallback to default name
     */
    public function getTranslatedNameAttribute(): string
    {
        $language = \App\Models\Language::where('code', app()->getLocale())->first();
        if ($language) {
            $translation = $this->translations()->where('language_id', $language->id)->first();
            if ($translation && $translation->name) {
                return $translation->name;
            }
        }
        return $this->name;
    }

    /**
     * Get translated description for current language, fallback to default description
     */
    public function getTranslatedDescriptionAttribute(): ?string
    {
        $language = \App\Models\Language::where('code', app()->getLocale())->first();
        if ($language) {
            $translation = $this->translations()->where('language_id', $language->id)->first();
            if ($translation && $translation->description) {
                return $translation->description;
            }
        }
        return $this->description;
    }
}

