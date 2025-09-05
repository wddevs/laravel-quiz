<?php

namespace App\Actions\Quiz;

use App\Models\Quiz;
use App\Models\Question;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class SyncQuestions
{
    /**
     * @param  Quiz  $quiz
     * @param  array $questions  Масив питань з фронта
     */
    public function execute(Quiz $quiz, array $questions): void
    {
        $keptQuestionIds = [];

        foreach ($questions as $q) {
            $question = isset($q['id'])
                ? $quiz->questions()->whereKey($q['id'])->first()
                : null;

            if ($question) {
                $question->update([
                    'order' => $q['order'],
                    'title' => $q['title'],
                    'type' => $q['type'],
                    'required' => $q['required'] ?? false,
                    'help_text' => $q['help_text'] ?? null,
                    'image_path' => $q['image_path'] ?? null,
                ]);
            } else {
                $question = $quiz->questions()->create([
                    'order' => $q['order'],
                    'title' => $q['title'],
                    'type' => $q['type'],
                    'required' => $q['required'] ?? false,
                    'help_text' => $q['help_text'] ?? null,
                    'image_path' => $q['image_path'] ?? null,
                ]);
            }

            $keptQuestionIds[] = $question->id;

            // answers
            $keptAnswerIds = [];
            foreach ($q['answers'] ?? [] as $a) {
                $answer = isset($a['id'])
                    ? $question->answers()->whereKey($a['id'])->first()
                    : null;

                if ($answer) {
                    $answer->update([
                        'order' => $a['order'],
                        'label' => $a['label'] ?? null,
                        'value' => $a['value'] ?? null,
                        'image_path' => $a['image_path'] ?? null,
                    ]);
                } else {
                    $answer = $question->answers()->create([
                        'order' => $a['order'],
                        'label' => $a['label'] ?? null,
                        'value' => $a['value'] ?? null,
                        'image_path' => $a['image_path'] ?? null,
                    ]);
                }
                $keptAnswerIds[] = $answer->id;
            }

            // видалити зайві answers
            $question->answers()
                ->whereNotIn('id', $keptAnswerIds)
                ->delete();
        }

        // видалити зайві questions
        $quiz->questions()
            ->whereNotIn('id', $keptQuestionIds)
            ->get()
            ->each(function (Question $q) {
                $q->answers()->delete();
                $q->delete();
            });
    }
}
