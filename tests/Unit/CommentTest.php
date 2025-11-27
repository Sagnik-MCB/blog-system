<?php

namespace Tests\Unit;

use App\Models\Comment;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CommentTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Post $post;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->post = Post::factory()->create(['user_id' => $this->user->id]);
    }

    public function test_can_create_a_comment(): void
    {
        $comment = Comment::factory()->create([
            'post_id' => $this->post->id,
            'user_id' => $this->user->id,
            'content' => 'Test comment',
        ]);

        $this->assertDatabaseHas('comments', [
            'content' => 'Test comment',
            'post_id' => $this->post->id,
        ]);
    }

    public function test_comment_belongs_to_post(): void
    {
        $comment = Comment::factory()->create([
            'post_id' => $this->post->id,
            'user_id' => $this->user->id,
        ]);

        $this->assertInstanceOf(Post::class, $comment->post);
        $this->assertEquals($this->post->id, $comment->post->id);
    }

    public function test_comment_belongs_to_user(): void
    {
        $comment = Comment::factory()->create([
            'post_id' => $this->post->id,
            'user_id' => $this->user->id,
        ]);

        $this->assertInstanceOf(User::class, $comment->user);
        $this->assertEquals($this->user->id, $comment->user->id);
    }

    public function test_comment_can_have_replies(): void
    {
        $parentComment = Comment::factory()->create([
            'post_id' => $this->post->id,
            'user_id' => $this->user->id,
        ]);

        $reply = Comment::factory()->create([
            'post_id' => $this->post->id,
            'user_id' => $this->user->id,
            'parent_id' => $parentComment->id,
        ]);

        $this->assertCount(1, $parentComment->replies);
        $this->assertEquals($parentComment->id, $reply->parent->id);
    }

    public function test_comment_scope_approved(): void
    {
        Comment::factory()->create([
            'post_id' => $this->post->id,
            'user_id' => $this->user->id,
            'is_approved' => true,
        ]);
        Comment::factory()->create([
            'post_id' => $this->post->id,
            'user_id' => $this->user->id,
            'is_approved' => false,
        ]);

        $approvedComments = Comment::approved()->get();

        $this->assertCount(1, $approvedComments);
    }

    public function test_comment_scope_pending(): void
    {
        Comment::factory()->create([
            'post_id' => $this->post->id,
            'user_id' => $this->user->id,
            'is_approved' => false,
        ]);
        Comment::factory()->create([
            'post_id' => $this->post->id,
            'user_id' => $this->user->id,
            'is_approved' => true,
        ]);

        $pendingComments = Comment::pending()->get();

        $this->assertCount(1, $pendingComments);
    }

    public function test_comment_scope_root_comments(): void
    {
        $parent = Comment::factory()->create([
            'post_id' => $this->post->id,
            'user_id' => $this->user->id,
            'parent_id' => null,
        ]);
        Comment::factory()->create([
            'post_id' => $this->post->id,
            'user_id' => $this->user->id,
            'parent_id' => $parent->id,
        ]);

        $rootComments = Comment::rootComments()->get();

        $this->assertCount(1, $rootComments);
    }

    public function test_comment_soft_delete(): void
    {
        $comment = Comment::factory()->create([
            'post_id' => $this->post->id,
            'user_id' => $this->user->id,
        ]);

        $comment->delete();

        $this->assertSoftDeleted('comments', ['id' => $comment->id]);
    }

    public function test_is_reply_method(): void
    {
        $parent = Comment::factory()->create([
            'post_id' => $this->post->id,
            'user_id' => $this->user->id,
            'parent_id' => null,
        ]);

        $reply = Comment::factory()->create([
            'post_id' => $this->post->id,
            'user_id' => $this->user->id,
            'parent_id' => $parent->id,
        ]);

        $this->assertFalse($parent->isReply());
        $this->assertTrue($reply->isReply());
    }
}

