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
            'header'  => Arr::get($settings,'thanks.header','Дякуємо!'),
            'content' => Arr::get($settings,'thanks.content',"Наш консультант у Чехії вже обробляє Вашу заявку та відповість скоро зв'яжеться з Вами Перегляньте наші соціальні мережі"),
            'socials' => collect(Arr::get($settings,'thanks.socials',[
                [
                    "name" => "Instagram",
                    "link" => "https://www.instagram.com/expert_docs/"
                ],
                [
                    "name" => "Youtube",
                    "link" => "https://www.youtube.com/@expert-docs"
                ],
                [
                    "name" => "Tiktok",
                    "link" => "https://www.tiktok.com/@expert_docs1"
                ],
            ]))
                ->map(fn($s)=>['name'=>$s['name']??'','link'=>$s['link']??''])->values(),
        ];
    }
}
