<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Enums\QuestionType;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class Question extends Model
{
    protected $fillable = [
        'quiz_id',
        'order',
        'title',
        'type',
        'required',
        'help_text',
        'image_path'
    ];

    protected $casts = [
        'required' => 'bool',
        'type'     => QuestionType::class,
    ];

    public function answers(): HasMany
    {
        return $this->hasMany(Answer::class)->orderBy('order');
    }

    public function getImageUrlAttribute(): ?string
    {
        return $this->image_path ? Storage::disk('public')->url($this->image_path) : null;
    }
}
