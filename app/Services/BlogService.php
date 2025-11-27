<?php

namespace App\Services;

use App\Models\Post;
use App\Models\User;
use App\Models\Comment;
use App\Models\ActivityLog;
use Illuminate\Http\UploadedFile;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\Collection;

class BlogService
{
    /**
     * Cache TTL in seconds (5 minutes).
     */
    protected int $cacheTtl = 300;

    /**
     * Get published posts with caching.
     */
    public function getPublishedPosts(int $perPage = 10, ?string $search = null): LengthAwarePaginator
    {
        $cacheKey = "posts.published.page." . request()->get('page', 1) . ".search." . ($search ?? 'none');

        if ($search) {
            // Don't cache search results
            return Post::published()
                ->with('author')
                ->where(function ($query) use ($search) {
                    $query->where('title', 'like', "%{$search}%")
                          ->orWhere('content', 'like', "%{$search}%");
                })
                ->latest('published_at')
                ->paginate($perPage);
        }

        return Cache::remember($cacheKey, $this->cacheTtl, function () use ($perPage) {
            return Post::published()
                ->with('author')
                ->latest('published_at')
                ->paginate($perPage);
        });
    }

    /**
     * Get a single post by slug with caching.
     */
    public function getPostBySlug(string $slug): ?Post
    {
        return Cache::remember("post.{$slug}", $this->cacheTtl, function () use ($slug) {
            return Post::where('slug', $slug)
                ->with(['author', 'approvedComments.user', 'approvedComments.approvedReplies.user'])
                ->first();
        });
    }

    /**
     * Create a new post.
     */
    public function createPost(User $user, array $data, ?UploadedFile $featuredImage = null): Post
    {
        $postData = [
            'user_id' => $user->id,
            'title' => $data['title'],
            'content' => $data['content'],
            'status' => $data['status'],
            'published_at' => $data['status'] === 'published' ? now() : null,
        ];

        if ($featuredImage) {
            $postData['featured_image'] = $this->uploadImage($featuredImage);
        }

        $post = Post::create($postData);

        // Log activity
        ActivityLog::log(
            action: 'create_post',
            description: "Created post: {$post->title}",
            model: $post
        );

        // Clear cache
        $this->clearPostCache();

        return $post;
    }

    /**
     * Update an existing post.
     */
    public function updatePost(Post $post, array $data, ?UploadedFile $featuredImage = null): Post
    {
        $updateData = [
            'title' => $data['title'],
            'content' => $data['content'],
            'status' => $data['status'],
        ];

        // Handle status change
        if ($data['status'] === 'published' && $post->status !== 'published') {
            $updateData['published_at'] = now();
        }

        if ($featuredImage) {
            // Delete old image
            if ($post->featured_image) {
                Storage::disk('public')->delete($post->featured_image);
            }
            $updateData['featured_image'] = $this->uploadImage($featuredImage);
        }

        $post->update($updateData);

        // Log activity
        ActivityLog::log(
            action: 'update_post',
            description: "Updated post: {$post->title}",
            model: $post
        );

        // Clear cache
        $this->clearPostCache($post->slug);

        return $post->fresh();
    }

    /**
     * Delete a post (soft delete).
     */
    public function deletePost(Post $post): bool
    {
        ActivityLog::log(
            action: 'delete_post',
            description: "Deleted post: {$post->title}",
            model: $post
        );

        $result = $post->delete();

        // Clear cache
        $this->clearPostCache($post->slug);

        return $result;
    }

    /**
     * Permanently delete a post.
     */
    public function forceDeletePost(Post $post): bool
    {
        // Delete featured image
        if ($post->featured_image) {
            Storage::disk('public')->delete($post->featured_image);
        }

        // Delete all comments
        $post->comments()->forceDelete();

        $result = $post->forceDelete();

        // Clear cache
        $this->clearPostCache($post->slug);

        return $result;
    }

    /**
     * Get popular posts (most commented).
     */
    public function getPopularPosts(int $limit = 5): Collection
    {
        return Cache::remember("posts.popular.{$limit}", $this->cacheTtl, function () use ($limit) {
            return Post::published()
                ->withCount('approvedComments')
                ->orderByDesc('approved_comments_count')
                ->take($limit)
                ->get();
        });
    }

    /**
     * Get recent posts.
     */
    public function getRecentPosts(int $limit = 5): Collection
    {
        return Cache::remember("posts.recent.{$limit}", $this->cacheTtl, function () use ($limit) {
            return Post::published()
                ->with('author')
                ->latest('published_at')
                ->take($limit)
                ->get();
        });
    }

    /**
     * Get posts by user.
     */
    public function getUserPosts(int $userId, int $perPage = 10): LengthAwarePaginator
    {
        return Post::byUser($userId)
            ->with('author')
            ->withCount('approvedComments')
            ->latest()
            ->paginate($perPage);
    }

    /**
     * Upload featured image.
     */
    protected function uploadImage(UploadedFile $file): string
    {
        return $file->store('posts', 'public');
    }

    /**
     * Clear post-related cache.
     */
    public function clearPostCache(?string $slug = null): void
    {
        // Clear specific post cache
        if ($slug) {
            Cache::forget("post.{$slug}");
        }

        // Clear list caches (simplified - in production you'd want a more sophisticated approach)
        Cache::flush(); // This is aggressive - in production use tags or specific keys
    }

    /**
     * Get blog statistics.
     */
    public function getStatistics(): array
    {
        return Cache::remember('blog.statistics', $this->cacheTtl, function () {
            return [
                'total_posts' => Post::count(),
                'published_posts' => Post::published()->count(),
                'total_comments' => Comment::approved()->count(),
                'total_users' => User::count(),
            ];
        });
    }
}

