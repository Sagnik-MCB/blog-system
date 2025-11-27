<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;

class Comment extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'post_id',
        'user_id',
        'parent_id',
        'content',
        'is_approved',
    ];

    protected $casts = [
        'is_approved' => 'boolean',
        'deleted_at' => 'datetime',
    ];

    /**
     * Get the post that the comment belongs to.
     */
    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class);
    }

    /**
     * Get the user who wrote the comment.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the parent comment.
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(Comment::class, 'parent_id');
    }

    /**
     * Get the replies to this comment.
     */
    public function replies(): HasMany
    {
        return $this->hasMany(Comment::class, 'parent_id');
    }

    /**
     * Get approved replies to this comment.
     */
    public function approvedReplies(): HasMany
    {
        return $this->hasMany(Comment::class, 'parent_id')->where('is_approved', true);
    }

    /**
     * Scope a query to only include approved comments.
     */
    public function scopeApproved(Builder $query): Builder
    {
        return $query->where('is_approved', true);
    }

    /**
     * Scope a query to only include pending comments.
     */
    public function scopePending(Builder $query): Builder
    {
        return $query->where('is_approved', false);
    }

    /**
     * Scope a query to only include root comments (no parent).
     */
    public function scopeRootComments(Builder $query): Builder
    {
        return $query->whereNull('parent_id');
    }

    /**
     * Scope a query to filter by post.
     */
    public function scopeForPost(Builder $query, int $postId): Builder
    {
        return $query->where('post_id', $postId);
    }

    /**
     * Check if comment has replies.
     */
    public function hasReplies(): bool
    {
        return $this->replies()->exists();
    }

    /**
     * Check if the comment is a reply.
     */
    public function isReply(): bool
    {
        return !is_null($this->parent_id);
    }
}

