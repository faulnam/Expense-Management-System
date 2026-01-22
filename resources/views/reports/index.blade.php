<x-app-layout>
    <div class="max-w-7xl mx-auto">
        <!-- Page Header -->
        <div class="mb-6">
            <h1 class="text-2xl font-semibold text-gray-900">Reports</h1>
            <p class="mt-1 text-sm text-gray-500">Generate and view expense reports and analytics</p>
        </div>

        <!-- Report Types Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
            <!-- Expense Report -->
            <div class="bg-white rounded-xl border border-gray-200 p-6 hover:border-slate-300 transition-colors">
                <div class="flex items-center mb-4">
                    <div class="p-3 rounded-lg bg-slate-100 text-slate-600">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                    </div>
                    <h3 class="ml-4 text-lg font-semibold text-gray-900">Expense Report</h3>
                </div>
                <p class="text-sm text-gray-500 mb-4">Generate detailed expense reports with filters by date, category, status, and department.</p>
                <a href="#" onclick="document.getElementById('expense-filter').classList.toggle('hidden'); return false;" class="inline-flex items-center text-slate-700 hover:text-slate-900 text-sm font-medium">
                    Generate Report
                    <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </a>
            </div>

            <!-- Budget Utilization -->
            <div class="bg-white rounded-xl border border-gray-200 p-6 hover:border-slate-300 transition-colors">
                <div class="flex items-center mb-4">
                    <div class="p-3 rounded-lg bg-emerald-50 text-emerald-600">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                        </svg>
                    </div>
                    <h3 class="ml-4 text-lg font-semibold text-gray-900">Budget Utilization</h3>
                </div>
                <p class="text-sm text-gray-500 mb-4">View budget consumption across departments and categories for the current period.</p>
                <a href="{{ route('reports.budget') }}" class="inline-flex items-center text-slate-700 hover:text-slate-900 text-sm font-medium">
                    View Report
                    <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </a>
            </div>

            <!-- Payment Report -->
            <div class="bg-white rounded-xl border border-gray-200 p-6 hover:border-slate-300 transition-colors">
                <div class="flex items-center mb-4">
                    <div class="p-3 rounded-lg bg-purple-50 text-purple-600">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                        </svg>
                    </div>
                    <h3 class="ml-4 text-lg font-semibold text-gray-900">Payment Report</h3>
                </div>
                <p class="text-sm text-gray-500 mb-4">Track all payments processed including gateway transactions and status.</p>
                <a href="{{ route('reports.payments') }}" class="inline-flex items-center text-slate-700 hover:text-slate-900 text-sm font-medium">
                    View Report
                    <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </a>
            </div>
        </div>

        <!-- Expense Report Filter Panel -->
        <div id="expense-filter" class="hidden bg-white rounded-xl border border-gray-200 p-6 mb-8">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Generate Expense Report</h3>
            <form id="export-form" action="{{ route('reports.export.excel') }}" method="GET" class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Date From</label>
                    <input type="date" name="date_from" value="{{ request('date_from', now()->startOfMonth()->format('Y-m-d')) }}" class="w-full rounded-lg border-gray-300 text-sm focus:border-slate-500 focus:ring-slate-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Date To</label>
                    <input type="date" name="date_to" value="{{ request('date_to', now()->format('Y-m-d')) }}" class="w-full rounded-lg border-gray-300 text-sm focus:border-slate-500 focus:ring-slate-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Status</label>
                    <select name="status" class="w-full rounded-lg border-gray-300 text-sm focus:border-slate-500 focus:ring-slate-500">
                        <option value="">All Status</option>
                        <option value="draft">Draft</option>
                        <option value="submitted">Submitted</option>
                        <option value="manager_approved">Manager Approved</option>
                        <option value="finance_approved">Finance Approved</option>
                        <option value="paid">Paid</option>
                        <option value="rejected">Rejected</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Category</label>
                    <select name="category_id" class="w-full rounded-lg border-gray-300 text-sm focus:border-slate-500 focus:ring-slate-500">
                        <option value="">All Categories</option>
                        @foreach($categories ?? [] as $category)
                            <option value="{{ $category->id }}">{{ $category->name }}</option>
                        @endforeach
                    </select>
                </div>
                @if(auth()->user()->isAdmin() || auth()->user()->isFinance())
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Department</label>
                    <select name="department" class="w-full rounded-lg border-gray-300 text-sm focus:border-slate-500 focus:ring-slate-500">
                        <option value="">All Departments</option>
                        @foreach($departments ?? [] as $dept)
                            <option value="{{ $dept }}">{{ $dept }}</option>
                        @endforeach
                    </select>
                </div>
                @endif
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Export Format</label>
                    <select name="format" id="export-format" class="w-full rounded-lg border-gray-300 text-sm focus:border-slate-500 focus:ring-slate-500">
                        <option value="excel">Excel (.xlsx)</option>
                        <option value="pdf">PDF</option>
                    </select>
                </div>
                <div class="md:col-span-3 flex justify-end gap-3 pt-2">
                    <button type="button" onclick="document.getElementById('expense-filter').classList.add('hidden')" class="px-4 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50 transition-colors">Cancel</button>
                    <button type="submit" class="inline-flex items-center gap-2 px-4 py-2 bg-slate-800 text-white text-sm font-medium rounded-lg hover:bg-slate-700 transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        Export Report
                    </button>
                </div>
            </form>
        </div>

        <!-- Summary Stats -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
            <div class="bg-white rounded-xl border border-gray-200 p-6">
                <p class="text-sm font-medium text-gray-500">Total Expenses (This Month)</p>
                <p class="text-2xl font-bold text-gray-900 mt-1">{{ $stats['total_this_month'] ?? 0 }}</p>
            </div>
            <div class="bg-white rounded-xl border border-gray-200 p-6">
                <p class="text-sm font-medium text-gray-500">Total Amount</p>
                <p class="text-2xl font-bold text-gray-900 mt-1">Rp {{ number_format($stats['total_amount_this_month'] ?? 0, 0, ',', '.') }}</p>
            </div>
            <div class="bg-white rounded-xl border border-gray-200 p-6">
                <p class="text-sm font-medium text-gray-500">Approved</p>
                <p class="text-2xl font-bold text-emerald-600 mt-1">{{ $stats['approved_this_month'] ?? 0 }}</p>
            </div>
            <div class="bg-white rounded-xl border border-gray-200 p-6">
                <p class="text-sm font-medium text-gray-500">Pending</p>
                <p class="text-2xl font-bold text-amber-600 mt-1">{{ $stats['pending_this_month'] ?? 0 }}</p>
            </div>
        </div>

        <!-- Recent Expenses Summary -->
        <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900">Expense Summary (This Month)</h3>
            </div>
            <div class="p-6">
                <!-- By Category Chart Placeholder -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <div>
                        <h4 class="text-sm font-semibold text-gray-700 mb-4">By Category</h4>
                        <div class="space-y-4">
                            @foreach($expensesByCategory ?? [] as $cat)
                            <div>
                                <div class="flex justify-between text-sm mb-1.5">
                                    <span class="text-gray-600">{{ $cat['name'] }}</span>
                                    <span class="font-semibold text-gray-900">Rp {{ number_format($cat['total'], 0, ',', '.') }}</span>
                                </div>
                                <div class="w-full bg-gray-100 rounded-full h-2">
                                    <div class="bg-slate-600 h-2 rounded-full" style="width: {{ $cat['percentage'] }}%"></div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    <div>
                        <h4 class="text-sm font-semibold text-gray-700 mb-4">By Status</h4>
                        <div class="space-y-4">
                            @foreach($expensesByStatus ?? [] as $status)
                            <div>
                                <div class="flex justify-between text-sm mb-1.5">
                                    <span class="text-gray-600">{{ ucfirst(str_replace('_', ' ', $status['status'])) }}</span>
                                    <span class="font-semibold text-gray-900">{{ $status['count'] }}</span>
                                </div>
                                <div class="w-full bg-gray-100 rounded-full h-2">
                                    <div class="{{ $status['color'] }} h-2 rounded-full" style="width: {{ $status['percentage'] }}%"></div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        document.getElementById('export-format').addEventListener('change', function() {
            var form = document.getElementById('export-form');
            if (this.value === 'pdf') {
                form.action = "{{ route('reports.export.pdf') }}";
            } else {
                form.action = "{{ route('reports.export.excel') }}";
            }
        });
    </script>
    @endpush
</x-app-layout>
