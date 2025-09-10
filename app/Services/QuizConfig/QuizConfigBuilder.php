<?php // app/Services/QuizConfig/QuizConfigBuilder.php
namespace App\Services\QuizConfig;

use App\Models\Quiz;
use App\Services\QuizConfig\Contracts\SectionComposer;
use App\Services\QuizConfig\Contracts\StepComposer;
use Illuminate\Support\Arr;

class QuizConfigBuilder
{
    public function __construct(
        private SectionComposer $theme,
        private SectionComposer $assistant,
        private SectionComposer $marketing,
        private SectionComposer $startPage,
        private StepComposer    $questions,
        private SectionComposer $leadForm,
        private SectionComposer $thanksPage,
    ) {}

    public function build(Quiz $quiz): array
    {
        $settings = $quiz->settings ?? [];

        return [
            'uuid'        => $quiz->uuid,
            'title'       => $quiz->title,
            'description' => $quiz->description,

            'theme'       => $this->theme->compose($quiz, $settings),
            'assistant'   => $this->assistant->compose($quiz, $settings),
            'marketing'   => $this->marketing->compose($quiz, $settings),

            'order'       => Arr::get($settings,'order', ['start','questions','leadform','thanks']),
            'startPage'   => $this->startPage->compose($quiz, $settings) ?: null,
            'steps'       => $this->questions->compose($quiz, $settings), // лише питання
            'leadForm'    => $this->leadForm->compose($quiz, $settings) ?: null,
            'thanksPage'  => $this->thanksPage->compose($quiz, $settings) ?: null,
        ];
    }

    public function etag(Quiz $quiz): string
    {
        $questionsUpdated = optional($quiz->questions->max('updated_at'))->timestamp ?? 0;
        $answersUpdated   = optional($quiz->questions->flatMap->answers->max('updated_at'))->timestamp ?? 0;

        $fingerprint = implode('|', [
            $quiz->id,
            $quiz->updated_at?->timestamp ?? 0,
            $questionsUpdated,
            $answersUpdated,
            md5(json_encode($quiz->settings ?? [])),
        ]);

        return '"' . sha1($fingerprint) . '"';
    }
}
