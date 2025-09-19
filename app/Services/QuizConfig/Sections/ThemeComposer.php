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
            'primary'     => Arr::get($settings,'theme.primary','#000'),
            'bg'          => Arr::get($settings,'theme.bg','#ffffff'),
            'text'        => Arr::get($settings,'theme.text','#000'),
            'title'        => Arr::get($settings,'theme.title','#000'),
            'font'        => Arr::get($settings,'theme.font','Inter'),

            'btnRadius'   => Arr::get($settings,'theme.btnRadius',12),
            'btnColor'   => Arr::get($settings,'theme.btnColor','#ffffff'),
            'btnTextColor'   => Arr::get($settings,'theme.btnTextColor','#000000'),

            'inputRadius' => Arr::get($settings,'theme.inputRadius',10),
        ];
    }
}
