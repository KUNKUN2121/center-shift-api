<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Shift extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'shift_period_id',
        'start_datetime',
        'end_datetime',
        'notes',
    ];

    protected $casts = [
        'start_datetime' => 'datetime',
        'end_datetime'   => 'datetime',
    ];


    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function shiftPeriod()
    {
        return $this->belongsTo(ShiftPeriod::class);
    }
}
