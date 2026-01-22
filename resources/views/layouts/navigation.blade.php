<nav x-data="{ open: false }" class="bg-slate-800 border-b border-slate-700">
    <!-- Primary Navigation Menu -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex">
                <!-- Logo -->
                <div class="shrink-0 flex items-center">
                    <a href="{{ route('dashboard') }}" class="flex items-center">
                        <svg class="h-8 w-8 text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                        </svg>
                        <span class="ml-2 text-white font-semibold text-lg">EMS</span>
                    </a>
                </div>

                <!-- Navigation Links -->
                <div class="hidden space-x-4 sm:-my-px sm:ms-10 sm:flex">
                    <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')" class="text-gray-300 hover:text-white">
                        {{ __('Dashboard') }}
                    </x-nav-link>

                    <x-nav-link :href="route('expenses.index')" :active="request()->routeIs('expenses.*')" class="text-gray-300 hover:text-white">
                        {{ __('Expenses') }}
                    </x-nav-link>

                    @if(auth()->user()->isManager() || auth()->user()->isAdmin())
                    <x-nav-link :href="route('approvals.index')" :active="request()->routeIs('approvals.*')" class="text-gray-300 hover:text-white">
                        {{ __('Approvals') }}
                    </x-nav-link>
                    @endif

                    @if(auth()->user()->isFinance() || auth()->user()->isAdmin())
                    <x-nav-link :href="route('payments.index')" :active="request()->routeIs('payments.*')" class="text-gray-300 hover:text-white">
                        {{ __('Payments') }}
                    </x-nav-link>
                    @endif

                    <x-nav-link :href="route('reports.index')" :active="request()->routeIs('reports.*')" class="text-gray-300 hover:text-white">
                        {{ __('Reports') }}
                    </x-nav-link>

                    @if(auth()->user()->isAdmin())
                    <x-dropdown align="left" width="48">
                        <x-slot name="trigger">
                            <button class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-300 hover:text-white focus:outline-none transition ease-in-out duration-150 {{ request()->routeIs('admin.*') ? 'text-white' : '' }}">
                                <span>Admin</span>
                                <svg class="ms-1 h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                            </button>
                        </x-slot>

                        <x-slot name="content">
                            <x-dropdown-link :href="route('admin.users.index')">
                                {{ __('Users') }}
                            </x-dropdown-link>
                            <x-dropdown-link :href="route('admin.categories.index')">
                                {{ __('Categories') }}
                            </x-dropdown-link>
                            <x-dropdown-link :href="route('admin.budgets.index')">
                                {{ __('Budgets') }}
                            </x-dropdown-link>
                            <x-dropdown-link :href="route('admin.audit-logs.index')">
                                {{ __('Audit Logs') }}
                            </x-dropdown-link>
                        </x-slot>
                    </x-dropdown>
                    @endif
                </div>
            </div>

            <!-- Settings Dropdown -->
            <div class="hidden sm:flex sm:items-center sm:ms-6">
                <x-dropdown align="right" width="48">
                    <x-slot name="trigger">
                        <button class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-300 bg-slate-700 hover:text-white hover:bg-slate-600 focus:outline-none transition ease-in-out duration-150">
                            <div class="flex flex-col items-end mr-2">
                                <span>{{ Auth::user()->name }}</span>
                                <span class="text-xs text-gray-400">{{ Auth::user()->role?->name ?? 'No Role' }}</span>
                            </div>

                            <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                            </svg>
                        </button>
                    </x-slot>

                    <x-slot name="content">
                        <x-dropdown-link :href="route('profile.edit')">
                            {{ __('Profile') }}
                        </x-dropdown-link>

                        <!-- Authentication -->
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf

                            <x-dropdown-link :href="route('logout')"
                                    onclick="event.preventDefault();
                                                this.closest('form').submit();">
                                {{ __('Log Out') }}
                            </x-dropdown-link>
                        </form>
                    </x-slot>
                </x-dropdown>
            </div>

            <!-- Hamburger -->
            <div class="-me-2 flex items-center sm:hidden">
                <button @click="open = ! open" class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-300 hover:bg-slate-700 focus:outline-none focus:bg-slate-700 focus:text-gray-300 transition duration-150 ease-in-out">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Responsive Navigation Menu -->
    <div :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden bg-slate-700">
        <div class="pt-2 pb-3 space-y-1">
            <x-responsive-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')" class="text-gray-300">
                {{ __('Dashboard') }}
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="route('expenses.index')" :active="request()->routeIs('expenses.*')" class="text-gray-300">
                {{ __('Expenses') }}
            </x-responsive-nav-link>
            @if(auth()->user()->isManager() || auth()->user()->isAdmin())
            <x-responsive-nav-link :href="route('approvals.index')" :active="request()->routeIs('approvals.*')" class="text-gray-300">
                {{ __('Approvals') }}
            </x-responsive-nav-link>
            @endif
            @if(auth()->user()->isFinance() || auth()->user()->isAdmin())
            <x-responsive-nav-link :href="route('payments.index')" :active="request()->routeIs('payments.*')" class="text-gray-300">
                {{ __('Payments') }}
            </x-responsive-nav-link>
            @endif
            <x-responsive-nav-link :href="route('reports.index')" :active="request()->routeIs('reports.*')" class="text-gray-300">
                {{ __('Reports') }}
            </x-responsive-nav-link>
            @if(auth()->user()->isAdmin())
            <x-responsive-nav-link :href="route('admin.users.index')" :active="request()->routeIs('admin.users.*')" class="text-gray-300">
                {{ __('Users') }}
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="route('admin.categories.index')" :active="request()->routeIs('admin.categories.*')" class="text-gray-300">
                {{ __('Categories') }}
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="route('admin.budgets.index')" :active="request()->routeIs('admin.budgets.*')" class="text-gray-300">
                {{ __('Budgets') }}
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="route('admin.audit-logs.index')" :active="request()->routeIs('admin.audit-logs.*')" class="text-gray-300">
                {{ __('Audit Logs') }}
            </x-responsive-nav-link>
            @endif
        </div>

        <!-- Responsive Settings Options -->
        <div class="pt-4 pb-1 border-t border-slate-600">
            <div class="px-4">
                <div class="font-medium text-base text-white">{{ Auth::user()->name }}</div>
                <div class="font-medium text-sm text-gray-400">{{ Auth::user()->email }}</div>
                <div class="font-medium text-xs text-gray-500">{{ Auth::user()->role?->name ?? 'No Role' }}</div>
            </div>

            <div class="mt-3 space-y-1">
                <x-responsive-nav-link :href="route('profile.edit')" class="text-gray-300">
                    {{ __('Profile') }}
                </x-responsive-nav-link>

                <!-- Authentication -->
                <form method="POST" action="{{ route('logout') }}">
                    @csrf

                    <x-responsive-nav-link :href="route('logout')" class="text-gray-300"
                            onclick="event.preventDefault();
                                        this.closest('form').submit();">
                        {{ __('Log Out') }}
                    </x-responsive-nav-link>
                </form>
            </div>
        </div>
    </div>
</nav>
