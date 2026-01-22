<x-app-layout>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Page Header -->
        <div class="mb-6">
            <h1 class="text-2xl font-bold text-gray-900">Audit Logs</h1>
            <p class="text-gray-500 mt-1">Track all system activities and user actions</p>
        </div>

        <!-- Filters -->
        <div class="bg-white rounded-xl border border-gray-200 p-4 mb-6">
            <form method="GET" action="{{ route('admin.audit-logs.index') }}" class="grid grid-cols-1 md:grid-cols-5 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">User</label>
                    <select name="user" class="w-full rounded-lg border-gray-300 text-sm focus:border-slate-500 focus:ring-slate-500">
                        <option value="">All Users</option>
                        @foreach($users ?? [] as $user)
                            <option value="{{ $user->id }}" {{ request('user') == $user->id ? 'selected' : '' }}>{{ $user->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Action</label>
                    <select name="action" class="w-full rounded-lg border-gray-300 text-sm focus:border-slate-500 focus:ring-slate-500">
                        <option value="">All Actions</option>
                        @foreach(['create', 'update', 'delete', 'submit', 'approve', 'reject', 'pay', 'flag', 'login', 'logout'] as $action)
                            <option value="{{ $action }}" {{ request('action') == $action ? 'selected' : '' }}>{{ ucfirst($action) }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Entity Type</label>
                    <select name="entity" class="w-full rounded-lg border-gray-300 text-sm focus:border-slate-500 focus:ring-slate-500">
                        <option value="">All Entities</option>
                        @foreach(['Expense', 'User', 'Approval', 'Payment', 'Budget', 'Category'] as $entity)
                            <option value="{{ $entity }}" {{ request('entity') == $entity ? 'selected' : '' }}>{{ $entity }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Date Range</label>
                    <input type="date" name="date" value="{{ request('date') }}" class="w-full rounded-lg border-gray-300 text-sm focus:border-slate-500 focus:ring-slate-500">
                </div>
                <div class="flex items-end gap-2">
                    <button type="submit" class="flex-1 bg-slate-800 text-white px-4 py-2 rounded-lg hover:bg-slate-700 text-sm font-medium transition-colors">Filter</button>
                    <a href="{{ route('admin.audit-logs.index') }}" class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 text-sm font-medium transition-colors">Reset</a>
                </div>
            </form>
        </div>

        <!-- Logs Table -->
        <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Timestamp</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">User</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Action</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Entity</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Description</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">IP Address</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($logs as $log)
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="px-4 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900">{{ $log->created_at->format('d M Y') }}</div>
                                    <div class="text-xs text-gray-500">{{ $log->created_at->format('H:i:s') }}</div>
                                </td>
                                <td class="px-4 py-4 whitespace-nowrap">
                                    <span class="text-sm text-gray-900">{{ $log->user->name ?? 'System' }}</span>
                                </td>
                                <td class="px-4 py-4 whitespace-nowrap">
                                    <span class="px-2.5 py-1 inline-flex text-xs font-semibold rounded-full 
                                        {{ in_array($log->action, ['create', 'approve', 'pay']) ? 'bg-emerald-100 text-emerald-800' : '' }}
                                        {{ in_array($log->action, ['update', 'submit']) ? 'bg-blue-100 text-blue-800' : '' }}
                                        {{ in_array($log->action, ['delete', 'reject', 'flag']) ? 'bg-red-100 text-red-800' : '' }}
                                        {{ in_array($log->action, ['login', 'logout']) ? 'bg-gray-100 text-gray-700' : '' }}">
                                        {{ ucfirst($log->action) }}
                                    </span>
                                </td>
                                <td class="px-4 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900">{{ $log->entity_type }}</div>
                                    @if($log->entity_id)
                                        <div class="text-xs text-gray-500">ID: {{ $log->entity_id }}</div>
                                    @endif
                                </td>
                                <td class="px-4 py-4">
                                    <span class="text-sm text-gray-600">{{ Str::limit($log->description, 60) }}</span>
                                    @if($log->old_values || $log->new_values)
                                        <button type="button" onclick="showDetails({{ json_encode($log) }})" class="text-slate-600 hover:text-slate-800 text-xs font-medium ml-2">[Details]</button>
                                    @endif
                                </td>
                                <td class="px-4 py-4 whitespace-nowrap">
                                    <span class="text-sm text-gray-500 font-mono">{{ $log->ip_address ?? 'N/A' }}</span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-16 text-center">
                                    <div class="flex flex-col items-center">
                                        <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mb-4">
                                            <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                            </svg>
                                        </div>
                                        <h3 class="text-sm font-semibold text-gray-900 mb-1">No audit logs</h3>
                                        <p class="text-sm text-gray-500">Audit logs will appear here as activities occur.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($logs->hasPages())
                <div class="bg-gray-50 px-4 py-3 border-t border-gray-200">
                    {{ $logs->links() }}
                </div>
            @endif
        </div>
    </div>

    <!-- Details Modal -->
    <div id="details-modal" class="hidden fixed inset-0 bg-gray-900/50 backdrop-blur-sm overflow-y-auto h-full w-full z-50">
        <div class="relative top-20 mx-auto max-w-2xl">
            <div class="bg-white rounded-xl shadow-xl border border-gray-200 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-5">Audit Log Details</h3>
                <div class="space-y-4">
                    <div>
                        <h4 class="text-sm font-medium text-gray-500 mb-1">Description</h4>
                        <p id="detail-description" class="text-sm text-gray-900"></p>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <h4 class="text-sm font-medium text-gray-500 mb-1">Old Values</h4>
                            <pre id="detail-old" class="text-xs bg-gray-50 p-3 rounded-lg border border-gray-200 overflow-auto max-h-48"></pre>
                        </div>
                        <div>
                            <h4 class="text-sm font-medium text-gray-500 mb-1">New Values</h4>
                            <pre id="detail-new" class="text-xs bg-gray-50 p-3 rounded-lg border border-gray-200 overflow-auto max-h-48"></pre>
                        </div>
                    </div>
                </div>
                <div class="flex justify-end mt-5 pt-4 border-t border-gray-200">
                    <button type="button" onclick="document.getElementById('details-modal').classList.add('hidden')" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">Close</button>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        function showDetails(log) {
            document.getElementById('detail-description').textContent = log.description || 'N/A';
            document.getElementById('detail-old').textContent = log.old_values ? JSON.stringify(JSON.parse(log.old_values), null, 2) : 'N/A';
            document.getElementById('detail-new').textContent = log.new_values ? JSON.stringify(JSON.parse(log.new_values), null, 2) : 'N/A';
            document.getElementById('details-modal').classList.remove('hidden');
        }
    </script>
    @endpush
</x-app-layout>
