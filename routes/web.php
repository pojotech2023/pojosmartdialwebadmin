<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\AssignBaseController;
use App\Http\Controllers\WhatsappNotificationController;
use App\Http\Controllers\CalendarController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\Api\AuthApiController;

/*
|--------------------------------------------------------------------------
| Redirect root to login
|--------------------------------------------------------------------------
*/
Route::get('/', function () {
    return redirect('/login');
});

/*
|--------------------------------------------------------------------------
| AUTH PAGES
|--------------------------------------------------------------------------
*/
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

/*
|--------------------------------------------------------------------------
| PROTECTED ROUTES (AFTER LOGIN)
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {

    Route::get('/dashboard', [AuthController::class, 'dashboard'])
        ->name('dashboard');

    Route::get('/dashboard/export', [AuthController::class, 'exportDashboard'])
        ->name('dashboard.export');

    Route::get('/dashboard/notifications-data', [AuthController::class, 'notificationsData'])
        ->name('dashboard.notifications.data');

    Route::get('/dashboard/whatsapp-notifications-data', [AuthController::class, 'whatsappNotificationsData'])
        ->name('dashboard.whatsapp-notifications.data');

    Route::get('/dashboard/lead-details-data/{assignBaseId}', [AuthController::class, 'leadDetailsData'])
        ->name('dashboard.lead-details.data');

    Route::post('/dashboard/notifications/mark-seen', [AuthController::class, 'markNotificationSeen'])
        ->name('dashboard.notifications.mark-seen');

    /*
    |--------------------------------------------------------------------------
    | ADMIN ONLY ROUTES (Employees, Assign Base, WhatsApp, Calendar, Settings)
    | Account users are redirected to dashboard
    |--------------------------------------------------------------------------
    */
    Route::middleware(\App\Http\Middleware\AdminOnly::class)->group(function () {
        Route::resource('/employees', UserController::class);

        Route::get('/assign-base', [AssignBaseController::class, 'index'])
            ->name('assign-base.index');

        Route::post('/assign-base/import', [AssignBaseController::class, 'import'])
            ->name('assign-base.import');

        Route::match(['delete', 'post'], '/assign-base/delete', [AssignBaseController::class, 'delete'])
            ->name('assign-base.delete');

        Route::get('/whatsapp', [WhatsappNotificationController::class, 'index'])
            ->name('whatsapp.index');

        Route::post('/whatsapp/import', [WhatsappNotificationController::class, 'import'])
            ->name('whatsapp.import');

        Route::post('/whatsapp/schedule', [WhatsappNotificationController::class, 'store'])
            ->name('whatsapp.store');

        Route::delete('/whatsapp/contact/delete', [WhatsappNotificationController::class, 'deleteContact'])
            ->name('whatsapp.contact.delete');

        Route::get('/calendar', [CalendarController::class, 'index'])
            ->name('calendar.index');

        Route::get('/calendar/events', [CalendarController::class, 'events'])
            ->name('calendar.events');

        // Settings (Product Types & Telecaller Statuses)
        Route::get('/settings', [SettingsController::class, 'index'])->name('settings.index');
        Route::post('/settings/products', [SettingsController::class, 'storeProduct'])->name('settings.products.store');
        Route::put('/settings/products/{product}', [SettingsController::class, 'updateProduct'])->name('settings.products.update');
        Route::delete('/settings/products/{product}', [SettingsController::class, 'destroyProduct'])->name('settings.products.destroy');

        Route::post('/settings/statuses', [SettingsController::class, 'storeStatus'])->name('settings.statuses.store');
        Route::put('/settings/statuses/{telecallerStatus}', [SettingsController::class, 'updateStatus'])->name('settings.statuses.update');
        Route::delete('/settings/statuses/{telecallerStatus}', [SettingsController::class, 'destroyStatus'])->name('settings.statuses.destroy');

        Route::resource('/products', ProductController::class)->except(['create', 'show', 'edit']);
    });

});

/*
|--------------------------------------------------------------------------
| API ROUTES (NO AUTH)
|--------------------------------------------------------------------------
*/
Route::post('/api/login', [AuthApiController::class, 'login']);
Route::post('/api/assign-base', [AuthApiController::class, 'assignBaseByEmployee']);
Route::post('/api/assign-base/update-single-status', [AuthApiController::class, 'updateAssignBaseStatus']);
Route::get('/loan-application-details/{assignBaseId}', [App\Http\Controllers\UserController::class, 'loanApplicationDetails'])->name('loan-application-details');