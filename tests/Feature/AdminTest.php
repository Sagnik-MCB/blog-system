<?php

namespace Tests\Feature;

use App\Models\Post;
use App\Models\User;
use App\Models\Comment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AdminTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        
        Role::create(['name' => 'admin']);
        Role::create(['name' => 'user']);
        
        $this->user = User::factory()->create();
        $this->user->assignRole('user');
        
        $this->admin = User::factory()->create();
        $this->admin->assignRole('admin');
    }

    public function test_regular_user_cannot_access_admin_dashboard(): void
    {
        $response = $this->actingAs($this->user)->get(route('admin.dashboard'));

        $response->assertStatus(403);
    }

    public function test_admin_can_access_admin_dashboard(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.dashboard'));

        $response->assertStatus(200);
    }

    public function test_admin_can_view_all_users(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.users.index'));

        $response->assertStatus(200);
        $response->assertSee($this->user->name);
    }

    public function test_admin_can_create_user(): void
    {
        $response = $this->actingAs($this->admin)->post(route('admin.users.store'), [
            'name' => 'New User',
            'email' => 'newuser@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'user',
            'is_active' => true,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('users', [
            'email' => 'newuser@example.com',
        ]);
    }

    public function test_admin_can_toggle_user_status(): void
    {
        $response = $this->actingAs($this->admin)
            ->post(route('admin.users.toggle-status', $this->user));

        $response->assertRedirect();
        $this->assertFalse($this->user->fresh()->is_active);
    }

    public function test_admin_cannot_toggle_own_status(): void
    {
        $response = $this->actingAs($this->admin)
            ->post(route('admin.users.toggle-status', $this->admin));

        $response->assertRedirect();
        $response->assertSessionHas('error');
    }

    public function test_admin_can_view_all_posts(): void
    {
        Post::factory(3)->create(['user_id' => $this->user->id]);

        $response = $this->actingAs($this->admin)->get(route('admin.posts.index'));

        $response->assertStatus(200);
    }

    public function test_admin_can_delete_any_post(): void
    {
        $post = Post::factory()->create(['user_id' => $this->user->id]);

        $response = $this->actingAs($this->admin)
            ->delete(route('admin.posts.destroy', $post->slug));

        $response->assertRedirect();
        $this->assertSoftDeleted('posts', ['id' => $post->id]);
    }

    public function test_admin_can_restore_deleted_post(): void
    {
        $post = Post::factory()->create(['user_id' => $this->user->id]);
        $post->delete();

        $response = $this->actingAs($this->admin)
            ->post(route('admin.posts.restore', $post->slug));

        $response->assertRedirect();
        $this->assertNull(Post::find($post->id)->deleted_at);
    }

    public function test_admin_can_view_all_comments(): void
    {
        $post = Post::factory()->create(['user_id' => $this->user->id]);
        Comment::factory(3)->create([
            'post_id' => $post->id,
            'user_id' => $this->user->id,
        ]);

        $response = $this->actingAs($this->admin)->get(route('admin.comments.index'));

        $response->assertStatus(200);
    }

    public function test_admin_can_approve_comment(): void
    {
        $post = Post::factory()->create(['user_id' => $this->user->id]);
        $comment = Comment::factory()->create([
            'post_id' => $post->id,
            'user_id' => $this->user->id,
            'is_approved' => false,
        ]);

        $response = $this->actingAs($this->admin)
            ->post(route('admin.comments.approve', $comment));

        $response->assertRedirect();
        $this->assertTrue($comment->fresh()->is_approved);
    }

    public function test_admin_can_delete_comment(): void
    {
        $post = Post::factory()->create(['user_id' => $this->user->id]);
        $comment = Comment::factory()->create([
            'post_id' => $post->id,
            'user_id' => $this->user->id,
        ]);

        $response = $this->actingAs($this->admin)
            ->delete(route('admin.comments.destroy', $comment));

        $response->assertRedirect();
        $this->assertSoftDeleted('comments', ['id' => $comment->id]);
    }
}

