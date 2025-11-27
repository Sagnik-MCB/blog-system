<?php

namespace App\Providers;

use App\Services\BlogService;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Blade;

class BlogServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Register BlogService as a singleton
        $this->app->singleton(BlogService::class, function ($app) {
            return new BlogService();
        });

        // Alias for easier access
        $this->app->alias(BlogService::class, 'blog');
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Share common data with all views
        View::composer('*', function ($view) {
            if (auth()->check()) {
                $view->with('currentUser', auth()->user());
            }
        });

        // Custom Blade directives
        $this->registerBladeDirectives();
    }

    /**
     * Register custom Blade directives.
     */
    protected function registerBladeDirectives(): void
    {
        // Check if user is admin
        Blade::if('admin', function () {
            return auth()->check() && auth()->user()->isAdmin();
        });

        // Check if user owns a resource
        Blade::if('owns', function ($model) {
            return auth()->check() && auth()->id() === $model->user_id;
        });

        // Check if user can manage a resource (owner or admin)
        Blade::if('canManage', function ($model) {
            return auth()->check() && (auth()->id() === $model->user_id || auth()->user()->isAdmin());
        });

        // Format date directive
        Blade::directive('formatDate', function ($expression) {
            return "<?php echo ($expression)->format('M d, Y'); ?>";
        });

        // Format datetime directive
        Blade::directive('formatDateTime', function ($expression) {
            return "<?php echo ($expression)->format('M d, Y H:i'); ?>";
        });

        // Reading time directive
        Blade::directive('readingTime', function ($expression) {
            return "<?php 
                \$words = str_word_count(strip_tags($expression));
                \$minutes = max(1, ceil(\$words / 200));
                echo \$minutes . ' min read';
            ?>";
        });

        // Truncate text directive
        Blade::directive('truncate', function ($expression) {
            list($text, $length) = explode(',', $expression);
            return "<?php echo Str::limit(strip_tags($text), $length); ?>";
        });
    }
}

