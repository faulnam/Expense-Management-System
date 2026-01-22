<x-app-layout>
    <div class="space-y-6">
        <!-- Back Button & Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div class="flex items-center gap-4">
                <a href="{{ route('approvals.index') }}" class="p-2 text-gray-500 hover:text-gray-700 hover:bg-gray-100 rounded-lg transition-colors">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                </a>
                <div>
                    <h1 class="text-2xl font-semibold text-gray-900">Review Expense</h1>
                    <p class="text-sm text-gray-500 mt-1">{{ $expense->expense_number }}</p>
                </div>
            </div>
            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 text-sm font-medium rounded-lg
                @if($expense->status === 'submitted') bg-amber-100 text-amber-800
                @elseif($expense->status === 'manager_approved') bg-indigo-100 text-indigo-800
                @elseif($expense->status === 'finance_approved') bg-emerald-100 text-emerald-800
                @else bg-gray-100 text-gray-700
                @endif">
                {{ ucfirst(str_replace('_', ' ', $expense->status)) }}
            </span>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Expense Details -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Main Info -->
                <div class="bg-white rounded-lg border border-gray-200 p-5">
                    <h3 class="text-base font-semibold text-gray-900 mb-4">Expense Details</h3>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <p class="text-xs font-medium text-gray-500 uppercase">Submitted By</p>
                            <div class="flex items-center gap-2 mt-1">
                                <div class="w-8 h-8 rounded-full bg-slate-200 flex items-center justify-center text-slate-700 font-medium text-xs">
                                    {{ strtoupper(substr($expense->user->name ?? 'N', 0, 1)) }}
                                </div>
                                <div>
                                    <span class="text-sm text-gray-900">{{ $expense->user->name ?? 'N/A' }}</span>
                                    <p class="text-xs text-gray-500">{{ $expense->user->department ?? '' }}</p>
                                </div>
                            </div>
                        </div>
                        <div>
                            <p class="text-xs font-medium text-gray-500 uppercase">Category</p>
                            <p class="text-sm text-gray-900 mt-1">{{ $expense->category->name ?? 'N/A' }}</p>
                        </div>
                        <div>
                            <p class="text-xs font-medium text-gray-500 uppercase">Expense Date</p>
                            <p class="text-sm text-gray-900 mt-1">{{ $expense->expense_date?->format('d M Y') ?? '-' }}</p>
                        </div>
                        <div>
                            <p class="text-xs font-medium text-gray-500 uppercase">Submitted</p>
                            <p class="text-sm text-gray-900 mt-1">{{ $expense->submitted_at?->format('d M Y H:i') ?? '-' }}</p>
                        </div>
                        <div class="col-span-2">
                            <p class="text-xs font-medium text-gray-500 uppercase">Description</p>
                            <p class="text-sm text-gray-900 mt-1">{{ $expense->description ?? '-' }}</p>
                        </div>
                    </div>
                </div>

                <!-- Amount Card -->
                <div class="bg-slate-700 rounded-lg p-5 text-white">
                    <p class="text-sm font-medium text-slate-300">Total Amount</p>
                    <p class="text-3xl font-bold mt-1">Rp {{ number_format($expense->amount, 0, ',', '.') }}</p>
                </div>

                <!-- Receipt/Attachment -->
                @if($expense->receipt_path)
                <div class="bg-white rounded-lg border border-gray-200 p-5">
                    <h3 class="text-base font-semibold text-gray-900 mb-4">Receipt/Attachment</h3>
                    <div class="border border-gray-200 rounded-lg p-4 bg-gray-50">
                        @if(Str::contains($expense->receipt_path, ['.jpg', '.jpeg', '.png', '.gif', '.webp']))
                            <img src="{{ route('expenses.receipt', $expense) }}" alt="Receipt" class="max-w-full max-h-96 mx-auto rounded">
                        @else
                            <div class="flex items-center justify-center gap-3">
                                <svg class="w-10 h-10 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                                <div>
                                    <p class="text-sm font-medium text-gray-900">Document Attached</p>
                                    <a href="{{ route('expenses.receipt', $expense) }}" target="_blank" class="text-sm text-slate-600 hover:text-slate-900">Download/View</a>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
                @endif

                <!-- Fraud Warnings -->
                @if(count($fraudWarnings) > 0)
                <div class="bg-amber-50 border border-amber-200 rounded-lg p-4">
                    <div class="flex items-start gap-3">
                        <svg class="w-5 h-5 text-amber-600 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                        </svg>
                        <div>
                            <h4 class="text-sm font-semibold text-amber-800">Potential Issues Detected</h4>
                            <ul class="mt-2 space-y-1">
                                @foreach($fraudWarnings as $warning)
                                <li class="text-sm text-amber-700">• {{ $warning }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>
                @endif

                <!-- Approval History -->
                <div class="bg-white rounded-lg border border-gray-200 p-5">
                    <h3 class="text-base font-semibold text-gray-900 mb-4">Approval History</h3>
                    @if($expense->approvals->count() > 0)
                    <div class="space-y-3">
                        @foreach($expense->approvals as $approval)
                        <div class="flex items-center gap-3 p-3 bg-gray-50 rounded-lg">
                            <div class="w-8 h-8 rounded-full {{ $approval->status == 'approved' ? 'bg-emerald-100 text-emerald-700' : 'bg-rose-100 text-rose-700' }} flex items-center justify-center font-medium text-xs">
                                {{ strtoupper(substr($approval->approver->name ?? 'N', 0, 1)) }}
                            </div>
                            <div class="flex-1">
                                <p class="text-sm font-medium text-gray-900">{{ $approval->approver->name ?? 'N/A' }}</p>
                                <p class="text-xs text-gray-500">{{ ucfirst($approval->level) }} • {{ $approval->created_at->format('d M Y H:i') }}</p>
                            </div>
                            <span class="inline-flex px-2 py-0.5 text-xs font-medium rounded {{ $approval->status == 'approved' ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-800' }}">
                                {{ ucfirst($approval->status) }}
                            </span>
                        </div>
                        @if($approval->notes)
                        <div class="ml-11 px-3 py-2 bg-gray-100 rounded text-sm text-gray-600">
                            {{ $approval->notes }}
                        </div>
                        @endif
                        @endforeach
                    </div>
                    @else
                    <p class="text-sm text-gray-500">No approval history yet</p>
                    @endif
                </div>
            </div>

            <!-- Approval Actions -->
            <div class="space-y-6">
                @if($expense->canBeApproved())
                <!-- Approve Form -->
                <div class="bg-white rounded-lg border border-gray-200 p-5">
                    <h3 class="text-base font-semibold text-gray-900 mb-4">Approval Decision</h3>
                    
                    <form action="{{ route('approvals.approve', $expense) }}" method="POST" class="space-y-4">
                        @csrf
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Notes (Optional)</label>
                            <textarea name="notes" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500" placeholder="Add approval notes..."></textarea>
                        </div>
                        <button type="submit" class="w-full px-4 py-2.5 bg-emerald-600 text-white text-sm font-medium rounded-lg hover:bg-emerald-700 transition-colors flex items-center justify-center gap-2">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            Approve Expense
                        </button>
                    </form>
                </div>

                <!-- Reject Form -->
                <div class="bg-white rounded-lg border border-gray-200 p-5">
                    <h3 class="text-base font-semibold text-gray-900 mb-4">Reject Expense</h3>
                    
                    <form action="{{ route('approvals.reject', $expense) }}" method="POST" class="space-y-4">
                        @csrf
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Reason for Rejection <span class="text-rose-500">*</span></label>
                            <textarea name="notes" rows="3" required class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-rose-500 focus:border-rose-500" placeholder="Explain why this expense is being rejected..."></textarea>
                        </div>
                        <button type="submit" class="w-full px-4 py-2.5 bg-rose-600 text-white text-sm font-medium rounded-lg hover:bg-rose-700 transition-colors flex items-center justify-center gap-2" onclick="return confirm('Are you sure you want to reject this expense?')">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                            Reject Expense
                        </button>
                    </form>
                </div>
                @else
                <div class="bg-gray-50 rounded-lg border border-gray-200 p-5">
                    <div class="text-center py-4">
                        <svg class="w-12 h-12 text-gray-300 mx-auto mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <p class="text-sm text-gray-500">This expense cannot be approved at this time</p>
                        <p class="text-xs text-gray-400 mt-1">Status: {{ ucfirst(str_replace('_', ' ', $expense->status)) }}</p>
                    </div>
                </div>
                @endif

                <!-- Quick Info -->
                <div class="bg-slate-50 rounded-lg border border-slate-200 p-5">
                    <h3 class="text-base font-semibold text-slate-900 mb-3">Quick Info</h3>
                    <div class="space-y-2 text-sm">
                        <div class="flex justify-between">
                            <span class="text-gray-500">Budget Category</span>
                            <span class="font-medium text-gray-900">{{ $expense->category->name ?? 'N/A' }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-500">Department</span>
                            <span class="font-medium text-gray-900">{{ $expense->user->department ?? 'N/A' }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-500">Employee</span>
                            <span class="font-medium text-gray-900">{{ $expense->user->employee_id ?? 'N/A' }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
