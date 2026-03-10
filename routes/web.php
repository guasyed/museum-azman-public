<?php

use App\Http\Controllers\AdminUserController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ArtworkController;
use App\Http\Controllers\ArtistController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\LocationController;
use App\Http\Controllers\MovementController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SettingController;
use Illuminate\Support\Facades\Route;

use Illuminate\Support\Facades\Artisan;

Route::get('/optimize-clear', function () {
    // Jalankan arahan artisan
    Artisan::call('optimize:clear');
    Artisan::call('cache:clear');
    Artisan::call('config:clear');
    Artisan::call('route:clear');
    Artisan::call('view:clear');

    return response()->json([
        'status' => 'success',
        'message' => 'Cache, config, route, dan view telah dibersihkan!'
    ]);
});


Route::middleware('guest')->group(function () {
	Route::get('login', [AuthController::class, 'showLogin'])->name('login');
	Route::post('login', [AuthController::class, 'login'])->name('login.perform');
	Route::get('forgot-password', [AuthController::class, 'showForgotPassword'])->name('password.request');
	Route::post('forgot-password', [AuthController::class, 'sendResetLink'])->name('password.email');
	Route::get('reset-password/{token}', [AuthController::class, 'showResetPassword'])->name('password.reset');
	Route::post('reset-password', [AuthController::class, 'resetPassword'])->name('password.update');
	Route::get('register', [AuthController::class, 'showRegister'])->name('register');
	Route::post('register', [AuthController::class, 'register'])->name('register.perform');
});
Route::get('/manifest.json', function () {
    return response()->file(public_path('manifest.json'));
});


Route::middleware('auth')->group(function () {
	Route::post('logout', [AuthController::class, 'logout'])->name('logout');
	Route::get('profile', [ProfileController::class, 'edit'])->name('profile.edit');
	Route::put('profile', [ProfileController::class, 'update'])->name('profile.update');
	Route::put('profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password.update');

	Route::get('/', DashboardController::class)->name('dashboard');
	Route::get('artworks/suggestions', [ArtworkController::class, 'suggestions'])->name('artworks.suggestions');
	Route::get('artworks/export/pdf', [ArtworkController::class, 'exportPdf'])->name('artworks.export.pdf');
	Route::resource('artworks', ArtworkController::class);
	Route::get('movements', [MovementController::class, 'index'])->name('movements.index');
	Route::post('movements', [MovementController::class, 'store'])->name('movements.store');
	Route::get('movements/{movement}/edit', [MovementController::class, 'edit'])->name('movements.edit')->middleware('admin');
	Route::put('movements/{movement}', [MovementController::class, 'update'])->name('movements.update')->middleware('admin');
	Route::get('locations', [LocationController::class, 'index'])->name('locations.index');
	Route::get('locations/create', [LocationController::class, 'create'])->name('locations.create')->middleware('admin');
	Route::post('locations', [LocationController::class, 'store'])->name('locations.store')->middleware('admin');
	Route::get('locations/{location}', [LocationController::class, 'show'])->name('locations.show');
	Route::get('locations/{location}/edit', [LocationController::class, 'edit'])->name('locations.edit')->middleware('admin');
	Route::put('locations/{location}', [LocationController::class, 'update'])->name('locations.update')->middleware('admin');
	Route::get('artists', [ArtistController::class, 'index'])->name('artists.index');
	Route::get('reports', [ReportController::class, 'index'])->name('reports.index');
	Route::get('reports/export/pdf', [ReportController::class, 'exportPdf'])->name('reports.export.pdf');
	Route::get('reports/export/excel', [ReportController::class, 'exportExcel'])->name('reports.export.excel');
	Route::get('settings', [SettingController::class, 'index'])->name('settings.index');
	Route::post('settings/{section}', [SettingController::class, 'update'])->name('settings.update');
	Route::post('settings/backup/generate', [SettingController::class, 'generateBackup'])->name('settings.backup.generate')->middleware('admin');
	Route::get('settings/backup/download', [SettingController::class, 'downloadBackup'])->name('settings.backup.download')->middleware('admin');
	Route::post('settings/backup/delete', [SettingController::class, 'deleteBackup'])->name('settings.backup.delete')->middleware('admin');

	Route::prefix('admin')->name('admin.')->middleware('admin')->group(function () {
		Route::get('technical-documentation', function () {
			$docPath = base_path('docs/Museum-Azman-Technical-Documentation.html');

			if (! file_exists($docPath)) {
				abort(404, 'Technical documentation file not found.');
			}

			return response()->file($docPath, [
				'Content-Type' => 'text/html; charset=UTF-8',
				'X-Robots-Tag' => 'noindex, nofollow',
			]);
		})->name('docs.technical');

		Route::get('users', [AdminUserController::class, 'index'])->name('users.index');
		Route::post('users', [AdminUserController::class, 'store'])->name('users.store');
		Route::get('users/{user}/edit', [AdminUserController::class, 'edit'])->name('users.edit');
		Route::put('users/{user}', [AdminUserController::class, 'update'])->name('users.update');
		Route::patch('users/{user}/approve', [AdminUserController::class, 'approve'])->name('users.approve');
		Route::delete('users/{user}', [AdminUserController::class, 'destroy'])->name('users.destroy');
	});
});
