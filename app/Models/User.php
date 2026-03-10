<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

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
        'name',
        'email',
        'role',
        'role_id',
        'avatar_path',
        'is_approved',
        'approved_at',
        'appearance_theme',
        'appearance_density',
        'appearance_accent_color',
        'appearance_heading_font',
        'appearance_body_font',
        'notification_movement_alerts',
        'notification_insurance_expiry',
        'notification_loan_return_due',
        'notification_restoration_due',
        'notification_valuation_updates',
        'notification_delivery_email',
        'notification_delivery_browser',
        'password',
    ];

    protected $appends = [
        'avatar_url',
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
            'approved_at' => 'datetime',
            'is_approved' => 'boolean',
            'notification_movement_alerts' => 'boolean',
            'notification_insurance_expiry' => 'boolean',
            'notification_loan_return_due' => 'boolean',
            'notification_restoration_due' => 'boolean',
            'notification_valuation_updates' => 'boolean',
            'notification_delivery_email' => 'boolean',
            'notification_delivery_browser' => 'boolean',
            'password' => 'hashed',
        ];
    }

    public function getAvatarUrlAttribute(): ?string
    {
        if (! $this->avatar_path) {
            return null;
        }

        return Storage::disk('public')->exists($this->avatar_path)
            ? asset('storage/'.$this->avatar_path)
            : null;
    }

    public function roleRelation(): BelongsTo
    {
        return $this->belongsTo(Role::class, 'role_id');
    }

    public function getRoleLabelAttribute(): string
    {
        if ($this->relationLoaded('roleRelation') && $this->roleRelation) {
            return $this->roleRelation->name;
        }

        return ucfirst((string) ($this->role ?: 'User'));
    }

    public function isAdmin(): bool
    {
        $roleSlug = optional($this->roleRelation)->slug;

        return in_array($roleSlug, ['admin', 'owner'], true)
            || in_array((string) $this->role, ['admin', 'owner'], true);
    }

    public function isLogisticsHandler(): bool
    {
        $roleSlug = optional($this->roleRelation)->slug;
        $legacyRole = strtolower(trim((string) $this->role));

        return $roleSlug === 'logistics-handler'
            || in_array($legacyRole, ['logistics-handler', 'logistics handler', 'handler', 'movement handler', 'user handler', 'movement-tracker'], true);
    }

    public function isApproved(): bool
    {
        return (bool) ($this->is_approved ?? true);
    }
}
