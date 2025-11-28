# Laravel Blog System

A comprehensive blog application built with Laravel, featuring user authentication, post management, commenting system, and an admin panel.

## 🚀 Features

### User Authentication
- ✅ User registration, login, and logout
- ✅ Laravel Breeze authentication scaffolding
- ✅ Social media login (Google, Facebook) via Laravel Socialite
- ✅ Email verification support
- ✅ Password reset functionality

### User Roles and Permissions
- ✅ Role-based access control using Spatie Laravel-Permission
- ✅ Two user types: Admin and Regular User
- ✅ Admins can manage all posts and users
- ✅ Regular users can manage their own posts

### Post Management
- ✅ Full CRUD operations for posts
- ✅ Posts include title, content, author, and timestamps
- ✅ Featured image upload support
- ✅ Draft and published status
- ✅ Automatic slug generation
- ✅ Soft deletes with restore functionality
- ✅ Search functionality
- ✅ Pagination

### Comments System
- ✅ CRUD operations for comments
- ✅ Nested/threaded comments (replies)
- ✅ Comment approval system
- ✅ Eloquent relationships (Post hasMany Comments)

### Admin Panel
- ✅ Dashboard with statistics (users, posts, comments)
- ✅ User management (create, edit, delete, activate/deactivate)
- ✅ Post management with trash/restore
- ✅ Comment management with bulk approve
- ✅ Activity log viewing
- ✅ Charts for post activity

### Advanced Routing
- ✅ Route groups for admin and authenticated users
- ✅ Route model binding (posts by slug)
- ✅ Named routes throughout
- ✅ Middleware protection

### Custom Middleware
- ✅ `LogUserActivity` - Logs user activities
- ✅ `CheckRole` - Role-based access control
- ✅ `CheckUserActive` - Prevents inactive users from accessing

### Service Provider
- ✅ `BlogServiceProvider` - Custom service provider for business logic
- ✅ Custom Blade directives (@admin, @owns, @canManage)
- ✅ Singleton BlogService for post operations

### Performance Optimization
- ✅ Cache implementation for posts and statistics
- ✅ Eager loading to reduce N+1 queries
- ✅ Database indexing on frequently queried columns
- ✅ Query optimization with scopes

### Testing
- ✅ Unit tests for Post, Comment, and User models
- ✅ Feature tests for PostController
- ✅ Feature tests for Admin functionality
- ✅ Middleware tests

### API (Bonus)
- ✅ RESTful API for mobile applications
- ✅ API authentication using Laravel Sanctum
- ✅ Versioned API (v1)
- ✅ Endpoints for posts, comments, users, and authentication

## 📋 Requirements

- PHP >= 8.2
- Composer
- MySQL >= 5.7 or MariaDB
- Node.js >= 18

## 🛠️ Installation

### 1. Clone the repository

```bash
git clone https://github.com/Sagnik-MCB/blog-system.git
cd blog-system
```

### 2. Install PHP dependencies

```bash
composer install
```

### 3. Install NPM dependencies

```bash
npm install
npm run build
```

### 4. Environment Setup

```bash
cp .env.example .env
php artisan key:generate
```

### 5. Configure Database

Edit `.env` file with your database credentials:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=blog_system
DB_USERNAME=root
DB_PASSWORD=root
```

### 6. Configure Social Login (Optional)

Add these to your `.env` file:

```env
# Google OAuth
GOOGLE_CLIENT_ID=your-google-client-id
GOOGLE_CLIENT_SECRET=your-google-client-secret
GOOGLE_REDIRECT_URL=http://localhost:8000/auth/google/callback

# Facebook OAuth
FACEBOOK_CLIENT_ID=your-facebook-app-id
FACEBOOK_CLIENT_SECRET=your-facebook-app-secret
FACEBOOK_REDIRECT_URL=http://localhost:8000/auth/facebook/callback
```

### 7. Run Migrations and Seeders

```bash
php artisan migrate
php artisan db:seed
```

### 8. Create Storage Link

```bash
php artisan storage:link
```

### 9. Start the Development Server

```bash
php artisan serve
```

Visit `http://localhost:8000` in your browser.

## 👤 Default Credentials

After running seeders, you can login with:

- **Admin User:**
  - Email: `admin@blog.com`
  - Password: `password`

## 📁 Project Structure

```
blog-system/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Admin/          # Admin panel controllers
│   │   │   ├── Api/            # API controllers
│   │   │   ├── PostController.php
│   │   │   ├── CommentController.php
│   │   │   └── SocialAuthController.php
│   │   └── Middleware/
│   │       ├── CheckRole.php
│   │       ├── CheckUserActive.php
│   │       └── LogUserActivity.php
│   ├── Models/
│   │   ├── User.php
│   │   ├── Post.php
│   │   ├── Comment.php
│   │   ├── SocialIdentity.php
│   │   └── ActivityLog.php
│   ├── Policies/
│   │   ├── PostPolicy.php
│   │   └── CommentPolicy.php
│   ├── Providers/
│   │   └── BlogServiceProvider.php
│   └── Services/
│       └── BlogService.php
├── database/
│   ├── factories/
│   ├── migrations/
│   └── seeders/
├── resources/
│   └── views/
│       ├── admin/              # Admin panel views
│       ├── posts/              # Post views
│       └── layouts/            # Layout templates
├── routes/
│   ├── web.php                 # Web routes
│   └── api.php                 # API routes
└── tests/
    ├── Unit/
    └── Feature/
```

## 🔌 API Documentation

### Authentication

#### Register
```
POST /api/v1/register
{
    "name": "John Doe",
    "email": "john@example.com",
    "password": "password",
    "password_confirmation": "password"
}
```

#### Login
```
POST /api/v1/login
{
    "email": "john@example.com",
    "password": "password"
}
```

### Posts

#### Get All Posts
```
GET /api/v1/posts
```

#### Get Single Post
```
GET /api/v1/posts/{slug}
```

#### Create Post (Authenticated)
```
POST /api/v1/posts
Authorization: Bearer {token}
{
    "title": "Post Title",
    "content": "Post content here...",
    "status": "published"
}
```

### Comments

#### Get Post Comments
```
GET /api/v1/posts/{slug}/comments
```

#### Create Comment (Authenticated)
```
POST /api/v1/posts/{slug}/comments
Authorization: Bearer {token}
{
    "content": "Comment text"
}
```

## 🧪 Running Tests

```bash
php artisan test
```

Or with coverage:

```bash
php artisan test --coverage
```

## 📊 Database Schema

### Users Table
- id, name, email, password, avatar, is_active, timestamps

### Posts Table
- id, user_id, title, slug, content, featured_image, status, published_at, timestamps, soft_deletes

### Comments Table
- id, post_id, user_id, parent_id, content, is_approved, timestamps, soft_deletes

### Social Identities Table
- id, user_id, provider_name, provider_id, access_token, refresh_token, timestamps

### Activity Logs Table
- id, user_id, action, model_type, model_id, description, properties, ip_address, user_agent, timestamps

## 🔒 Roles and Permissions

### Admin Role
- View admin dashboard
- Manage all users
- Manage all posts
- Manage all comments
- Approve/reject comments

### User Role
- Create posts
- Edit own posts
- Delete own posts
- Create comments
- Edit own comments
- Delete own comments

## 🎨 Frontend

The application uses:
- **Tailwind CSS** for styling
- **Alpine.js** for interactivity
- **Laravel Blade** for templating
- **Chart.js** for admin dashboard charts

## 👏 Acknowledgements

- [Laravel](https://laravel.com)
- [Laravel Breeze](https://laravel.com/docs/starter-kits#laravel-breeze)
- [Spatie Laravel Permission](https://spatie.be/docs/laravel-permission)
- [Laravel Socialite](https://laravel.com/docs/socialite)
- [Tailwind CSS](https://tailwindcss.com)
