<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Quiz extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'is_active',
        'settings',
        'user_id',
        'uuid',
        'domain_allowlist',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'settings' => 'array',
        'domain_allowlist' => 'array',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $quiz) {
            $quiz->uuid ??= (string) Str::uuid();
        });
    }

    public function questions(): HasMany
    {
        return $this->hasMany(Question::class)->orderBy('order');
    }
}
