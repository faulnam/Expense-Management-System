<x-guest-layout>
    <!-- Error Message -->
    @if ($errors->any())
        <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-xl">
            <p class="text-sm text-red-600">{{ $errors->first() }}</p>
        </div>
    @endif

    <h2 class="text-xl font-semibold text-gray-900 mb-1">Sign In</h2>
    <p class="text-gray-500 text-sm mb-6">Enter your credentials to access your account</p>

    <!-- Login Form -->
    <form method="POST" action="{{ route('login') }}">
        @csrf

        <div class="space-y-5">
            <!-- Email -->
            <div>
                <label for="email" class="block text-sm font-medium text-gray-700 mb-1.5">Email Address</label>
                <input 
                    type="email" 
                    id="email" 
                    name="email" 
                    value="{{ old('email') }}"
                    required 
                    autofocus
                    class="w-full px-4 py-2.5 border border-gray-300 rounded-xl text-gray-900 text-sm focus:outline-none focus:ring-2 focus:ring-slate-500 focus:border-transparent transition"
                    placeholder="you@company.com"
                >
            </div>

            <!-- Password -->
            <div>
                <label for="password" class="block text-sm font-medium text-gray-700 mb-1.5">Password</label>
                <input 
                    type="password" 
                    id="password" 
                    name="password" 
                    required
                    class="w-full px-4 py-2.5 border border-gray-300 rounded-xl text-gray-900 text-sm focus:outline-none focus:ring-2 focus:ring-slate-500 focus:border-transparent transition"
                    placeholder="••••••••"
                >
            </div>

            <!-- Remember -->
            <div class="flex items-center justify-between">
                <label class="flex items-center cursor-pointer">
                    <input type="checkbox" name="remember" class="w-4 h-4 text-slate-600 border-gray-300 rounded focus:ring-slate-500">
                    <span class="ml-2 text-sm text-gray-600">Remember me</span>
                </label>
            </div>

            <!-- Submit -->
            <button type="submit" class="w-full py-3 bg-slate-800 hover:bg-slate-700 text-white text-sm font-semibold rounded-xl transition-colors focus:outline-none focus:ring-2 focus:ring-slate-500 focus:ring-offset-2">
                Sign In
            </button>
        </div>
    </form>

    <!-- Demo Accounts -->
    <div class="mt-8 pt-6 border-t border-gray-200">
        <p class="text-xs text-gray-400 text-center mb-3">Demo Accounts (password: <code class="bg-gray-100 px-1.5 py-0.5 rounded text-gray-600">password</code>)</p>
        <div class="grid grid-cols-1 gap-2 text-xs">
            <div class="flex items-center justify-between p-2 bg-gray-50 rounded-lg">
                <span class="text-gray-500">Admin</span>
                <span class="text-gray-700 font-mono">admin@company.com</span>
            </div>
            <div class="flex items-center justify-between p-2 bg-gray-50 rounded-lg">
                <span class="text-gray-500">Finance</span>
                <span class="text-gray-700 font-mono">finance@company.com</span>
            </div>
            <div class="flex items-center justify-between p-2 bg-gray-50 rounded-lg">
                <span class="text-gray-500">Manager</span>
                <span class="text-gray-700 font-mono">manager.it@company.com</span>
            </div>
        </div>
    </div>
</x-guest-layout>
