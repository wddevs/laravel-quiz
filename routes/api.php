<?php

use App\Http\Controllers\Api\V1\QuizConfigController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::get('quizzes/{uuid}/config', [QuizConfigController::class,'show']);
});
