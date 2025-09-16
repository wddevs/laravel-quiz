<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\Client\DashboardController as ClientDashboardController;
use App\Http\Controllers\Client\QuizController;
use App\Http\Controllers\Admin\UploadController;
use App\Http\Controllers\WidgetScriptController;
use App\Http\Controllers\LeadController;
use App\Http\Controllers\User\AssetController;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

Route::middleware(['auth','verified','role:client'])
    ->group(function () {
        Route::get('/dashboard', ClientDashboardController::class)
            ->name('dashboard');

        Route::get('/dashboard/quizzes/preview/{quiz}', [QuizController::class,'show'])->name('quizzes.preview');
        Route::get('/dashboard/quizzes/create', [QuizController::class,'create'])->name('quizzes.create');
        Route::get('/dashboard/quizzes/edit/{quiz}', [QuizController::class,'edit'])->name('quizzes.edit');

        Route::post('/dashboard/quizzes', [QuizController::class,'store'])->name('quizzes.store');
        Route::get('/dashboard/quizzes/', [QuizController::class,'index'])->name('quizzes.index');
        Route::delete('/dashboard/quizzes/{quiz}', [QuizController::class,'destroy'])->name('quizzes.destroy');
        Route::put('/dashboard/quizzes/{quiz}', [QuizController::class,'update'])->name('quizzes.update');
        Route::post('/admin/upload', UploadController::class)->name('admin.upload');

        Route::get('/leads',[LeadController::class,'index'])->name('leads.index');
        Route::get('/leads/{uuid}',[LeadController::class,'show'])->name('leads.show');
        Route::delete('/leads/{uuid}',[LeadController::class,'destroy'])->name('leads.destroy');
        Route::post('/leads/{uuid}/block-ip',[LeadController::class,'blockIp'])->name('leads.blockIp');
    });

Route::middleware(['web'])->group(function () {
    Route::get('/widget/{uuid}', [WidgetScriptController::class, 'script'])
        ->whereUuid('uuid')
        ->name('widget.embed');

    Route::get('/embed/{any?}', function () {
        return File::get(public_path('embed/index.html'));
    })->where('any', '.*');
});

Route::middleware(['auth'])->prefix('user/assets')->name('user.assets.')->group(function () {
    Route::get('/', [AssetController::class, 'index'])->name('index');
    Route::post('/', [AssetController::class, 'store'])->name('store');
    Route::delete('{asset}', [AssetController::class, 'destroy'])->name('destroy');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
