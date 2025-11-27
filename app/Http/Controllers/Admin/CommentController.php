<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Comment;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class CommentController extends Controller
{
    /**
     * Display a listing of all comments.
     */
    public function index(Request $request): View
    {
        $query = Comment::with(['post', 'user'])->withTrashed();

        // Search functionality
        if ($search = $request->get('search')) {
            $query->where('content', 'like', "%{$search}%");
        }

        // Filter by approval status
        if ($request->has('approved')) {
            $approved = $request->get('approved');
            if ($approved === 'pending') {
                $query->where('is_approved', false);
            } elseif ($approved === 'approved') {
                $query->where('is_approved', true);
            }
        }

        // Filter by trashed
        if ($request->get('trashed') === 'only') {
            $query->onlyTrashed();
        }

        $comments = $query->latest()->paginate(20)->withQueryString();

        return view('admin.comments.index', compact('comments'));
    }

    /**
     * Approve a comment.
     */
    public function approve(Comment $comment): RedirectResponse
    {
        $comment->update(['is_approved' => true]);

        ActivityLog::log(
            action: 'admin_approve_comment',
            description: "Admin approved comment on post: {$comment->post->title}",
            model: $comment
        );

        return back()->with('success', 'Comment approved!');
    }

    /**
     * Reject a comment.
     */
    public function reject(Comment $comment): RedirectResponse
    {
        $comment->update(['is_approved' => false]);

        ActivityLog::log(
            action: 'admin_reject_comment',
            description: "Admin rejected comment on post: {$comment->post->title}",
            model: $comment
        );

        return back()->with('success', 'Comment rejected!');
    }

    /**
     * Bulk approve comments.
     */
    public function bulkApprove(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'comment_ids' => 'required|array',
            'comment_ids.*' => 'exists:comments,id',
        ]);

        Comment::whereIn('id', $validated['comment_ids'])
            ->update(['is_approved' => true]);

        ActivityLog::log(
            action: 'admin_bulk_approve_comments',
            description: "Admin bulk approved " . count($validated['comment_ids']) . " comments",
            properties: ['comment_ids' => $validated['comment_ids']]
        );

        return back()->with('success', 'Selected comments approved!');
    }

    /**
     * Remove the specified comment from storage.
     */
    public function destroy(Comment $comment): RedirectResponse
    {
        ActivityLog::log(
            action: 'admin_delete_comment',
            description: "Admin deleted comment on post: {$comment->post->title}",
            model: $comment
        );

        $comment->delete();

        return back()->with('success', 'Comment deleted!');
    }

    /**
     * Restore a soft-deleted comment.
     */
    public function restore(int $id): RedirectResponse
    {
        $comment = Comment::onlyTrashed()->findOrFail($id);
        $comment->restore();

        ActivityLog::log(
            action: 'admin_restore_comment',
            description: "Admin restored comment",
            model: $comment
        );

        return back()->with('success', 'Comment restored!');
    }

    /**
     * Permanently delete a comment.
     */
    public function forceDelete(int $id): RedirectResponse
    {
        $comment = Comment::onlyTrashed()->findOrFail($id);

        ActivityLog::log(
            action: 'admin_force_delete_comment',
            description: "Admin permanently deleted comment"
        );

        $comment->forceDelete();

        return back()->with('success', 'Comment permanently deleted!');
    }
}

