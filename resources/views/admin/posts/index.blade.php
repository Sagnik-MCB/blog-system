<x-admin-layout>
    <x-slot name="title">Manage Posts</x-slot>
    <x-slot name="header">Post Management</x-slot>

    <!-- Filters -->
    <div class="bg-white rounded-xl shadow-sm p-4 mb-6">
        <form action="{{ route('admin.posts.index') }}" method="GET" class="flex flex-wrap gap-4 items-end">
            <div class="flex-1 min-w-[200px]">
                <label class="block text-sm font-medium text-gray-700 mb-1">Search</label>
                <input type="text" name="search" value="{{ request('search') }}" 
                       placeholder="Title or content..."
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
            </div>
            <div class="w-40">
                <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                <select name="status" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                    <option value="">All Status</option>
                    <option value="published" {{ request('status') === 'published' ? 'selected' : '' }}>Published</option>
                    <option value="draft" {{ request('status') === 'draft' ? 'selected' : '' }}>Draft</option>
                    <option value="trashed" {{ request('status') === 'trashed' ? 'selected' : '' }}>Trashed</option>
                </select>
            </div>
            <button type="submit" class="px-6 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700">
                Filter
            </button>
            <a href="{{ route('admin.posts.index') }}" class="px-6 py-2 text-gray-600 hover:text-gray-800">
                Reset
            </a>
        </form>
    </div>

    <!-- Posts Table -->
    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold">All Posts ({{ $posts->total() }})</h3>
        </div>
        
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Post</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Author</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($posts as $post)
                    <tr class="hover:bg-gray-50 {{ $post->trashed() ? 'bg-red-50' : '' }}">
                        <td class="px-6 py-4">
                            <div class="flex items-center">
                                @if($post->featured_image)
                                    <img src="{{ asset('storage/' . $post->featured_image) }}" 
                                         alt="{{ $post->title }}"
                                         class="w-16 h-12 object-cover rounded mr-4">
                                @else
                                    <div class="w-16 h-12 bg-gray-100 rounded mr-4 flex items-center justify-center">
                                        <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                        </svg>
                                    </div>
                                @endif
                                <div>
                                    <a href="{{ route('admin.posts.show', $post->slug) }}" 
                                       class="font-medium text-gray-900 hover:text-indigo-600">
                                        {{ Str::limit($post->title, 50) }}
                                    </a>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="text-sm text-gray-900">{{ $post->author->name ?? 'Unknown' }}</span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if($post->trashed())
                                <span class="px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                    Trashed
                                </span>
                            @elseif($post->status === 'published')
                                <span class="px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                    Published
                                </span>
                            @else
                                <span class="px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                    Draft
                                </span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ $post->created_at->format('M d, Y') }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            @if($post->trashed())
                                <form action="{{ route('admin.posts.restore', $post->slug) }}" method="POST" class="inline">
                                    @csrf
                                    <button type="submit" class="text-green-600 hover:text-green-900 mr-3">Restore</button>
                                </form>
                                <form action="{{ route('admin.posts.force-delete', $post->slug) }}" method="POST" class="inline"
                                      onsubmit="return confirm('Permanently delete this post?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:text-red-900">Delete Permanently</button>
                                </form>
                            @else
                                <a href="{{ route('admin.posts.show', $post->slug) }}" class="text-gray-600 hover:text-gray-900 mr-3">View</a>
                                <a href="{{ route('admin.posts.edit', $post->slug) }}" class="text-indigo-600 hover:text-indigo-900 mr-3">Edit</a>
                                <form action="{{ route('admin.posts.destroy', $post->slug) }}" method="POST" class="inline"
                                      onsubmit="return confirm('Move this post to trash?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:text-red-900">Trash</button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-6 py-8 text-center text-gray-500">
                            No posts found
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        @if($posts->hasPages())
            <div class="px-6 py-4 border-t border-gray-200">
                {{ $posts->links() }}
            </div>
        @endif
    </div>
</x-admin-layout>

