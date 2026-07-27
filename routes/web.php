<?php

use App\Http\Controllers\AdminUserController;
use App\Http\Controllers\AdminActivityLogController;
use App\Http\Controllers\AdminImportController;
use App\Http\Controllers\AdminContactMessageController;
use App\Http\Controllers\AdminVisitRequestController;
use App\Http\Controllers\AdminMuseumEventController;
use App\Http\Controllers\AdminPublicArtistController;
use App\Http\Controllers\AdminPublicCollectionController;
use App\Http\Controllers\AdminAboutPageController;
use App\Http\Controllers\AdminHomePageController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ArtworkController;
use App\Http\Controllers\ArtistController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\LocationController;
use App\Http\Controllers\MovementController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\ContactMessageController;
use App\Http\Controllers\VisitRequestController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;

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
})->middleware(['auth', 'admin']);


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

Route::get('/storage/{path}', function (string $path) {
	$path = ltrim($path, '/');

	if (str_contains($path, '..')) {
		abort(404);
	}

	if (! Storage::disk('public')->exists($path)) {
		abort(404);
	}

	$absolutePath = Storage::disk('public')->path($path);
	$extension = strtolower(pathinfo($absolutePath, PATHINFO_EXTENSION));
	$mimeMap = [
		'webp' => 'image/webp',
		'png' => 'image/png',
		'jpg' => 'image/jpeg',
		'jpeg' => 'image/jpeg',
		'svg' => 'image/svg+xml',
		'gif' => 'image/gif',
		'avif' => 'image/avif',
		'ico' => 'image/x-icon',
	];
	$contentType = $mimeMap[$extension] ?? Storage::disk('public')->mimeType($path) ?? 'application/octet-stream';

	return response()->file($absolutePath, [
		'Content-Type' => $contentType,
		'Cache-Control' => 'public, max-age=31536000, immutable',
	]);
})->where('path', '.*');

$publicMuseumPage = function (string $publicPage = 'home') {
	$homeArtworks = \App\Models\Artwork::query()
		->with('artist')
		->whereNotNull('source_image_url')
		->where('source_image_url', '!=', '')
		->inRandomOrder()
		->take(18)
		->get();

	$publicEvents = collect();
	$eventContent = \App\Models\MuseumEvent::CONTENT_DEFAULTS;
	$eventsHeroImageUrl = null;
	$eventsStoryImageUrl = null;
	$publicArtistProfiles = collect();
	$artistsCmsConfigured = false;
	$artistContent = \App\Models\PublicArtistProfile::CONTENT_DEFAULTS;
	$publicCollectionItems = collect();
	$collectionCmsConfigured = false;
	$collectionContent = \App\Models\PublicCollectionItem::CONTENT_DEFAULTS;
	$aboutContent = \App\Support\AboutPageContent::DEFAULTS;
	$aboutHeroImageUrl = null;
	$aboutSpaceImageUrl = null;
	$homeContent = \App\Support\HomePageContent::DEFAULTS;
	$homeHeroVideoUrl = null;
	$homeHeroPosterUrl = null;
	$homeStoryImageUrl = null;
	$homeCustomProgrammeImageUrls = [];
	$homeCustomCollectionImageUrls = [];
	$homeExperienceBackgroundUrl = null;
	$homeFeaturedEvents = collect();
	$homeFeaturedArtists = collect();
	$homeSelectedWorks = collect();
	$homeStoryWork = null;
	if ($publicPage === 'home') {
		if (\Illuminate\Support\Facades\Schema::hasTable('settings')) {
			$homeContent = array_replace($homeContent, \App\Models\Setting::query()->whereIn('key', array_keys($homeContent))->pluck('value', 'key')->all());
			$videoPath = $homeContent['public_home_hero_video_path'];
			$posterPath = $homeContent['public_home_hero_poster_path'];
			$homeHeroVideoUrl = $videoPath && Storage::disk('public')->exists($videoPath) ? Storage::url($videoPath) : null;
			$homeHeroPosterUrl = $posterPath && Storage::disk('public')->exists($posterPath) ? Storage::url($posterPath) : null;
			$storyImagePath = $homeContent['public_home_story_image_path'];
			$homeStoryImageUrl = $storyImagePath && Storage::disk('public')->exists($storyImagePath) ? Storage::url($storyImagePath) : null;
			$experienceBackgroundPath = $homeContent['public_home_experience_background_path'];
			$homeExperienceBackgroundUrl = $experienceBackgroundPath && Storage::disk('public')->exists($experienceBackgroundPath) ? Storage::url($experienceBackgroundPath) : null;
			foreach (range(1, 3) as $slot) {
				$programmePath = $homeContent["public_home_programme_{$slot}_image_path"];
				$collectionPath = $homeContent["public_home_collection_{$slot}_image_path"];
				$homeCustomProgrammeImageUrls[$slot] = $programmePath && Storage::disk('public')->exists($programmePath) ? Storage::url($programmePath) : null;
				$homeCustomCollectionImageUrls[$slot] = $collectionPath && Storage::disk('public')->exists($collectionPath) ? Storage::url($collectionPath) : null;
			}
		}
		$homeSelectionSettings = \Illuminate\Support\Facades\Schema::hasTable('settings') ? \App\Models\Setting::query()->whereIn('key', ['public_home_featured_event_ids', 'public_home_featured_artist_ids', 'public_home_selected_work_ids', 'public_home_story_work_id'])->pluck('value', 'key') : collect();
		$orderedSelection = static function ($records, ?string $json, int $limit) {
			$ids = json_decode((string) $json, true);
			if (! is_array($ids) || $ids === []) return $records->take($limit)->values();
			return collect($ids)->map(fn ($id) => $records->firstWhere('id', (int) $id))->take($limit)->values();
		};
		if (\Illuminate\Support\Facades\Schema::hasTable('museum_events')) {
			$records = \App\Models\MuseumEvent::where('is_published', true)->orderBy('sort_order')->get();
			$homeFeaturedEvents = $orderedSelection($records, $homeSelectionSettings['public_home_featured_event_ids'] ?? null, 3);
		}
		if (\Illuminate\Support\Facades\Schema::hasTable('public_artist_profiles')) {
			$records = \App\Models\PublicArtistProfile::where('is_published', true)->with(['artist.artworks.images'])->orderBy('sort_order')->get();
			$homeFeaturedArtists = $orderedSelection($records, $homeSelectionSettings['public_home_featured_artist_ids'] ?? null, 4);
		}
		if (\Illuminate\Support\Facades\Schema::hasTable('public_collection_items')) {
			$records = \App\Models\PublicCollectionItem::where('is_published', true)->with(['artwork.artist', 'artwork.images'])->orderBy('sort_order')->get();
			$homeSelectedWorks = $orderedSelection($records, $homeSelectionSettings['public_home_selected_work_ids'] ?? null, 3);
			$homeStoryWork = $orderedSelection($records, $homeSelectionSettings['public_home_story_work_id'] ?? null, 1)->first();
		}
	}
	if ($publicPage === 'events' && \Illuminate\Support\Facades\Schema::hasTable('museum_events')) {
		$publicEvents = \App\Models\MuseumEvent::query()
			->where('is_published', true)
			->orderBy('sort_order')
			->orderBy('id')
			->get()
			->groupBy('section');

		if (\Illuminate\Support\Facades\Schema::hasTable('settings')) {
			$eventContent = array_replace(
				$eventContent,
				\App\Models\Setting::query()->whereIn('key', array_keys($eventContent))->pluck('value', 'key')->all(),
			);
			$eventsHeroPath = $eventContent['public_events_hero_image_path'];
			$eventsStoryPath = $eventContent['public_events_story_image_path'];
			$eventsHeroImageUrl = $eventsHeroPath && Storage::disk('public')->exists($eventsHeroPath) ? Storage::url($eventsHeroPath) : null;
			$eventsStoryImageUrl = $eventsStoryPath && Storage::disk('public')->exists($eventsStoryPath) ? Storage::url($eventsStoryPath) : null;
		}
	}

	if ($publicPage === 'artists' && \Illuminate\Support\Facades\Schema::hasTable('public_artist_profiles')) {
		$artistsCmsConfigured = \App\Models\PublicArtistProfile::query()->exists();
		$publicArtistProfiles = \App\Models\PublicArtistProfile::query()
			->where('is_published', true)
			->with(['artist.artworks.images'])
			->orderBy('sort_order')
			->orderBy('id')
			->get();

		if (\Illuminate\Support\Facades\Schema::hasTable('settings')) {
			$artistContent = array_replace(
				$artistContent,
				\App\Models\Setting::query()->whereIn('key', array_keys($artistContent))->pluck('value', 'key')->all(),
			);
		}
	}

	if ($publicPage === 'collection' && \Illuminate\Support\Facades\Schema::hasTable('public_collection_items')) {
		$collectionCmsConfigured = \App\Models\PublicCollectionItem::query()->exists();
		$publicCollectionItems = \App\Models\PublicCollectionItem::query()
			->where('is_published', true)
			->with(['artwork.artist', 'artwork.images'])
			->orderBy('sort_order')
			->orderBy('id')
			->get();

		if (\Illuminate\Support\Facades\Schema::hasTable('settings')) {
			$collectionContent = array_replace(
				$collectionContent,
				\App\Models\Setting::query()->whereIn('key', array_keys($collectionContent))->pluck('value', 'key')->all(),
			);
		}
	}

	if ($publicPage === 'about' && \Illuminate\Support\Facades\Schema::hasTable('settings')) {
		$aboutContent = array_replace(
			$aboutContent,
			\App\Models\Setting::query()->whereIn('key', array_keys($aboutContent))->pluck('value', 'key')->all(),
		);
		$heroPath = $aboutContent['public_about_hero_image_path'];
		$spacePath = $aboutContent['public_about_space_image_path'];
		$aboutHeroImageUrl = $heroPath && Storage::disk('public')->exists($heroPath) ? Storage::url($heroPath) : null;
		$aboutSpaceImageUrl = $spacePath && Storage::disk('public')->exists($spacePath) ? Storage::url($spacePath) : null;
	}

	return view('welcome', compact('homeArtworks', 'publicPage', 'publicEvents', 'eventContent', 'eventsHeroImageUrl', 'eventsStoryImageUrl', 'publicArtistProfiles', 'artistsCmsConfigured', 'artistContent', 'publicCollectionItems', 'collectionCmsConfigured', 'collectionContent', 'aboutContent', 'aboutHeroImageUrl', 'aboutSpaceImageUrl', 'homeContent', 'homeHeroVideoUrl', 'homeHeroPosterUrl', 'homeStoryImageUrl', 'homeCustomProgrammeImageUrls', 'homeCustomCollectionImageUrls', 'homeExperienceBackgroundUrl', 'homeFeaturedEvents', 'homeFeaturedArtists', 'homeSelectedWorks', 'homeStoryWork'));
};

Route::get('/', function () use ($publicMuseumPage) {
	return $publicMuseumPage('home');
})->name('home');

Route::get('/museum/about', function () use ($publicMuseumPage) {
	return $publicMuseumPage('about');
})->name('public.about');

Route::get('/museum/events', function () use ($publicMuseumPage) {
	return $publicMuseumPage('events');
})->name('public.events');

Route::get('/museum/artists', function () use ($publicMuseumPage) {
	return $publicMuseumPage('artists');
})->name('public.artists');

Route::get('/museum/collection', function () use ($publicMuseumPage) {
	return $publicMuseumPage('collection');
})->name('public.collection');

Route::get('/museum/visit', function () use ($publicMuseumPage) {
	return $publicMuseumPage('visit');
})->name('public.visit');
Route::post('/museum/visit', [VisitRequestController::class, 'store'])
	->middleware('throttle:5,1')
	->name('public.visit.store');

Route::get('/museum/contact', function () use ($publicMuseumPage) {
	return $publicMuseumPage('contact');
})->name('public.contact');
Route::post('/museum/contact', [ContactMessageController::class, 'store'])
	->middleware('throttle:10,1')
	->name('public.contact.store');


Route::middleware('auth')->group(function () {
	Route::post('logout', [AuthController::class, 'logout'])->name('logout');
	Route::get('profile', [ProfileController::class, 'edit'])->name('profile.edit');
	Route::put('profile', [ProfileController::class, 'update'])->name('profile.update');
	Route::put('profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password.update');
	Route::post('notifications/mark-read', function (\Illuminate\Http\Request $request) {
		$user = $request->user();
		if ($user && \Illuminate\Support\Facades\Schema::hasTable('notifications')) {
			$user->unreadNotifications->markAsRead();
		}

		return response()->json(['status' => 'ok']);
	})->name('notifications.mark-read');

	Route::get('dashboard', DashboardController::class)->name('dashboard');
	Route::get('artworks/suggestions', [ArtworkController::class, 'suggestions'])->name('artworks.suggestions');
	Route::get('artworks/export/pdf', [ArtworkController::class, 'exportPdf'])->name('artworks.export.pdf');
	Route::resource('artworks', ArtworkController::class);
	Route::get('movements', [MovementController::class, 'index'])->name('movements.index');
	Route::post('movements', [MovementController::class, 'store'])->name('movements.store');
	Route::get('movements/{movement}/edit', [MovementController::class, 'edit'])->name('movements.edit');
	Route::put('movements/{movement}', [MovementController::class, 'update'])->name('movements.update');
	Route::delete('movements/{movement}', [MovementController::class, 'destroy'])->name('movements.destroy');
	Route::get('locations', [LocationController::class, 'index'])->name('locations.index');
	Route::get('locations/create', [LocationController::class, 'create'])->name('locations.create')->middleware('admin');
	Route::post('locations', [LocationController::class, 'store'])->name('locations.store')->middleware('admin');
	Route::get('locations/{location}', [LocationController::class, 'show'])->name('locations.show');
	Route::get('locations/{location}/edit', [LocationController::class, 'edit'])->name('locations.edit')->middleware('admin');
	Route::put('locations/{location}', [LocationController::class, 'update'])->name('locations.update')->middleware('admin');
	Route::get('artists', [ArtistController::class, 'index'])->name('artists.index');
	Route::post('artists', [ArtistController::class, 'store'])->name('artists.store')->middleware('admin');
	Route::get('artists/{artist}/edit', [ArtistController::class, 'edit'])->name('artists.edit')->middleware('admin');
	Route::put('artists/{artist}', [ArtistController::class, 'update'])->name('artists.update')->middleware('admin');
	Route::delete('artists/{artist}', [ArtistController::class, 'destroy'])->name('artists.destroy')->middleware('admin');
	Route::get('reports', [ReportController::class, 'index'])->name('reports.index');
	Route::get('reports/export/pdf', [ReportController::class, 'exportPdf'])->name('reports.export.pdf');
	Route::get('reports/export/excel', [ReportController::class, 'exportExcel'])->name('reports.export.excel');
	Route::get('settings', [SettingController::class, 'index'])->name('settings.index');
	Route::post('settings/statuses', [SettingController::class, 'storeStatus'])->name('settings.statuses.store')->middleware('admin');
	Route::post('settings/statuses/{status}/toggle', [SettingController::class, 'toggleStatus'])->name('settings.statuses.toggle')->middleware('admin');
	Route::delete('settings/statuses/{status}', [SettingController::class, 'destroyStatus'])->name('settings.statuses.destroy')->middleware('admin');
	Route::post('settings/{section}', [SettingController::class, 'update'])->name('settings.update');
	Route::post('settings/backup/generate', [SettingController::class, 'generateBackup'])->name('settings.backup.generate')->middleware('admin');
	Route::get('settings/backup/download', [SettingController::class, 'downloadBackup'])->name('settings.backup.download')->middleware('admin');
	Route::post('settings/backup/delete', [SettingController::class, 'deleteBackup'])->name('settings.backup.delete')->middleware('admin');

	Route::prefix('admin')->name('admin.')->middleware('admin')->group(function () {
		Route::get('events', [AdminMuseumEventController::class, 'index'])->name('events.index');
		Route::post('events', [AdminMuseumEventController::class, 'store'])->name('events.store');
		Route::put('events/{event}', [AdminMuseumEventController::class, 'update'])->name('events.update');
		Route::delete('events/{event}', [AdminMuseumEventController::class, 'destroy'])->name('events.destroy');
		Route::put('events-content', [AdminMuseumEventController::class, 'updateContent'])->name('events.content.update');
		Route::get('public-artists', [AdminPublicArtistController::class, 'index'])->name('public-artists.index');
		Route::post('public-artists', [AdminPublicArtistController::class, 'store'])->name('public-artists.store');
		Route::put('public-artists/{profile}', [AdminPublicArtistController::class, 'update'])->name('public-artists.update');
		Route::delete('public-artists/{profile}', [AdminPublicArtistController::class, 'destroy'])->name('public-artists.destroy');
		Route::put('public-artists-content', [AdminPublicArtistController::class, 'updateContent'])->name('public-artists.content.update');
		Route::get('public-collection', [AdminPublicCollectionController::class, 'index'])->name('public-collection.index');
		Route::post('public-collection', [AdminPublicCollectionController::class, 'store'])->name('public-collection.store');
		Route::put('public-collection/{item}', [AdminPublicCollectionController::class, 'update'])->name('public-collection.update');
		Route::delete('public-collection/{item}', [AdminPublicCollectionController::class, 'destroy'])->name('public-collection.destroy');
		Route::put('public-collection-content', [AdminPublicCollectionController::class, 'updateContent'])->name('public-collection.content.update');
		Route::get('about-page', [AdminAboutPageController::class, 'index'])->name('about.index');
		Route::put('about-page', [AdminAboutPageController::class, 'update'])->name('about.update');
		Route::get('home-page', [AdminHomePageController::class, 'index'])->name('home.index');
		Route::put('home-page', [AdminHomePageController::class, 'update'])->name('home.update');
		Route::get('visit-requests', [AdminVisitRequestController::class, 'index'])->name('visit-requests.index');
		Route::patch('visit-requests/{visitRequest}/reviewed', [AdminVisitRequestController::class, 'markReviewed'])->name('visit-requests.reviewed');
		Route::get('messages', [AdminContactMessageController::class, 'index'])->name('contact-messages.index');
		Route::patch('messages/{contactMessage}/read', [AdminContactMessageController::class, 'markRead'])->name('contact-messages.read');
		Route::get('imports/csv', [AdminImportController::class, 'index'])->name('imports.csv.index');
		Route::post('imports/csv', [AdminImportController::class, 'store'])->name('imports.csv.store');

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

		Route::get('activity-logs', [AdminActivityLogController::class, 'index'])->name('activity-logs.index');
	});
});

Route::fallback(function () {
	return auth()->check()
		? redirect()->route('dashboard')
		: redirect()->route('login');
});
