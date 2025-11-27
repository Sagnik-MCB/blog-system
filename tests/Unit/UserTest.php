<?php

namespace Tests\Unit;

use App\Models\User;
use App\Models\Post;
use App\Models\Comment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class UserTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Role::create(['name' => 'admin']);
        Role::create(['name' => 'user']);
    }

    public function test_can_create_a_user(): void
    {
        $user = User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        $this->assertDatabaseHas('users', [
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);
    }

    public function test_user_has_many_posts(): void
    {
        $user = User::factory()->create();
        Post::factory(3)->create(['user_id' => $user->id]);

        $this->assertCount(3, $user->posts);
    }

    public function test_user_has_many_comments(): void
    {
        $user = User::factory()->create();
        $post = Post::factory()->create(['user_id' => $user->id]);
        Comment::factory(3)->create([
            'post_id' => $post->id,
            'user_id' => $user->id,
        ]);

        $this->assertCount(3, $user->comments);
    }

    public function test_user_can_be_assigned_admin_role(): void
    {
        $user = User::factory()->create();
        $user->assignRole('admin');

        $this->assertTrue($user->hasRole('admin'));
        $this->assertTrue($user->isAdmin());
    }

    public function test_user_can_be_assigned_user_role(): void
    {
        $user = User::factory()->create();
        $user->assignRole('user');

        $this->assertTrue($user->hasRole('user'));
        $this->assertFalse($user->isAdmin());
    }

    public function test_user_scope_active(): void
    {
        User::factory()->create(['is_active' => true]);
        User::factory()->create(['is_active' => false]);

        $activeUsers = User::active()->get();

        $this->assertCount(1, $activeUsers);
    }

    public function test_avatar_url_returns_gravatar_when_no_avatar(): void
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'avatar' => null,
        ]);

        $this->assertStringContainsString('gravatar.com', $user->avatar_url);
    }

    public function test_avatar_url_returns_storage_path_when_avatar_exists(): void
    {
        $user = User::factory()->create([
            'avatar' => 'avatars/test.jpg',
        ]);

        $this->assertStringContainsString('storage/avatars/test.jpg', $user->avatar_url);
    }

    public function test_published_posts_count_attribute(): void
    {
        $user = User::factory()->create();
        Post::factory(2)->create([
            'user_id' => $user->id,
            'status' => 'published',
            'published_at' => now(),
        ]);
        Post::factory()->create([
            'user_id' => $user->id,
            'status' => 'draft',
        ]);

        $this->assertEquals(2, $user->published_posts_count);
    }

    public function test_user_is_active_by_default(): void
    {
        $user = User::factory()->create();

        $this->assertTrue($user->is_active);
    }
}

