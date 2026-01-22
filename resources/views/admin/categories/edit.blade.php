<x-app-layout>
    <div class="space-y-6">
        <!-- Back Button & Header -->
        <div class="flex items-center gap-4">
            <a href="{{ route('admin.categories.index') }}" class="p-2 text-gray-500 hover:text-gray-700 hover:bg-gray-100 rounded-lg transition-colors">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
            </a>
            <div>
                <h1 class="text-2xl font-semibold text-gray-900">Edit Category</h1>
                <p class="text-sm text-gray-500 mt-1">{{ $category->name }}</p>
            </div>
        </div>

        <!-- Form -->
        <div class="bg-white rounded-lg border border-gray-200">
            <form action="{{ route('admin.categories.update', $category) }}" method="POST" class="p-6 space-y-6">
                @csrf
                @method('PUT')
                
                <div class="grid grid-cols-1 gap-6">
                    <!-- Name -->
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700 mb-1.5">Category Name <span class="text-rose-500">*</span></label>
                        <input type="text" name="name" id="name" value="{{ old('name', $category->name) }}" required 
                            class="w-full rounded-lg border-gray-300 text-sm focus:border-slate-500 focus:ring-slate-500" placeholder="e.g., Travel, Office Supplies">
                        @error('name')
                            <p class="mt-1 text-sm text-rose-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Description -->
                    <div>
                        <label for="description" class="block text-sm font-medium text-gray-700 mb-1.5">Description</label>
                        <textarea name="description" id="description" rows="3" class="w-full rounded-lg border-gray-300 text-sm focus:border-slate-500 focus:ring-slate-500" placeholder="Optional description...">{{ old('description', $category->description) }}</textarea>
                        @error('description')
                            <p class="mt-1 text-sm text-rose-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Max Amount -->
                    <div>
                        <label for="max_amount" class="block text-sm font-medium text-gray-700 mb-1.5">Maximum Amount per Expense (Rp)</label>
                        <input type="number" name="max_amount" id="max_amount" value="{{ old('max_amount', $category->max_amount) }}" min="0" step="1000" 
                            class="w-full rounded-lg border-gray-300 text-sm focus:border-slate-500 focus:ring-slate-500" placeholder="Leave empty for no limit">
                        <p class="mt-1 text-xs text-gray-500">Leave empty for no limit on individual expense amounts</p>
                        @error('max_amount')
                            <p class="mt-1 text-sm text-rose-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Requires Receipt -->
                    <div>
                        <label class="flex items-center gap-3">
                            <input type="checkbox" name="requires_receipt" value="1" {{ old('requires_receipt', $category->requires_receipt) ? 'checked' : '' }} class="rounded border-gray-300 text-slate-600 focus:ring-slate-500">
                            <span class="text-sm font-medium text-gray-700">Requires Receipt</span>
                        </label>
                        <p class="mt-1 text-xs text-gray-500 ml-6">Expenses in this category must include a receipt upload</p>
                    </div>

                    <!-- Active Status -->
                    <div>
                        <label class="flex items-center gap-3">
                            <input type="checkbox" name="is_active" value="1" {{ old('is_active', $category->is_active) ? 'checked' : '' }} class="rounded border-gray-300 text-slate-600 focus:ring-slate-500">
                            <span class="text-sm font-medium text-gray-700">Active</span>
                        </label>
                    </div>
                </div>

                <!-- Actions -->
                <div class="flex justify-end gap-3 pt-4 border-t border-gray-200">
                    <a href="{{ route('admin.categories.index') }}" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">Cancel</a>
                    <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-slate-800 rounded-lg hover:bg-slate-700 transition-colors">Update Category</button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
