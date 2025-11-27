<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\Post;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;

class CommentController extends Controller
{
    /**
     * Store a newly created comment in storage.
     */
    public function store(Request $request, Post $post): RedirectResponse
    {
        $validated = $request->validate([
            'content' => 'required|string|max:1000',
            'parent_id' => 'nullable|exists:comments,id',
        ]);

        $comment = $post->comments()->create([
            'user_id' => $request->user()->id,
            'content' => $validated['content'],
            'parent_id' => $validated['parent_id'] ?? null,
            'is_approved' => true, // Auto-approve for now, can be changed
        ]);

        ActivityLog::log(
            action: 'create_comment',
            description: "Comment added to post: {$post->title}",
            model: $comment
        );

        return redirect()
            ->route('posts.show', $post)
            ->with('success', 'Comment added successfully!');
    }

    /**
     * Update the specified comment in storage.
     */
    public function update(Request $request, Comment $comment): RedirectResponse
    {
        Gate::authorize('update', $comment);

        $validated = $request->validate([
            'content' => 'required|string|max:1000',
        ]);

        $comment->update($validated);

        ActivityLog::log(
            action: 'update_comment',
            description: "Comment updated on post: {$comment->post->title}",
            model: $comment
        );

        return redirect()
            ->route('posts.show', $comment->post)
            ->with('success', 'Comment updated successfully!');
    }

    /**
     * Remove the specified comment from storage.
     */
    public function destroy(Comment $comment): RedirectResponse
    {
        Gate::authorize('delete', $comment);

        $post = $comment->post;
        
        ActivityLog::log(
            action: 'delete_comment',
            description: "Comment deleted from post: {$post->title}",
            model: $comment
        );

        $comment->delete();

        return redirect()
            ->route('posts.show', $post)
            ->with('success', 'Comment deleted successfully!');
    }

    /**
     * Approve a pending comment (Admin only).
     */
    public function approve(Comment $comment): RedirectResponse
    {
        Gate::authorize('approve', $comment);

        $comment->update(['is_approved' => true]);

        ActivityLog::log(
            action: 'approve_comment',
            description: "Comment approved on post: {$comment->post->title}",
            model: $comment
        );

        return back()->with('success', 'Comment approved!');
    }

    /**
     * Reject a comment (Admin only).
     */
    public function reject(Comment $comment): RedirectResponse
    {
        Gate::authorize('approve', $comment);

        $comment->update(['is_approved' => false]);

        ActivityLog::log(
            action: 'reject_comment',
            description: "Comment rejected on post: {$comment->post->title}",
            model: $comment
        );

        return back()->with('success', 'Comment rejected!');
    }
}

