<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Category extends Model
{
    use HasFactory;

    protected $fillable = [
        'certification_id',
        'name',
    ];

    // この分野が属する資格（N:1）
    public function certification(): BelongsTo
    {
        return $this->belongsTo(Certification::class);
    }

    // この分野の学習記録（1:N）
    public function studyLogs(): HasMany
    {
        return $this->hasMany(StudyLog::class);
    }
}
