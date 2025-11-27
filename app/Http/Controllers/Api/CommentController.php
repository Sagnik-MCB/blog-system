<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Comment;
use App\Models\Post;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Gate;

class CommentController extends Controller
{
    /**
     * Display comments for a post.
     */
    public function index(Post $post): JsonResponse
    {
        $comments = $post->approvedComments()
            ->with(['user', 'approvedReplies.user'])
            ->rootComments()
            ->latest()
            ->paginate(20);

        return response()->json([
            'comments' => $comments,
        ]);
    }

    /**
     * Store a newly created comment.
     */
    public function store(Request $request, Post $post): JsonResponse
    {
        $validated = $request->validate([
            'content' => 'required|string|max:1000',
            'parent_id' => 'nullable|exists:comments,id',
        ]);

        $comment = $post->comments()->create([
            'user_id' => $request->user()->id,
            'content' => $validated['content'],
            'parent_id' => $validated['parent_id'] ?? null,
            'is_approved' => true,
        ]);

        ActivityLog::log(
            action: 'api_create_comment',
            description: "Comment added to post: {$post->title}",
            model: $comment
        );

        return response()->json([
            'message' => 'Comment added successfully',
            'comment' => $comment->load('user'),
        ], 201);
    }

    /**
     * Update the specified comment.
     */
    public function update(Request $request, Comment $comment): JsonResponse
    {
        Gate::authorize('update', $comment);

        $validated = $request->validate([
            'content' => 'required|string|max:1000',
        ]);

        $comment->update($validated);

        ActivityLog::log(
            action: 'api_update_comment',
            description: "Comment updated",
            model: $comment
        );

        return response()->json([
            'message' => 'Comment updated successfully',
            'comment' => $comment->load('user'),
        ]);
    }

    /**
     * Remove the specified comment.
     */
    public function destroy(Comment $comment): JsonResponse
    {
        Gate::authorize('delete', $comment);

        ActivityLog::log(
            action: 'api_delete_comment',
            description: "Comment deleted",
            model: $comment
        );

        $comment->delete();

        return response()->json([
            'message' => 'Comment deleted successfully',
        ]);
    }

    /**
     * Admin: Get all comments.
     */
    public function adminIndex(Request $request): JsonResponse
    {
        $query = Comment::with(['post', 'user'])->withTrashed();

        if ($request->has('approved')) {
            $approved = $request->get('approved');
            if ($approved === 'pending') {
                $query->where('is_approved', false);
            } elseif ($approved === 'approved') {
                $query->where('is_approved', true);
            }
        }

        if ($request->get('trashed') === 'only') {
            $query->onlyTrashed();
        }

        $comments = $query->latest()->paginate($request->get('per_page', 20));

        return response()->json([
            'comments' => $comments,
        ]);
    }

    /**
     * Admin: Approve a comment.
     */
    public function approve(Comment $comment): JsonResponse
    {
        $comment->update(['is_approved' => true]);

        ActivityLog::log(
            action: 'api_approve_comment',
            description: "Comment approved",
            model: $comment
        );

        return response()->json([
            'message' => 'Comment approved',
            'comment' => $comment->load('user'),
        ]);
    }

    /**
     * Admin: Reject a comment.
     */
    public function reject(Comment $comment): JsonResponse
    {
        $comment->update(['is_approved' => false]);

        ActivityLog::log(
            action: 'api_reject_comment',
            description: "Comment rejected",
            model: $comment
        );

        return response()->json([
            'message' => 'Comment rejected',
            'comment' => $comment->load('user'),
        ]);
    }
}

