<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class QuizStatDaily extends Model
{
    protected $fillable = ['date','quiz_id','project_domain','impressions','opens','leads'];

    protected $table = 'quiz_stats_daily';

    public static function inc(int $quizId, ?string $domain, string $date, string $field, int $by = 1): void
    {
        static::query()->upsert(
            [
                [
                    'date'=>$date,
                    'quiz_id'=>$quizId,
                    'project_domain'=>$domain,
                    $field=>$by
                ]
            ],
            [
                'date',
                'quiz_id',
                'project_domain'],
            [
                $field => DB::raw("$field + VALUES($field)")
            ]
        );
    }
}
