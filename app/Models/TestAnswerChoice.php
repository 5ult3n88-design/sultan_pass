<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TestAnswerChoice extends Model
{
    use HasFactory;

    protected $fillable = [
        'test_question_id',
        'choice_text',
        'is_correct',
        'category_id',
        'order',
    ];

    protected $casts = [
        'is_correct' => 'boolean',
        'order' => 'integer',
    ];

    // Relationships
    public function question(): BelongsTo
    {
        return $this->belongsTo(TestQuestion::class, 'test_question_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(TestCategory::class);
    }
}
