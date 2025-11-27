<x-app-layout>
    <x-slot name="title">Dashboard - {{ config('app.name') }}</x-slot>

    <x-slot name="header">
        <h2 class="font-serif text-2xl font-bold text-gray-900">
            Welcome back, {{ auth()->user()->name }}!
        </h2>
    </x-slot>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Quick Stats -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <div class="bg-white rounded-xl shadow-sm p-6">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-indigo-100 text-indigo-600 mr-4">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"></path>
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-500">Total Posts</p>
                        <p class="text-2xl font-bold text-gray-900">{{ auth()->user()->posts()->count() }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm p-6">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-green-100 text-green-600 mr-4">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-500">Published</p>
                        <p class="text-2xl font-bold text-gray-900">{{ auth()->user()->posts()->published()->count() }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm p-6">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-purple-100 text-purple-600 mr-4">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-500">Comments</p>
                        <p class="text-2xl font-bold text-gray-900">{{ auth()->user()->comments()->count() }}</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <!-- Recent Posts -->
            <div class="bg-white rounded-xl shadow-sm p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="font-serif text-lg font-bold text-gray-900">Recent Posts</h3>
                    <a href="{{ route('posts.my-posts') }}" class="text-sm text-indigo-600 hover:text-indigo-800">
                        View all →
                    </a>
                </div>
                
                @if($recentPosts->count() > 0)
                    <div class="space-y-4">
                        @foreach($recentPosts as $post)
                            <div class="flex items-center justify-between py-3 border-b border-gray-100 last:border-0">
                                <div class="flex-1 min-w-0">
                                    <a href="{{ route('posts.show', $post) }}" 
                                       class="font-medium text-gray-900 hover:text-indigo-600 truncate block">
                                        {{ $post->title }}
                                    </a>
                                    <p class="text-sm text-gray-500">
                                        {{ $post->created_at->format('M d, Y') }}
                                    </p>
                                </div>
                                <span class="ml-4 px-2.5 py-0.5 rounded-full text-xs font-medium {{ $post->status === 'published' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">
                                    {{ ucfirst($post->status) }}
                                </span>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-8">
                        <p class="text-gray-500 mb-4">No posts yet</p>
                        <a href="{{ route('posts.create') }}" class="text-indigo-600 hover:text-indigo-800 font-medium">
                            Create your first post
                        </a>
                    </div>
                @endif
            </div>

            <!-- Recent Comments -->
            <div class="bg-white rounded-xl shadow-sm p-6">
                <h3 class="font-serif text-lg font-bold text-gray-900 mb-4">Recent Comments</h3>
                
                @if($recentComments->count() > 0)
                    <div class="space-y-4">
                        @foreach($recentComments as $comment)
                            <div class="py-3 border-b border-gray-100 last:border-0">
                                <p class="text-gray-700 line-clamp-2 mb-1">{{ $comment->content }}</p>
                                <p class="text-sm text-gray-500">
                                    on <a href="{{ route('posts.show', $comment->post) }}" 
                                          class="text-indigo-600 hover:text-indigo-800">
                                        {{ Str::limit($comment->post->title, 30) }}
                                    </a>
                                    • {{ $comment->created_at->diffForHumans() }}
                                </p>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-8">
                        <p class="text-gray-500">No comments yet</p>
                    </div>
                @endif
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="mt-8 bg-gradient-to-r from-indigo-500 to-purple-600 rounded-xl shadow-sm p-6 text-white">
            <h3 class="text-lg font-bold mb-4">Quick Actions</h3>
            <div class="flex flex-wrap gap-4">
                <a href="{{ route('posts.create') }}" 
                   class="inline-flex items-center px-4 py-2 bg-white/20 hover:bg-white/30 rounded-lg transition">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                    Write New Post
                </a>
                <a href="{{ route('posts.my-posts') }}" 
                   class="inline-flex items-center px-4 py-2 bg-white/20 hover:bg-white/30 rounded-lg transition">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                    </svg>
                    Manage Posts
                </a>
                <a href="{{ route('profile.edit') }}" 
                   class="inline-flex items-center px-4 py-2 bg-white/20 hover:bg-white/30 rounded-lg transition">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                    </svg>
                    Edit Profile
                </a>
            </div>
        </div>
    </div>
</x-app-layout>
