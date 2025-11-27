<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Post;
use App\Models\ActivityLog;
use App\Services\BlogService;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class PostController extends Controller
{
    public function __construct(
        protected BlogService $blogService
    ) {}

    /**
     * Display a listing of all posts.
     */
    public function index(Request $request): View
    {
        $query = Post::with('author')->withTrashed();

        // Search functionality
        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('content', 'like', "%{$search}%");
            });
        }

        // Filter by status
        if ($status = $request->get('status')) {
            if ($status === 'trashed') {
                $query->onlyTrashed();
            } else {
                $query->where('status', $status);
            }
        }

        // Filter by author
        if ($author = $request->get('author')) {
            $query->where('user_id', $author);
        }

        $posts = $query->latest()->paginate(15)->withQueryString();

        return view('admin.posts.index', compact('posts'));
    }

    /**
     * Display the specified post.
     */
    public function show(string $slug): View
    {
        $post = Post::withTrashed()
            ->where('slug', $slug)
            ->with(['author', 'comments.user'])
            ->firstOrFail();

        return view('admin.posts.show', compact('post'));
    }

    /**
     * Show the form for editing the specified post.
     */
    public function edit(string $slug): View
    {
        $post = Post::withTrashed()->where('slug', $slug)->firstOrFail();
        return view('admin.posts.edit', compact('post'));
    }

    /**
     * Update the specified post in storage.
     */
    public function update(Request $request, string $slug): RedirectResponse
    {
        $post = Post::withTrashed()->where('slug', $slug)->firstOrFail();

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

        ActivityLog::log(
            action: 'admin_update_post',
            description: "Admin updated post: {$post->title}",
            model: $post
        );

        return redirect()
            ->route('admin.posts.index')
            ->with('success', 'Post updated successfully!');
    }

    /**
     * Remove the specified post from storage.
     */
    public function destroy(string $slug): RedirectResponse
    {
        $post = Post::where('slug', $slug)->firstOrFail();

        ActivityLog::log(
            action: 'admin_delete_post',
            description: "Admin deleted post: {$post->title}",
            model: $post
        );

        $post->delete();

        return redirect()
            ->route('admin.posts.index')
            ->with('success', 'Post moved to trash!');
    }

    /**
     * Restore a soft-deleted post.
     */
    public function restore(string $slug): RedirectResponse
    {
        $post = Post::onlyTrashed()->where('slug', $slug)->firstOrFail();
        
        $post->restore();

        ActivityLog::log(
            action: 'admin_restore_post',
            description: "Admin restored post: {$post->title}",
            model: $post
        );

        return redirect()
            ->route('admin.posts.index')
            ->with('success', 'Post restored successfully!');
    }

    /**
     * Permanently delete a post.
     */
    public function forceDelete(string $slug): RedirectResponse
    {
        $post = Post::onlyTrashed()->where('slug', $slug)->firstOrFail();

        ActivityLog::log(
            action: 'admin_force_delete_post',
            description: "Admin permanently deleted post: {$post->title}",
            model: $post
        );

        $this->blogService->forceDeletePost($post);

        return redirect()
            ->route('admin.posts.index')
            ->with('success', 'Post permanently deleted!');
    }
}

