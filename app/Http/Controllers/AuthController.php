<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\User;
use App\Notifications\NewUserRegistrationNotification;
use App\Services\ActivityLogger;
use App\Services\ImageOptimizer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function __construct(private readonly ImageOptimizer $imageOptimizer)
    {
    }

    public function showLogin(): View|RedirectResponse
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }

        return view('auth.login');
    }

    public function showRegister(): View|RedirectResponse
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }

        $requestableRoles = Role::query()
            ->whereNotIn('slug', ['owner', 'admin'])
            ->orderBy('name')
            ->get();

        return view('auth.register', compact('requestableRoles'));
    }

    public function showForgotPassword(): View|RedirectResponse
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }

        return view('auth.forgot-password');
    }

    public function sendResetLink(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email'],
        ]);

        $status = Password::sendResetLink([
            'email' => $validated['email'],
        ]);

        if ($status === Password::RESET_LINK_SENT) {
            return back()->with('success', 'Reset link sent to your email address.');
        }

        return back()->withErrors([
            'email' => __($status),
        ])->onlyInput('email');
    }

    public function showResetPassword(Request $request, string $token): View|RedirectResponse
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }

        return view('auth.reset-password', [
            'token' => $token,
            'email' => (string) $request->string('email'),
        ]);
    }

    public function resetPassword(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'token' => ['required', 'string'],
            'email' => ['required', 'email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $status = Password::reset(
            $validated,
            function (User $user, string $password): void {
                $user->forceFill([
                    'password' => Hash::make($password),
                    'remember_token' => Str::random(60),
                ])->save();
            }
        );

        if ($status === Password::PASSWORD_RESET) {
            return redirect()->route('login')->with('success', 'Password has been reset. You can sign in now.');
        }

        return back()->withErrors([
            'email' => __($status),
        ])->onlyInput('email');
    }

    public function register(Request $request): RedirectResponse
    {
        $requestableRoleIds = Role::query()
            ->whereNotIn('slug', ['owner', 'admin'])
            ->pluck('id')
            ->all();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'role_id' => ['required', 'integer', Rule::in($requestableRoleIds)],
            'avatar' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:10240'],
        ]);

        $userPayload = [
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => 'user',
            'role_id' => (int) $validated['role_id'],
            'is_approved' => false,
            'approved_at' => null,
        ];

        if ($request->hasFile('avatar')) {
            $stored = $this->imageOptimizer->storeUploaded($request->file('avatar'), 'avatars');
            if ($stored && isset($stored['path'])) {
                $userPayload['avatar_path'] = $stored['path'];
            }
        }

        $user = User::query()->create($userPayload);
        $user->load('roleRelation');

        ActivityLogger::log('auth.register', "New user registered: {$user->name} ({$user->email})", $user);

        if (Schema::hasTable('notifications')) {
            $admins = User::query()
                ->with('roleRelation')
                ->get()
                ->filter(fn (User $candidate) => $candidate->isAdmin() && $candidate->isApproved())
                ->unique('id');

            foreach ($admins as $admin) {
                $admin->notify(new NewUserRegistrationNotification($user));
            }
        }

        return redirect()->route('login')->with('success', 'Registration submitted. Please wait for admin approval before signing in.');
    }

    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (! Auth::attempt($credentials, false)) {
            return back()->withErrors([
                'email' => 'Invalid email or password.',
            ])->onlyInput('email');
        }

        $authenticatedUser = $request->user();
        if ($authenticatedUser && ! ((bool) ($authenticatedUser->is_approved ?? true))) {
            Auth::logout();

            return back()->withErrors([
                'email' => 'Your account is pending admin approval.',
            ])->onlyInput('email');
        }

        $request->session()->regenerate();

        ActivityLogger::log('auth.login', "User logged in: {$request->user()?->name}", $request->user());

        return redirect()->intended(route('dashboard'));
    }

    public function logout(Request $request): RedirectResponse
    {
        $user = $request->user();
        ActivityLogger::log('auth.logout', "User logged out: {$user?->name}", $user);

        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
