<x-app-layout>
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Page Header -->
        <div class="flex items-center justify-between mb-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Edit Expense</h1>
                <p class="text-gray-500 mt-1">{{ $expense->expense_number }}</p>
            </div>
            <a href="{{ route('expenses.show', $expense) }}" class="inline-flex items-center gap-2 text-sm font-medium text-gray-600 hover:text-gray-900 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Back to Details
            </a>
        </div>

        <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
            <form action="{{ route('expenses.update', $expense) }}" method="POST" enctype="multipart/form-data" class="p-6 space-y-6">
                @csrf
                @method('PUT')

                <!-- Category -->
                <div>
                    <label for="category_id" class="block text-sm font-medium text-gray-700 mb-1.5">Category <span class="text-red-500">*</span></label>
                    <select name="category_id" id="category_id" required class="w-full rounded-lg border-gray-300 text-sm focus:border-slate-500 focus:ring-slate-500 @error('category_id') border-red-300 @enderror">
                        <option value="">Select Category</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}" {{ old('category_id', $expense->category_id) == $category->id ? 'selected' : '' }} data-limit="{{ $category->max_amount }}">
                                {{ $category->name }}
                                @if($category->max_amount)
                                    (Max: Rp {{ number_format($category->max_amount, 0, ',', '.') }})
                                @endif
                            </option>
                        @endforeach
                    </select>
                    @error('category_id')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Expense Date -->
                <div>
                    <label for="expense_date" class="block text-sm font-medium text-gray-700 mb-1.5">Expense Date <span class="text-red-500">*</span></label>
                    <input type="date" name="expense_date" id="expense_date" required value="{{ old('expense_date', $expense->expense_date->format('Y-m-d')) }}" max="{{ date('Y-m-d') }}" class="w-full rounded-lg border-gray-300 text-sm focus:border-slate-500 focus:ring-slate-500 @error('expense_date') border-red-300 @enderror">
                    @error('expense_date')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Amount -->
                <div>
                    <label for="amount" class="block text-sm font-medium text-gray-700 mb-1.5">Amount (Rp) <span class="text-red-500">*</span></label>
                    <div class="relative rounded-lg">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <span class="text-gray-500 text-sm">Rp</span>
                        </div>
                        <input type="number" name="amount" id="amount" required min="1" step="1" value="{{ old('amount', $expense->amount) }}" placeholder="0" class="w-full pl-12 rounded-lg border-gray-300 text-sm focus:border-slate-500 focus:ring-slate-500 @error('amount') border-red-300 @enderror">
                    </div>
                    <p id="category-limit-warning" class="mt-1.5 text-sm text-amber-600 hidden">
                        <svg class="inline w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                        </svg>
                        Amount exceeds category limit!
                    </p>
                    @error('amount')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Description -->
                <div>
                    <label for="description" class="block text-sm font-medium text-gray-700 mb-1.5">Description <span class="text-red-500">*</span></label>
                    <textarea name="description" id="description" rows="3" required placeholder="Describe the expense purpose..." class="w-full rounded-lg border-gray-300 text-sm focus:border-slate-500 focus:ring-slate-500 @error('description') border-red-300 @enderror">{{ old('description', $expense->description) }}</textarea>
                    @error('description')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Current Receipt -->
                @if($expense->receipt_path)
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Current Receipt</label>
                    <div class="flex items-center">
                        <a href="{{ $expense->receipt_url }}" target="_blank" class="inline-flex items-center gap-2 text-sm text-slate-600 hover:text-slate-800 font-medium">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                            </svg>
                            {{ $expense->receipt_original_name ?? 'View Current Receipt' }}
                        </a>
                    </div>
                </div>
                @endif

                <!-- New Receipt Upload -->
                <div>
                    <label for="receipt" class="block text-sm font-medium text-gray-700 mb-1.5">{{ $expense->receipt_path ? 'Replace Receipt (optional)' : 'Receipt' }} <span class="text-red-500">{{ $expense->receipt_path ? '' : '*' }}</span></label>
                    <div class="flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-xl hover:border-slate-400 transition-colors bg-gray-50/50">
                        <div class="space-y-1 text-center">
                            <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                                <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                            <div class="flex text-sm text-gray-600">
                                <label for="receipt" class="relative cursor-pointer bg-white rounded-md font-medium text-slate-700 hover:text-slate-900 focus-within:outline-none px-2 py-1 border border-gray-300 hover:border-slate-400 transition-colors">
                                    <span>Upload a file</span>
                                    <input id="receipt" name="receipt" type="file" class="sr-only" accept="image/*,application/pdf" {{ $expense->receipt_path ? '' : 'required' }}>
                                </label>
                                <p class="pl-2 self-center">or drag and drop</p>
                            </div>
                            <p class="text-xs text-gray-500">PNG, JPG, PDF up to 10MB</p>
                            <p id="file-name" class="text-sm text-emerald-600 font-medium"></p>
                        </div>
                    </div>
                    @error('receipt')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Action Buttons -->
                <div class="flex justify-end gap-3 pt-4 border-t border-gray-200">
                    <a href="{{ route('expenses.show', $expense) }}" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                        Cancel
                    </a>
                    <button type="submit" name="action" value="draft" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                        Save as Draft
                    </button>
                    <button type="submit" name="action" value="submit" class="px-4 py-2 text-sm font-medium text-white bg-slate-800 rounded-lg hover:bg-slate-700 transition-colors">
                        Update & Submit
                    </button>
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
    <script>
        // File name display
        document.getElementById('receipt').addEventListener('change', function(e) {
            const fileName = e.target.files[0]?.name;
            document.getElementById('file-name').textContent = fileName || '';
        });

        // Category limit warning
        const categorySelect = document.getElementById('category_id');
        const amountInput = document.getElementById('amount');
        const warningEl = document.getElementById('category-limit-warning');

        function checkCategoryLimit() {
            const selected = categorySelect.options[categorySelect.selectedIndex];
            const limit = parseFloat(selected.dataset.limit) || 0;
            const amount = parseFloat(amountInput.value) || 0;

            if (limit > 0 && amount > limit) {
                warningEl.classList.remove('hidden');
            } else {
                warningEl.classList.add('hidden');
            }
        }

        categorySelect.addEventListener('change', checkCategoryLimit);
        amountInput.addEventListener('input', checkCategoryLimit);
        
        // Check on page load
        checkCategoryLimit();
    </script>
    @endpush
</x-app-layout>
