<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\QuizConfig\QuizConfigBuilder;

// Sections
use App\Services\QuizConfig\Sections\ThemeComposer;
use App\Services\QuizConfig\Sections\AssistantComposer;
use App\Services\QuizConfig\Sections\MarketingComposer;
use App\Services\QuizConfig\Sections\StartPageComposer;
use App\Services\QuizConfig\Sections\LeadFormComposer;
use App\Services\QuizConfig\Sections\ThanksPageComposer;

// Steps
use App\Services\QuizConfig\Sections\QuestionsStepComposer;

class QuizConfigServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Прості бінди — Laravel сам інʼєктить у Builder
        $this->app->bind(ThemeComposer::class);
        $this->app->bind(AssistantComposer::class);
        $this->app->bind(MarketingComposer::class);
        $this->app->bind(StartPageComposer::class);
        $this->app->bind(LeadFormComposer::class);
        $this->app->bind(ThanksPageComposer::class);
        $this->app->bind(QuestionsStepComposer::class);

        $this->app->bind(QuizConfigBuilder::class, function ($app) {
            return new QuizConfigBuilder(
                theme:       $app->make(ThemeComposer::class),
                assistant:   $app->make(AssistantComposer::class),
                marketing:   $app->make(MarketingComposer::class),
                startPage:   $app->make(StartPageComposer::class),
                questions:   $app->make(QuestionsStepComposer::class),
                leadForm:    $app->make(LeadFormComposer::class),
                thanksPage:  $app->make(ThanksPageComposer::class),
            );
        });
    }
}
