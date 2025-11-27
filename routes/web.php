<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\SocialAuthController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\Admin\PostController as AdminPostController;
use App\Http\Controllers\Admin\CommentController as AdminCommentController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public Routes
|--------------------------------------------------------------------------
*/

// Home page - redirect to posts
Route::get('/', function () {
    return redirect()->route('posts.index');
})->name('home');

// Public posts listing and viewing
Route::get('/posts', [PostController::class, 'index'])->name('posts.index');
Route::get('/posts/{post:slug}', [PostController::class, 'show'])->name('posts.show');

/*
|--------------------------------------------------------------------------
| Social Authentication Routes
|--------------------------------------------------------------------------
*/

Route::prefix('auth')->name('auth.social.')->group(function () {
    Route::get('{provider}/redirect', [SocialAuthController::class, 'redirect'])->name('redirect');
    Route::get('{provider}/callback', [SocialAuthController::class, 'callback'])->name('callback');
});

/*
|--------------------------------------------------------------------------
| Authenticated User Routes
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'verified', 'log.activity'])->group(function () {
    
    // Dashboard
    Route::get('/dashboard', function () {
        $user = auth()->user();
        $recentPosts = $user->posts()->latest()->take(5)->get();
        $recentComments = $user->comments()->with('post')->latest()->take(5)->get();
        
        return view('dashboard', compact('recentPosts', 'recentComments'));
    })->name('dashboard');

    // Profile routes
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // My Posts
    Route::get('/my-posts', [PostController::class, 'myPosts'])->name('posts.my-posts');

    // Post CRUD (for authenticated users)
    Route::resource('posts', PostController::class)->except(['index', 'show']);
    
    // Post restore and force delete
    Route::post('/posts/{post}/restore', [PostController::class, 'restore'])
        ->name('posts.restore')
        ->withTrashed();
    Route::delete('/posts/{post}/force-delete', [PostController::class, 'forceDelete'])
        ->name('posts.force-delete')
        ->withTrashed();

    // Comments
    Route::post('/posts/{post}/comments', [CommentController::class, 'store'])->name('comments.store');
    Route::put('/comments/{comment}', [CommentController::class, 'update'])->name('comments.update');
    Route::delete('/comments/{comment}', [CommentController::class, 'destroy'])->name('comments.destroy');
    Route::post('/comments/{comment}/approve', [CommentController::class, 'approve'])->name('comments.approve');
    Route::post('/comments/{comment}/reject', [CommentController::class, 'reject'])->name('comments.reject');
});

/*
|--------------------------------------------------------------------------
| Admin Routes
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'verified', 'role:admin', 'log.activity'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        
        // Admin Dashboard
        Route::get('/', [AdminDashboardController::class, 'index'])->name('dashboard');

        // User Management
        Route::resource('users', AdminUserController::class);
        Route::post('/users/{user}/toggle-status', [AdminUserController::class, 'toggleStatus'])
            ->name('users.toggle-status');

        // Post Management
        Route::get('/posts', [AdminPostController::class, 'index'])->name('posts.index');
        Route::get('/posts/{slug}', [AdminPostController::class, 'show'])->name('posts.show');
        Route::get('/posts/{slug}/edit', [AdminPostController::class, 'edit'])->name('posts.edit');
        Route::put('/posts/{slug}', [AdminPostController::class, 'update'])->name('posts.update');
        Route::delete('/posts/{slug}', [AdminPostController::class, 'destroy'])->name('posts.destroy');
        Route::post('/posts/{slug}/restore', [AdminPostController::class, 'restore'])->name('posts.restore');
        Route::delete('/posts/{slug}/force-delete', [AdminPostController::class, 'forceDelete'])->name('posts.force-delete');

        // Comment Management
        Route::get('/comments', [AdminCommentController::class, 'index'])->name('comments.index');
        Route::post('/comments/{comment}/approve', [AdminCommentController::class, 'approve'])->name('comments.approve');
        Route::post('/comments/{comment}/reject', [AdminCommentController::class, 'reject'])->name('comments.reject');
        Route::post('/comments/bulk-approve', [AdminCommentController::class, 'bulkApprove'])->name('comments.bulk-approve');
        Route::delete('/comments/{comment}', [AdminCommentController::class, 'destroy'])->name('comments.destroy');
        Route::post('/comments/{comment}/restore', [AdminCommentController::class, 'restore'])
            ->name('comments.restore')
            ->withTrashed();
        Route::delete('/comments/{comment}/force-delete', [AdminCommentController::class, 'forceDelete'])
            ->name('comments.force-delete')
            ->withTrashed();
    });

/*
|--------------------------------------------------------------------------
| Auth Routes (from Breeze)
|--------------------------------------------------------------------------
*/

require __DIR__.'/auth.php';
