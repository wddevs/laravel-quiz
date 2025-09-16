<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Answer extends Model
{
    protected $fillable = [
        'question_id',
        'order',
        'label',
        'value',
        'image_path'
    ];

    public function getImageUrlAttribute(): ?string
    {
        return $this->image_path ? $this->image_path : null;
    }
}
