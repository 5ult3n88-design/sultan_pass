<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TestCategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'test_id',
        'name',
        'description',
        'color',
        'order',
    ];

    protected $casts = [
        'order' => 'integer',
    ];

    // Relationships
    public function test(): BelongsTo
    {
        return $this->belongsTo(Test::class);
    }

    public function answerChoices(): HasMany
    {
        return $this->hasMany(TestAnswerChoice::class, 'category_id');
    }
}
