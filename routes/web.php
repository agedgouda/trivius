<?php

use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Laravel\Jetstream\Http\Controllers\Inertia\PrivacyPolicyController;
use Laravel\Jetstream\Http\Controllers\Inertia\TermsOfServiceController;

use App\Http\Controllers\GameController;
use App\Http\Controllers\QuestionController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Middleware\EnsureIsAdmin;
use Inertia\Inertia;



// Terms of Service Route
Route::get('/terms-of-service', [TermsOfServiceController::class, 'show'])->name('terms.show');

// Privacy Policy Route
Route::get('/privacy-policy', [PrivacyPolicyController::class, 'show'])->name('policy.show');


Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

Route::get('/register/{userId?}', [RegisterController::class, 'show'])->name('register.prepopulated');
Route::post('register/{userId?}', [RegisterController::class, 'store'])->name('register.submit');

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
    Route::get('/dashboard', function () {
        return redirect()->route('games');
    })->name('dashboard');
    // Games Routes Group
    Route::prefix('games')->group(function () {
        Route::get('/', [GameController::class, 'index'])->name('games');
        Route::get('/{game}/answers', [GameController::class, 'showAnswers'])->name('games.showAnswers');
        Route::get('/create', [GameController::class, 'create'])->name('games.create');
        Route::get('/{game}/edit', [GameController::class, 'edit'])->name('games.edit');
        Route::get('/{game}/{user}/questions', [GameController::class, 'showQuestions'])->name('games.showQuestions');
        Route::put('/{game}', [GameController::class, 'update'])->name('games.update');
        Route::get('/{game}', [GameController::class, 'show'])->name('games.show');
        Route::post('/', [GameController::class, 'store'])->name('games.store');
        Route::post('/{game}/resend-invite/{user}', [GameController::class, 'resendInvite'])
            ->name('games.resend-invite');
        Route::post('/{game}/invite', [GameController::class, 'createUserAndInvite'])->name('games.invite');
        Route::post('/{game}/answers', [GameController::class, 'storeAnswers'])->name('games.answers');
        Route::put('/{game}/{user}/{attendance}', [GameController::class, 'updateAttendance'])->name('games.updateAttendance');
    });

});

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
    EnsureIsAdmin::class, // Custom middleware to check if the user is an admin
])->group(function () {
    Route::prefix('questions')->group(function () {
        Route::get('/', [QuestionController::class, 'index'])->name('questions');
        Route::post('/', [QuestionController::class, 'store'])->name('questions.store');
        Route::put('/{question}', [QuestionController::class, 'update'])->name('questions.update');
    });

});
