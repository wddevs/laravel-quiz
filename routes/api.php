<?php

use App\Http\Controllers\Api\V1\QuizConfigController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\QuizSubmissionController;

Route::prefix('v1')->group(function () {
    Route::get('quizzes/{uuid}/config', [QuizConfigController::class,'show']);
    Route::post('quizzes/{uuid}/submissions', [QuizSubmissionController::class, 'store'])
        ->name('api.v1.quizzes.submissions.store')
        ->middleware('throttle:30,1'); // базовий захист
});
