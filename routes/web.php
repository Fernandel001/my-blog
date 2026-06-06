<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\LikeController;
use App\Http\Controllers\PostController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Routes publiques
|--------------------------------------------------------------------------
*/

Route::get('/', [PostController::class, 'index'])->name('home');

// Commentaires — réservés aux utilisateurs connectés
Route::post('/posts/{post}/comments', [CommentController::class, 'store'])
    ->middleware('auth')
    ->name('posts.comments.store');

Route::delete('/comments/{comment}', [CommentController::class, 'destroy'])
    ->middleware('auth')
    ->name('comments.destroy');

Route::patch('/comments/{comment}', [CommentController::class, 'update'])
    ->middleware('auth')
    ->name('comments.update');

// Likes — réservés aux utilisateurs connectés
Route::post('/posts/{post}/like', [LikeController::class, 'toggle'])
    ->middleware('auth')
    ->name('posts.like');

/*
|--------------------------------------------------------------------------
| Authentification (admin uniquement)
|--------------------------------------------------------------------------
*/

Route::middleware('guest')->group(function () {
    Route::get('/login',    [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login',   [AuthController::class, 'login']);
    Route::get('/otp',      [AuthController::class, 'showOtpForm'])->name('otp.show');
    Route::post('/otp',     [AuthController::class, 'verifyOtp'])->name('otp.verify');
});

Route::post('/logout', [AuthController::class, 'logout'])
    ->middleware('auth')
    ->name('logout');

/*
|--------------------------------------------------------------------------
| Espace admin — protégé par le middleware 'auth'
|--------------------------------------------------------------------------
*/

Route::middleware('auth')->prefix('admin')->name('admin.')->group(function () {

    Route::get('/dashboard', fn () => redirect()->route('home'))->name('dashboard');

    // Gestion des posts — admin uniquement
    Route::get('/posts/create', function () {
        if (auth()->user()->email !== 'admin@thehackerexperiment.com') {
            return redirect()->route('home');
        }
        return app(\App\Http\Controllers\PostController::class)->create();
    })->name('posts.create');

    Route::post('/posts', function (\Illuminate\Http\Request $request) {
        if (auth()->user()->email !== 'admin@thehackerexperiment.com') {
            return redirect()->route('home');
        }
        return app(\App\Http\Controllers\PostController::class)->store($request);
    })->name('posts.store');
});
