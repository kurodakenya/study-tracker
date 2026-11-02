<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudyLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'certification_id',
        'category_id',
        'studied_on',
        'duration_minutes',
        'content',
    ];

    protected function casts(): array
    {
        return [
            'studied_on' => 'date',
            'duration_minutes' => 'integer',
        ];
    }

    // この記録の所有ユーザー（N:1）
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // この記録の対象資格（N:1）
    public function certification(): BelongsTo
    {
        return $this->belongsTo(Certification::class);
    }

    // この記録の学習分野（N:1、任意）
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }
}
