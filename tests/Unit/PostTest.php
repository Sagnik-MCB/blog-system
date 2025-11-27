<?php

namespace Tests\Unit;

use App\Models\Post;
use App\Models\User;
use App\Models\Comment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PostTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    public function test_can_create_a_post(): void
    {
        $post = Post::factory()->create([
            'user_id' => $this->user->id,
            'title' => 'Test Post',
            'content' => 'This is test content.',
            'status' => 'published',
        ]);

        $this->assertDatabaseHas('posts', [
            'title' => 'Test Post',
            'user_id' => $this->user->id,
        ]);
    }

    public function test_post_generates_slug_automatically(): void
    {
        $post = Post::create([
            'user_id' => $this->user->id,
            'title' => 'My Amazing Blog Post',
            'content' => 'Content here',
            'status' => 'draft',
        ]);

        $this->assertStringContainsString('my-amazing-blog-post', $post->slug);
    }

    public function test_post_belongs_to_user(): void
    {
        $post = Post::factory()->create(['user_id' => $this->user->id]);

        $this->assertInstanceOf(User::class, $post->author);
        $this->assertEquals($this->user->id, $post->author->id);
    }

    public function test_post_has_many_comments(): void
    {
        $post = Post::factory()->create(['user_id' => $this->user->id]);
        Comment::factory(3)->create(['post_id' => $post->id, 'user_id' => $this->user->id]);

        $this->assertCount(3, $post->comments);
    }

    public function test_post_scope_published(): void
    {
        Post::factory()->create([
            'user_id' => $this->user->id,
            'status' => 'published',
            'published_at' => now()->subDay(),
        ]);
        Post::factory()->create([
            'user_id' => $this->user->id,
            'status' => 'draft',
        ]);

        $publishedPosts = Post::published()->get();

        $this->assertCount(1, $publishedPosts);
    }

    public function test_post_scope_draft(): void
    {
        Post::factory()->create([
            'user_id' => $this->user->id,
            'status' => 'draft',
        ]);
        Post::factory()->create([
            'user_id' => $this->user->id,
            'status' => 'published',
            'published_at' => now(),
        ]);

        $draftPosts = Post::draft()->get();

        $this->assertCount(1, $draftPosts);
    }

    public function test_post_soft_delete(): void
    {
        $post = Post::factory()->create(['user_id' => $this->user->id]);
        $postId = $post->id;

        $post->delete();

        $this->assertSoftDeleted('posts', ['id' => $postId]);
        $this->assertDatabaseHas('posts', ['id' => $postId]);
    }

    public function test_post_can_be_restored(): void
    {
        $post = Post::factory()->create(['user_id' => $this->user->id]);
        $post->delete();

        $post->restore();

        $this->assertNull($post->fresh()->deleted_at);
    }

    public function test_excerpt_attribute(): void
    {
        $post = Post::factory()->create([
            'user_id' => $this->user->id,
            'content' => '<p>' . str_repeat('a', 500) . '</p>',
        ]);

        $this->assertLessThanOrEqual(203, strlen($post->excerpt)); // 200 chars + "..."
    }

    public function test_reading_time_attribute(): void
    {
        $post = Post::factory()->create([
            'user_id' => $this->user->id,
            'content' => str_repeat('word ', 400), // 400 words = 2 min read
        ]);

        $this->assertEquals(2, $post->reading_time);
    }

    public function test_is_published_method(): void
    {
        $publishedPost = Post::factory()->create([
            'user_id' => $this->user->id,
            'status' => 'published',
            'published_at' => now()->subHour(),
        ]);

        $draftPost = Post::factory()->create([
            'user_id' => $this->user->id,
            'status' => 'draft',
        ]);

        $this->assertTrue($publishedPost->isPublished());
        $this->assertFalse($draftPost->isPublished());
    }
}

