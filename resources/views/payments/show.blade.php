<x-app-layout>
    <div class="space-y-6">
        <!-- Back Button & Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div class="flex items-center gap-4">
                <a href="{{ route('payments.index') }}" class="p-2 text-gray-500 hover:text-gray-700 hover:bg-gray-100 rounded-lg transition-colors">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                </a>
                <div>
                    <h1 class="text-2xl font-semibold text-gray-900">Process Payment</h1>
                    <p class="text-sm text-gray-500 mt-1">{{ $expense->expense_number }}</p>
                </div>
            </div>
            @if($expense->is_flagged)
            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-gray-200 text-gray-700 text-sm font-medium rounded-lg">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 21v-4m0 0V5a2 2 0 012-2h6.5l1 1H21l-3 6 3 6h-8.5l-1-1H5a2 2 0 00-2 2zm9-13.5V9"/>
                </svg>
                Flagged
            </span>
            @endif
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Expense Details -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Main Info -->
                <div class="bg-white rounded-lg border border-gray-200 p-5">
                    <h3 class="text-base font-semibold text-gray-900 mb-4">Expense Details</h3>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <p class="text-xs font-medium text-gray-500 uppercase">Employee</p>
                            <div class="flex items-center gap-2 mt-1">
                                <div class="w-8 h-8 rounded-full bg-slate-200 flex items-center justify-center text-slate-700 font-medium text-xs">
                                    {{ strtoupper(substr($expense->user->name ?? 'N', 0, 1)) }}
                                </div>
                                <span class="text-sm text-gray-900">{{ $expense->user->name ?? 'N/A' }}</span>
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
                            <p class="text-sm text-gray-900 mt-1">{{ $expense->submitted_at?->format('d M Y') ?? '-' }}</p>
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

                <!-- Fraud Warnings -->
                @if(count($fraudWarnings) > 0)
                <div class="bg-gray-100 border border-gray-300 rounded-lg p-4">
                    <div class="flex items-start gap-3">
                        <svg class="w-5 h-5 text-gray-600 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                        </svg>
                        <div>
                            <h4 class="text-sm font-semibold text-gray-800">Potential Issues Detected</h4>
                            <ul class="mt-2 space-y-1">
                                @foreach($fraudWarnings as $warning)
                                <li class="text-sm text-gray-700">• {{ $warning }}</li>
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
                            <div class="w-8 h-8 rounded-full bg-slate-200 flex items-center justify-center text-slate-700 font-medium text-xs">
                                {{ strtoupper(substr($approval->approver->name ?? 'N', 0, 1)) }}
                            </div>
                            <div class="flex-1">
                                <p class="text-sm font-medium text-gray-900">{{ $approval->approver->name ?? 'N/A' }}</p>
                                <p class="text-xs text-gray-500">{{ $approval->created_at->format('d M Y H:i') }}</p>
                            </div>
                            <span class="inline-flex px-2 py-0.5 text-xs font-medium rounded {{ $approval->status == 'approved' ? 'bg-slate-200 text-slate-800' : 'bg-gray-300 text-gray-700' }}">
                                {{ ucfirst($approval->status) }}
                            </span>
                        </div>
                        @endforeach
                    </div>
                    @else
                    <p class="text-sm text-gray-500">No approval history</p>
                    @endif
                </div>
            </div>

            <!-- Payment Form -->
            <div class="space-y-6">
                <div class="bg-white rounded-lg border border-gray-200 p-5">
                    <h3 class="text-base font-semibold text-gray-900 mb-4">Payment Details</h3>
                    
                    @if($expense->canBePaid())
                    <form action="{{ route('payments.process', $expense) }}" method="POST" class="space-y-4">
                        @csrf
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Payment Method</label>
                            <select name="payment_method" id="payment_method" required class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-slate-500 focus:border-slate-500">
                                <option value="">Select method</option>
                                <optgroup label="Manual Payment">
                                    <option value="bank_transfer">Bank Transfer (Manual)</option>
                                    <option value="cash">Cash</option>
                                    <option value="check">Check</option>
                                </optgroup>
                                <optgroup label="QRIS">
                                    <option value="qris">QRIS (All Banks)</option>
                                </optgroup>
                                <optgroup label="Virtual Account">
                                    <option value="bni_va">BNI Virtual Account</option>
                                    <option value="bri_va">BRI Virtual Account</option>
                                    <option value="cimb_niaga_va">CIMB Niaga Virtual Account</option>
                                    <option value="permata_va">Permata Virtual Account</option>
                                    <option value="maybank_va">Maybank Virtual Account</option>
                                    <option value="sampoerna_va">Bank Sampoerna Virtual Account</option>
                                    <option value="bnc_va">Bank Neo Commerce Virtual Account</option>
                                    <option value="artha_graha_va">Artha Graha Virtual Account</option>
                                    <option value="atm_bersama_va">ATM Bersama Virtual Account</option>
                                </optgroup>
                                <optgroup label="International">
                                    <option value="paypal">PayPal</option>
                                </optgroup>
                            </select>
                        </div>

                        <!-- Online Payment Info (shown for QRIS/VA) -->
                        <div id="onlinePaymentInfo" class="hidden">
                            <div class="p-3 bg-indigo-50 border border-indigo-200 rounded-lg">
                                <div class="flex items-start gap-2">
                                    <svg class="w-5 h-5 text-indigo-600 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    <div>
                                        <p class="text-sm font-medium text-indigo-800">Online Payment</p>
                                        <p class="text-xs text-indigo-700 mt-1">Payment will be processed via Pak Kasir payment gateway. You'll receive payment details after submitting.</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Bank Details (shown for bank_transfer) -->
                        <div id="bankDetails" class="hidden space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Bank Name</label>
                                <input type="text" name="bank_name" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-slate-500 focus:border-slate-500" placeholder="e.g. BCA, Mandiri">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Account Number</label>
                                <input type="text" name="account_number" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-slate-500 focus:border-slate-500" placeholder="Enter account number">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Account Name</label>
                                <input type="text" name="account_name" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-slate-500 focus:border-slate-500" placeholder="Enter account holder name">
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Notes (Optional)</label>
                            <textarea name="notes" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-slate-500 focus:border-slate-500" placeholder="Add any payment notes..."></textarea>
                        </div>

                        <button type="submit" class="w-full px-4 py-2.5 bg-slate-700 text-white text-sm font-medium rounded-lg hover:bg-slate-800 transition-colors">
                            Process Payment
                        </button>
                    </form>
                    @else
                    <div class="text-center py-6">
                        <svg class="w-12 h-12 text-gray-300 mx-auto mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <p class="text-sm text-gray-500">This expense is not eligible for payment</p>
                        <p class="text-xs text-gray-400 mt-1">Status: {{ ucfirst($expense->status) }}</p>
                    </div>
                    @endif
                </div>

                <!-- Flag/Unflag Option -->
                <div class="bg-white rounded-lg border border-gray-200 p-5">
                    <h3 class="text-base font-semibold text-gray-900 mb-4">Flag Options</h3>
                    @if($expense->is_flagged)
                    <div class="mb-4 p-3 bg-gray-50 rounded-lg">
                        <p class="text-sm text-gray-700"><span class="font-medium">Flagged by:</span> {{ $expense->flaggedByUser->name ?? 'N/A' }}</p>
                        <p class="text-sm text-gray-700 mt-1"><span class="font-medium">Reason:</span> {{ $expense->flag_reason }}</p>
                        <p class="text-xs text-gray-500 mt-1">{{ $expense->flagged_at?->format('d M Y H:i') }}</p>
                    </div>
                    <form action="{{ route('payments.unflag', $expense) }}" method="POST">
                        @csrf
                        <button type="submit" class="w-full px-4 py-2 bg-gray-100 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-200 transition-colors">
                            Remove Flag
                        </button>
                    </form>
                    @else
                    <form action="{{ route('payments.flag', $expense) }}" method="POST" class="space-y-3">
                        @csrf
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Reason for flagging</label>
                            <textarea name="reason" rows="3" required class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-slate-500 focus:border-slate-500" placeholder="Explain why this expense should be flagged..."></textarea>
                        </div>
                        <button type="submit" class="w-full px-4 py-2 bg-gray-100 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-200 transition-colors">
                            Flag Expense
                        </button>
                    </form>
                    @endif
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        document.getElementById('payment_method').addEventListener('change', function() {
            var bankDetails = document.getElementById('bankDetails');
            var onlinePaymentInfo = document.getElementById('onlinePaymentInfo');
            var selectedMethod = this.value;
            
            // Online payment methods (QRIS, VA, PayPal)
            var onlineMethods = ['qris', 'bni_va', 'bri_va', 'cimb_niaga_va', 'permata_va', 'maybank_va', 'sampoerna_va', 'bnc_va', 'artha_graha_va', 'atm_bersama_va', 'paypal'];
            
            if (selectedMethod === 'bank_transfer') {
                bankDetails.classList.remove('hidden');
                onlinePaymentInfo.classList.add('hidden');
            } else if (onlineMethods.includes(selectedMethod)) {
                bankDetails.classList.add('hidden');
                onlinePaymentInfo.classList.remove('hidden');
            } else {
                bankDetails.classList.add('hidden');
                onlinePaymentInfo.classList.add('hidden');
            }
        });
    </script>
    @endpush
</x-app-layout>
