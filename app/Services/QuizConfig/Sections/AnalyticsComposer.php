<?php

namespace App\Services\QuizConfig\Sections;

use App\Models\Quiz;
use Illuminate\Support\Arr;
use App\Services\QuizConfig\Contracts\SectionComposer;

class AnalyticsComposer implements SectionComposer
{
    /**
     * Очікує, що $settings — це $quiz->settings (array) і в ньому є гілка 'analytics'.
     * Повертає нормалізований шматок конфіга для фронта.
     */
    public function compose(Quiz $quiz, array $settings): array
    {
        $enabled = (bool) Arr::get($settings, 'analytics.enabled', false);

        $providers = [
            'ga4' => [
                'enabled'       => (bool) Arr::get($settings, 'analytics.providers.ga4.enabled', false),
                'measurementId' => trim((string) Arr::get($settings, 'analytics.providers.ga4.measurementId', '')),
            ],
            'fb' => [
                'enabled' => (bool) Arr::get($settings, 'analytics.providers.fb.enabled', false),
                'pixelId' => trim((string) Arr::get($settings, 'analytics.providers.fb.pixelId', '')),
            ],
            'tt' => [
                'enabled' => (bool) Arr::get($settings, 'analytics.providers.tt.enabled', false),
                'pixelId' => trim((string) Arr::get($settings, 'analytics.providers.tt.pixelId', '')),
            ],
            'gtm' => [
                'enabled'    => (bool) Arr::get($settings, 'analytics.providers.gtm.enabled', false),
                'containerId'=> trim((string) Arr::get($settings, 'analytics.providers.gtm.containerId', '')),
            ],
        ];

        $scripts = [
            'head'    => (string) Arr::get($settings, 'analytics.scripts.head', ''),
            'bodyEnd' => (string) Arr::get($settings, 'analytics.scripts.bodyEnd', ''),
        ];

        $events = [
            'impression' => Arr::get($settings, 'analytics.events.impression', 'quiz_impression'),
            'start'      => Arr::get($settings, 'analytics.events.start', 'quiz_start'),
            'step'       => Arr::get($settings, 'analytics.events.step', 'quiz_step_view'),
            'leadView'   => Arr::get($settings, 'analytics.events.leadView', 'lead_view'),
            'leadSubmit' => Arr::get($settings, 'analytics.events.leadSubmit', 'lead_submit'),
        ];

        return compact('enabled', 'providers', 'scripts', 'events');
    }
}
