<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\PostController;
use App\Http\Controllers\Api\CommentController;
use App\Http\Controllers\Api\UserController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group.
|
*/

// API Version 1
Route::prefix('v1')->name('api.v1.')->group(function () {

    /*
    |--------------------------------------------------------------------------
    | Public API Routes
    |--------------------------------------------------------------------------
    */
    
    // Authentication
    Route::post('/register', [AuthController::class, 'register'])->name('register');
    Route::post('/login', [AuthController::class, 'login'])->name('login');
    
    // Public posts
    Route::get('/posts', [PostController::class, 'index'])->name('posts.index');
    Route::get('/posts/{post:slug}', [PostController::class, 'show'])->name('posts.show');
    Route::get('/posts/{post:slug}/comments', [CommentController::class, 'index'])->name('posts.comments.index');

    /*
    |--------------------------------------------------------------------------
    | Protected API Routes
    |--------------------------------------------------------------------------
    */
    
    Route::middleware('auth:sanctum')->group(function () {
        
        // Auth
        Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
        Route::get('/user', [AuthController::class, 'user'])->name('user');
        
        // User posts
        Route::get('/my-posts', [PostController::class, 'myPosts'])->name('posts.my');
        
        // Post CRUD
        Route::post('/posts', [PostController::class, 'store'])->name('posts.store');
        Route::put('/posts/{post:slug}', [PostController::class, 'update'])->name('posts.update');
        Route::delete('/posts/{post:slug}', [PostController::class, 'destroy'])->name('posts.destroy');
        
        // Comments
        Route::post('/posts/{post:slug}/comments', [CommentController::class, 'store'])->name('comments.store');
        Route::put('/comments/{comment}', [CommentController::class, 'update'])->name('comments.update');
        Route::delete('/comments/{comment}', [CommentController::class, 'destroy'])->name('comments.destroy');

        /*
        |--------------------------------------------------------------------------
        | Admin API Routes
        |--------------------------------------------------------------------------
        */
        
        Route::middleware('role:admin')->prefix('admin')->name('admin.')->group(function () {
            // Statistics
            Route::get('/statistics', [AuthController::class, 'statistics'])->name('statistics');
            
            // User management
            Route::get('/users', [UserController::class, 'index'])->name('users.index');
            Route::get('/users/{user}', [UserController::class, 'show'])->name('users.show');
            Route::put('/users/{user}', [UserController::class, 'update'])->name('users.update');
            Route::delete('/users/{user}', [UserController::class, 'destroy'])->name('users.destroy');
            Route::post('/users/{user}/toggle-status', [UserController::class, 'toggleStatus'])->name('users.toggle-status');
            
            // Post management
            Route::get('/posts', [PostController::class, 'adminIndex'])->name('posts.index');
            Route::post('/posts/{post}/restore', [PostController::class, 'restore'])->name('posts.restore');
            Route::delete('/posts/{post}/force-delete', [PostController::class, 'forceDelete'])->name('posts.force-delete');
            
            // Comment management
            Route::get('/comments', [CommentController::class, 'adminIndex'])->name('comments.index');
            Route::post('/comments/{comment}/approve', [CommentController::class, 'approve'])->name('comments.approve');
            Route::post('/comments/{comment}/reject', [CommentController::class, 'reject'])->name('comments.reject');
        });
    });
});

