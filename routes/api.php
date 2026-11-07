<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthApiController;

Route::post('/login', [AuthApiController::class, 'login']);
Route::post('/assign-base-by-employee', [AuthApiController::class, 'assignBaseByEmployee']);
Route::post('/update-single-status', [AuthApiController::class, 'updateAssignBaseStatus']);
Route::post('/update-password', [AuthApiController::class, 'updatePassword']);
Route::post('/add-customer', [AuthApiController::class, 'addCustomer']);
Route::post('/create-notification', [AuthApiController::class, 'createNotification']);
Route::post('/save-notification', [AuthApiController::class, 'saveNotification']);
Route::post('/get-due-notifications', [AuthApiController::class, 'getDueNotifications']);
Route::post('/register-device-token', [AuthApiController::class, 'registerDeviceToken']);
Route::post('/send-push-notification', [AuthApiController::class, 'sendPushNotification']);
Route::post('/schedule-notification', [AuthApiController::class, 'sendScheduledNotification']);
Route::post('/schedule-whatsapp-notification', [AuthApiController::class, 'scheduleWhatsappNotification']);
Route::post('/whatsapp-notifications-data', [AuthApiController::class, 'whatsappNotificationsData']);
Route::post('/process-scheduled-whatsapp-notifications', [AuthApiController::class, 'processScheduledWhatsappNotifications']);
Route::post('/process-scheduled-notifications', [AuthApiController::class, 'processScheduledNotifications']);
Route::get('/get-notification/{assignBaseId}', [AuthApiController::class, 'getNotification']);
Route::post('/save-loan-application', [AuthApiController::class, 'saveLoanApplication']);
Route::get('/get-loan-application/{assignBaseId}', [AuthApiController::class, 'getLoanApplication']);
Route::post('/get-loan-application-by-employee', [AuthApiController::class, 'getLoanApplicationByEmployee']);
Route::match(['get', 'post'], '/calendar-events', [AuthApiController::class, 'calendarEvents']);
Route::post('/mark-notification-seen', [AuthApiController::class, 'markNotificationSeen']);
Route::post('/mark-reminder-seen', [AuthApiController::class, 'markNotificationSeen']);
Route::get('/products', [AuthApiController::class, 'getProducts']);
Route::get('/product-types', [AuthApiController::class, 'getProducts']);