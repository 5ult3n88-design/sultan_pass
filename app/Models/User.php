<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'username',
        'full_name',
        'email',
        'password',
        'role',
        'language_pref',
        'rank',
        'department',
        'status',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function language(): BelongsTo
    {
        return $this->belongsTo(Language::class, 'language_pref');
    }

    public function passwordResetRequests(): HasMany
    {
        return $this->hasMany(PasswordResetRequest::class);
    }

    public function assessments()
    {
        return $this->belongsToMany(Assessment::class, 'assessment_participants', 'participant_id', 'assessment_id')
            ->withPivot('status', 'score', 'feedback')
            ->withTimestamps();
    }

    public function participantResponses(): HasMany
    {
        return $this->hasMany(ParticipantResponse::class, 'participant_id');
    }

    /**
     * Get the user's display name (full_name or username)
     */
    public function getNameAttribute(): string
    {
        return $this->full_name ?? $this->username ?? $this->email;
    }

    /**
     * Role hierarchy mapping: higher number = higher privilege
     */
    protected static array $roleHierarchy = [
        'participant' => 1,
        'assessor' => 2,
        'manager' => 3,
        'admin' => 4,
    ];

    /**
     * Check if user has a specific role
     */
    public function hasRole(string $role): bool
    {
        return $this->role === $role;
    }

    /**
     * Check if user has any of the given roles
     */
    public function hasAnyRole(array $roles): bool
    {
        return in_array($this->role, $roles, true);
    }

    /**
     * Check if user has higher or equal privilege than given role
     */
    public function hasRoleOrAbove(string $role): bool
    {
        $userLevel = self::$roleHierarchy[$this->role] ?? 0;
        $requiredLevel = self::$roleHierarchy[$role] ?? 0;

        return $userLevel >= $requiredLevel;
    }

    /**
     * Check if user can view another user's data (based on hierarchy)
     */
    public function canView(User $otherUser): bool
    {
        if ($this->id === $otherUser->id) {
            return true;
        }

        // Participants can only view themselves
        if ($this->hasRole('participant')) {
            return false;
        }

        $userLevel = self::$roleHierarchy[$this->role] ?? 0;
        $otherLevel = self::$roleHierarchy[$otherUser->role] ?? 0;

        // Higher or equal roles can view lower or equal roles
        return $userLevel >= $otherLevel;
    }
}
