<?php

namespace App\Services\QuizConfig\Sections;

use App\Models\Quiz;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;
use App\Services\QuizConfig\Contracts\SectionComposer;

class StartPageComposer implements SectionComposer
{
    public function compose(Quiz $quiz, array $settings): array
    {
        if (!Arr::get($settings,'start.enabled', true)) return [];

        $bg = Arr::get($settings,'start.bg', '/images/home-bg.jpg');
        $logo = Arr::get($settings,'start.logo', '/images/logo.webp');
        if ($bg && !str_starts_with($bg,'http'))   $bg   = asset($bg);
        if ($logo && !str_starts_with($logo,'http')) $logo = asset($logo);

        return [
            'title'      => Arr::get($settings,'start.title', $quiz->title),
            'subtitle'   => Arr::get($settings,'start.subtitle', $quiz->description),
            'buttonText' => Arr::get($settings,'start.buttonText','Почати'),
            'bg'         => $bg,
            'logo'       => $logo,
            'enabled'    =>  Arr::get($settings,'start.enabled',true),
        ];
    }
}
