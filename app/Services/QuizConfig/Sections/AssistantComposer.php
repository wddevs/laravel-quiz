<?php // app/Services/QuizConfig/Sections/AssistantComposer.php
namespace App\Services\QuizConfig\Sections;

use App\Models\Quiz;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;
use App\Services\QuizConfig\Contracts\SectionComposer;

class AssistantComposer implements SectionComposer
{
    public function compose(Quiz $quiz, array $settings): array
    {
        $avatar = Arr::get($settings,'assistant.avatar', '/images/manager.webp');
        if ($avatar && !str_starts_with($avatar,'http')) {
            $avatar = asset($avatar);
        }

        return [
            'enabled' => (bool) Arr::get($settings,'assistant.enabled',true),
            'name'    => Arr::get($settings,'assistant.name', 'Менеджер'),
            'title'   => Arr::get($settings,'assistant.title', 'Помічник нотаріуса'),
            'avatar'  => $avatar,
        ];
    }
}
