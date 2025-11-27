<x-app-layout>
    <x-slot name="title">Edit: {{ $post->title }} - {{ config('app.name') }}</x-slot>

    <x-slot name="header">
        <h2 class="font-serif text-2xl font-bold text-gray-900">
            {{ __('Edit Post') }}
        </h2>
    </x-slot>

    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="bg-white rounded-xl shadow-sm p-6 md:p-8">
            <form action="{{ route('posts.update', $post) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <!-- Title -->
                <div class="mb-6">
                    <label for="title" class="block text-sm font-medium text-gray-700 mb-2">
                        Title <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="title" id="title" value="{{ old('title', $post->title) }}"
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent text-lg"
                           placeholder="Enter your post title..."
                           required>
                    @error('title')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Current Featured Image -->
                @if($post->featured_image)
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Current Featured Image</label>
                        <div class="relative inline-block">
                            <img src="{{ asset('storage/' . $post->featured_image) }}" 
                                 alt="Current featured image" 
                                 class="max-h-48 rounded-lg">
                        </div>
                    </div>
                @endif

                <!-- New Featured Image -->
                <div class="mb-6">
                    <label for="featured_image" class="block text-sm font-medium text-gray-700 mb-2">
                        {{ $post->featured_image ? 'Replace Featured Image' : 'Featured Image' }}
                    </label>
                    <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-lg hover:border-indigo-400 transition"
                         id="drop-zone">
                        <div class="space-y-1 text-center">
                            <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                                <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                            <div class="flex text-sm text-gray-600">
                                <label for="featured_image" class="relative cursor-pointer bg-white rounded-md font-medium text-indigo-600 hover:text-indigo-500 focus-within:outline-none">
                                    <span>Upload a file</span>
                                    <input id="featured_image" name="featured_image" type="file" class="sr-only" accept="image/*">
                                </label>
                                <p class="pl-1">or drag and drop</p>
                            </div>
                            <p class="text-xs text-gray-500">PNG, JPG, GIF up to 2MB</p>
                        </div>
                    </div>
                    <div id="image-preview" class="mt-4 hidden">
                        <img src="" alt="Preview" class="max-h-48 rounded-lg mx-auto">
                    </div>
                    @error('featured_image')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Content -->
                <div class="mb-6">
                    <label for="content" class="block text-sm font-medium text-gray-700 mb-2">
                        Content <span class="text-red-500">*</span>
                    </label>
                    <textarea name="content" id="content" rows="15"
                              class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                              placeholder="Write your post content here... (HTML is supported)"
                              required>{{ old('content', $post->content) }}</textarea>
                    @error('content')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Status -->
                <div class="mb-8">
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Status <span class="text-red-500">*</span>
                    </label>
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

                <!-- Submit Buttons -->
                <div class="flex items-center justify-between">
                    <a href="{{ route('posts.show', $post) }}" 
                       class="text-gray-600 hover:text-gray-800 font-medium">
                        ← Back to Post
                    </a>
                    <div class="flex items-center space-x-4">
                        <a href="{{ route('posts.show', $post) }}" 
                           class="px-6 py-2.5 text-gray-600 hover:text-gray-800 font-medium">
                            Cancel
                        </a>
                        <button type="submit" 
                                class="px-8 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-medium rounded-lg transition">
                            Update Post
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
    <script>
        // Image preview
        const imageInput = document.getElementById('featured_image');
        const imagePreview = document.getElementById('image-preview');
        const previewImg = imagePreview.querySelector('img');

        imageInput.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    previewImg.src = e.target.result;
                    imagePreview.classList.remove('hidden');
                }
                reader.readAsDataURL(file);
            }
        });
    </script>
    @endpush
</x-app-layout>

