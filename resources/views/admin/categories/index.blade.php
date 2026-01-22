<x-app-layout>
    <div class="max-w-5xl mx-auto">
        <!-- Page Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
            <div>
                <h1 class="text-2xl font-semibold text-gray-900">Expense Categories</h1>
                <p class="mt-1 text-sm text-gray-500">Manage expense categories and their limits</p>
            </div>
            <button type="button" onclick="document.getElementById('create-modal').classList.remove('hidden')" class="inline-flex items-center justify-center gap-2 px-4 py-2.5 bg-slate-800 text-white text-sm font-medium rounded-lg hover:bg-slate-700 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                </svg>
                Add Category
            </button>
        </div>

        <!-- Categories Table -->
        <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3.5 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Category</th>
                            <th class="px-4 py-3.5 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Code</th>
                            <th class="px-4 py-3.5 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Max Amount</th>
                            <th class="px-4 py-3.5 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Requires Receipt</th>
                            <th class="px-4 py-3.5 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Status</th>
                            <th class="px-4 py-3.5 text-right text-xs font-semibold text-gray-600 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($categories as $category)
                            <tr class="hover:bg-gray-50 transition-colors {{ !$category->is_active ? 'bg-gray-50 opacity-60' : '' }}">
                                <td class="px-4 py-4 whitespace-nowrap">
                                    <div>
                                        <div class="text-sm font-semibold text-gray-900">{{ $category->name }}</div>
                                        @if($category->description)
                                            <div class="text-sm text-gray-500">{{ Str::limit($category->description, 50) }}</div>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-4 py-4 whitespace-nowrap">
                                    <span class="text-sm font-mono text-gray-700">{{ $category->code }}</span>
                                </td>
                                <td class="px-4 py-4 whitespace-nowrap">
                                    @if($category->max_amount)
                                        <span class="text-sm font-medium text-gray-900">Rp {{ number_format($category->max_amount, 0, ',', '.') }}</span>
                                    @else
                                        <span class="text-sm text-gray-400">No limit</span>
                                    @endif
                                </td>
                                <td class="px-4 py-4 whitespace-nowrap">
                                    @if($category->requires_receipt)
                                        <span class="text-sm font-medium text-emerald-600">Yes</span>
                                    @else
                                        <span class="text-sm text-gray-400">No</span>
                                    @endif
                                </td>
                                <td class="px-4 py-4 whitespace-nowrap">
                                    @if($category->is_active)
                                        <span class="px-2.5 py-1 inline-flex text-xs font-semibold rounded-full bg-emerald-100 text-emerald-800">Active</span>
                                    @else
                                        <span class="px-2.5 py-1 inline-flex text-xs font-semibold rounded-full bg-red-100 text-red-800">Inactive</span>
                                    @endif
                                </td>
                                <td class="px-4 py-4 whitespace-nowrap text-right">
                                    <div class="flex justify-end gap-3">
                                        <button type="button" onclick="editCategory({{ json_encode($category) }})" class="text-sm font-medium text-slate-600 hover:text-slate-900">Edit</button>
                                        <form action="{{ route('admin.categories.toggle-status', $category) }}" method="POST" class="inline">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="text-sm font-medium {{ $category->is_active ? 'text-red-600 hover:text-red-800' : 'text-emerald-600 hover:text-emerald-800' }}">
                                                {{ $category->is_active ? 'Deactivate' : 'Activate' }}
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-16 text-center">
                                    <div class="flex flex-col items-center">
                                        <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mb-4">
                                            <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                                            </svg>
                                        </div>
                                        <h3 class="text-sm font-semibold text-gray-900 mb-1">No categories found</h3>
                                        <p class="text-sm text-gray-500">Get started by adding a new category.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Create Modal -->
    <div id="create-modal" class="hidden fixed inset-0 bg-gray-900/50 backdrop-blur-sm overflow-y-auto h-full w-full z-50">
        <div class="relative top-20 mx-auto max-w-md">
            <div class="bg-white rounded-xl shadow-xl border border-gray-200 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-5">Add New Category</h3>
                <form action="{{ route('admin.categories.store') }}" method="POST" class="space-y-4">
                    @csrf
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700 mb-1.5">Name <span class="text-red-500">*</span></label>
                        <input type="text" name="name" id="name" required class="w-full rounded-lg border-gray-300 text-sm focus:border-slate-500 focus:ring-slate-500">
                    </div>
                    <div>
                        <label for="code" class="block text-sm font-medium text-gray-700 mb-1.5">Code <span class="text-red-500">*</span></label>
                        <input type="text" name="code" id="code" required maxlength="10" class="w-full rounded-lg border-gray-300 text-sm focus:border-slate-500 focus:ring-slate-500">
                    </div>
                    <div>
                        <label for="description" class="block text-sm font-medium text-gray-700 mb-1.5">Description</label>
                        <textarea name="description" id="description" rows="2" class="w-full rounded-lg border-gray-300 text-sm focus:border-slate-500 focus:ring-slate-500"></textarea>
                    </div>
                    <div>
                        <label for="max_amount" class="block text-sm font-medium text-gray-700 mb-1.5">Max Amount (Rp)</label>
                        <input type="number" name="max_amount" id="max_amount" min="0" step="1000" class="w-full rounded-lg border-gray-300 text-sm focus:border-slate-500 focus:ring-slate-500">
                    </div>
                    <div class="flex items-center">
                        <input type="checkbox" name="requires_receipt" id="requires_receipt" value="1" checked class="rounded border-gray-300 text-slate-600 focus:ring-slate-500">
                        <label for="requires_receipt" class="ml-2 block text-sm text-gray-700">Requires Receipt</label>
                    </div>
                    <div class="flex items-center">
                        <input type="checkbox" name="is_active" id="is_active_create" value="1" checked class="rounded border-gray-300 text-slate-600 focus:ring-slate-500">
                        <label for="is_active_create" class="ml-2 block text-sm text-gray-700">Active</label>
                    </div>
                    <div class="flex justify-end gap-3 pt-3">
                        <button type="button" onclick="document.getElementById('create-modal').classList.add('hidden')" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">Cancel</button>
                        <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-slate-800 rounded-lg hover:bg-slate-700 transition-colors">Create Category</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Modal -->
    <div id="edit-modal" class="hidden fixed inset-0 bg-gray-900/50 backdrop-blur-sm overflow-y-auto h-full w-full z-50">
        <div class="relative top-20 mx-auto max-w-md">
            <div class="bg-white rounded-xl shadow-xl border border-gray-200 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-5">Edit Category</h3>
                <form id="edit-form" method="POST" class="space-y-4">
                    @csrf
                    @method('PUT')
                    <div>
                        <label for="edit_name" class="block text-sm font-medium text-gray-700 mb-1.5">Name <span class="text-red-500">*</span></label>
                        <input type="text" name="name" id="edit_name" required class="w-full rounded-lg border-gray-300 text-sm focus:border-slate-500 focus:ring-slate-500">
                    </div>
                    <div>
                        <label for="edit_code" class="block text-sm font-medium text-gray-700 mb-1.5">Code <span class="text-red-500">*</span></label>
                        <input type="text" name="code" id="edit_code" required maxlength="10" class="w-full rounded-lg border-gray-300 text-sm focus:border-slate-500 focus:ring-slate-500">
                    </div>
                    <div>
                        <label for="edit_description" class="block text-sm font-medium text-gray-700 mb-1.5">Description</label>
                        <textarea name="description" id="edit_description" rows="2" class="w-full rounded-lg border-gray-300 text-sm focus:border-slate-500 focus:ring-slate-500"></textarea>
                    </div>
                    <div>
                        <label for="edit_max_amount" class="block text-sm font-medium text-gray-700 mb-1.5">Max Amount (Rp)</label>
                        <input type="number" name="max_amount" id="edit_max_amount" min="0" step="1000" class="w-full rounded-lg border-gray-300 text-sm focus:border-slate-500 focus:ring-slate-500">
                    </div>
                    <div class="flex items-center">
                        <input type="checkbox" name="requires_receipt" id="edit_requires_receipt" value="1" class="rounded border-gray-300 text-slate-600 focus:ring-slate-500">
                        <label for="edit_requires_receipt" class="ml-2 block text-sm text-gray-700">Requires Receipt</label>
                    </div>
                    <div class="flex items-center">
                        <input type="checkbox" name="is_active" id="edit_is_active" value="1" class="rounded border-gray-300 text-slate-600 focus:ring-slate-500">
                        <label for="edit_is_active" class="ml-2 block text-sm text-gray-700">Active</label>
                    </div>
                    <div class="flex justify-end gap-3 pt-3">
                        <button type="button" onclick="document.getElementById('edit-modal').classList.add('hidden')" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">Cancel</button>
                        <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-slate-800 rounded-lg hover:bg-slate-700 transition-colors">Update Category</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        function editCategory(category) {
            document.getElementById('edit-form').action = '/admin/categories/' + category.id;
            document.getElementById('edit_name').value = category.name;
            document.getElementById('edit_code').value = category.code;
            document.getElementById('edit_description').value = category.description || '';
            document.getElementById('edit_max_amount').value = category.max_amount || '';
            document.getElementById('edit_requires_receipt').checked = category.requires_receipt;
            document.getElementById('edit_is_active').checked = category.is_active;
            document.getElementById('edit-modal').classList.remove('hidden');
        }
    </script>
    @endpush
</x-app-layout>
