<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AnswerScore extends Model
{
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'answer_id',
        'score_value',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'score_value' => 'decimal:2',
    ];

    public function answer(): BelongsTo
    {
        return $this->belongsTo(AssessmentAnswer::class, 'answer_id');
    }
}

