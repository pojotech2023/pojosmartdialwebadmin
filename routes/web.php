<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\AssignBaseController;
use App\Http\Controllers\WhatsappNotificationController;
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

    Route::post('/dashboard/notifications/mark-seen', [AuthController::class, 'markNotificationSeen'])
        ->name('dashboard.notifications.mark-seen');

    Route::resource('/employees', UserController::class);

    Route::get('/assign-base', [AssignBaseController::class, 'index'])
        ->name('assign-base.index');

    Route::post('/assign-base/import', [AssignBaseController::class, 'import'])
        ->name('assign-base.import');

    Route::delete('/assign-base/delete', [AssignBaseController::class, 'delete'])
        ->name('assign-base.delete');

    Route::get('/whatsapp', [WhatsappNotificationController::class, 'index'])
        ->name('whatsapp.index');

    Route::post('/whatsapp/import', [WhatsappNotificationController::class, 'import'])
        ->name('whatsapp.import');

    Route::post('/whatsapp/schedule', [WhatsappNotificationController::class, 'store'])
        ->name('whatsapp.store');

    Route::delete('/whatsapp/contact/delete', [WhatsappNotificationController::class, 'deleteContact'])
        ->name('whatsapp.contact.delete');

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