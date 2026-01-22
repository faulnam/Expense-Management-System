<x-app-layout>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Page Header -->
        <div class="flex items-center justify-between mb-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Budget Utilization Report</h1>
                <p class="text-gray-500 mt-1">Track budget spending across departments and categories</p>
            </div>
            <a href="{{ route('reports.index') }}" class="inline-flex items-center gap-2 text-sm font-medium text-gray-600 hover:text-gray-900 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Back to Reports
            </a>
        </div>

        <!-- Period Selector -->
        <div class="bg-white rounded-xl border border-gray-200 p-4 mb-6">
            <form method="GET" action="{{ route('reports.budget') }}" class="flex flex-wrap items-end gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Period</label>
                    <select name="period" class="rounded-lg border-gray-300 text-sm focus:border-slate-500 focus:ring-slate-500">
                        <option value="monthly" {{ request('period', 'monthly') == 'monthly' ? 'selected' : '' }}>Monthly</option>
                        <option value="quarterly" {{ request('period') == 'quarterly' ? 'selected' : '' }}>Quarterly</option>
                        <option value="yearly" {{ request('period') == 'yearly' ? 'selected' : '' }}>Yearly</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Department</label>
                    <select name="department" class="rounded-lg border-gray-300 text-sm focus:border-slate-500 focus:ring-slate-500">
                        <option value="">All Departments</option>
                        @foreach($departments ?? [] as $dept)
                            <option value="{{ $dept }}" {{ request('department') == $dept ? 'selected' : '' }}>{{ $dept }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <button type="submit" class="px-4 py-2 bg-slate-800 text-white rounded-lg hover:bg-slate-700 text-sm font-medium transition-colors">Apply</button>
                </div>
            </form>
        </div>

        <!-- Overall Summary -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
            <div class="bg-white rounded-xl border border-gray-200 p-6">
                <p class="text-sm font-medium text-gray-500">Total Budget</p>
                <p class="text-2xl font-bold text-gray-900 mt-1">Rp {{ number_format($summary['total_budget'] ?? 0, 0, ',', '.') }}</p>
            </div>
            <div class="bg-white rounded-xl border border-gray-200 p-6">
                <p class="text-sm font-medium text-gray-500">Total Used</p>
                <p class="text-2xl font-bold text-slate-600 mt-1">Rp {{ number_format($summary['total_used'] ?? 0, 0, ',', '.') }}</p>
            </div>
            <div class="bg-white rounded-xl border border-gray-200 p-6">
                <p class="text-sm font-medium text-gray-500">Remaining</p>
                <p class="text-2xl font-bold {{ ($summary['total_remaining'] ?? 0) < 0 ? 'text-red-600' : 'text-emerald-600' }} mt-1">
                    Rp {{ number_format($summary['total_remaining'] ?? 0, 0, ',', '.') }}
                </p>
            </div>
        </div>

        <!-- Budget Details Table -->
        <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-base font-semibold text-gray-900">Budget Details by Category</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Category</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Department</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Budget</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Used</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Remaining</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Utilization</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Status</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($budgets ?? [] as $budget)
                            @php
                                $limitAmount = is_array($budget) ? ($budget['limit_amount'] ?? 0) : ($budget->limit_amount ?? 0);
                                $usedAmount = is_array($budget) ? ($budget['used_amount'] ?? 0) : ($budget->used_amount ?? 0);
                                $categoryName = is_array($budget) ? ($budget['category_name'] ?? 'N/A') : ($budget->category->name ?? 'N/A');
                                $department = is_array($budget) ? ($budget['department'] ?? 'All') : ($budget->department ?? 'All');
                                $percentage = $limitAmount > 0 ? ($usedAmount / $limitAmount) * 100 : 0;
                                $statusColor = $percentage >= 100 ? 'red' : ($percentage >= 80 ? 'amber' : 'emerald');
                            @endphp
                            <tr class="hover:bg-gray-50 transition-colors {{ $percentage >= 100 ? 'bg-red-50' : ($percentage >= 80 ? 'bg-amber-50' : '') }}">
                                <td class="px-4 py-4 whitespace-nowrap">
                                    <span class="text-sm font-medium text-gray-900">{{ $categoryName }}</span>
                                </td>
                                <td class="px-4 py-4 whitespace-nowrap">
                                    <span class="text-sm text-gray-500">{{ $department }}</span>
                                </td>
                                <td class="px-4 py-4 whitespace-nowrap">
                                    <span class="text-sm text-gray-900">Rp {{ number_format($limitAmount, 0, ',', '.') }}</span>
                                </td>
                                <td class="px-4 py-4 whitespace-nowrap">
                                    <span class="text-sm text-gray-900">Rp {{ number_format($usedAmount, 0, ',', '.') }}</span>
                                </td>
                                <td class="px-4 py-4 whitespace-nowrap">
                                    <span class="text-sm font-medium {{ ($limitAmount - $usedAmount) < 0 ? 'text-red-600' : 'text-gray-900' }}">
                                        Rp {{ number_format($limitAmount - $usedAmount, 0, ',', '.') }}
                                    </span>
                                </td>
                                <td class="px-4 py-4 whitespace-nowrap">
                                    <div class="flex items-center gap-2">
                                        <div class="w-24 bg-gray-200 rounded-full h-2">
                                            <div class="bg-{{ $statusColor }}-500 h-2 rounded-full" style="width: {{ min($percentage, 100) }}%"></div>
                                        </div>
                                        <span class="text-sm text-gray-600">{{ number_format($percentage, 1) }}%</span>
                                    </div>
                                </td>
                                <td class="px-4 py-4 whitespace-nowrap">
                                    @if($percentage >= 100)
                                        <span class="px-2.5 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">Over Budget</span>
                                    @elseif($percentage >= 80)
                                        <span class="px-2.5 py-1 text-xs font-semibold rounded-full bg-amber-100 text-amber-800">Warning</span>
                                    @else
                                        <span class="px-2.5 py-1 text-xs font-semibold rounded-full bg-emerald-100 text-emerald-800">On Track</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-6 py-16 text-center">
                                    <div class="flex flex-col items-center">
                                        <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mb-4">
                                            <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                            </svg>
                                        </div>
                                        <h3 class="text-sm font-semibold text-gray-900 mb-1">No budget data available</h3>
                                        <p class="text-sm text-gray-500">Budget information will appear here.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
