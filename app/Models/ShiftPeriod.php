<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShiftPeriod extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'year',
        'month',
        'start_date',
        'end_date',
        'status',
    ];


    public function submissions()
    {
        return $this->hasMany(Submission::class);
    }

    public function shifts()
    {
        return $this->hasMany(Shift::class);
    }
}
