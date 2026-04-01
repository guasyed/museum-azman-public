<?php

namespace App\Http\Controllers;

use App\Services\ImageOptimizer;
use App\Services\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function __construct(private readonly ImageOptimizer $imageOptimizer)
    {
    }

    public function edit(Request $request): View
    {
        return view('profile.edit', [
            'user' => $request->user(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $user = $request->user();

        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'avatar' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:10240'],
        ];

        if ($user->isAdmin()) {
            $rules['email'] = ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)];
        }

        $validated = $request->validate($rules);

        if (! $user->isAdmin()) {
            unset($validated['email']);
        }

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

        $user->update($validated);

        ActivityLogger::log('profile.updated', "Profile updated by: {$user->name}", $user);

        return redirect()->route('profile.edit')->with('success', 'Profile details updated successfully.');
    }

    public function updatePassword(Request $request): RedirectResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'string', 'min:8', 'confirmed', 'different:current_password'],
        ]);

        $user->forceFill([
            'password' => Hash::make($validated['password']),
        ])->save();

        ActivityLogger::log('profile.password_changed', "Password changed by: {$user->name}", $user);

        return redirect()->route('profile.edit')->with('success', 'Password updated successfully.');
    }
}
