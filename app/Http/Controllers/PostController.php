<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Services\BlogService;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\Gate;

class PostController extends Controller
{
    public function __construct(
        protected BlogService $blogService
    ) {}

    /**
     * Display a listing of all published posts.
     */
    public function index(Request $request): View
    {
        $posts = $this->blogService->getPublishedPosts(
            perPage: 10,
            search: $request->get('search')
        );

        return view('posts.index', compact('posts'));
    }

    /**
     * Show the form for creating a new post.
     */
    public function create(): View
    {
        return view('posts.create');
    }

    /**
     * Store a newly created post in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'featured_image' => 'nullable|image|max:2048',
            'status' => 'required|in:draft,published',
        ]);

        $post = $this->blogService->createPost(
            user: $request->user(),
            data: $validated,
            featuredImage: $request->file('featured_image')
        );

        return redirect()
            ->route('posts.show', $post)
            ->with('success', 'Post created successfully!');
    }

    /**
     * Display the specified post.
     */
    public function show(Post $post): View
    {
        // Only allow viewing published posts or own posts
        if (!$post->isPublished() && $post->user_id !== auth()->id() && !auth()->user()?->isAdmin()) {
            abort(404);
        }

        $post->load(['author', 'approvedComments.user', 'approvedComments.approvedReplies.user']);
        
        return view('posts.show', compact('post'));
    }

    /**
     * Show the form for editing the specified post.
     */
    public function edit(Post $post): View
    {
        Gate::authorize('update', $post);

        return view('posts.edit', compact('post'));
    }

    /**
     * Update the specified post in storage.
     */
    public function update(Request $request, Post $post): RedirectResponse
    {
        Gate::authorize('update', $post);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'featured_image' => 'nullable|image|max:2048',
            'status' => 'required|in:draft,published',
        ]);

        $this->blogService->updatePost(
            post: $post,
            data: $validated,
            featuredImage: $request->file('featured_image')
        );

        return redirect()
            ->route('posts.show', $post)
            ->with('success', 'Post updated successfully!');
    }

    /**
     * Remove the specified post from storage.
     */
    public function destroy(Post $post): RedirectResponse
    {
        Gate::authorize('delete', $post);

        $this->blogService->deletePost($post);

        return redirect()
            ->route('posts.index')
            ->with('success', 'Post deleted successfully!');
    }

    /**
     * Display user's own posts.
     */
    public function myPosts(Request $request): View
    {
        $posts = Post::byUser($request->user()->id)
            ->with('author')
            ->latest()
            ->paginate(10);

        return view('posts.my-posts', compact('posts'));
    }

    /**
     * Restore a soft-deleted post.
     */
    public function restore(int $id): RedirectResponse
    {
        $post = Post::withTrashed()->findOrFail($id);
        
        Gate::authorize('restore', $post);

        $post->restore();

        return redirect()
            ->route('posts.show', $post)
            ->with('success', 'Post restored successfully!');
    }

    /**
     * Permanently delete a post.
     */
    public function forceDelete(int $id): RedirectResponse
    {
        $post = Post::withTrashed()->findOrFail($id);
        
        Gate::authorize('forceDelete', $post);

        $this->blogService->forceDeletePost($post);

        return redirect()
            ->route('posts.index')
            ->with('success', 'Post permanently deleted!');
    }
}

