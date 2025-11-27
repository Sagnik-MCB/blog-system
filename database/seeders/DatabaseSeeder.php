<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Post;
use App\Models\Comment;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create roles
        $adminRole = Role::firstOrCreate(['name' => 'admin']);
        $userRole = Role::firstOrCreate(['name' => 'user']);

        // Create permissions
        $permissions = [
            // Post permissions
            'create posts',
            'edit posts',
            'delete posts',
            'publish posts',
            'manage all posts',
            
            // Comment permissions
            'create comments',
            'edit comments',
            'delete comments',
            'approve comments',
            'manage all comments',
            
            // User permissions
            'manage users',
            'view admin dashboard',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Assign permissions to admin role
        $adminRole->givePermissionTo(Permission::all());

        // Assign basic permissions to user role
        $userRole->givePermissionTo([
            'create posts',
            'edit posts',
            'delete posts',
            'create comments',
            'edit comments',
            'delete comments',
        ]);

        // Create admin user
        $admin = User::factory()->create([
            'name' => 'Admin User',
            'email' => 'admin@blog.com',
            'password' => bcrypt('password'),
            'is_active' => true,
        ]);
        $admin->assignRole('admin');

        // Create regular users
        $users = User::factory(5)->create([
            'is_active' => true,
        ]);
        
        foreach ($users as $user) {
            $user->assignRole('user');
        }

        // Create sample posts
        $allUsers = User::all();
        
        foreach ($allUsers as $user) {
            // Create 3-5 posts per user
            $postsCount = rand(3, 5);
            
            for ($i = 0; $i < $postsCount; $i++) {
                $post = Post::factory()->create([
                    'user_id' => $user->id,
                ]);

                // Add 0-5 comments per post
                $commentsCount = rand(0, 5);
                $commenters = $allUsers->random(min($commentsCount, $allUsers->count()));
                
                foreach ($commenters as $commenter) {
                    Comment::factory()->create([
                        'post_id' => $post->id,
                        'user_id' => $commenter->id,
                    ]);
                }
            }
        }

        $this->command->info('Database seeded successfully!');
        $this->command->info('Admin login: admin@blog.com / password');
    }
}
