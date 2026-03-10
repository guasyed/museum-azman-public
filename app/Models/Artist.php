<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Artist extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'country',
        'birth_year',
        'biography',
    ];

    public function artworks(): HasMany
    {
        return $this->hasMany(Artwork::class);
    }
}
