<?php

use App\Http\Controllers\Api\V1\QuizConfigController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\QuizSubmissionController;
use App\Http\Controllers\Api\V1\AnalyticsEventController;
use App\Http\Middleware\QuizCors;



Route::prefix('v1')->group(function () {
    // PRE-FLIGHT для всіх публічних ручок квіза
    Route::options('quizzes/{uuid}/config', fn() => response('', 204))
        ->middleware(QuizCors::class);
    Route::options('quizzes/{uuid}/events', fn() => response('', 204))
        ->middleware(QuizCors::class);
    Route::options('quizzes/{uuid}/submissions', fn() => response('', 204))
        ->middleware(QuizCors::class);

    // Основні ручки
    Route::get('quizzes/{uuid}/config', [QuizConfigController::class,'show'])
        ->middleware(QuizCors::class);

    Route::post('quizzes/{uuid}/submissions', [QuizSubmissionController::class, 'store'])
        ->middleware(['throttle:30,1', QuizCors::class])
        ->name('api.v1.quizzes.submissions.store');

    Route::post('quizzes/{uuid}/events', [AnalyticsEventController::class, 'store'])
        ->middleware(QuizCors::class);
});
