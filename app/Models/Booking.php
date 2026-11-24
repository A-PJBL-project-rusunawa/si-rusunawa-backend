<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Booking extends Model
{
    protected $fillable = [
        'user_id',
        'room_id',
        'nim',
        'phone',
        'booking_date',
        'check_in_date',
        'notes',
        'status',
        'admin_notes',
    ];

    protected function casts(): array
    {
        return [
            'booking_date' => 'date',
            'check_in_date' => 'date',
            'checked_in_at' => 'datetime',
            'checked_out_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }
}