<div class="flex space-x-4" id="comment-{{ $comment->id }}">
    <img class="w-10 h-10 rounded-full object-cover flex-shrink-0" 
         src="{{ $comment->user->avatar_url }}" 
         alt="{{ $comment->user->name }}">
    
    <div class="flex-1">
        <div class="bg-gray-50 rounded-lg p-4">
            <div class="flex items-center justify-between mb-2">
                <div>
                    <span class="font-medium text-gray-900">{{ $comment->user->name }}</span>
                    <span class="text-gray-500 text-sm ml-2">
                        {{ $comment->created_at->diffForHumans() }}
                    </span>
                </div>
                
                @canManage($comment)
                    <div class="flex items-center space-x-2">
                        <button onclick="toggleEditForm({{ $comment->id }})" 
                                class="text-gray-400 hover:text-gray-600 text-sm">
                            Edit
                        </button>
                        <form action="{{ route('comments.destroy', $comment) }}" method="POST" class="inline"
                              onsubmit="return confirm('Delete this comment?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-red-400 hover:text-red-600 text-sm">
                                Delete
                            </button>
                        </form>
                    </div>
                @endcanManage
            </div>
            
            <div id="comment-content-{{ $comment->id }}">
                <p class="text-gray-700">{{ $comment->content }}</p>
            </div>
            
            <!-- Edit Form (hidden by default) -->
            <form action="{{ route('comments.update', $comment) }}" method="POST" 
                  id="edit-form-{{ $comment->id }}" class="hidden mt-2">
                @csrf
                @method('PUT')
                <textarea name="content" rows="3" 
                          class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 focus:border-transparent">{{ $comment->content }}</textarea>
                <div class="flex justify-end space-x-2 mt-2">
                    <button type="button" onclick="toggleEditForm({{ $comment->id }})" 
                            class="px-3 py-1.5 text-gray-600 hover:text-gray-800 text-sm">
                        Cancel
                    </button>
                    <button type="submit" 
                            class="px-3 py-1.5 bg-indigo-600 text-white rounded text-sm hover:bg-indigo-700">
                        Update
                    </button>
                </div>
            </form>
        </div>
        
        @auth
            <!-- Reply Button -->
            <button onclick="toggleReplyForm({{ $comment->id }})" 
                    class="text-sm text-gray-500 hover:text-indigo-600 mt-2">
                Reply
            </button>
            
            <!-- Reply Form (hidden by default) -->
            <form action="{{ route('comments.store', $comment->post) }}" method="POST" 
                  id="reply-form-{{ $comment->id }}" class="hidden mt-3">
                @csrf
                <input type="hidden" name="parent_id" value="{{ $comment->id }}">
                <textarea name="content" rows="2" 
                          placeholder="Write a reply..."
                          class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 focus:border-transparent"></textarea>
                <div class="flex justify-end space-x-2 mt-2">
                    <button type="button" onclick="toggleReplyForm({{ $comment->id }})" 
                            class="px-3 py-1.5 text-gray-600 hover:text-gray-800 text-sm">
                        Cancel
                    </button>
                    <button type="submit" 
                            class="px-3 py-1.5 bg-indigo-600 text-white rounded text-sm hover:bg-indigo-700">
                        Reply
                    </button>
                </div>
            </form>
        @endauth
        
        <!-- Nested Replies -->
        @if($comment->approvedReplies->count() > 0)
            <div class="mt-4 space-y-4 pl-4 border-l-2 border-gray-100">
                @foreach($comment->approvedReplies as $reply)
                    @include('posts.partials.comment', ['comment' => $reply])
                @endforeach
            </div>
        @endif
    </div>
</div>

@once
    @push('scripts')
    <script>
        function toggleReplyForm(commentId) {
            const form = document.getElementById('reply-form-' + commentId);
            form.classList.toggle('hidden');
        }
        
        function toggleEditForm(commentId) {
            const form = document.getElementById('edit-form-' + commentId);
            const content = document.getElementById('comment-content-' + commentId);
            form.classList.toggle('hidden');
            content.classList.toggle('hidden');
        }
    </script>
    @endpush
@endonce

