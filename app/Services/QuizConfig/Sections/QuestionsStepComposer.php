<?php

namespace App\Services\QuizConfig\Sections;

use App\Models\Quiz;
use App\Services\QuizConfig\Contracts\StepComposer;

class QuestionsStepComposer implements StepComposer
{
    public function name(): string { return 'questions'; }

    public function compose(Quiz $quiz, array $settings): array
    {
        return $quiz->questions->map(function ($q) {
            return [
                'id'       => $q->id,
                'order'    => $q->order,
                'title'    => $q->title,
                'type'     => $q->type, // radio|checkbox|text
                'required' => (bool) $q->required,
                'help'     => $q->help_text,
                'image'    => $q->image_url,
                'answers'  => $q->answers->map(fn($a) => [
                    'id'    => $a->id,
                    'order' => $a->order,
                    'label' => $a->label,
                    'value' => $a->value,
                    'image' => $a->image_url,
                ])->values(),
            ];
        })->values()->all();
    }
}
