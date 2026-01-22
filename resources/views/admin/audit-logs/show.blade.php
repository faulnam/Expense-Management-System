<x-app-layout>
    <div class="space-y-6">
        <!-- Back Button & Header -->
        <div class="flex items-center gap-4">
            <a href="{{ route('admin.audit-logs.index') }}" class="p-2 text-gray-500 hover:text-gray-700 hover:bg-gray-100 rounded-lg transition-colors">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
            </a>
            <div>
                <h1 class="text-2xl font-semibold text-gray-900">Audit Log Details</h1>
                <p class="text-sm text-gray-500 mt-1">ID: {{ $auditLog->id }}</p>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Main Info -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Action Info -->
                <div class="bg-white rounded-lg border border-gray-200 p-5">
                    <h3 class="text-base font-semibold text-gray-900 mb-4">Action Information</h3>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <p class="text-xs font-medium text-gray-500 uppercase">Action</p>
                            <span class="inline-flex mt-1 px-2.5 py-1 text-sm font-medium rounded-lg
                                @switch($auditLog->action)
                                    @case('created') bg-emerald-100 text-emerald-800 @break
                                    @case('updated') bg-indigo-100 text-indigo-800 @break
                                    @case('deleted') bg-rose-100 text-rose-800 @break
                                    @case('approved') bg-emerald-100 text-emerald-800 @break
                                    @case('rejected') bg-rose-100 text-rose-800 @break
                                    @case('submitted') bg-amber-100 text-amber-800 @break
                                    @case('login') bg-cyan-100 text-cyan-800 @break
                                    @case('logout') bg-gray-100 text-gray-700 @break
                                    @default bg-gray-100 text-gray-700
                                @endswitch">
                                {{ ucfirst($auditLog->action) }}
                            </span>
                        </div>
                        <div>
                            <p class="text-xs font-medium text-gray-500 uppercase">Timestamp</p>
                            <p class="text-sm text-gray-900 mt-1">{{ $auditLog->created_at->format('d M Y H:i:s') }}</p>
                        </div>
                        <div>
                            <p class="text-xs font-medium text-gray-500 uppercase">Model Type</p>
                            <p class="text-sm text-gray-900 mt-1">{{ class_basename($auditLog->auditable_type) }}</p>
                        </div>
                        <div>
                            <p class="text-xs font-medium text-gray-500 uppercase">Model ID</p>
                            <p class="text-sm text-gray-900 mt-1">{{ $auditLog->auditable_id ?? '-' }}</p>
                        </div>
                        <div class="col-span-2">
                            <p class="text-xs font-medium text-gray-500 uppercase">Description</p>
                            <p class="text-sm text-gray-900 mt-1">{{ $auditLog->description }}</p>
                        </div>
                    </div>
                </div>

                <!-- Changes Data -->
                @if($auditLog->old_values || $auditLog->new_values)
                <div class="bg-white rounded-lg border border-gray-200 p-5">
                    <h3 class="text-base font-semibold text-gray-900 mb-4">Changes</h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        @if($auditLog->old_values)
                        <div>
                            <p class="text-xs font-medium text-gray-500 uppercase mb-2">Old Values</p>
                            <div class="bg-rose-50 border border-rose-200 rounded-lg p-3">
                                <pre class="text-xs text-rose-800 overflow-x-auto">{{ json_encode($auditLog->old_values, JSON_PRETTY_PRINT) }}</pre>
                            </div>
                        </div>
                        @endif
                        
                        @if($auditLog->new_values)
                        <div>
                            <p class="text-xs font-medium text-gray-500 uppercase mb-2">New Values</p>
                            <div class="bg-emerald-50 border border-emerald-200 rounded-lg p-3">
                                <pre class="text-xs text-emerald-800 overflow-x-auto">{{ json_encode($auditLog->new_values, JSON_PRETTY_PRINT) }}</pre>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
                @endif

                <!-- Metadata -->
                @if($auditLog->metadata)
                <div class="bg-white rounded-lg border border-gray-200 p-5">
                    <h3 class="text-base font-semibold text-gray-900 mb-4">Additional Data</h3>
                    <div class="bg-gray-50 border border-gray-200 rounded-lg p-3">
                        <pre class="text-xs text-gray-700 overflow-x-auto">{{ json_encode($auditLog->metadata, JSON_PRETTY_PRINT) }}</pre>
                    </div>
                </div>
                @endif
            </div>

            <!-- Sidebar -->
            <div class="space-y-6">
                <!-- User Info -->
                <div class="bg-white rounded-lg border border-gray-200 p-5">
                    <h3 class="text-base font-semibold text-gray-900 mb-4">Performed By</h3>
                    @if($auditLog->user)
                    <div class="flex items-center gap-3">
                        <div class="w-12 h-12 rounded-full bg-slate-200 flex items-center justify-center text-slate-700 font-semibold">
                            {{ strtoupper(substr($auditLog->user->name, 0, 1)) }}
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-900">{{ $auditLog->user->name }}</p>
                            <p class="text-xs text-gray-500">{{ $auditLog->user->email }}</p>
                            <p class="text-xs text-gray-400">{{ $auditLog->user->role->name ?? 'N/A' }}</p>
                        </div>
                    </div>
                    @else
                    <p class="text-sm text-gray-500">System Action</p>
                    @endif
                </div>

                <!-- Request Info -->
                <div class="bg-slate-50 rounded-lg border border-slate-200 p-5">
                    <h3 class="text-base font-semibold text-slate-900 mb-3">Request Info</h3>
                    <div class="space-y-2 text-sm">
                        <div class="flex justify-between">
                            <span class="text-gray-500">IP Address</span>
                            <span class="font-mono text-gray-900">{{ $auditLog->ip_address ?? '-' }}</span>
                        </div>
                        <div class="flex flex-col">
                            <span class="text-gray-500 mb-1">User Agent</span>
                            <span class="text-xs text-gray-700 break-words">{{ Str::limit($auditLog->user_agent ?? '-', 100) }}</span>
                        </div>
                    </div>
                </div>

                <!-- Timeline -->
                <div class="bg-white rounded-lg border border-gray-200 p-5">
                    <h3 class="text-base font-semibold text-gray-900 mb-3">Timeline</h3>
                    <div class="space-y-2 text-sm">
                        <div class="flex justify-between">
                            <span class="text-gray-500">Date</span>
                            <span class="text-gray-900">{{ $auditLog->created_at->format('d M Y') }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-500">Time</span>
                            <span class="text-gray-900">{{ $auditLog->created_at->format('H:i:s') }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-500">Time Ago</span>
                            <span class="text-gray-900">{{ $auditLog->created_at->diffForHumans() }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
