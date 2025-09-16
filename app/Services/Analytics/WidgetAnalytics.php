<?php

namespace App\Services\Analytics;

use App\Models\Quiz;
use App\Models\QuizEventUnique;
use App\Models\QuizStatDaily;

class WidgetAnalytics
{
    public function hit(Quiz $quiz, string $type, ?string $domain, string $vid, ?string $pageUrl = null): bool
    {
        $date = now()->toDateString();
        $pageHash = $type === 'impression' ? $this->pageHash($pageUrl) : '';

        try {
            QuizEventUnique::create([
                'date'           => $date,
                'quiz_id'        => $quiz->id,
                'project_domain' => $domain,
                'type'           => $type,          // impression|open|lead
                'visitor_id'     => $vid,
                'page_hash'      => $pageHash,
            ]);
        } catch (\Illuminate\Database\QueryException) {
            return false; // уже враховано
        }

        $field = match ($type) {
            'impression' => 'impressions',
            'open'       => 'opens',
            'lead'       => 'leads',
            default      => null,
        };

        if ($field) {
            QuizStatDaily::inc($quiz->id, $domain, $date, $field, 1);
            return true;
        }
        return false;
    }

    private function pageHash(?string $url): string
    {
        if (!$url) return '';
        $p = parse_url($url);
        $key = ($p['scheme'] ?? '') . '://' . ($p['host'] ?? '') . ($p['path'] ?? '/');
        return sha1($key);
    }
}
