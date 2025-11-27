<x-app-layout>
    <x-slot name="title">Blog - {{ config('app.name') }}</x-slot>

    <!-- Hero Section -->
    <div class="gradient-bg text-white py-16 mb-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h1 class="font-serif text-4xl md:text-5xl font-bold mb-4">Welcome to Our Blog</h1>
            <p class="text-lg text-indigo-100 max-w-2xl mx-auto">Discover stories, insights, and ideas from our community of writers.</p>
            
            <!-- Search Form -->
            <form action="{{ route('posts.index') }}" method="GET" class="mt-8 max-w-xl mx-auto">
                <div class="flex">
                    <input type="text" name="search" value="{{ request('search') }}" 
                           placeholder="Search posts..." 
                           class="flex-1 px-4 py-3 rounded-l-lg border-0 focus:ring-2 focus:ring-indigo-300 text-gray-900">
                    <button type="submit" class="bg-indigo-800 hover:bg-indigo-900 px-6 py-3 rounded-r-lg transition">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        @if(request('search'))
            <div class="mb-6 flex items-center justify-between">
                <p class="text-gray-600">
                    Showing results for: <span class="font-semibold">"{{ request('search') }}"</span>
                </p>
                <a href="{{ route('posts.index') }}" class="text-indigo-600 hover:text-indigo-800">Clear search</a>
            </div>
        @endif

        @if($posts->count() > 0)
            <div class="grid gap-8 md:grid-cols-2 lg:grid-cols-3">
                @foreach($posts as $post)
                    <article class="bg-white rounded-xl shadow-sm overflow-hidden card-hover">
                        @if($post->featured_image)
                            <img src="{{ asset('storage/' . $post->featured_image) }}" alt="{{ $post->title }}" 
                                 class="w-full h-48 object-cover">
                        @else
                            <div class="w-full h-48 bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center">
                                <svg class="w-16 h-16 text-white/50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"></path>
                                </svg>
                            </div>
                        @endif
                        
                        <div class="p-6">
                            <div class="flex items-center text-sm text-gray-500 mb-3">
                                <img class="w-8 h-8 rounded-full object-cover mr-2" src="{{ $post->author->avatar_url }}" alt="{{ $post->author->name }}">
                                <span>{{ $post->author->name }}</span>
                                <span class="mx-2">•</span>
                                <time datetime="{{ $post->published_at }}">{{ $post->published_at->format('M d, Y') }}</time>
                            </div>
                            
                            <h2 class="font-serif text-xl font-bold text-gray-900 mb-2 line-clamp-2">
                                <a href="{{ route('posts.show', $post) }}" class="hover:text-indigo-600 transition">
                                    {{ $post->title }}
                                </a>
                            </h2>
                            
                            <p class="text-gray-600 mb-4 line-clamp-3">{{ $post->excerpt }}</p>
                            
                            <div class="flex items-center justify-between text-sm">
                                <span class="text-gray-500">{{ $post->reading_time }} min read</span>
                                <a href="{{ route('posts.show', $post) }}" class="text-indigo-600 hover:text-indigo-800 font-medium">
                                    Read more →
                                </a>
                            </div>
                        </div>
                    </article>
                @endforeach
            </div>

            <!-- Pagination -->
            <div class="mt-10">
                {{ $posts->links() }}
            </div>
        @else
            <div class="text-center py-16">
                <svg class="w-16 h-16 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"></path>
                </svg>
                <h3 class="text-lg font-medium text-gray-900 mb-2">No posts found</h3>
                <p class="text-gray-500">
                    @if(request('search'))
                        No posts match your search criteria. Try a different search term.
                    @else
                        Be the first to share your thoughts!
                    @endif
                </p>
                @auth
                    <a href="{{ route('posts.create') }}" class="inline-flex items-center mt-4 px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition">
                        Write your first post
                    </a>
                @endauth
            </div>
        @endif
    </div>
</x-app-layout>

