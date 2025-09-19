<?php // app/Services/QuizConfig/Sections/MarketingComposer.php
namespace App\Services\QuizConfig\Sections;

use App\Models\Quiz;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;
use App\Services\QuizConfig\Contracts\SectionComposer;

class MarketingComposer implements SectionComposer
{
    public function compose(Quiz $quiz, array $settings): array
    {
        $bonuses = collect(Arr::get($settings,'marketing.bonuses',[]))->map(function ($b) {
            $img = $b['image'] ?? null;
            if ($img && !str_starts_with($img,'http')) {
                $img = Storage::disk('public')->url($img);
            }
            return [
                'name'  => $b['name'] ?? '',
                'link'  => $b['link'] ?? null,
                'step'  => $b['step'] ?? 'thanks',
                'image' => $img,
            ];
        })->values();

        return [
            'discount' => [
                'enabled'=> (bool) Arr::get($settings,'marketing.discount.enabled',false),
                'type'   => Arr::get($settings,'marketing.discount.type', 'percent'),
                'effect' => Arr::get($settings,'marketing.discount.effect', 'increasing'),
                'value'  => Arr::get($settings,'marketing.discount.value', 10),
                'title'  => Arr::get($settings,'marketing.discount.title', 'Discount for you!'),
            ],
            'bonusesEnabled' => (bool) Arr::get($settings,'marketing.bonusesEnabled',false),
            'bonusesTitle'   => Arr::get($settings,'marketing.bonusesTitle'),
            'bonuses'        => $bonuses,
        ];
    }
}
