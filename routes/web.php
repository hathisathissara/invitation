<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\GuestController;
use App\Http\Controllers\GuestGalleryController;
use App\Http\Controllers\GalleryController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\InvitationController;
use App\Http\Controllers\CalendarController;
use App\Http\Controllers\CustomizeController;
use App\Http\Controllers\AdminController;
use Illuminate\Support\Facades\Route;

// Welcome Page (Home)
Route::get('/', function () {
    return view('site.home');
})->name('home');
 
Route::get('/features', function () {
    return view('site.features');
})->name('features');
 
Route::get('/pricing', function () {
    return view('site.pricing');
})->name('pricing');
 
Route::get('/template', function () {
    return view('site.template');
})->name('template');
 
Route::get('/privacy', function () {
    return view('site.privacy');
})->name('privacy');
 
Route::get('/terms', function () {
    return view('site.terms');
})->name('terms');
 
Route::get('/refund', function () {
    return view('site.refund');
})->name('refund');

// Profile Routes (Breeze Default)
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// =====================================================================
// 🔒 Couple Dashboard Routes Group (Strictly Auth-Protected Couple Panel)
// =====================================================================
Route::middleware(['auth','role:couple'])->prefix('dashboard')->group(function () {
    // Dashboard Home Route
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    
    // Checklist (Tasks) Routes
    Route::get('/checklist', [TaskController::class, 'index'])->name('tasks.index');
    Route::post('/checklist', [TaskController::class, 'store'])->name('tasks.store');
    Route::patch('/checklist/{task}/toggle', [TaskController::class, 'toggle'])->name('tasks.toggle');
    Route::delete('/checklist/{task}', [TaskController::class, 'destroy'])->name('tasks.destroy');
   
    // Wedding Events Routes
    Route::get('/events', [EventController::class, 'index'])->name('events.index');
    Route::post('/events', [EventController::class, 'store'])->name('events.store');
    Route::delete('/events/{event}', [EventController::class, 'destroy'])->name('events.destroy');

    // Guests List Routes
    Route::get('/guests', [GuestController::class, 'index'])->name('guests.index');
    Route::post('/guests', [GuestController::class, 'store'])->name('guests.store');
    Route::delete('/guests/{guest}', [GuestController::class, 'destroy'])->name('guests.destroy');
    
    // Guest AJAX Helper Routes
    Route::get('/guests/mark-sent/{id}', [GuestController::class, 'markSent']);
    Route::get('/guests/live-status', [GuestController::class, 'liveStatus'])->name('guests.live-status');

    // Guest Shared Photo Gallery Routes
    Route::get('/guest-gallery', [GuestGalleryController::class, 'index'])->name('guest-gallery.index');
    Route::delete('/guest-gallery/{image}', [GuestGalleryController::class, 'destroy'])->name('guest-gallery.destroy');
    Route::get('/guest-gallery/live-check', [GuestGalleryController::class, 'liveCheck'])->name('guest-gallery.live-check');
    Route::get('/guest-gallery/download/{id}', [GuestGalleryController::class, 'downloadJpg'])->name('guest-gallery.download');

    // Couple Gallery Routes
    Route::get('/gallery', [GalleryController::class, 'index'])->name('gallery.index');
    Route::post('/gallery/upload', [GalleryController::class, 'upload'])->name('gallery.upload');
    Route::post('/gallery/story', [GalleryController::class, 'updateStory'])->name('gallery.story');
    Route::delete('/gallery/{id}', [GalleryController::class, 'destroy'])->name('gallery.destroy');
    Route::patch('/gallery/{id}/cover', [GalleryController::class, 'setCover'])->name('gallery.cover');

    // Settings Routes
    Route::get('/settings', [SettingsController::class, 'index'])->name('settings.index');
    Route::post('/settings/wedding', [SettingsController::class, 'updateWedding'])->name('settings.wedding');
    Route::post('/settings/password', [SettingsController::class, 'updatePassword'])->name('settings.password');
    Route::delete('/settings/delete', [SettingsController::class, 'destroy'])->name('settings.destroy');

    // Payment, Upgrades & Refund Routes
    Route::get('/payment', [PaymentController::class, 'index'])->name('payment.index');
    Route::get('/payment/dismiss-refund', [PaymentController::class, 'dismissRefund'])->name('payment.dismiss-refund');
    Route::post('/payment/slip', [PaymentController::class, 'storeSlip'])->name('payment.slip');
    Route::post('/payment/upgrade', [PaymentController::class, 'upgradeSlip'])->name('payment.upgrade');
    Route::post('/payment/refund-request', [PaymentController::class, 'requestRefund'])->name('payment.refund-request');
    Route::post('/payment/bank-details', [PaymentController::class, 'submitBankDetails'])->name('payment.bank-details');
    
    // Live polling endpoints for header and payment card
    Route::get('/status-check', [PaymentController::class, 'globalStatusCheck'])->name('global.status-check');
    Route::get('/payment/live-check', [PaymentController::class, 'paymentLiveCheck'])->name('payment.live-check');

     // Customize Invitation Routes
    Route::get('/customize', [CustomizeController::class, 'index'])->name('customize.index');
    Route::post('/customize/design', [CustomizeController::class, 'updateDesign'])->name('customize.design');
    Route::post('/customize/language', [CustomizeController::class, 'updateLanguage'])->name('customize.language');
    Route::post('/customize/music', [CustomizeController::class, 'updateMusic'])->name('customize.music');
});

// =====================================================================
// 🔒 Secured Administrator Control Panel Routes
// =====================================================================
Route::middleware(['auth', 'role:admin'])->prefix('admin')->group(function () {
    // 1. Admin Dashboard Home (legacy index.php)
    Route::get('/', [AdminController::class, 'index'])->name('admin.index');
    Route::get('/index', [AdminController::class, 'index']);
    Route::get('/live-stats', [AdminController::class, 'liveStats'])->name('admin.live-stats');
    Route::get('/toggle-status/{id}', [AdminController::class, 'toggleStatus'])->name('admin.toggle-status');
    Route::get('/notify-delete/{id}', [AdminController::class, 'notifyDelete'])->name('admin.notify-delete');
    
    // 2. Delete Account Page (legacy admin_delete_account.php)
    Route::get('/users/{id}/delete', [AdminController::class, 'confirmDelete'])->name('admin.delete.confirm');
    Route::delete('/users/{id}', [AdminController::class, 'destroyUser'])->name('admin.delete.destroy');
    
    // 3. Upgrades Panel (legacy admin_upgrades.php)
    Route::get('/upgrades', [AdminController::class, 'upgradesIndex'])->name('admin.upgrades');
    Route::get('/upgrades/live', [AdminController::class, 'liveUpgrades'])->name('admin.upgrades.live');
    Route::post('/upgrades/{id}/approve', [AdminController::class, 'approveUpgrade'])->name('admin.upgrades.approve');
    Route::post('/upgrades/{id}/reject', [AdminController::class, 'rejectUpgrade'])->name('admin.upgrades.reject');
    
    // 4. Refunds Panel (legacy admin_refunds.php)
    Route::get('/refunds', [AdminController::class, 'refundsIndex'])->name('admin.refunds');
    Route::get('/refunds/live', [AdminController::class, 'liveRefunds'])->name('admin.refunds.live');
    Route::post('/refunds/{id}/approve', [AdminController::class, 'approveRefund'])->name('admin.refunds.approve');
    Route::post('/refunds/{id}/reject', [AdminController::class, 'rejectRefund'])->name('admin.refunds.reject');
    Route::post('/refunds/{id}/complete', [AdminController::class, 'completeRefund'])->name('admin.refunds.complete');
});

// =====================================================================
// 🌎 Public Guest Facing Routes (Outside Auth Group!)
// =====================================================================
// 1. Envelope Gate (invite.php වෙනුවට)
Route::get('/invitation/{slug}', [InvitationController::class, 'invite'])->name('invitation.invite');
Route::post('/invitation/{slug}', [InvitationController::class, 'verifyPhone'])->name('invitation.verify');

// 2. Styled Invitation Page (view_invitation.php වෙනුවට)
Route::get('/invitation/{slug}/view', [InvitationController::class, 'viewInvitation'])->name('invitation.view');
Route::post('/invitation/{slug}/rsvp', [InvitationController::class, 'submitRsvp'])->name('invitation.rsvp');
Route::post('/invitation/{slug}/upload', [InvitationController::class, 'uploadPhoto'])->name('invitation.upload');

// Calendar ICS Download Route (Public)
Route::get('/calendar/download/{id}', [CalendarController::class, 'download'])->name('calendar.download');



require __DIR__.'/auth.php';