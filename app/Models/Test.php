<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Test extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'title',
        'description',
        'test_type',
        'total_marks',
        'passing_marks',
        'duration_minutes',
        'status',
        'created_by',
    ];

    protected $casts = [
        'total_marks' => 'integer',
        'passing_marks' => 'integer',
        'duration_minutes' => 'integer',
    ];

    // Relationships
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function questions(): HasMany
    {
        return $this->hasMany(TestQuestion::class);
    }

    public function categories(): HasMany
    {
        return $this->hasMany(TestCategory::class);
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(TestAssignment::class);
    }

    // Helper methods
    public function isPercentile(): bool
    {
        return $this->test_type === 'percentile';
    }

    public function isCategorical(): bool
    {
        return $this->test_type === 'categorical';
    }

    public function isPublished(): bool
    {
        return $this->status === 'published';
    }

    public function isDraft(): bool
    {
        return $this->status === 'draft';
    }
}
