<?php

use App\Http\Controllers\AccountController;
use App\Http\Controllers\AdminUserController;
use App\Http\Controllers\ArchiveController;
use App\Http\Controllers\ContentAdminController;
use App\Http\Controllers\InterviewController;
use App\Http\Controllers\InvitationController;
use App\Http\Controllers\PhotographController;
use App\Http\Controllers\PresentationAdminController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ResearchController;
use App\Models\InterfaceTranslation;
use App\Support\Words;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/atlas', [ArchiveController::class, 'index'])->name('atlas');
Route::get('/atlas/export/{format}', [ResearchController::class, 'export'])->middleware('throttle:10,1')->name('research.export');
Route::get('/citations/{observation}', [ResearchController::class, 'citation'])->name('research.citation');
Route::get('/sites/{site}', [ArchiveController::class, 'show'])->name('sites.show');

Route::get('/', function (Request $request) {
    $locale = app()->getLocale();
    abort_unless(in_array($locale, ['en', 'zh-Hans'], true), 404);
    app()->setLocale($locale);
    $strings = InterfaceTranslation::where('locale', $locale)->pluck('value', 'key');

    return view('archive-home', compact('locale', 'strings'));
})->name('home');

Route::middleware(['auth', 'verified', 'can:admin'])->group(function () {
    Route::get('/admin/content', [ContentAdminController::class, 'index'])->name('admin.content');
    Route::get('/admin/translations', [ContentAdminController::class, 'index'])->name('admin.translations');
    Route::patch('/admin/content', [ContentAdminController::class, 'update'])->middleware('throttle:30,1')->name('admin.content.update');
});

Route::get('/markers/{category}', [PresentationAdminController::class, 'marker'])->name('assets.marker');
Route::get('/homepage-images/{image}', [PresentationAdminController::class, 'photo'])->whereNumber('image')->name('assets.photo');
Route::middleware(['auth', 'verified', 'can:admin'])->group(function () {
    Route::get('/admin/assets', [PresentationAdminController::class, 'index'])->name('assets.admin');
    Route::post('/admin/categories', [PresentationAdminController::class, 'category'])->name('assets.category.create');
    Route::patch('/admin/categories/{category}', [PresentationAdminController::class, 'category'])->name('assets.category.update');
    Route::post('/admin/homepage-images', [PresentationAdminController::class, 'upload'])->name('assets.photo.upload');
    Route::delete('/admin/homepage-images/{image}', [PresentationAdminController::class, 'remove'])->whereNumber('image')->name('assets.photo.remove');
});

Route::middleware('guest')->group(function () {
    Route::get('/invitation/{token}', [InvitationController::class, 'show'])->name('invitation.show');
    Route::post('/invitation/{token}', [InvitationController::class, 'accept'])->middleware('throttle:5,1')->name('invitation.accept');
    Route::view('/login', 'account', ['screen' => 'login'])->name('login');
    Route::view('/register', 'account', ['screen' => 'register'])->name('register');
    Route::view('/forgot-password', 'account', ['screen' => 'forgot'])->name('password.request');
    Route::get('/reset-password/{token}', fn (string $token) => view('account', ['screen' => 'reset', 'token' => $token]))->name('password.reset');
    Route::post('/login', [AccountController::class, 'login'])->middleware('throttle:5,1');
    Route::post('/register', [AccountController::class, 'register'])->middleware('throttle:5,1');
    Route::post('/forgot-password', [AccountController::class, 'forgot'])->middleware('throttle:5,1')->name('password.email');
    Route::post('/reset-password', [AccountController::class, 'reset'])->middleware('throttle:5,1')->name('password.update');
});
Route::middleware('auth')->group(function () {
    Route::post('/logout', [AccountController::class, 'logout'])->name('logout');
    Route::view('/email/verify', 'account', ['screen' => 'verify'])->name('verification.notice');
    Route::get('/email/verify/{id}/{hash}', function (EmailVerificationRequest $request) {
        $request->fulfill();

        return redirect()->route('profile');
    })->middleware(['signed', 'throttle:6,1'])->name('verification.verify');
    Route::post('/email/verification-notification', function (Request $request) {
        if (! $request->user()->hasVerifiedEmail()) {
            $request->user()->sendEmailVerificationNotification();
        }

        return back()->with('status', Words::get('verify.sent'));
    })->middleware('throttle:1,1')->name('verification.send');
    Route::middleware('verified')->group(function () {
        Route::get('/observations/create', [ArchiveController::class, 'create'])->name('observations.create');
        Route::post('/observations', [ArchiveController::class, 'store'])->middleware('throttle:20,1')->name('observations.store');
        Route::get('/my-submissions', [ArchiveController::class, 'mine'])->name('observations.mine');
        Route::get('/observations/{observation}', [ArchiveController::class, 'privateShow'])->name('observations.private');
        Route::post('/observations/{observation}/decision', [ArchiveController::class, 'decide'])->middleware(['can:moderate', 'throttle:30,1'])->name('observations.decide');
        Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
        Route::post('/admin/invitations', [InvitationController::class, 'store'])->middleware(['can:admin', 'throttle:10,1'])->name('invitations.store');
        Route::post('/admin/invitations/{invitation}/resend', [InvitationController::class, 'resend'])->middleware(['can:admin', 'throttle:5,1'])->name('invitations.resend');
        Route::delete('/admin/invitations/{invitation}', [InvitationController::class, 'revoke'])->middleware('can:admin')->name('invitations.revoke');
        Route::get('/admin/users', [AdminUserController::class, 'index'])->middleware('can:admin')->name('admin.users');
        Route::patch('/admin/users/{user}', [AdminUserController::class, 'update'])->middleware(['can:admin', 'throttle:30,1'])->name('admin.users.update');
        Route::view('/profile', 'account', ['screen' => 'profile'])->name('profile');
        Route::view('/admin', 'account', ['screen' => 'admin'])->middleware('can:admin')->name('admin');
        Route::get('/moderation', [ArchiveController::class, 'queue'])->middleware('can:moderate')->name('moderation');
    });
});

Route::get('/photographs/{photograph}', [PhotographController::class, 'publicImage'])->name('photos.public');
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/private/photographs/{photograph}', [PhotographController::class, 'preview'])->name('photos.preview');
    Route::get('/private/photographs/{photograph}/original', [PhotographController::class, 'original'])->middleware('can:moderate')->name('photos.original');
    Route::post('/private/photographs/{photograph}/review', [PhotographController::class, 'review'])->middleware(['can:moderate', 'throttle:30,1'])->name('photos.review');
});

Route::get('/interviews/{interview}', [InterviewController::class, 'publicVideo'])->name('videos.public');
Route::get('/interviews/{interview}/captions/{language}', [InterviewController::class, 'captions'])->name('videos.captions');
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/private/interviews/{interview}', [InterviewController::class, 'preview'])->name('videos.preview');
    Route::get('/private/interviews/{interview}/captions/{language}', [InterviewController::class, 'captions'])->name('videos.private-captions');
    Route::get('/private/interviews/{interview}/original', [InterviewController::class, 'original'])->middleware('can:moderate')->name('videos.original');
    Route::post('/private/interviews/{interview}/prepare', [InterviewController::class, 'prepare'])->middleware(['can:moderate', 'throttle:10,1'])->name('videos.prepare');
    Route::post('/private/interviews/{interview}/review', [InterviewController::class, 'review'])->middleware(['can:moderate', 'throttle:30,1'])->name('videos.review');
});
