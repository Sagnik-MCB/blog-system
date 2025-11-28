<x-admin-layout>
    <x-slot name="title">Manage Comments</x-slot>
    <x-slot name="header">Comment Management</x-slot>

    <!-- Filters -->
    <div class="bg-white rounded-xl shadow-sm p-4 mb-6">
        <form action="{{ route('admin.comments.index') }}" method="GET" class="flex flex-wrap gap-4 items-end">
            <div class="flex-1 min-w-[200px]">
                <label class="block text-sm font-medium text-gray-700 mb-1">Search</label>
                <input type="text" name="search" value="{{ request('search') }}" 
                       placeholder="Search comments..."
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
            </div>
            <div class="w-40">
                <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                <select name="approved" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                    <option value="">All</option>
                    <option value="approved" {{ request('approved') === 'approved' ? 'selected' : '' }}>Approved</option>
                    <option value="pending" {{ request('approved') === 'pending' ? 'selected' : '' }}>Pending</option>
                </select>
            </div>
            <button type="submit" class="px-6 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700">
                Filter
            </button>
            <a href="{{ route('admin.comments.index') }}" class="px-6 py-2 text-gray-600 hover:text-gray-800">
                Reset
            </a>
        </form>
    </div>

    <!-- Bulk Actions Form (separate from the table to avoid nested forms) -->
    <form id="bulk-form" action="{{ route('admin.comments.bulk-approve') }}" method="POST">
        @csrf
    </form>
        
    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
            <h3 class="text-lg font-semibold">All Comments ({{ $comments->total() }})</h3>
            <button type="button" onclick="submitBulkForm()" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 text-sm">
                Approve Selected
            </button>
        </div>
        
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left">
                        <input type="checkbox" id="select-all" 
                               class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Comment</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Post</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($comments as $comment)
                    <tr class="hover:bg-gray-50 {{ !$comment->is_approved ? 'bg-yellow-50' : '' }}">
                        <td class="px-6 py-4">
                            <input type="checkbox" data-comment-id="{{ $comment->id }}"
                                   class="comment-checkbox rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex items-start">
                                <img class="w-8 h-8 rounded-full object-cover mr-3" 
                                     src="{{ $comment->user->avatar_url }}" alt="{{ $comment->user->name }}">
                                <div>
                                    <span class="font-medium text-gray-900">{{ $comment->user->name }}</span>
                                    <p class="text-sm text-gray-600 mt-1">{{ Str::limit($comment->content, 100) }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <a href="{{ route('admin.posts.show', $comment->post->slug) }}" 
                               class="text-indigo-600 hover:text-indigo-800 text-sm">
                                {{ Str::limit($comment->post->title, 30) }}
                            </a>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if($comment->is_approved)
                                <span class="px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                    Approved
                                </span>
                            @else
                                <span class="px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                    Pending
                                </span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ $comment->created_at->format('M d, Y') }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            @if(!$comment->is_approved)
                                <form action="{{ route('admin.comments.approve', $comment) }}" method="POST" class="inline">
                                    @csrf
                                    <button type="submit" class="text-green-600 hover:text-green-900 mr-3">Approve</button>
                                </form>
                            @else
                                <form action="{{ route('admin.comments.reject', $comment) }}" method="POST" class="inline">
                                    @csrf
                                    <button type="submit" class="text-yellow-600 hover:text-yellow-900 mr-3">Reject</button>
                                </form>
                            @endif
                            <form action="{{ route('admin.comments.destroy', $comment) }}" method="POST" class="inline"
                                  onsubmit="return confirm('Delete this comment?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-600 hover:text-red-900">Delete</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-6 py-8 text-center text-gray-500">
                            No comments found
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        @if($comments->hasPages())
            <div class="px-6 py-4 border-t border-gray-200">
                {{ $comments->links() }}
            </div>
        @endif
    </div>

    @push('scripts')
    <script>
        document.getElementById('select-all').addEventListener('change', function() {
            document.querySelectorAll('.comment-checkbox').forEach(checkbox => {
                checkbox.checked = this.checked;
            });
        });

        function submitBulkForm() {
            const checkedBoxes = document.querySelectorAll('.comment-checkbox:checked');
            if (checkedBoxes.length === 0) {
                alert('Please select at least one comment to approve.');
                return;
            }
            
            const form = document.getElementById('bulk-form');
            // Remove existing hidden inputs for comment_ids
            form.querySelectorAll('input[name="comment_ids[]"]').forEach(input => input.remove());
            
            // Add hidden inputs for each selected comment
            checkedBoxes.forEach(checkbox => {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'comment_ids[]';
                input.value = checkbox.dataset.commentId;
                form.appendChild(input);
            });
            
            form.submit();
        }
    </script>
    @endpush
</x-admin-layout>

