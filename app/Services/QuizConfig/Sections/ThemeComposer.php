<?php // app/Services/QuizConfig/Sections/ThemeComposer.php
namespace App\Services\QuizConfig\Sections;

use App\Models\Quiz;
use Illuminate\Support\Arr;
use App\Services\QuizConfig\Contracts\SectionComposer;

class ThemeComposer implements SectionComposer
{
    public function compose(Quiz $quiz, array $settings): array
    {
        return [
            'primary'     => Arr::get($settings,'theme.primary','#3B82F6'),
            'bg'          => Arr::get($settings,'theme.bg','#ffffff'),
            'text'        => Arr::get($settings,'theme.text','#0f172a'),
            'font'        => Arr::get($settings,'theme.font','Inter'),
            'btnRadius'   => Arr::get($settings,'theme.btnRadius',12),
            'inputRadius' => Arr::get($settings,'theme.inputRadius',10),
        ];
    }
}
