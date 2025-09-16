<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QuizSubmission extends Model
{
    protected $fillable = [
        'quiz_id',
        'uuid',
        'status',
        'paid',
        'viewed_at',
        'contact_name',
        'contact_phone',
        'contact_email',
        'contact_text',
        'ip',
        'referrer',
        'source_url',
        'country',
        'city',
        'discount_percent',
        'answers',
        'extra',
        'result',
    ];

    protected $casts = [
        'paid'=>'bool',
        'viewed_at'=>'datetime',
        'answers'=>'array',
        'extra'=>'array',
        'result'=>'array'
    ];

    public function quiz(): BelongsTo
    {
        return $this->belongsTo(Quiz::class);
    }
}
