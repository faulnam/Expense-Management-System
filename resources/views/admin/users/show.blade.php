<x-app-layout>
    <div class="space-y-6">
        <!-- Back Button & Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div class="flex items-center gap-4">
                <a href="{{ route('admin.users.index') }}" class="p-2 text-gray-500 hover:text-gray-700 hover:bg-gray-100 rounded-lg transition-colors">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                </a>
                <div>
                    <h1 class="text-2xl font-semibold text-gray-900">User Details</h1>
                    <p class="text-sm text-gray-500 mt-1">{{ $user->employee_id ?? 'ID not set' }}</p>
                </div>
            </div>
            <div class="flex items-center gap-3">
                <span class="inline-flex px-3 py-1.5 text-sm font-medium rounded-lg {{ $user->is_active ? 'bg-emerald-100 text-emerald-800' : 'bg-gray-100 text-gray-700' }}">
                    {{ $user->is_active ? 'Active' : 'Inactive' }}
                </span>
                <a href="{{ route('admin.users.edit', $user) }}" class="inline-flex items-center gap-2 px-4 py-2 bg-slate-800 text-white text-sm font-medium rounded-lg hover:bg-slate-700 transition-colors">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                    </svg>
                    Edit User
                </a>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Main Info -->
            <div class="lg:col-span-2 space-y-6">
                <!-- User Profile -->
                <div class="bg-white rounded-lg border border-gray-200 p-6">
                    <div class="flex items-start gap-6">
                        <div class="w-20 h-20 rounded-full bg-slate-200 flex items-center justify-center text-slate-700 font-bold text-2xl">
                            {{ strtoupper(substr($user->name, 0, 1)) }}
                        </div>
                        <div class="flex-1">
                            <h2 class="text-xl font-semibold text-gray-900">{{ $user->name }}</h2>
                            <p class="text-sm text-gray-500 mt-1">{{ $user->email }}</p>
                            <div class="flex items-center gap-3 mt-3">
                                <span class="inline-flex px-2.5 py-1 text-xs font-semibold rounded-lg bg-indigo-100 text-indigo-800">
                                    {{ $user->role->name ?? 'No Role' }}
                                </span>
                                @if($user->department)
                                <span class="inline-flex px-2.5 py-1 text-xs font-medium rounded-lg bg-gray-100 text-gray-700">
                                    {{ $user->department }}
                                </span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <!-- User Details -->
                <div class="bg-white rounded-lg border border-gray-200 p-5">
                    <h3 class="text-base font-semibold text-gray-900 mb-4">User Information</h3>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <p class="text-xs font-medium text-gray-500 uppercase">Employee ID</p>
                            <p class="text-sm text-gray-900 mt-1">{{ $user->employee_id ?? '-' }}</p>
                        </div>
                        <div>
                            <p class="text-xs font-medium text-gray-500 uppercase">Department</p>
                            <p class="text-sm text-gray-900 mt-1">{{ $user->department ?? '-' }}</p>
                        </div>
                        <div>
                            <p class="text-xs font-medium text-gray-500 uppercase">Phone</p>
                            <p class="text-sm text-gray-900 mt-1">{{ $user->phone ?? '-' }}</p>
                        </div>
                        <div>
                            <p class="text-xs font-medium text-gray-500 uppercase">Role</p>
                            <p class="text-sm text-gray-900 mt-1">{{ $user->role->name ?? '-' }}</p>
                        </div>
                        <div>
                            <p class="text-xs font-medium text-gray-500 uppercase">Manager</p>
                            <p class="text-sm text-gray-900 mt-1">{{ $user->manager->name ?? 'No Manager' }}</p>
                        </div>
                        <div>
                            <p class="text-xs font-medium text-gray-500 uppercase">Joined</p>
                            <p class="text-sm text-gray-900 mt-1">{{ $user->created_at->format('d M Y') }}</p>
                        </div>
                    </div>
                </div>

                <!-- Recent Expenses -->
                @if($user->expenses && $user->expenses->count() > 0)
                <div class="bg-white rounded-lg border border-gray-200 p-5">
                    <h3 class="text-base font-semibold text-gray-900 mb-4">Recent Expenses</h3>
                    <div class="space-y-3">
                        @foreach($user->expenses as $expense)
                        <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                            <div>
                                <p class="text-sm font-medium text-gray-900">{{ $expense->expense_number }}</p>
                                <p class="text-xs text-gray-500">{{ $expense->category->name ?? 'N/A' }} • {{ $expense->expense_date?->format('d M Y') }}</p>
                            </div>
                            <div class="text-right">
                                <p class="text-sm font-semibold text-gray-900">Rp {{ number_format($expense->amount, 0, ',', '.') }}</p>
                                <span class="inline-flex px-2 py-0.5 text-xs font-medium rounded
                                    @switch($expense->status)
                                        @case('draft') bg-gray-100 text-gray-700 @break
                                        @case('submitted') bg-amber-100 text-amber-800 @break
                                        @case('manager_approved') bg-indigo-100 text-indigo-800 @break
                                        @case('finance_approved') bg-emerald-100 text-emerald-800 @break
                                        @case('paid') bg-emerald-600 text-white @break
                                        @case('rejected') bg-rose-100 text-rose-800 @break
                                        @default bg-gray-100 text-gray-700
                                    @endswitch">
                                    {{ ucfirst(str_replace('_', ' ', $expense->status)) }}
                                </span>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif

                <!-- Subordinates -->
                @if($user->subordinates && $user->subordinates->count() > 0)
                <div class="bg-white rounded-lg border border-gray-200 p-5">
                    <h3 class="text-base font-semibold text-gray-900 mb-4">Team Members ({{ $user->subordinates->count() }})</h3>
                    <div class="space-y-3">
                        @foreach($user->subordinates as $subordinate)
                        <div class="flex items-center gap-3 p-3 bg-gray-50 rounded-lg">
                            <div class="w-10 h-10 rounded-full bg-slate-200 flex items-center justify-center text-slate-700 font-medium">
                                {{ strtoupper(substr($subordinate->name, 0, 1)) }}
                            </div>
                            <div class="flex-1">
                                <p class="text-sm font-medium text-gray-900">{{ $subordinate->name }}</p>
                                <p class="text-xs text-gray-500">{{ $subordinate->email }}</p>
                            </div>
                            <span class="inline-flex px-2 py-0.5 text-xs font-medium rounded {{ $subordinate->is_active ? 'bg-emerald-100 text-emerald-800' : 'bg-gray-100 text-gray-600' }}">
                                {{ $subordinate->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif
            </div>

            <!-- Sidebar -->
            <div class="space-y-6">
                <!-- Actions -->
                <div class="bg-white rounded-lg border border-gray-200 p-5">
                    <h3 class="text-base font-semibold text-gray-900 mb-4">Actions</h3>
                    <div class="space-y-3">
                        <a href="{{ route('admin.users.edit', $user) }}" class="w-full px-4 py-2.5 bg-slate-800 text-white text-sm font-medium rounded-lg hover:bg-slate-700 transition-colors flex items-center justify-center gap-2">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                            </svg>
                            Edit User
                        </a>
                        <form action="{{ route('admin.users.toggle-status', $user) }}" method="POST">
                            @csrf
                            <button type="submit" class="w-full px-4 py-2.5 {{ $user->is_active ? 'bg-amber-100 text-amber-700 hover:bg-amber-200' : 'bg-emerald-100 text-emerald-700 hover:bg-emerald-200' }} text-sm font-medium rounded-lg transition-colors flex items-center justify-center gap-2">
                                @if($user->is_active)
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/>
                                </svg>
                                Deactivate User
                                @else
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                Activate User
                                @endif
                            </button>
                        </form>
                        @if($user->id !== auth()->id())
                        <form action="{{ route('admin.users.destroy', $user) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this user? This action cannot be undone.')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="w-full px-4 py-2.5 bg-white border border-rose-300 text-rose-600 text-sm font-medium rounded-lg hover:bg-rose-50 transition-colors flex items-center justify-center gap-2">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                </svg>
                                Delete User
                            </button>
                        </form>
                        @endif
                    </div>
                </div>

                <!-- Stats -->
                <div class="bg-slate-50 rounded-lg border border-slate-200 p-5">
                    <h3 class="text-base font-semibold text-slate-900 mb-3">Statistics</h3>
                    <div class="space-y-3">
                        <div class="flex justify-between items-center">
                            <span class="text-sm text-gray-500">Total Expenses</span>
                            <span class="text-sm font-semibold text-gray-900">{{ $user->expenses_count ?? $user->expenses()->count() }}</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-sm text-gray-500">Team Members</span>
                            <span class="text-sm font-semibold text-gray-900">{{ $user->subordinates_count ?? $user->subordinates()->count() }}</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-sm text-gray-500">Account Age</span>
                            <span class="text-sm font-semibold text-gray-900">{{ $user->created_at->diffForHumans(null, true) }}</span>
                        </div>
                    </div>
                </div>

                <!-- Account Info -->
                <div class="bg-white rounded-lg border border-gray-200 p-5">
                    <h3 class="text-base font-semibold text-gray-900 mb-3">Account Info</h3>
                    <div class="space-y-2 text-sm">
                        <div class="flex justify-between">
                            <span class="text-gray-500">Created</span>
                            <span class="text-gray-900">{{ $user->created_at->format('d M Y') }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-500">Last Updated</span>
                            <span class="text-gray-900">{{ $user->updated_at->format('d M Y') }}</span>
                        </div>
                        @if($user->email_verified_at)
                        <div class="flex justify-between">
                            <span class="text-gray-500">Email Verified</span>
                            <span class="text-emerald-600">Yes</span>
                        </div>
                        @else
                        <div class="flex justify-between">
                            <span class="text-gray-500">Email Verified</span>
                            <span class="text-amber-600">No</span>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
