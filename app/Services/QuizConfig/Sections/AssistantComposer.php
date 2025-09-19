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
        $avatar = Arr::get($settings,'assistant.avatar', false);

        if ($avatar && !str_starts_with($avatar,'http')) {
            $avatar = asset($avatar);
        }

        return [
            'enabled' => (bool) Arr::get($settings,'assistant.enabled',false),
            'name'    => Arr::get($settings,'assistant.name', 'Manager'),
            'title'   => Arr::get($settings,'assistant.title', false),
            'avatar'  => $avatar,
        ];
    }
}
