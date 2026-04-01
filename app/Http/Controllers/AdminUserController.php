<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\User;
use App\Notifications\NewUserRegistrationNotification;
use App\Services\ActivityLogger;
use App\Services\ImageOptimizer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AdminUserController extends Controller
{
    public function __construct(private readonly ImageOptimizer $imageOptimizer)
    {
    }

    public function index(): View
    {
        $sort = request()->query('sort', 'name');
        $direction = strtolower((string) request()->query('direction', 'asc')) === 'desc' ? 'desc' : 'asc';

        // Allowlist sortable columns to keep ordering predictable and safe.
        $sortableColumns = ['name', 'email', 'role', 'status'];
        $sortColumn = in_array($sort, $sortableColumns, true) ? $sort : 'name';

        $usersQuery = User::query()->with('roleRelation');

        if ($sortColumn === 'role') {
            $usersQuery
                ->leftJoin('roles as sort_roles', 'sort_roles.id', '=', 'users.role_id')
                ->select('users.*')
                ->orderBy('sort_roles.name', $direction)
                ->orderBy('users.name');
        } elseif ($sortColumn === 'status') {
            $usersQuery
                ->orderBy('users.is_approved', $direction)
                ->orderBy('users.name');
        } else {
            $usersQuery->orderBy($sortColumn, $direction);
        }

        $users = $usersQuery->get();
        $roles = Role::query()->orderBy('name')->get();
        $registrationNotifications = collect();
        if (Schema::hasTable('notifications')) {
            $registrationNotifications = request()->user()
                ?->unreadNotifications()
                ->where('type', NewUserRegistrationNotification::class)
                ->latest()
                ->take(8)
                ->get() ?? collect();
        }

        return view('admin.users.index', compact('users', 'roles', 'sortColumn', 'direction', 'registrationNotifications'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'role_id' => ['required', 'exists:roles,id'],
            'password' => ['required', 'string', 'min:8'],
            'avatar' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:10240'],
        ]);

        $role = Role::query()->findOrFail((int) $validated['role_id']);
        $validated['role'] = $role->slug === 'admin' ? 'admin' : 'user';

        if ($request->hasFile('avatar')) {
            $stored = $this->imageOptimizer->storeUploaded($request->file('avatar'), 'avatars');
            if ($stored && isset($stored['path'])) {
                $validated['avatar_path'] = $stored['path'];
            }
        }

        $validated['password'] = Hash::make($validated['password']);
        $validated['is_approved'] = true;
        $validated['approved_at'] = now();

        $newUser = User::query()->create($validated);

        ActivityLogger::log('admin.user_created', "Admin created user: {$newUser->name} ({$newUser->email})", $newUser);

        return redirect()->route('admin.users.index')->with('success', 'User created successfully.');
    }

    public function edit(User $user): View
    {
        $user->load('roleRelation');
        $roles = Role::query()->orderBy('name')->get();

        return view('admin.users.edit', compact('user', 'roles'));
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'role_id' => ['required', 'exists:roles,id'],
            'password' => ['nullable', 'string', 'min:8'],
            'avatar' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:10240'],
        ]);

        $role = Role::query()->findOrFail((int) $validated['role_id']);
        $validated['role'] = $role->slug === 'admin' ? 'admin' : 'user';

        if ($request->hasFile('avatar')) {
            $oldPath = $user->avatar_path;

            $stored = $this->imageOptimizer->storeUploaded($request->file('avatar'), 'avatars');
            if ($stored && isset($stored['path'])) {
                $validated['avatar_path'] = $stored['path'];

                if ($oldPath) {
                    Storage::disk('public')->delete($oldPath);
                }
            }
        }

        if (! empty($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }

        $user->update($validated);

        ActivityLogger::log('admin.user_updated', "Admin updated user: {$user->name} ({$user->email})", $user);

        return redirect()->route('admin.users.index')->with('success', 'User updated successfully.');
    }

    public function approve(User $user): RedirectResponse
    {
        if ($user->isApproved()) {
            return redirect()->route('admin.users.index')->with('success', 'User is already approved.');
        }

        $user->forceFill([
            'is_approved' => true,
            'approved_at' => now(),
        ])->save();

        ActivityLogger::log('admin.user_approved', "Admin approved user: {$user->name} ({$user->email})", $user);

        $currentAdmin = request()->user();
        if ($currentAdmin && Schema::hasTable('notifications')) {
            $currentAdmin->unreadNotifications()
                ->where('type', NewUserRegistrationNotification::class)
                ->where('data->user_id', $user->id)
                ->update(['read_at' => now()]);
        }

        return redirect()->route('admin.users.index')->with('success', 'User approved successfully.');
    }

    public function destroy(User $user): RedirectResponse
    {
        $currentUserId = request()->user()?->id;
        if ($currentUserId !== null && (int) $currentUserId === (int) $user->id) {
            return redirect()->route('admin.users.index')->withErrors([
                'users' => 'You cannot delete your own account.',
            ]);
        }

        $userName = $user->name;
        $userEmail = $user->email;

        if ($user->avatar_path) {
            Storage::disk('public')->delete($user->avatar_path);
        }

        $user->delete();

        ActivityLogger::log('admin.user_deleted', "Admin deleted user: {$userName} ({$userEmail})");

        return redirect()->route('admin.users.index')->with('success', 'User deleted successfully.');
    }
}
