<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Location extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'type',
        'address',
        'last_audit_date',
    ];

    protected function casts(): array
    {
        return [
            'last_audit_date' => 'date',
        ];
    }

    public function artworks(): HasMany
    {
        return $this->hasMany(Artwork::class);
    }
}
