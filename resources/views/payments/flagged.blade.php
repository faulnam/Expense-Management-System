<x-app-layout>
    <div class="max-w-7xl mx-auto">
        <!-- Page Header -->
        <div class="mb-6">
            <h1 class="text-2xl font-semibold text-gray-900">Flagged Expenses</h1>
            <p class="mt-1 text-sm text-gray-500">Review and resolve flagged expense claims</p>
        </div>

        <!-- Tabs -->
        <div class="mb-6">
            <nav class="flex space-x-1 bg-gray-100 rounded-lg p-1 w-fit">
                <a href="{{ route('payments.index') }}" class="px-4 py-2 text-sm font-medium rounded-md transition-colors text-gray-600 hover:text-gray-900">
                    Pending Payment
                </a>
                <a href="{{ route('payments.flagged') }}" class="px-4 py-2 text-sm font-medium rounded-md transition-colors bg-white text-gray-900 shadow-sm">
                    Flagged Expenses
                </a>
            </nav>
        </div>

        <!-- Alert -->
        <div class="bg-red-50 border border-red-200 rounded-xl p-4 mb-6">
            <div class="flex">
                <svg class="h-5 w-5 text-red-500 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                </svg>
                <div class="ml-3">
                    <h3 class="text-sm font-semibold text-red-800">Flagged expenses require review</h3>
                    <p class="mt-1 text-sm text-red-700">These expenses have been flagged for suspicious activity or require additional verification before payment can be processed.</p>
                </div>
            </div>
        </div>

        <!-- Flagged Expenses Table -->
        <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3.5 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Expense</th>
                            <th class="px-4 py-3.5 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Employee</th>
                            <th class="px-4 py-3.5 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Amount</th>
                            <th class="px-4 py-3.5 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Flag Reason</th>
                            <th class="px-4 py-3.5 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Flagged By</th>
                            <th class="px-4 py-3.5 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Date</th>
                            <th class="px-4 py-3.5 text-right text-xs font-semibold text-gray-600 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($expenses as $expense)
                            <tr class="hover:bg-red-100/50 bg-red-50 transition-colors">
                                <td class="px-4 py-4 whitespace-nowrap">
                                    <div>
                                        <a href="{{ route('expenses.show', $expense) }}" class="text-sm font-semibold text-slate-800 hover:text-slate-600">{{ $expense->expense_number }}</a>
                                        <div class="text-sm text-gray-500">{{ $expense->category->name ?? 'N/A' }}</div>
                                    </div>
                                </td>
                                <td class="px-4 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900">{{ $expense->user->name ?? 'N/A' }}</div>
                                    <div class="text-sm text-gray-500">{{ $expense->user->department ?? 'N/A' }}</div>
                                </td>
                                <td class="px-4 py-4 whitespace-nowrap">
                                    <span class="text-sm font-semibold text-gray-900">Rp {{ number_format($expense->amount, 0, ',', '.') }}</span>
                                </td>
                                <td class="px-4 py-4">
                                    <span class="text-sm text-red-700">{{ $expense->flag_reason }}</span>
                                </td>
                                <td class="px-4 py-4 whitespace-nowrap">
                                    <span class="text-sm text-gray-700">{{ $expense->flaggedByUser->name ?? 'System' }}</span>
                                </td>
                                <td class="px-4 py-4 whitespace-nowrap">
                                    <span class="text-sm text-gray-500">{{ $expense->flagged_at?->format('d M Y, H:i') ?? 'N/A' }}</span>
                                </td>
                                <td class="px-4 py-4 whitespace-nowrap text-right">
                                    <div class="flex justify-end gap-3">
                                        <a href="{{ route('expenses.show', $expense) }}" class="text-sm font-medium text-slate-600 hover:text-slate-900">View</a>
                                        <form action="{{ route('payments.unflag', $expense) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to unflag this expense?')">
                                            @csrf
                                            <button type="submit" class="text-sm font-medium text-emerald-600 hover:text-emerald-800">Unflag</button>
                                        </form>
                                        <button type="button" onclick="openRejectModal({{ $expense->id }})" class="text-sm font-medium text-red-600 hover:text-red-800">Reject</button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-6 py-16 text-center">
                                    <div class="flex flex-col items-center">
                                        <div class="w-16 h-16 bg-emerald-50 rounded-full flex items-center justify-center mb-4">
                                            <svg class="w-8 h-8 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                            </svg>
                                        </div>
                                        <h3 class="text-sm font-semibold text-gray-900 mb-1">No flagged expenses</h3>
                                        <p class="text-sm text-gray-500">All expenses are cleared for processing.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($expenses->hasPages())
                <div class="bg-gray-50 px-4 py-3 border-t border-gray-200">
                    {{ $expenses->links() }}
                </div>
            @endif
        </div>
    </div>

    <!-- Reject Modal -->
    <div id="reject-modal" class="hidden fixed inset-0 bg-gray-900/50 backdrop-blur-sm overflow-y-auto h-full w-full z-50">
        <div class="relative top-20 mx-auto max-w-md">
            <div class="bg-white rounded-xl shadow-xl border border-gray-200 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Reject Expense</h3>
                <form id="reject-form" method="POST">
                    @csrf
                    <div class="mb-5">
                        <label for="rejection_reason" class="block text-sm font-medium text-gray-700 mb-1.5">Reason for Rejection</label>
                        <textarea name="rejection_reason" id="rejection_reason" rows="3" required class="w-full rounded-lg border-gray-300 text-sm focus:border-slate-500 focus:ring-slate-500" placeholder="Please provide a reason..."></textarea>
                    </div>
                    <div class="flex justify-end gap-3">
                        <button type="button" onclick="closeRejectModal()" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">Cancel</button>
                        <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-red-600 rounded-lg hover:bg-red-700 transition-colors">Reject Expense</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        function openRejectModal(expenseId) {
            document.getElementById('reject-form').action = '/approvals/' + expenseId + '/reject';
            document.getElementById('reject-modal').classList.remove('hidden');
        }

        function closeRejectModal() {
            document.getElementById('reject-modal').classList.add('hidden');
        }
    </script>
    @endpush
</x-app-layout>
