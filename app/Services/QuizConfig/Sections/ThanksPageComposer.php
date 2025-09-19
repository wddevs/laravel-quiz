<?php

namespace App\Services\QuizConfig\Sections;

use App\Models\Quiz;
use Illuminate\Support\Arr;
use App\Services\QuizConfig\Contracts\SectionComposer;

class ThanksPageComposer implements SectionComposer
{
    public function compose(Quiz $quiz, array $settings): array
    {
        if (!Arr::get($settings,'thanks.enabled', true)) return [];

        return [
            'header'  => Arr::get($settings,'thanks.header','Thank You!'),
            'content' => Arr::get($settings,'thanks.content',""),
            'socials' => collect(Arr::get($settings,'thanks.socials',[
                [
                    "name" => "Instagram",
                    "link" => ""
                ],
                [
                    "name" => "Youtube",
                    "link" => ""
                ],
                [
                    "name" => "Tiktok",
                    "link" => ""
                ],
            ]))
                ->map(fn($s)=>['name'=>$s['name']??'','link'=>$s['link']??''])->values(),
        ];
    }
}
