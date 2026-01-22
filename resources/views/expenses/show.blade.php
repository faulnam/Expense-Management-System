<x-app-layout>
    <div class="space-y-6">
        <!-- Back Button & Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div class="flex items-center gap-4">
                <a href="{{ route('expenses.index') }}" class="p-2 text-gray-500 hover:text-gray-700 hover:bg-gray-100 rounded-lg transition-colors">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                </a>
                <div>
                    <h1 class="text-2xl font-semibold text-gray-900">Expense Details</h1>
                    <p class="text-sm text-gray-500 mt-1">{{ $expense->expense_number }}</p>
                </div>
            </div>
            <div class="flex items-center gap-3">
                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 text-sm font-medium rounded-lg
                    @switch($expense->status)
                        @case('draft') bg-gray-100 text-gray-700 @break
                        @case('submitted') bg-amber-100 text-amber-800 @break
                        @case('manager_approved') bg-indigo-100 text-indigo-800 @break
                        @case('finance_approved') bg-emerald-100 text-emerald-800 @break
                        @case('paid') bg-emerald-600 text-white @break
                        @case('rejected') bg-rose-100 text-rose-800 @break
                        @default bg-gray-100 text-gray-700
                    @endswitch">
                    @if($expense->status === 'draft')
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                    @elseif($expense->status === 'submitted')
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    @elseif(in_array($expense->status, ['manager_approved', 'finance_approved', 'paid']))
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    @else
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    @endif
                    {{ ucfirst(str_replace('_', ' ', $expense->status)) }}
                </span>
                @if($expense->isDraft())
                    <a href="{{ route('expenses.edit', $expense) }}" class="inline-flex items-center gap-2 px-4 py-2 bg-slate-800 text-white text-sm font-medium rounded-lg hover:bg-slate-700 transition-colors">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                        </svg>
                        Edit
                    </a>
                @endif
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Main Details -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Expense Info -->
                <div class="bg-white rounded-lg border border-gray-200 p-5">
                    <h3 class="text-base font-semibold text-gray-900 mb-4">Expense Information</h3>
                    <div class="grid grid-cols-2 gap-4">
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
                            <p class="text-sm text-gray-900 mt-1">{{ $expense->submitted_at?->format('d M Y H:i') ?? 'Not submitted yet' }}</p>
                        </div>
                        <div>
                            <p class="text-xs font-medium text-gray-500 uppercase">Created</p>
                            <p class="text-sm text-gray-900 mt-1">{{ $expense->created_at->format('d M Y H:i') }}</p>
                        </div>
                        <div class="col-span-2">
                            <p class="text-xs font-medium text-gray-500 uppercase">Description</p>
                            <p class="text-sm text-gray-900 mt-1">{{ $expense->description ?? '-' }}</p>
                        </div>
                        @if($expense->merchant_name)
                        <div>
                            <p class="text-xs font-medium text-gray-500 uppercase">Merchant</p>
                            <p class="text-sm text-gray-900 mt-1">{{ $expense->merchant_name }}</p>
                        </div>
                        @endif
                    </div>
                </div>

                <!-- Amount Card -->
                <div class="bg-slate-700 rounded-lg p-5 text-white">
                    <p class="text-sm font-medium text-slate-300">Total Amount</p>
                    <p class="text-3xl font-bold mt-1">Rp {{ number_format($expense->amount, 0, ',', '.') }}</p>
                </div>

                <!-- Budget Warning -->
                @if(isset($budgetWarning) && $budgetWarning)
                <div class="bg-amber-50 border border-amber-200 rounded-lg p-4">
                    <div class="flex items-start gap-3">
                        <svg class="w-5 h-5 text-amber-600 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                        </svg>
                        <div>
                            <h4 class="text-sm font-semibold text-amber-800">Budget Warning</h4>
                            <p class="text-sm text-amber-700 mt-1">
                                Budget utilization is at {{ number_format($budgetWarning['usage_percentage'], 1) }}%.
                                Remaining: Rp {{ number_format($budgetWarning['remaining'], 0, ',', '.') }}
                            </p>
                        </div>
                    </div>
                </div>
                @endif

                <!-- Fraud Warnings -->
                @if(isset($fraudWarnings) && count($fraudWarnings) > 0)
                <div class="bg-rose-50 border border-rose-200 rounded-lg p-4">
                    <div class="flex items-start gap-3">
                        <svg class="w-5 h-5 text-rose-600 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                        </svg>
                        <div>
                            <h4 class="text-sm font-semibold text-rose-800">Fraud Detection Alerts</h4>
                            <ul class="mt-2 space-y-1">
                                @foreach($fraudWarnings as $warning)
                                <li class="text-sm text-rose-700">• {{ $warning }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>
                @endif

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

                <!-- Approval History -->
                @if($expense->approvals && $expense->approvals->count() > 0)
                <div class="bg-white rounded-lg border border-gray-200 p-5">
                    <h3 class="text-base font-semibold text-gray-900 mb-4">Approval History</h3>
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
                </div>
                @endif
            </div>

            <!-- Sidebar -->
            <div class="space-y-6">
                <!-- Actions -->
                @if($expense->isDraft())
                <div class="bg-white rounded-lg border border-gray-200 p-5">
                    <h3 class="text-base font-semibold text-gray-900 mb-4">Actions</h3>
                    <div class="space-y-3">
                        <form action="{{ route('expenses.submit', $expense) }}" method="POST">
                            @csrf
                            <button type="submit" class="w-full px-4 py-2.5 bg-emerald-600 text-white text-sm font-medium rounded-lg hover:bg-emerald-700 transition-colors flex items-center justify-center gap-2">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                Submit for Approval
                            </button>
                        </form>
                        <a href="{{ route('expenses.edit', $expense) }}" class="w-full px-4 py-2.5 bg-gray-100 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-200 transition-colors flex items-center justify-center gap-2">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                            </svg>
                            Edit Expense
                        </a>
                        <form action="{{ route('expenses.destroy', $expense) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this expense?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="w-full px-4 py-2.5 bg-white border border-rose-300 text-rose-600 text-sm font-medium rounded-lg hover:bg-rose-50 transition-colors flex items-center justify-center gap-2">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                </svg>
                                Delete Expense
                            </button>
                        </form>
                    </div>
                </div>
                @endif

                <!-- Status Timeline -->
                <div class="bg-white rounded-lg border border-gray-200 p-5">
                    <h3 class="text-base font-semibold text-gray-900 mb-4">Status Timeline</h3>
                    <div class="space-y-4">
                        <!-- Draft -->
                        <div class="flex items-start gap-3">
                            <div class="w-6 h-6 rounded-full flex items-center justify-center flex-shrink-0 {{ $expense->status !== 'draft' ? 'bg-emerald-100 text-emerald-600' : 'bg-slate-100 text-slate-600' }}">
                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-900">Created</p>
                                <p class="text-xs text-gray-500">{{ $expense->created_at->format('d M Y H:i') }}</p>
                            </div>
                        </div>
                        
                        <!-- Submitted -->
                        <div class="flex items-start gap-3">
                            <div class="w-6 h-6 rounded-full flex items-center justify-center flex-shrink-0 {{ $expense->submitted_at ? 'bg-emerald-100 text-emerald-600' : 'bg-gray-100 text-gray-400' }}">
                                @if($expense->submitted_at)
                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                @else
                                <span class="w-2 h-2 rounded-full bg-gray-300"></span>
                                @endif
                            </div>
                            <div>
                                <p class="text-sm font-medium {{ $expense->submitted_at ? 'text-gray-900' : 'text-gray-400' }}">Submitted</p>
                                <p class="text-xs text-gray-500">{{ $expense->submitted_at?->format('d M Y H:i') ?? 'Pending' }}</p>
                            </div>
                        </div>
                        
                        <!-- Manager Approved -->
                        <div class="flex items-start gap-3">
                            <div class="w-6 h-6 rounded-full flex items-center justify-center flex-shrink-0 
                                @if(in_array($expense->status, ['manager_approved', 'finance_approved', 'paid'])) bg-emerald-100 text-emerald-600 
                                @elseif($expense->status === 'rejected') bg-rose-100 text-rose-600 
                                @else bg-gray-100 text-gray-400 @endif">
                                @if(in_array($expense->status, ['manager_approved', 'finance_approved', 'paid']))
                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                @elseif($expense->status === 'rejected')
                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                @else
                                <span class="w-2 h-2 rounded-full bg-gray-300"></span>
                                @endif
                            </div>
                            <div>
                                <p class="text-sm font-medium {{ in_array($expense->status, ['manager_approved', 'finance_approved', 'paid', 'rejected']) ? 'text-gray-900' : 'text-gray-400' }}">Manager Review</p>
                                <p class="text-xs text-gray-500">{{ $expense->manager_approved_at?->format('d M Y H:i') ?? 'Pending' }}</p>
                            </div>
                        </div>
                        
                        <!-- Finance Approved -->
                        <div class="flex items-start gap-3">
                            <div class="w-6 h-6 rounded-full flex items-center justify-center flex-shrink-0 
                                @if(in_array($expense->status, ['finance_approved', 'paid'])) bg-emerald-100 text-emerald-600 
                                @elseif($expense->status === 'rejected') bg-rose-100 text-rose-600 
                                @else bg-gray-100 text-gray-400 @endif">
                                @if(in_array($expense->status, ['finance_approved', 'paid']))
                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                @elseif($expense->status === 'rejected')
                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                @else
                                <span class="w-2 h-2 rounded-full bg-gray-300"></span>
                                @endif
                            </div>
                            <div>
                                <p class="text-sm font-medium {{ in_array($expense->status, ['finance_approved', 'paid', 'rejected']) ? 'text-gray-900' : 'text-gray-400' }}">Finance Review</p>
                                <p class="text-xs text-gray-500">{{ $expense->finance_approved_at?->format('d M Y H:i') ?? 'Pending' }}</p>
                            </div>
                        </div>
                        
                        <!-- Paid -->
                        <div class="flex items-start gap-3">
                            <div class="w-6 h-6 rounded-full flex items-center justify-center flex-shrink-0 {{ $expense->status === 'paid' ? 'bg-emerald-100 text-emerald-600' : 'bg-gray-100 text-gray-400' }}">
                                @if($expense->status === 'paid')
                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                @else
                                <span class="w-2 h-2 rounded-full bg-gray-300"></span>
                                @endif
                            </div>
                            <div>
                                <p class="text-sm font-medium {{ $expense->status === 'paid' ? 'text-gray-900' : 'text-gray-400' }}">Paid</p>
                                <p class="text-xs text-gray-500">{{ $expense->paid_at?->format('d M Y H:i') ?? 'Pending' }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Quick Info -->
                <div class="bg-slate-50 rounded-lg border border-slate-200 p-5">
                    <h3 class="text-base font-semibold text-slate-900 mb-3">Expense Info</h3>
                    <div class="space-y-2 text-sm">
                        <div class="flex justify-between">
                            <span class="text-gray-500">Expense Number</span>
                            <span class="font-medium text-gray-900">{{ $expense->expense_number }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-500">Category</span>
                            <span class="font-medium text-gray-900">{{ $expense->category->name ?? 'N/A' }}</span>
                        </div>
                        @if($expense->user)
                        <div class="flex justify-between">
                            <span class="text-gray-500">Submitted By</span>
                            <span class="font-medium text-gray-900">{{ $expense->user->name }}</span>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
