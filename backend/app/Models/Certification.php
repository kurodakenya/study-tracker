<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Certification extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'exam_date',
        'status',
        'memo',
    ];

    protected function casts(): array
    {
        return [
            'exam_date' => 'date',
        ];
    }

    // この資格を持つユーザー（N:1）
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // この資格の分野一覧（1:N）
    public function categories(): HasMany
    {
        return $this->hasMany(Category::class);
    }

    // この資格の学習記録（1:N）
    public function studyLogs(): HasMany
    {
        return $this->hasMany(StudyLog::class);
    }
}
