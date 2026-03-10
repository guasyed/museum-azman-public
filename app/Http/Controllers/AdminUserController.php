<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\User;
use App\Services\ImageOptimizer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
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
        $users = User::query()->with('roleRelation')->orderBy('name')->get();
        $roles = Role::query()->orderBy('name')->get();

        return view('admin.users.index', compact('users', 'roles'));
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

        User::query()->create($validated);

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

        return redirect()->route('admin.users.index')->with('success', 'User updated successfully.');
    }
}
