<?php

namespace Tests\Feature;

use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class PostControllerTest extends TestCase
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

    public function test_guest_can_view_posts_index(): void
    {
        Post::factory(5)->create([
            'user_id' => $this->user->id,
            'status' => 'published',
            'published_at' => now(),
        ]);

        $response = $this->get(route('posts.index'));

        $response->assertStatus(200);
        $response->assertViewIs('posts.index');
    }

    public function test_guest_can_view_published_post(): void
    {
        $post = Post::factory()->create([
            'user_id' => $this->user->id,
            'status' => 'published',
            'published_at' => now(),
        ]);

        $response = $this->get(route('posts.show', $post));

        $response->assertStatus(200);
        $response->assertViewIs('posts.show');
    }

    public function test_guest_cannot_view_draft_post(): void
    {
        $post = Post::factory()->create([
            'user_id' => $this->user->id,
            'status' => 'draft',
        ]);

        $response = $this->get(route('posts.show', $post));

        $response->assertStatus(404);
    }

    public function test_authenticated_user_can_create_post(): void
    {
        $response = $this->actingAs($this->user)->post(route('posts.store'), [
            'title' => 'Test Post',
            'content' => 'This is the content of the test post.',
            'status' => 'draft',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('posts', [
            'title' => 'Test Post',
            'user_id' => $this->user->id,
        ]);
    }

    public function test_authenticated_user_can_update_own_post(): void
    {
        $post = Post::factory()->create(['user_id' => $this->user->id]);

        $response = $this->actingAs($this->user)->put(route('posts.update', $post), [
            'title' => 'Updated Title',
            'content' => 'Updated content',
            'status' => 'published',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('posts', [
            'id' => $post->id,
            'title' => 'Updated Title',
        ]);
    }

    public function test_user_cannot_update_others_post(): void
    {
        $otherUser = User::factory()->create();
        $post = Post::factory()->create(['user_id' => $otherUser->id]);

        $response = $this->actingAs($this->user)->put(route('posts.update', $post), [
            'title' => 'Updated Title',
            'content' => 'Updated content',
            'status' => 'published',
        ]);

        $response->assertStatus(403);
    }

    public function test_admin_can_update_any_post(): void
    {
        $post = Post::factory()->create(['user_id' => $this->user->id]);

        $response = $this->actingAs($this->admin)->put(route('posts.update', $post), [
            'title' => 'Admin Updated Title',
            'content' => 'Admin updated content',
            'status' => 'published',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('posts', [
            'id' => $post->id,
            'title' => 'Admin Updated Title',
        ]);
    }

    public function test_authenticated_user_can_delete_own_post(): void
    {
        $post = Post::factory()->create(['user_id' => $this->user->id]);

        $response = $this->actingAs($this->user)->delete(route('posts.destroy', $post));

        $response->assertRedirect();
        $this->assertSoftDeleted('posts', ['id' => $post->id]);
    }

    public function test_search_functionality_works(): void
    {
        Post::factory()->create([
            'user_id' => $this->user->id,
            'title' => 'Laravel Tutorial',
            'status' => 'published',
            'published_at' => now(),
        ]);
        Post::factory()->create([
            'user_id' => $this->user->id,
            'title' => 'PHP Basics',
            'status' => 'published',
            'published_at' => now(),
        ]);

        $response = $this->get(route('posts.index', ['search' => 'Laravel']));

        $response->assertStatus(200);
        $response->assertSee('Laravel Tutorial');
        $response->assertDontSee('PHP Basics');
    }

    public function test_my_posts_page_shows_only_user_posts(): void
    {
        Post::factory()->create([
            'user_id' => $this->user->id,
            'title' => 'My Post',
        ]);
        Post::factory()->create([
            'user_id' => $this->admin->id,
            'title' => 'Admin Post',
        ]);

        $response = $this->actingAs($this->user)->get(route('posts.my-posts'));

        $response->assertStatus(200);
        $response->assertSee('My Post');
        $response->assertDontSee('Admin Post');
    }
}

