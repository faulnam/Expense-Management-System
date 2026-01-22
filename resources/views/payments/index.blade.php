<x-app-layout>
    <div class="space-y-6">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-semibold text-gray-900">Payments</h1>
                <p class="text-sm text-gray-500 mt-1">Process approved expenses and manage payment history</p>
            </div>
            <a href="{{ route('payments.flagged') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-white border border-gray-300 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-50 transition-colors">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 21v-4m0 0V5a2 2 0 012-2h6.5l1 1H21l-3 6 3 6h-8.5l-1-1H5a2 2 0 00-2 2zm9-13.5V9"/>
                </svg>
                Flagged Expenses
            </a>
        </div>

        <!-- Filters -->
        <div class="bg-white rounded-lg border border-gray-200 p-4">
            <form method="GET" class="flex flex-col sm:flex-row gap-4">
                <div class="flex-1">
                    <label class="block text-xs font-medium text-gray-500 mb-1">Category</label>
                    <select name="category_id" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-slate-500 focus:border-slate-500">
                        <option value="">All Categories</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}" {{ request('category_id') == $category->id ? 'selected' : '' }}>
                                {{ $category->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="flex-1">
                    <label class="block text-xs font-medium text-gray-500 mb-1">Payment Status</label>
                    <select name="payment_status" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-slate-500 focus:border-slate-500">
                        <option value="">All Statuses</option>
                        <option value="completed" {{ request('payment_status') == 'completed' ? 'selected' : '' }}>Completed</option>
                        <option value="pending" {{ request('payment_status') == 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="failed" {{ request('payment_status') == 'failed' ? 'selected' : '' }}>Failed</option>
                    </select>
                </div>
                <div class="flex items-end gap-2">
                    <button type="submit" class="px-4 py-2 bg-slate-700 text-white text-sm font-medium rounded-lg hover:bg-slate-800 transition-colors">
                        Filter
                    </button>
                    <a href="{{ route('payments.index') }}" class="px-4 py-2 bg-gray-100 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-200 transition-colors">
                        Reset
                    </a>
                </div>
            </form>
        </div>

        <!-- Pending Payments -->
        <div class="bg-white rounded-lg border border-gray-200">
            <div class="px-5 py-4 border-b border-gray-100">
                <h3 class="text-base font-semibold text-gray-900">Pending Payments</h3>
                <p class="text-sm text-gray-500 mt-0.5">Approved expenses awaiting payment processing</p>
            </div>
            @if($pendingExpenses->count() > 0)
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 uppercase">Expense</th>
                            <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 uppercase">Employee</th>
                            <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 uppercase">Category</th>
                            <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 uppercase">Amount</th>
                            <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 uppercase">Approved</th>
                            <th class="px-5 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($pendingExpenses as $expense)
                        <tr class="hover:bg-gray-50">
                            <td class="px-5 py-4">
                                <div class="text-sm font-medium text-gray-900">{{ $expense->expense_number }}</div>
                                <div class="text-xs text-gray-500">{{ Str::limit($expense->description, 40) }}</div>
                            </td>
                            <td class="px-5 py-4">
                                <div class="flex items-center gap-2">
                                    <div class="w-8 h-8 rounded-full bg-slate-200 flex items-center justify-center text-slate-700 font-medium text-xs">
                                        {{ strtoupper(substr($expense->user->name ?? 'N', 0, 1)) }}
                                    </div>
                                    <div class="text-sm text-gray-900">{{ $expense->user->name ?? 'N/A' }}</div>
                                </div>
                            </td>
                            <td class="px-5 py-4 text-sm text-gray-600">{{ $expense->category->name ?? 'N/A' }}</td>
                            <td class="px-5 py-4 text-sm font-semibold text-gray-900">Rp {{ number_format($expense->amount, 0, ',', '.') }}</td>
                            <td class="px-5 py-4 text-sm text-gray-500">{{ $expense->approved_at?->format('d M Y') ?? '-' }}</td>
                            <td class="px-5 py-4 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="{{ route('payments.show', $expense) }}" class="inline-flex items-center gap-1 px-3 py-1.5 bg-slate-700 text-white text-xs font-medium rounded-lg hover:bg-slate-800 transition-colors">
                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                                        </svg>
                                        Process
                                    </a>
                                    <button type="button" onclick="openFlagModal({{ $expense->id }})" class="inline-flex items-center gap-1 px-3 py-1.5 bg-gray-100 text-gray-700 text-xs font-medium rounded-lg hover:bg-gray-200 transition-colors">
                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 21v-4m0 0V5a2 2 0 012-2h6.5l1 1H21l-3 6 3 6h-8.5l-1-1H5a2 2 0 00-2 2zm9-13.5V9"/>
                                        </svg>
                                        Flag
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="px-5 py-3 border-t border-gray-100">
                {{ $pendingExpenses->appends(request()->query())->links() }}
            </div>
            @else
            <div class="px-5 py-12 text-center">
                <svg class="w-12 h-12 text-gray-300 mx-auto mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <p class="text-sm text-gray-500">No pending payments</p>
            </div>
            @endif
        </div>

        <!-- Payment History -->
        <div class="bg-white rounded-lg border border-gray-200">
            <div class="px-5 py-4 border-b border-gray-100">
                <h3 class="text-base font-semibold text-gray-900">Payment History</h3>
                <p class="text-sm text-gray-500 mt-0.5">Completed and processed payments</p>
            </div>
            @if($payments->count() > 0)
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 uppercase">Payment ID</th>
                            <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 uppercase">Expense</th>
                            <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 uppercase">Employee</th>
                            <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 uppercase">Amount</th>
                            <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 uppercase">Method</th>
                            <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                            <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($payments as $payment)
                        <tr class="hover:bg-gray-50">
                            <td class="px-5 py-4 text-sm font-medium text-gray-900">{{ $payment->payment_number }}</td>
                            <td class="px-5 py-4 text-sm text-gray-600">{{ $payment->expense->expense_number ?? 'N/A' }}</td>
                            <td class="px-5 py-4 text-sm text-gray-600">{{ $payment->expense->user->name ?? 'N/A' }}</td>
                            <td class="px-5 py-4 text-sm font-semibold text-gray-900">Rp {{ number_format($payment->amount, 0, ',', '.') }}</td>
                            <td class="px-5 py-4 text-sm text-gray-600">{{ ucfirst(str_replace('_', ' ', $payment->payment_method)) }}</td>
                            <td class="px-5 py-4">
                                <span class="inline-flex px-2 py-0.5 text-xs font-medium rounded
                                    @if($payment->status == 'completed') bg-emerald-600 text-white
                                    @elseif($payment->status == 'pending') bg-amber-500 text-white
                                    @elseif($payment->status == 'processing') bg-indigo-500 text-white
                                    @elseif($payment->status == 'failed') bg-rose-500 text-white
                                    @else bg-gray-100 text-gray-600
                                    @endif">
                                    {{ ucfirst($payment->status) }}
                                </span>
                            </td>
                            <td class="px-5 py-4 text-sm text-gray-500">{{ $payment->created_at->format('d M Y H:i') }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="px-5 py-3 border-t border-gray-100">
                {{ $payments->appends(request()->query())->links() }}
            </div>
            @else
            <div class="px-5 py-12 text-center">
                <svg class="w-12 h-12 text-gray-300 mx-auto mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                </svg>
                <p class="text-sm text-gray-500">No payment history yet</p>
            </div>
            @endif
        </div>
    </div>

    <!-- Flag Modal -->
    <div id="flagModal" class="fixed inset-0 z-50 hidden" x-data="{ expenseId: null }">
        <div class="fixed inset-0 bg-black/50" onclick="closeFlagModal()"></div>
        <div class="fixed inset-0 flex items-center justify-center p-4">
            <div class="bg-white rounded-xl shadow-xl w-full max-w-md relative">
                <div class="px-5 py-4 border-b border-gray-100">
                    <h3 class="text-lg font-semibold text-gray-900">Flag Expense</h3>
                    <p class="text-sm text-gray-500 mt-1">Provide a reason for flagging this expense</p>
                </div>
                <form id="flagForm" method="POST">
                    @csrf
                    <div class="p-5">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Reason</label>
                        <textarea name="reason" rows="4" required class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-slate-500 focus:border-slate-500" placeholder="Explain why this expense needs to be flagged..."></textarea>
                    </div>
                    <div class="px-5 py-4 bg-gray-50 rounded-b-xl flex justify-end gap-3">
                        <button type="button" onclick="closeFlagModal()" class="px-4 py-2 bg-white border border-gray-300 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-50 transition-colors">
                            Cancel
                        </button>
                        <button type="submit" class="px-4 py-2 bg-slate-700 text-white text-sm font-medium rounded-lg hover:bg-slate-800 transition-colors">
                            Flag Expense
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        function openFlagModal(expenseId) {
            document.getElementById('flagModal').classList.remove('hidden');
            document.getElementById('flagForm').action = '/payments/' + expenseId + '/flag';
        }
        function closeFlagModal() {
            document.getElementById('flagModal').classList.add('hidden');
        }
    </script>
    @endpush
</x-app-layout>
