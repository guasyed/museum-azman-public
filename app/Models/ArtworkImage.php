<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class ArtworkImage extends Model
{
    use HasFactory;

    protected $fillable = [
        'artwork_id',
        'path',
        'mime_type',
        'size_bytes',
        'position',
        'original_name',
    ];

    protected $appends = ['url'];

    public function artwork(): BelongsTo
    {
        return $this->belongsTo(Artwork::class);
    }

    public function getUrlAttribute(): ?string
    {
        return Storage::disk('public')->exists($this->path)
            ? asset('storage/'.$this->path)
            : null;
    }
}
