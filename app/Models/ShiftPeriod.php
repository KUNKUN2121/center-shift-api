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
        'deadline',
        'status',
    ];

    // 日付として扱う設定（ISO8601形式 に自動変換）
    protected $casts = [
        'deadline' => 'datetime',
    ];

    // ▼ リレーション定義

    public function submissions()
    {
        return $this->hasMany(Submission::class);
    }

    public function shifts()
    {
        return $this->hasMany(Shift::class);
    }
}
