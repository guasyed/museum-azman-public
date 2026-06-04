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
        'from_location_code',
        'to_location',
        'to_location_code',
        'date_out',
        'expected_return_date',
        'completed_date',
        'movement_type',
        'external_reason',
        'external_party',
        'responsible_handler',
        'approved_by',
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
            'completed_date' => 'date',
        ];
    }

    public function artwork(): BelongsTo
    {
        return $this->belongsTo(Artwork::class);
    }
}
