<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Movement extends Model
{
    use HasFactory;

    protected $fillable = [
        'external_movement_id',
        'artwork_id',
        'from_location',
        'to_location',
        'date_out',
        'expected_return_date',
        'responsible_handler',
        'reason',
        'status',
        'notes',
        'condition_report',
    ];

    protected function casts(): array
    {
        return [
            'date_out' => 'date',
            'expected_return_date' => 'date',
        ];
    }

    public function artwork(): BelongsTo
    {
        return $this->belongsTo(Artwork::class);
    }
}
