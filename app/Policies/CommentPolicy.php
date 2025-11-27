<?php

namespace App\Policies;

use App\Models\Comment;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class CommentPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(?User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(?User $user, Comment $comment): bool
    {
        // Approved comments are visible to everyone
        if ($comment->is_approved) {
            return true;
        }

        // Unapproved comments only visible to owner, post owner, or admin
        return $user && (
            $user->id === $comment->user_id || 
            $user->id === $comment->post->user_id ||
            $user->isAdmin()
        );
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return true; // All authenticated users can create comments
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Comment $comment): bool
    {
        // Only owner can update their comment
        return $user->id === $comment->user_id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Comment $comment): bool
    {
        // Owner, post owner, or admin can delete
        return $user->id === $comment->user_id || 
               $user->id === $comment->post->user_id ||
               $user->isAdmin();
    }

    /**
     * Determine whether the user can approve comments.
     */
    public function approve(User $user, Comment $comment): bool
    {
        // Post owner or admin can approve comments
        return $user->id === $comment->post->user_id || $user->isAdmin();
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Comment $comment): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Comment $comment): bool
    {
        return $user->isAdmin();
    }
}

