<x-app-layout>
    <div class="space-y-6">
        <!-- Back Button & Header -->
        <div class="flex items-center gap-4">
            <a href="{{ route('admin.budgets.index') }}" class="p-2 text-gray-500 hover:text-gray-700 hover:bg-gray-100 rounded-lg transition-colors">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
            </a>
            <div>
                <h1 class="text-2xl font-semibold text-gray-900">Create Budget</h1>
                <p class="text-sm text-gray-500 mt-1">Set up a new budget allocation</p>
            </div>
        </div>

        <!-- Form -->
        <div class="bg-white rounded-lg border border-gray-200">
            <form action="{{ route('admin.budgets.store') }}" method="POST" class="p-6 space-y-6">
                @csrf
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Category -->
                    <div>
                        <label for="category_id" class="block text-sm font-medium text-gray-700 mb-1.5">Category <span class="text-rose-500">*</span></label>
                        <select name="category_id" id="category_id" required class="w-full rounded-lg border-gray-300 text-sm focus:border-slate-500 focus:ring-slate-500">
                            <option value="">Select Category</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>
                                    {{ $category->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('category_id')
                            <p class="mt-1 text-sm text-rose-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Department -->
                    <div>
                        <label for="department" class="block text-sm font-medium text-gray-700 mb-1.5">Department</label>
                        <select name="department" id="department" class="w-full rounded-lg border-gray-300 text-sm focus:border-slate-500 focus:ring-slate-500">
                            <option value="">All Departments</option>
                            @foreach($departments as $dept)
                                <option value="{{ $dept }}" {{ old('department') == $dept ? 'selected' : '' }}>
                                    {{ $dept }}
                                </option>
                            @endforeach
                        </select>
                        @error('department')
                            <p class="mt-1 text-sm text-rose-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Year -->
                    <div>
                        <label for="year" class="block text-sm font-medium text-gray-700 mb-1.5">Year <span class="text-rose-500">*</span></label>
                        <select name="year" id="year" required class="w-full rounded-lg border-gray-300 text-sm focus:border-slate-500 focus:ring-slate-500">
                            @foreach($years as $year)
                                <option value="{{ $year }}" {{ old('year', now()->year) == $year ? 'selected' : '' }}>
                                    {{ $year }}
                                </option>
                            @endforeach
                        </select>
                        @error('year')
                            <p class="mt-1 text-sm text-rose-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Month -->
                    <div>
                        <label for="month" class="block text-sm font-medium text-gray-700 mb-1.5">Month <span class="text-rose-500">*</span></label>
                        <select name="month" id="month" required class="w-full rounded-lg border-gray-300 text-sm focus:border-slate-500 focus:ring-slate-500">
                            @foreach($months as $num => $name)
                                <option value="{{ $num }}" {{ old('month', now()->month) == $num ? 'selected' : '' }}>
                                    {{ $name }}
                                </option>
                            @endforeach
                        </select>
                        @error('month')
                            <p class="mt-1 text-sm text-rose-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Limit Amount -->
                    <div>
                        <label for="limit_amount" class="block text-sm font-medium text-gray-700 mb-1.5">Budget Limit (Rp) <span class="text-rose-500">*</span></label>
                        <input type="number" name="limit_amount" id="limit_amount" value="{{ old('limit_amount') }}" required min="0" step="1000" 
                            class="w-full rounded-lg border-gray-300 text-sm focus:border-slate-500 focus:ring-slate-500" placeholder="0">
                        @error('limit_amount')
                            <p class="mt-1 text-sm text-rose-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Warning Threshold -->
                    <div>
                        <label for="warning_threshold" class="block text-sm font-medium text-gray-700 mb-1.5">Warning Threshold (%) <span class="text-rose-500">*</span></label>
                        <input type="number" name="warning_threshold" id="warning_threshold" value="{{ old('warning_threshold', 80) }}" required min="0" max="100" 
                            class="w-full rounded-lg border-gray-300 text-sm focus:border-slate-500 focus:ring-slate-500" placeholder="80">
                        <p class="mt-1 text-xs text-gray-500">Alert when budget usage reaches this percentage</p>
                        @error('warning_threshold')
                            <p class="mt-1 text-sm text-rose-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Notes -->
                    <div class="md:col-span-2">
                        <label for="notes" class="block text-sm font-medium text-gray-700 mb-1.5">Notes</label>
                        <textarea name="notes" id="notes" rows="3" class="w-full rounded-lg border-gray-300 text-sm focus:border-slate-500 focus:ring-slate-500" placeholder="Optional notes about this budget...">{{ old('notes') }}</textarea>
                        @error('notes')
                            <p class="mt-1 text-sm text-rose-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Active Status -->
                    <div class="md:col-span-2">
                        <label class="flex items-center gap-3">
                            <input type="checkbox" name="is_active" value="1" checked class="rounded border-gray-300 text-slate-600 focus:ring-slate-500">
                            <span class="text-sm font-medium text-gray-700">Active</span>
                        </label>
                    </div>
                </div>

                <!-- Actions -->
                <div class="flex justify-end gap-3 pt-4 border-t border-gray-200">
                    <a href="{{ route('admin.budgets.index') }}" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">Cancel</a>
                    <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-slate-800 rounded-lg hover:bg-slate-700 transition-colors">Create Budget</button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
