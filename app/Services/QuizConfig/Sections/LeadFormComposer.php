<?php

namespace App\Services\QuizConfig\Sections;

use App\Models\Quiz;
use Illuminate\Support\Arr;
use App\Services\QuizConfig\Contracts\SectionComposer;

class LeadFormComposer implements SectionComposer
{
    public function compose(Quiz $quiz, array $settings): array
    {
        if (
            !Arr::get($settings,'leadform.enabled', true)
//            || !Arr::has($settings,'leadform.fields')
        ) {
            return [];
        }

//        $fields = collect($settings['leadform']['fields'])->map(fn($f) => [
//            'key'         => $f['key'],
//            'label'       => $f['label'] ?? $f['key'],
//            'type'        => $this->mapFieldType($f['type'] ?? 'text'),
//            'placeholder' => $f['placeholder'] ?? '',
//            'required'    => (bool) ($f['required'] ?? false),
//        ])->values();

        $fields = [
            [
                'key'         => 'name',
                'label'       => "Ім'я",
                'type'        => 'name',
                'placeholder' => 'Ваше ім\'я',
                'required'    => true,
            ],
            [
                'key'         => 'phone',
                'label'       => 'Телефон',
                'type'        => 'tel',
                'placeholder' => '',
                'required'    => true,
            ]
        ];

        return [
            'header'          => Arr::get($settings,'leadform.header','Залиште свій номер телефону та отримайте терміни та вартість за Вашим запитом у месенджері'),
            'content'         => Arr::get($settings,'leadform.content','Помічний нотаріуса перегляне заповнену Вами інформацію, та напише відповідь Вам протягом 30 хвилин'),
            'buttonText'      => Arr::get($settings,'leadform.buttonText','Відправити'),
            'sendOnFirstStep' => (bool) Arr::get($settings,'leadform.sendOnFirstStep', true),
            'fields'          => $fields,
            'motivation'  => [
                'enabled' => (bool) Arr::get($settings,'leadform.motivationEnabled', false),
                'text'    => Arr::get($settings,'leadform.motivationText'),
            ]
        ];
    }

    private function mapFieldType(string $t): string
    {
        $t = strtolower($t);
        return match ($t) {
            'email' => 'email',
            'phone','tel' => 'tel',
            default => 'text',
        };
    }
}
