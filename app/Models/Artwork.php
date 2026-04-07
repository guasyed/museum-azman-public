<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class Artwork extends Model
{
    use HasFactory;

    protected $fillable = [
        'artist_id',
        'location_id',
        'title',
        'slug',
        'year',
        'description',
        'medium',
        'size_from_cm',
        'size_to_cm',
        'acquisition_date',
        'acquisition_price',
        'current_valuation',
        'provenance',
        'status',
        'primary_image_path',
        'source_image_url',
    ];

    protected $appends = [
        'primary_image_url',
    ];

    protected function casts(): array
    {
        return [
            'acquisition_date' => 'date',
            'acquisition_price' => 'decimal:2',
            'current_valuation' => 'decimal:2',
            'size_from_cm' => 'decimal:2',
            'size_to_cm' => 'decimal:2',
        ];
    }

    public function artist(): BelongsTo
    {
        return $this->belongsTo(Artist::class);
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

    public function images(): HasMany
    {
        return $this->hasMany(ArtworkImage::class)->orderBy('position');
    }

    public function movements(): HasMany
    {
        return $this->hasMany(Movement::class)->latest('date_out');
    }

    public function getPrimaryImageUrlAttribute(): ?string
    {
        $disk = Storage::disk('public');

        if ($this->primary_image_path && $disk->exists($this->primary_image_path)) {
            return Storage::url($this->primary_image_path);
        }

        $fallbackPath = null;

        if ($this->relationLoaded('images')) {
            $fallbackPath = optional($this->images->first())->path;
        } elseif ($this->exists) {
            $fallbackPath = optional($this->images()->select('path')->orderBy('position')->first())->path;
        }

        if ($fallbackPath && $disk->exists($fallbackPath)) {
            return Storage::url($fallbackPath);
        }

        $sourceImageUrl = is_string($this->source_image_url) ? trim($this->source_image_url) : '';

        if ($sourceImageUrl !== '' && filter_var($sourceImageUrl, FILTER_VALIDATE_URL)) {
            $scheme = strtolower((string) parse_url($sourceImageUrl, PHP_URL_SCHEME));

            if (in_array($scheme, ['http', 'https'], true)) {
                return $sourceImageUrl;
            }
        }

        return null;
    }
}
