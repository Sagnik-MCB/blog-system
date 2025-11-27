<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Post;
use App\Models\User;
use App\Models\Comment;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /**
     * Display the admin dashboard.
     */
    public function index(): View
    {
        $stats = Cache::remember('admin.dashboard.stats', 300, function () {
            return [
                'total_users' => User::count(),
                'total_posts' => Post::count(),
                'published_posts' => Post::published()->count(),
                'draft_posts' => Post::draft()->count(),
                'total_comments' => Comment::count(),
                'pending_comments' => Comment::pending()->count(),
                'new_users_this_week' => User::where('created_at', '>=', now()->subWeek())->count(),
                'new_posts_this_week' => Post::where('created_at', '>=', now()->subWeek())->count(),
            ];
        });

        // Get recent activities
        $recentActivities = ActivityLog::with('user')
            ->latest()
            ->take(10)
            ->get();

        // Get popular posts (most commented)
        $popularPosts = Post::published()
            ->withCount('comments')
            ->orderByDesc('comments_count')
            ->take(5)
            ->get();

        // Get chart data for posts over last 30 days
        $chartData = $this->getPostsChartData();

        return view('admin.dashboard', compact('stats', 'recentActivities', 'popularPosts', 'chartData'));
    }

    /**
     * Get posts chart data for the last 30 days.
     */
    protected function getPostsChartData(): array
    {
        $posts = Post::select(DB::raw('DATE(created_at) as date'), DB::raw('count(*) as count'))
            ->where('created_at', '>=', now()->subDays(30))
            ->groupBy('date')
            ->orderBy('date')
            ->pluck('count', 'date')
            ->toArray();

        $dates = [];
        $counts = [];
        
        for ($i = 29; $i >= 0; $i--) {
            $date = now()->subDays($i)->format('Y-m-d');
            $dates[] = now()->subDays($i)->format('M d');
            $counts[] = $posts[$date] ?? 0;
        }

        return [
            'labels' => $dates,
            'data' => $counts,
        ];
    }
}

