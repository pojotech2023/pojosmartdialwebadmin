<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthApiController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\FollowUpController;

Route::post('/login', [AuthApiController::class, 'login']);
Route::get('/telecaller/monthly-target', [AuthApiController::class, 'monthlyTarget']);
Route::get('/follow-ups', [FollowUpController::class, 'apiData']);
Route::match(['get', 'post'], '/assign-base', [AuthApiController::class, 'assignBaseByEmployee']);
Route::match(['get', 'post'], '/assign-base-by-employee', [AuthApiController::class, 'assignBaseByEmployee']);
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
Route::match(['get', 'post'], '/get-customer-notes', [CustomerController::class, 'getCustomerNotes']);
Route::get('/get-customer-notes/{assignBaseId}', [CustomerController::class, 'getCustomerNotes']);
Route::match(['get', 'post'], '/calendar-events', [AuthApiController::class, 'calendarEvents']);
Route::post('/mark-notification-seen', [AuthApiController::class, 'markNotificationSeen']);
Route::post('/mark-reminder-seen', [AuthApiController::class, 'markNotificationSeen']);
// Products API (for Mobile App & Frontend)
Route::match(['get', 'post'], '/products', [AuthApiController::class, 'getProducts']);
Route::match(['get', 'post'], '/product-types', [AuthApiController::class, 'getProducts']);
Route::post('/products/create', [AuthApiController::class, 'createProduct']);
Route::post('/products/store', [AuthApiController::class, 'createProduct']);
Route::match(['post', 'put'], '/products/update', [AuthApiController::class, 'updateProduct']);
Route::match(['post', 'put'], '/products/{id}/update', [AuthApiController::class, 'updateProduct']);
Route::match(['post', 'delete'], '/products/delete', [AuthApiController::class, 'deleteProduct']);
Route::match(['post', 'delete'], '/products/{id}/delete', [AuthApiController::class, 'deleteProduct']);

// Telecaller Statuses API (for Mobile App & Frontend)
Route::match(['get', 'post'], '/telecaller-statuses', [AuthApiController::class, 'getTelecallerStatuses']);
Route::match(['get', 'post'], '/statuses', [AuthApiController::class, 'getTelecallerStatuses']);
Route::post('/telecaller-statuses/create', [AuthApiController::class, 'createTelecallerStatus']);
Route::post('/telecaller-statuses/store', [AuthApiController::class, 'createTelecallerStatus']);
Route::match(['post', 'put'], '/telecaller-statuses/update', [AuthApiController::class, 'updateTelecallerStatus']);
Route::match(['post', 'put'], '/telecaller-statuses/{id}/update', [AuthApiController::class, 'updateTelecallerStatus']);
Route::match(['post', 'delete'], '/telecaller-statuses/delete', [AuthApiController::class, 'deleteTelecallerStatus']);
Route::match(['post', 'delete'], '/telecaller-statuses/{id}/delete', [AuthApiController::class, 'deleteTelecallerStatus']);

// Account Users API (for Mobile App lead assignment to Accounts)
Route::match(['get', 'post'], '/accounts', [AuthApiController::class, 'getAccountUsers']);
Route::match(['get', 'post'], '/account-users', [AuthApiController::class, 'getAccountUsers']);
Route::match(['get', 'post'], '/account-employees', [AuthApiController::class, 'getAccountUsers']);

// A. Remark Select Status Dropdown API
Route::match(['get', 'post'], '/remark-statuses', [AuthApiController::class, 'getRemarkStatuses']);

// B. Assigned To Employee Dropdown List API
Route::match(['get', 'post'], '/employees', [AuthApiController::class, 'getEmployeesList']);
Route::match(['get', 'post'], '/employee-list', [AuthApiController::class, 'getEmployeesList']);

// C. Assigned To Save API
Route::post('/assign-lead', [AuthApiController::class, 'assignLead']);

// D. Lead Project Details Submit & Prefill APIs
Route::post('/save-lead-details', [AuthApiController::class, 'saveLeadDetails']);
Route::match(['get', 'post'], '/lead-details/{assignBaseId?}', [AuthApiController::class, 'getLeadDetails']);

// E. Account Statuses & Lead Status Update APIs (for Mobile App & Web)
Route::match(['get', 'post'], '/account-statuses', [AuthApiController::class, 'getAccountStatuses']);
Route::match(['get', 'post'], '/assigned-to-list', [AuthApiController::class, 'getEmployeesList']);
Route::match(['get', 'post'], '/assigned-to-users', [AuthApiController::class, 'getEmployeesList']);
Route::post('/update-lead-status', [AuthApiController::class, 'updateLeadStatus']);
Route::post('/update-status', [AuthApiController::class, 'updateLeadStatus']);

// F. Lead Account Status (for Mobile App & Web)
Route::match(['get', 'post'], '/lead-account-status/{assignBaseId?}', [AuthApiController::class, 'getLeadAccountStatus']);
Route::post('/update-account-status', [AuthApiController::class, 'updateAccountStatus']);

