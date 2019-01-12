<?php

use App\Http\Controllers\AdminSourcesController;
use App\Http\Controllers\Auth\LogoutController;
use App\Http\Controllers\PostsController;
use App\Http\Controllers\SourceMutesController;
use App\Http\Controllers\TagMutesController;
use App\Http\Controllers\TopicsController;
use App\Http\Controllers\UserSourcesController;
use App\Http\Controllers\UserMutesController;
use App\Http\Controllers\UserVerificationController;
use App\Http\Controllers\VotesController;
use App\Http\Middleware\AdminMiddleware;
use App\Http\Middleware\VerifiedUserMiddleware;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

Auth::routes();

Route::get('rss', function () {
    return new Response(
        file_get_contents(storage_path('mock/rss.xml')),
        200,
        ['Content-Type' => 'application/xml']
    );
});

Route::get('/logout', [LogoutController::class, 'logout']);

Route::middleware('auth')->prefix('profile')->group(function () {

    Route::middleware(VerifiedUserMiddleware::class)->group(function () {
        Route::get('sources', [UserSourcesController::class, 'index']);
        Route::post('sources', [UserSourcesController::class, 'update']);
        Route::post('sources/delete', [UserSourcesController::class, 'delete']);
    });

    Route::post('posts/{post}/add-vote', [VotesController::class, 'store']);
    Route::post('posts/{post}/remove-vote', [VotesController::class, 'delete']);

    Route::post('sources/{source}/mute', [SourceMutesController::class, 'store']);
    Route::post('sources/{source}/unmute', [SourceMutesController::class, 'delete']);

    Route::post('tags/{tag}/mute', [TagMutesController::class, 'store']);
    Route::post('tags/{tag}/unmute', [TagMutesController::class, 'delete']);

    Route::get('mutes', [UserMutesController::class, 'index']);

    Route::post('verification/resend', [UserVerificationController::class, 'resend']);
    Route::get('verify/{verificationToken}', [UserVerificationController::class, 'verify']);
});

Route::middleware(['auth', AdminMiddleware::class])->prefix('admin')->group(function () {
    Route::get('sources', [AdminSourcesController::class, 'index']);
    Route::post('sources/{source}/activate', [AdminSourcesController::class, 'activate']);
});

Route::get('/', [PostsController::class, 'index']);
Route::get('latest', [PostsController::class, 'latest']);
Route::get('/topic/{topic}', [PostsController::class, 'topic']);
Route::get('/tag/{tag}', [PostsController::class, 'tag']);

Route::get('topics', [TopicsController::class, 'index']);

Route::get('{post}', [PostsController::class, 'show']);
