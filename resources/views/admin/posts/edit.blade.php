<x-admin-layout>
    <x-slot name="title">Edit Post</x-slot>
    <x-slot name="header">Edit Post</x-slot>

    <div class="max-w-4xl">
        <div class="bg-white rounded-xl shadow-sm p-6">
            <form action="{{ route('admin.posts.update', $post->slug) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <div class="mb-6">
                    <label for="title" class="block text-sm font-medium text-gray-700 mb-2">Title</label>
                    <input type="text" name="title" id="title" value="{{ old('title', $post->title) }}"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                           required>
                    @error('title')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                @if($post->featured_image)
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Current Featured Image</label>
                        <img src="{{ asset('storage/' . $post->featured_image) }}" alt="Current featured image" 
                             class="max-h-48 rounded-lg">
                    </div>
                @endif

                <div class="mb-6">
                    <label for="featured_image" class="block text-sm font-medium text-gray-700 mb-2">
                        {{ $post->featured_image ? 'Replace Featured Image' : 'Featured Image' }}
                    </label>
                    <input type="file" name="featured_image" id="featured_image" accept="image/*"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                    @error('featured_image')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="mb-6">
                    <label for="content" class="block text-sm font-medium text-gray-700 mb-2">Content</label>
                    <textarea name="content" id="content" rows="15"
                              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                              required>{{ old('content', $post->content) }}</textarea>
                    @error('content')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                    <div class="flex space-x-6">
                        <label class="flex items-center">
                            <input type="radio" name="status" value="draft" 
                                   {{ old('status', $post->status) === 'draft' ? 'checked' : '' }}
                                   class="w-4 h-4 text-indigo-600 focus:ring-indigo-500 border-gray-300">
                            <span class="ml-2 text-gray-700">Draft</span>
                        </label>
                        <label class="flex items-center">
                            <input type="radio" name="status" value="published" 
                                   {{ old('status', $post->status) === 'published' ? 'checked' : '' }}
                                   class="w-4 h-4 text-indigo-600 focus:ring-indigo-500 border-gray-300">
                            <span class="ml-2 text-gray-700">Published</span>
                        </label>
                    </div>
                </div>

                <div class="flex justify-end space-x-4">
                    <a href="{{ route('admin.posts.index') }}" class="px-6 py-2 text-gray-600 hover:text-gray-800">
                        Cancel
                    </a>
                    <button type="submit" class="px-6 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700">
                        Update Post
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-admin-layout>

