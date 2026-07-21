<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VisitRequest extends Model
{
    protected $fillable = [
        'name', 'phone', 'email', 'occupation', 'company', 'city', 'social',
        'purpose', 'category', 'preferred_date', 'guests', 'source', 'message',
        'preferences', 'reviewed_at', 'ip_address',
    ];

    protected function casts(): array
    {
        return [
            'preferred_date' => 'date',
            'preferences' => 'array',
            'reviewed_at' => 'datetime',
        ];
    }
}
