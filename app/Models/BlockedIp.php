<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BlockedIp extends Model {
    protected $fillable = [
        'ip',
        'quiz_id',
        'user_id',
        'reason'
    ];

    public function quiz(): BelongsTo {
        return $this->belongsTo(Quiz::class);
    }
}
