<x-admin-layout>
    <x-slot name="title">View User</x-slot>
    <x-slot name="header">User Details</x-slot>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- User Info -->
        <div class="lg:col-span-1">
            <div class="bg-white rounded-xl shadow-sm p-6">
                <div class="text-center">
                    <img class="w-24 h-24 rounded-full object-cover mx-auto mb-4" 
                         src="{{ $user->avatar_url }}" alt="{{ $user->name }}">
                    <h2 class="text-xl font-bold text-gray-900">{{ $user->name }}</h2>
                    <p class="text-gray-500">{{ $user->email }}</p>
                    
                    <div class="mt-4 space-x-2">
                        @foreach($user->roles as $role)
                            <span class="px-3 py-1 rounded-full text-sm font-medium 
                                {{ $role->name === 'admin' ? 'bg-purple-100 text-purple-800' : 'bg-gray-100 text-gray-800' }}">
                                {{ ucfirst($role->name) }}
                            </span>
                        @endforeach
                        <span class="px-3 py-1 rounded-full text-sm font-medium 
                            {{ $user->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                            {{ $user->is_active ? 'Active' : 'Inactive' }}
                        </span>
                    </div>
                </div>
                
                <div class="mt-6 border-t border-gray-200 pt-6">
                    <dl class="space-y-4">
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Joined</dt>
                            <dd class="text-sm text-gray-900">{{ $user->created_at->format('F d, Y') }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Total Posts</dt>
                            <dd class="text-sm text-gray-900">{{ $user->posts->count() }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Total Comments</dt>
                            <dd class="text-sm text-gray-900">{{ $user->comments->count() }}</dd>
                        </div>
                    </dl>
                </div>
                
                <div class="mt-6 flex space-x-3">
                    <a href="{{ route('admin.users.edit', $user) }}" 
                       class="flex-1 px-4 py-2 bg-indigo-600 text-white text-center rounded-lg hover:bg-indigo-700">
                        Edit
                    </a>
                    @if($user->id !== auth()->id())
                        <form action="{{ route('admin.users.toggle-status', $user) }}" method="POST" class="flex-1">
                            @csrf
                            <button type="submit" 
                                    class="w-full px-4 py-2 {{ $user->is_active ? 'bg-yellow-100 text-yellow-800 hover:bg-yellow-200' : 'bg-green-100 text-green-800 hover:bg-green-200' }} rounded-lg">
                                {{ $user->is_active ? 'Deactivate' : 'Activate' }}
                            </button>
                        </form>
                    @endif
                </div>
            </div>
        </div>

        <!-- Recent Posts & Activity -->
        <div class="lg:col-span-2 space-y-8">
            <!-- Recent Posts -->
            <div class="bg-white rounded-xl shadow-sm p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Recent Posts</h3>
                @if($user->posts->count() > 0)
                    <div class="space-y-4">
                        @foreach($user->posts as $post)
                            <div class="flex items-center justify-between py-2 border-b border-gray-100 last:border-0">
                                <div>
                                    <a href="{{ route('admin.posts.show', $post->slug) }}" 
                                       class="font-medium text-gray-900 hover:text-indigo-600">
                                        {{ $post->title }}
                                    </a>
                                    <p class="text-sm text-gray-500">{{ $post->created_at->format('M d, Y') }}</p>
                                </div>
                                <span class="px-2.5 py-0.5 rounded-full text-xs font-medium 
                                    {{ $post->status === 'published' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">
                                    {{ ucfirst($post->status) }}
                                </span>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-gray-500 text-center py-4">No posts yet</p>
                @endif
            </div>

            <!-- Recent Activity -->
            <div class="bg-white rounded-xl shadow-sm p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Recent Activity</h3>
                @if($activities->count() > 0)
                    <div class="space-y-4">
                        @foreach($activities as $activity)
                            <div class="flex items-start space-x-3 py-2 border-b border-gray-100 last:border-0">
                                <span class="px-2 py-1 text-xs rounded-full 
                                    {{ str_contains($activity->action, 'delete') ? 'bg-red-100 text-red-800' : '' }}
                                    {{ str_contains($activity->action, 'create') ? 'bg-green-100 text-green-800' : '' }}
                                    {{ str_contains($activity->action, 'update') ? 'bg-blue-100 text-blue-800' : '' }}
                                    {{ str_contains($activity->action, 'login') ? 'bg-gray-100 text-gray-800' : '' }}">
                                    {{ str_replace('_', ' ', $activity->action) }}
                                </span>
                                <div class="flex-1">
                                    <p class="text-sm text-gray-700">{{ $activity->description ?? '-' }}</p>
                                    <p class="text-xs text-gray-500">{{ $activity->created_at->diffForHumans() }}</p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-gray-500 text-center py-4">No activity recorded</p>
                @endif
            </div>
        </div>
    </div>
</x-admin-layout>

