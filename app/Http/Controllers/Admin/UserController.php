<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Role;
use App\Services\AuditService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    protected AuditService $auditService;

    public function __construct(AuditService $auditService)
    {
        $this->auditService = $auditService;
    }

    public function index(Request $request)
    {
        $query = User::with('role');

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                    ->orWhere('email', 'like', '%' . $request->search . '%')
                    ->orWhere('employee_id', 'like', '%' . $request->search . '%');
            });
        }

        if ($request->filled('role_id')) {
            $query->where('role_id', $request->role_id);
        }

        if ($request->filled('department')) {
            $query->where('department', $request->department);
        }

        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active');
        }

        $users = $query->latest()->paginate(15)->withQueryString();
        $roles = Role::all();
        $departments = User::whereNotNull('department')
            ->distinct()
            ->pluck('department');

        return view('admin.users.index', compact('users', 'roles', 'departments'));
    }

    public function create()
    {
        $roles = Role::all();
        $managers = User::whereHas('role', fn($q) => $q->where('slug', 'manager'))
            ->active()
            ->get();
        $departments = User::whereNotNull('department')
            ->distinct()
            ->pluck('department');

        return view('admin.users.create', compact('roles', 'managers', 'departments'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'role_id' => 'required|exists:roles,id',
            'employee_id' => 'nullable|string|unique:users,employee_id',
            'department' => 'nullable|string|max:100',
            'position' => 'nullable|string|max:100',
            'manager_id' => 'nullable|exists:users,id',
            'phone' => 'nullable|string|max:20',
            'is_active' => 'boolean',
        ]);

        $validated['password'] = Hash::make($validated['password']);
        $validated['is_active'] = $request->boolean('is_active', true);

        $user = User::create($validated);

        $this->auditService->logCreate(
            User::class,
            $user->id,
            "User {$user->name} created",
            ['name' => $user->name, 'email' => $user->email, 'role' => $user->role->name]
        );

        return redirect()
            ->route('admin.users.index')
            ->with('success', 'User created successfully.');
    }

    public function show(User $user)
    {
        $user->load(['role', 'manager', 'subordinates', 'expenses' => function ($q) {
            $q->latest()->take(5);
        }]);

        return view('admin.users.show', compact('user'));
    }

    public function edit(User $user)
    {
        $roles = Role::all();
        $managers = User::whereHas('role', fn($q) => $q->where('slug', 'manager'))
            ->where('id', '!=', $user->id)
            ->active()
            ->get();
        $departments = User::whereNotNull('department')
            ->distinct()
            ->pluck('department');

        return view('admin.users.edit', compact('user', 'roles', 'managers', 'departments'));
    }

    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'email', Rule::unique('users')->ignore($user->id)],
            'password' => 'nullable|string|min:8|confirmed',
            'role_id' => 'required|exists:roles,id',
            'employee_id' => ['nullable', 'string', Rule::unique('users')->ignore($user->id)],
            'department' => 'nullable|string|max:100',
            'position' => 'nullable|string|max:100',
            'manager_id' => 'nullable|exists:users,id',
            'phone' => 'nullable|string|max:20',
            'is_active' => 'boolean',
        ]);

        $oldValues = $user->only(['name', 'email', 'role_id', 'department', 'position', 'is_active']);

        if (!empty($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }

        $validated['is_active'] = $request->boolean('is_active', true);

        $user->update($validated);

        $this->auditService->logUpdate(
            User::class,
            $user->id,
            "User {$user->name} updated",
            $oldValues,
            $user->only(['name', 'email', 'role_id', 'department', 'position', 'is_active'])
        );

        return redirect()
            ->route('admin.users.index')
            ->with('success', 'User updated successfully.');
    }

    public function destroy(User $user)
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', 'You cannot delete your own account.');
        }

        $this->auditService->logDelete(
            User::class,
            $user->id,
            "User {$user->name} deleted",
            $user->toArray()
        );

        $user->delete();

        return redirect()
            ->route('admin.users.index')
            ->with('success', 'User deleted successfully.');
    }

    public function toggleStatus(User $user)
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', 'You cannot deactivate your own account.');
        }

        $user->is_active = !$user->is_active;
        $user->save();

        $status = $user->is_active ? 'activated' : 'deactivated';

        $this->auditService->logUpdate(
            User::class,
            $user->id,
            "User {$user->name} {$status}",
            ['is_active' => !$user->is_active],
            ['is_active' => $user->is_active]
        );

        return back()->with('success', "User {$status} successfully.");
    }
}
