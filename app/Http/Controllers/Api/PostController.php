<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Post;
use App\Models\ActivityLog;
use App\Services\BlogService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Gate;

class PostController extends Controller
{
    public function __construct(
        protected BlogService $blogService
    ) {}

    /**
     * Display a listing of published posts.
     */
    public function index(Request $request): JsonResponse
    {
        $posts = $this->blogService->getPublishedPosts(
            perPage: $request->get('per_page', 15),
            search: $request->get('search')
        );

        return response()->json([
            'posts' => $posts,
        ]);
    }

    /**
     * Display the specified post.
     */
    public function show(Post $post): JsonResponse
    {
        if (!$post->isPublished() && (!auth()->check() || auth()->id() !== $post->user_id)) {
            return response()->json(['message' => 'Post not found'], 404);
        }

        $post->load(['author', 'approvedComments.user']);

        return response()->json([
            'post' => $post,
        ]);
    }

    /**
     * Get authenticated user's posts.
     */
    public function myPosts(Request $request): JsonResponse
    {
        $posts = $this->blogService->getUserPosts(
            userId: $request->user()->id,
            perPage: $request->get('per_page', 15)
        );

        return response()->json([
            'posts' => $posts,
        ]);
    }

    /**
     * Store a newly created post.
     */
    public function store(Request $request): JsonResponse
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

        return response()->json([
            'message' => 'Post created successfully',
            'post' => $post->load('author'),
        ], 201);
    }

    /**
     * Update the specified post.
     */
    public function update(Request $request, Post $post): JsonResponse
    {
        Gate::authorize('update', $post);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'featured_image' => 'nullable|image|max:2048',
            'status' => 'required|in:draft,published',
        ]);

        $post = $this->blogService->updatePost(
            post: $post,
            data: $validated,
            featuredImage: $request->file('featured_image')
        );

        return response()->json([
            'message' => 'Post updated successfully',
            'post' => $post->load('author'),
        ]);
    }

    /**
     * Remove the specified post.
     */
    public function destroy(Post $post): JsonResponse
    {
        Gate::authorize('delete', $post);

        $this->blogService->deletePost($post);

        return response()->json([
            'message' => 'Post deleted successfully',
        ]);
    }

    /**
     * Admin: Get all posts including drafts and trashed.
     */
    public function adminIndex(Request $request): JsonResponse
    {
        $query = Post::with('author')->withTrashed();

        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('content', 'like', "%{$search}%");
            });
        }

        if ($status = $request->get('status')) {
            if ($status === 'trashed') {
                $query->onlyTrashed();
            } else {
                $query->where('status', $status);
            }
        }

        $posts = $query->latest()->paginate($request->get('per_page', 15));

        return response()->json([
            'posts' => $posts,
        ]);
    }

    /**
     * Admin: Restore a soft-deleted post.
     */
    public function restore(int $id): JsonResponse
    {
        $post = Post::onlyTrashed()->findOrFail($id);
        $post->restore();

        ActivityLog::log(
            action: 'api_restore_post',
            description: "Restored post: {$post->title}",
            model: $post
        );

        return response()->json([
            'message' => 'Post restored successfully',
            'post' => $post->load('author'),
        ]);
    }

    /**
     * Admin: Permanently delete a post.
     */
    public function forceDelete(int $id): JsonResponse
    {
        $post = Post::onlyTrashed()->findOrFail($id);

        ActivityLog::log(
            action: 'api_force_delete_post',
            description: "Permanently deleted post: {$post->title}"
        );

        $this->blogService->forceDeletePost($post);

        return response()->json([
            'message' => 'Post permanently deleted',
        ]);
    }
}

