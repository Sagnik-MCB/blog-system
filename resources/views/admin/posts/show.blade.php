<x-admin-layout>
    <x-slot name="title">View Post</x-slot>
    <x-slot name="header">Post Details</x-slot>

    <div class="max-w-4xl">
        <!-- Post Info -->
        <div class="bg-white rounded-xl shadow-sm p-6 mb-6">
            @if($post->featured_image)
                <img src="{{ asset('storage/' . $post->featured_image) }}" alt="{{ $post->title }}" 
                     class="w-full h-64 object-cover rounded-lg mb-6">
            @endif
            
            <div class="flex items-center justify-between mb-4">
                <div class="flex items-center space-x-4">
                    @if($post->trashed())
                        <span class="px-3 py-1 rounded-full text-sm font-medium bg-red-100 text-red-800">Trashed</span>
                    @elseif($post->status === 'published')
                        <span class="px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800">Published</span>
                    @else
                        <span class="px-3 py-1 rounded-full text-sm font-medium bg-yellow-100 text-yellow-800">Draft</span>
                    @endif
                </div>
                <div class="flex space-x-3">
                    @if($post->trashed())
                        <form action="{{ route('admin.posts.restore', $post->slug) }}" method="POST">
                            @csrf
                            <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700">
                                Restore
                            </button>
                        </form>
                    @else
                        <a href="{{ route('admin.posts.edit', $post->slug) }}" 
                           class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700">
                            Edit
                        </a>
                        <form action="{{ route('admin.posts.destroy', $post->slug) }}" method="POST"
                              onsubmit="return confirm('Move this post to trash?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700">
                                Trash
                            </button>
                        </form>
                    @endif
                </div>
            </div>

            <h1 class="text-2xl font-bold text-gray-900 mb-4">{{ $post->title }}</h1>
            
            <div class="flex items-center text-sm text-gray-500 mb-6">
                <img class="w-8 h-8 rounded-full object-cover mr-2" 
                     src="{{ $post->author->avatar_url ?? '' }}" alt="{{ $post->author->name ?? 'Unknown' }}">
                <span>{{ $post->author->name ?? 'Unknown' }}</span>
                <span class="mx-2">•</span>
                <span>{{ $post->created_at->format('F d, Y') }}</span>
                @if($post->published_at)
                    <span class="mx-2">•</span>
                    <span>Published: {{ $post->published_at->format('F d, Y') }}</span>
                @endif
            </div>
            
            <div class="prose max-w-none">
                {!! $post->content !!}
            </div>
        </div>

        <!-- Comments -->
        <div class="bg-white rounded-xl shadow-sm p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Comments ({{ $post->comments->count() }})</h3>
            
            @if($post->comments->count() > 0)
                <div class="space-y-4">
                    @foreach($post->comments as $comment)
                        <div class="flex space-x-3 py-3 border-b border-gray-100 last:border-0 {{ !$comment->is_approved ? 'bg-yellow-50 -mx-3 px-3 rounded' : '' }}">
                            <img class="w-10 h-10 rounded-full object-cover" 
                                 src="{{ $comment->user->avatar_url }}" alt="{{ $comment->user->name }}">
                            <div class="flex-1">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <span class="font-medium text-gray-900">{{ $comment->user->name }}</span>
                                        <span class="text-sm text-gray-500 ml-2">{{ $comment->created_at->diffForHumans() }}</span>
                                        @if(!$comment->is_approved)
                                            <span class="ml-2 px-2 py-0.5 text-xs rounded-full bg-yellow-100 text-yellow-800">Pending</span>
                                        @endif
                                    </div>
                                    <div class="flex space-x-2">
                                        @if(!$comment->is_approved)
                                            <form action="{{ route('admin.comments.approve', $comment) }}" method="POST">
                                                @csrf
                                                <button type="submit" class="text-green-600 hover:text-green-800 text-sm">Approve</button>
                                            </form>
                                        @endif
                                        <form action="{{ route('admin.comments.destroy', $comment) }}" method="POST"
                                              onsubmit="return confirm('Delete this comment?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-600 hover:text-red-800 text-sm">Delete</button>
                                        </form>
                                    </div>
                                </div>
                                <p class="text-gray-700 mt-1">{{ $comment->content }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <p class="text-gray-500 text-center py-4">No comments yet</p>
            @endif
        </div>
    </div>
</x-admin-layout>

