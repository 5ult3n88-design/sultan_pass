<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PasswordResetRequest extends Model
{
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'email',
        'status',
        'token',
        'approved_by',
        'approved_at',
        'declined_at',
        'temporary_password_encrypted',
        'temporary_password_expires_at',
        'notes',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'approved_at' => 'datetime',
        'declined_at' => 'datetime',
        'temporary_password_expires_at' => 'datetime',
    ];

    public const STATUS_PENDING = 'pending';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_DECLINED = 'declined';

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    public function getTemporaryPasswordAttribute(): ?string
    {
        if (! $this->temporary_password_encrypted) {
            return null;
        }

        try {
            return decrypt($this->temporary_password_encrypted);
        } catch (\Throwable $exception) {
            report($exception);
            return null;
        }
    }
}
