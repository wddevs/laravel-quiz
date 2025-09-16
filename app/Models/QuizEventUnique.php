<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QuizEventUnique extends Model
{
    protected $fillable = ['date','quiz_id','project_domain','type','visitor_id','page_hash'];
}
