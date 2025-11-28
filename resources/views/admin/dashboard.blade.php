<x-admin-layout>
    <x-slot name="title">Admin Dashboard</x-slot>
    <x-slot name="header">Dashboard Overview</x-slot>

    <!-- Stats Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <div class="bg-white rounded-xl shadow-sm p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500">Total Users</p>
                    <p class="text-3xl font-bold text-gray-900">{{ number_format($stats['total_users']) }}</p>
                </div>
                <div class="p-3 rounded-full bg-blue-100 text-blue-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                    </svg>
                </div>
            </div>
            <p class="text-sm text-green-600 mt-2">+{{ $stats['new_users_this_week'] }} this week</p>
        </div>

        <div class="bg-white rounded-xl shadow-sm p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500">Total Posts</p>
                    <p class="text-3xl font-bold text-gray-900">{{ number_format($stats['total_posts']) }}</p>
                </div>
                <div class="p-3 rounded-full bg-indigo-100 text-indigo-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"></path>
                    </svg>
                </div>
            </div>
            <p class="text-sm text-green-600 mt-2">+{{ $stats['new_posts_this_week'] }} this week</p>
        </div>

        <div class="bg-white rounded-xl shadow-sm p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500">Published Posts</p>
                    <p class="text-3xl font-bold text-gray-900">{{ number_format($stats['published_posts']) }}</p>
                </div>
                <div class="p-3 rounded-full bg-green-100 text-green-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
            </div>
            <p class="text-sm text-gray-500 mt-2">{{ $stats['draft_posts'] }} drafts</p>
        </div>

        <div class="bg-white rounded-xl shadow-sm p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500">Total Comments</p>
                    <p class="text-3xl font-bold text-gray-900">{{ number_format($stats['total_comments']) }}</p>
                </div>
                <div class="p-3 rounded-full bg-purple-100 text-purple-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                    </svg>
                </div>
            </div>
            <p class="text-sm text-yellow-600 mt-2">{{ $stats['pending_comments'] }} pending</p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        <!-- Posts Chart -->
        <div class="bg-white rounded-xl shadow-sm p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Posts Activity (Last 30 Days)</h3>
            <div style="height: 250px; position: relative;">
                <canvas id="postsChart"></canvas>
            </div>
        </div>

        <!-- Popular Posts -->
        <div class="bg-white rounded-xl shadow-sm p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Popular Posts</h3>
            <div class="space-y-4">
                @forelse($popularPosts as $post)
                    <div class="flex items-center justify-between py-2 border-b border-gray-100 last:border-0">
                        <div class="flex-1 min-w-0">
                            <a href="{{ route('admin.posts.show', $post->slug) }}" 
                               class="font-medium text-gray-900 hover:text-indigo-600 truncate block">
                                {{ Str::limit($post->title, 40) }}
                            </a>
                            <p class="text-sm text-gray-500">by {{ $post->author->name ?? 'Unknown' }}</p>
                        </div>
                        <span class="ml-4 px-2.5 py-0.5 rounded-full text-xs font-medium bg-indigo-100 text-indigo-800">
                            {{ $post->comments_count }} comments
                        </span>
                    </div>
                @empty
                    <p class="text-gray-500 text-center py-4">No posts yet</p>
                @endforelse
            </div>
        </div>

        <!-- Recent Activity -->
        <div class="bg-white rounded-xl shadow-sm p-6 lg:col-span-2">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Recent Activity</h3>
            <div class="overflow-x-auto">
                <table class="min-w-full">
                    <thead>
                        <tr class="text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            <th class="pb-3">User</th>
                            <th class="pb-3">Action</th>
                            <th class="pb-3">Description</th>
                            <th class="pb-3">Time</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($recentActivities as $activity)
                            <tr>
                                <td class="py-3">
                                    <div class="flex items-center">
                                        <img class="w-8 h-8 rounded-full object-cover mr-2" 
                                             src="{{ $activity->user?->avatar_url ?? 'https://www.gravatar.com/avatar/?d=mp' }}" 
                                             alt="">
                                        <span class="text-sm text-gray-900">{{ $activity->user?->name ?? 'System' }}</span>
                                    </div>
                                </td>
                                <td class="py-3">
                                    <span class="px-2 py-1 text-xs rounded-full 
                                        {{ str_contains($activity->action, 'delete') ? 'bg-red-100 text-red-800' : '' }}
                                        {{ str_contains($activity->action, 'create') ? 'bg-green-100 text-green-800' : '' }}
                                        {{ str_contains($activity->action, 'update') ? 'bg-blue-100 text-blue-800' : '' }}
                                        {{ str_contains($activity->action, 'login') ? 'bg-gray-100 text-gray-800' : '' }}">
                                        {{ str_replace('_', ' ', $activity->action) }}
                                    </span>
                                </td>
                                <td class="py-3 text-sm text-gray-600">
                                    {{ Str::limit($activity->description ?? '-', 50) }}
                                </td>
                                <td class="py-3 text-sm text-gray-500">
                                    {{ $activity->created_at->diffForHumans() }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="py-4 text-center text-gray-500">No recent activity</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        const ctx = document.getElementById('postsChart').getContext('2d');
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: @json($chartData['labels']),
                datasets: [{
                    label: 'Posts',
                    data: @json($chartData['data']),
                    borderColor: 'rgb(99, 102, 241)',
                    backgroundColor: 'rgba(99, 102, 241, 0.1)',
                    tension: 0.4,
                    fill: true,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1
                        }
                    }
                }
            }
        });
    </script>
    @endpush
</x-admin-layout>

