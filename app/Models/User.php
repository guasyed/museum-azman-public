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
}
