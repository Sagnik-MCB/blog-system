<x-app-layout>
    <x-slot name="title">{{ $post->title }} - {{ config('app.name') }}</x-slot>

    <article class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Post Header -->
        <header class="mb-8">
            @if($post->featured_image)
                <img src="{{ asset('storage/' . $post->featured_image) }}" alt="{{ $post->title }}" 
                     class="w-full h-64 md:h-96 object-cover rounded-xl mb-8">
            @endif

            <h1 class="font-serif text-3xl md:text-4xl lg:text-5xl font-bold text-gray-900 mb-6">
                {{ $post->title }}
            </h1>

            <div class="flex flex-wrap items-center gap-4 text-gray-600">
                <div class="flex items-center">
                    <img class="w-12 h-12 rounded-full object-cover mr-3" src="{{ $post->author->avatar_url }}" alt="{{ $post->author->name }}">
                    <div>
                        <p class="font-medium text-gray-900">{{ $post->author->name }}</p>
                        <p class="text-sm">
                            <time datetime="{{ $post->published_at ?? $post->created_at }}">
                                {{ ($post->published_at ?? $post->created_at)->format('F d, Y') }}
                            </time>
                            • {{ $post->reading_time }} min read
                        </p>
                    </div>
                </div>

                @canManage($post)
                    <div class="flex gap-2 ml-auto">
                        <a href="{{ route('posts.edit', $post) }}" 
                           class="inline-flex items-center px-3 py-1.5 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg text-sm transition">
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                            </svg>
                            Edit
                        </a>
                        <form action="{{ route('posts.destroy', $post) }}" method="POST" 
                              onsubmit="return confirm('Are you sure you want to delete this post?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" 
                                    class="inline-flex items-center px-3 py-1.5 bg-red-50 hover:bg-red-100 text-red-600 rounded-lg text-sm transition">
                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                </svg>
                                Delete
                            </button>
                        </form>
                    </div>
                @endcanManage

                @if(!$post->isPublished())
                    <span class="px-3 py-1 bg-yellow-100 text-yellow-800 rounded-full text-sm font-medium">
                        Draft
                    </span>
                @endif
            </div>
        </header>

        <!-- Post Content -->
        <div class="prose prose-lg max-w-none mb-12">
            {!! $post->content !!}
        </div>

        <!-- Share Section -->
        <div class="border-t border-b border-gray-200 py-6 mb-12">
            <p class="text-gray-600 mb-3">Share this post:</p>
            <div class="flex gap-3">
                <a href="https://twitter.com/intent/tweet?url={{ urlencode(request()->url()) }}&text={{ urlencode($post->title) }}" 
                   target="_blank" class="p-2 bg-gray-100 hover:bg-blue-100 rounded-lg transition">
                    <svg class="w-5 h-5 text-gray-600" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/>
                    </svg>
                </a>
                <a href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode(request()->url()) }}" 
                   target="_blank" class="p-2 bg-gray-100 hover:bg-blue-100 rounded-lg transition">
                    <svg class="w-5 h-5 text-gray-600" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
                    </svg>
                </a>
                <a href="https://www.linkedin.com/shareArticle?mini=true&url={{ urlencode(request()->url()) }}&title={{ urlencode($post->title) }}" 
                   target="_blank" class="p-2 bg-gray-100 hover:bg-blue-100 rounded-lg transition">
                    <svg class="w-5 h-5 text-gray-600" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433c-1.144 0-2.063-.926-2.063-2.065 0-1.138.92-2.063 2.063-2.063 1.14 0 2.064.925 2.064 2.063 0 1.139-.925 2.065-2.064 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"/>
                    </svg>
                </a>
            </div>
        </div>

        <!-- Comments Section -->
        <section id="comments" class="mb-12">
            <h2 class="font-serif text-2xl font-bold text-gray-900 mb-6">
                Comments ({{ $post->approvedComments->count() }})
            </h2>

            @auth
                <!-- Comment Form -->
                <form action="{{ route('comments.store', $post) }}" method="POST" class="mb-8">
                    @csrf
                    <div class="mb-4">
                        <label for="content" class="sr-only">Your comment</label>
                        <textarea name="content" id="content" rows="4" 
                                  placeholder="Share your thoughts..."
                                  class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent resize-none"
                                  required>{{ old('content') }}</textarea>
                        @error('content')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <button type="submit" 
                            class="px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-medium rounded-lg transition">
                        Post Comment
                    </button>
                </form>
            @else
                <div class="bg-gray-50 rounded-lg p-6 text-center mb-8">
                    <p class="text-gray-600 mb-3">Join the conversation!</p>
                    <a href="{{ route('login') }}" class="text-indigo-600 hover:text-indigo-800 font-medium">
                        Log in to comment
                    </a>
                </div>
            @endauth

            <!-- Comments List -->
            <div class="space-y-6">
                @forelse($post->approvedComments->whereNull('parent_id') as $comment)
                    @include('posts.partials.comment', ['comment' => $comment])
                @empty
                    <p class="text-gray-500 text-center py-8">No comments yet. Be the first to share your thoughts!</p>
                @endforelse
            </div>
        </section>
    </article>
</x-app-layout>

