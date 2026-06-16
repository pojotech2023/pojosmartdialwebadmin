<?php

namespace App\Http\Controllers\Api;
use App\Models\AssignBase;
use App\Models\Notification;
use App\Models\DeviceToken;
use App\Http\Controllers\Controller;
use App\Services\FirebaseService;
use App\Jobs\SendScheduledNotification;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Artisan;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;

class AuthApiController extends Controller
{
    public function login(Request $request)
    {
        // Validate input
        $request->validate([
            'mobile'   => 'required',
            'password' => 'required',
        ]);

        // Find user by mobile with application relationship
        $user = User::with('application')->where('mobile', $request->mobile)->first();

        // Check mobile & password
        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'status'  => false,
                'message' => 'Invalid mobile number or password'
            ], 401);
        }

        // Success response (ONLY THAT EMPLOYEE)
        return response()->json([
            'status'  => true,
            'message' => 'Login successful',
            'application_id' => $user->application_id,
            'application_type' => $user->application->name ?? null,
            'data' => [
                'emp_id' => $user->id,
                'name'   => $user->name,
                'mobile' => $user->mobile,
                'joined' => $user->created_at->format('d-m-Y'),
                
            ]
        ]);
    }
    public function saveLoanApplication(Request $request)
    {
        try {
            $request->validate([
                'assign_base_id' => 'required|integer|exists:assign_bases,id',
                'login_date' => 'nullable|date',
                'app_number' => 'nullable|string',
                'logged_bank' => 'nullable|string',
                'promo_code' => 'nullable|string',
                'req_amount' => 'nullable|numeric',
                'approved_amount' => 'nullable|numeric',
                'status' => 'nullable|in:PENDING,APPROVED,REJECTED,IN_PROCESS',
                'remarks' => 'nullable|string',
                'net_salary' => 'nullable|numeric',
                'bank_ac' => 'nullable|string',
                'add_proof' => 'nullable|string',
                'category' => 'nullable|string',
                'bank_manager' => 'nullable|string',
                'dsa_name' => 'nullable|string',
                'ref_1' => 'nullable|string',
                'ref_2' => 'nullable|string',
                'tenure' => 'nullable|integer',
                'location' => 'nullable|string',
                'discr' => 'nullable|string',
                'date' => 'nullable|date',
                'mode' => 'nullable|string',
                'aadhar_card_front_upload' => 'nullable|file|mimes:jpg,jpeg,png,pdf',
                'aadhar_card_back_upload' => 'nullable|file|mimes:jpg,jpeg,png,pdf',
                'pan_upload' => 'nullable|file|mimes:jpg,jpeg,png,pdf',
                'bank_statement_upload' => 'nullable|file|mimes:jpg,jpeg,png,pdf',
                'salary_slip_upload' => 'nullable|file|mimes:jpg,jpeg,png,pdf',
            ]);

            $loanData = $request->except(['aadhar_card_front_upload', 'aadhar_card_back_upload', 'pan_upload', 'bank_statement_upload', 'salary_slip_upload']);

            // Handle file uploads
            if ($request->hasFile('aadhar_card_front_upload')) {
                $loanData['aadhar_card_front_upload'] = $request->file('aadhar_card_front_upload')->store('loan_documents', 'public');
            }
            if ($request->hasFile('aadhar_card_back_upload')) {
                $loanData['aadhar_card_back_upload'] = $request->file('aadhar_card_back_upload')->store('loan_documents', 'public');
            }
            if ($request->hasFile('pan_upload')) {
                $loanData['pan_upload'] = $request->file('pan_upload')->store('loan_documents', 'public');
            }
            if ($request->hasFile('bank_statement_upload')) {
                $loanData['bank_statement_upload'] = $request->file('bank_statement_upload')->store('loan_documents', 'public');
            }
            if ($request->hasFile('salary_slip_upload')) {
                $loanData['salary_slip_upload'] = $request->file('salary_slip_upload')->store('loan_documents', 'public');
            }

            $loanApplication = \App\Models\LoanApplication::updateOrCreate(
                ['assign_base_id' => $request->assign_base_id],
                $loanData
            );

            return response()->json([
                'status' => true,
                'message' => 'Loan application saved successfully',
                
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }

   
    public function getLoanApplicationByEmployee(Request $request)
{
    try {
        $request->validate([
            'emp_id' => 'required|integer|exists:users,id',
            'assign_base_id' => 'required|integer|exists:assign_bases,id',
        ]);

        // First check if the assign_base belongs to this employee
        $assignBase = AssignBase::where('id', $request->assign_base_id)
            ->where('emp_id', $request->emp_id)
            ->first();

        if (!$assignBase) {
            return response()->json([
                'status' => false,
                'message' => 'This customer is not assigned to the specified employee'
            ], 404);
        }

        // Get the loan application
        $loanApplication = \App\Models\LoanApplication::where('assign_base_id', $request->assign_base_id)->first();

        if (!$loanApplication) {
            return response()->json([
                'status' => false,
                'message' => 'No loan application found for this customer'
            ], 404);
        }

        return response()->json([
            'status' => true,
            'message' => 'Loan application found successfully',
            'loan_data' => [
                'login_date' => $loanApplication->login_date ? $loanApplication->login_date->format('d/m/Y') : null,
                'app_number' => $loanApplication->app_number,
                'logged_bank' => $loanApplication->logged_bank,
                'promo_code' => $loanApplication->promo_code,
                'req_amount' => $loanApplication->req_amount,
                'approved_amount' => $loanApplication->approved_amount,
                'status' => $loanApplication->status,
                'remarks' => $loanApplication->remarks,
                'net_salary' => $loanApplication->net_salary,
                'bank_ac' => $loanApplication->bank_ac,
                'category' => $loanApplication->category,
                'bank_manager' => $loanApplication->bank_manager,
                'dsa_name' => $loanApplication->dsa_name,
                'ref_1' => $loanApplication->ref_1,
                'ref_2' => $loanApplication->ref_2,
                'tenure' => $loanApplication->tenure,
                'location' => $loanApplication->location,
                'discr' => $loanApplication->discr,
                'date' => $loanApplication->date ? $loanApplication->date->format('d/m/Y') : null,
                'mode' => $loanApplication->mode,
                'aadhar_card_front_upload' => $loanApplication->aadhar_card_front_upload ? url('storage/' . $loanApplication->aadhar_card_front_upload) : null,
                'aadhar_card_back_upload' => $loanApplication->aadhar_card_back_upload ? url('storage/' . $loanApplication->aadhar_card_back_upload) : null,
                'pan_upload' => $loanApplication->pan_upload ? url('storage/' . $loanApplication->pan_upload) : null,
                'bank_statement_upload' => $loanApplication->bank_statement_upload ? url('storage/' . $loanApplication->bank_statement_upload) : null,
                'salary_slip_upload' => $loanApplication->salary_slip_upload ? url('storage/' . $loanApplication->salary_slip_upload) : null,
            ]
        ]);

    } catch (\Exception $e) {
        return response()->json([
            'status' => false,
            'message' => 'Error: ' . $e->getMessage()
        ], 500);
    }
}


    public function assignBaseByEmployee(Request $request)
    {
        $request->validate([
            'emp_id' => 'required|integer',
            'assign_date' => 'required|date_format:d/m/Y',  // Accept DD/MM/YYYY format
            'status'      => 'nullable|in:NEW,CONFIRMED,FOLLOW UP,NOPE',


        ]);

        // Check if employee exists
        $user = User::find($request->emp_id);
        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'Employee not found',
            ], 404);
        }

        // Parse input date to Y-m-d for DB comparison
        $assignDate = Carbon::createFromFormat('d/m/Y', $request->assign_date)->format('Y-m-d');

        // Query with filter: emp_id, assign_date, and status = 'NEW'
        $data = AssignBase::where('emp_id', $request->emp_id)
            ->where('assign_date', '=', $assignDate)
            ->when($request->status, fn ($q) => $q->where('status', $request->status))
            ->selectRaw("id, customer_name, company_name, status, mobile, DATE_FORMAT(assign_date, '%d/%m/%Y') as date")
            ->get();

        return response()->json([
            'status' => true,
            'data' => $data,
        ]);
    }
  
    public function updateAssignBaseStatus(Request $request)
    {
        $request->validate([
            'id' => 'required|integer',
            'emp_id' => 'required|integer',
            'status' => 'required|in:NEW,CONFIRMED,FOLLOW UP,NOPE',
        ]);

        $assignBase = AssignBase::where('id', $request->id)->where('emp_id', $request->emp_id)->first();
        if (!$assignBase) {
            return response()->json([
                'status' => false,
                'message' => 'Assign Base record not found or does not belong to the employee',
            ], 404);
        }

        $assignBase->update(['status' => $request->status]);

        return response()->json([
            'status' => true,
            'message' => 'Status updated successfully',
        ]);
    }

    public function updatePassword(Request $request)
    {
        // Validate input
        $request->validate([
            'mobile'      => 'required|string',
            'new_password' => 'required|string|min:8',
        ]);

        // Find user by mobile
        $user = User::where('mobile', $request->mobile)->first();

        if (!$user) {
            return response()->json([
                'status'  => false,
                'message' => 'User not found'
            ], 404);
        }

        // Update password
        $user->update(['password' => Hash::make($request->new_password)]);

        return response()->json([
            'status'  => true,
            'message' => 'Password updated successfully'
        ]);
    }

    public function addCustomer(Request $request)
    {
        try {
            // Validate input
            $request->validate([
                'emp_id'       => 'required|integer|exists:users,id',
                'name'         => 'required|string',
                'company_name' => 'required|string',
                'status'       => 'required|in:NEW,CONFIRMED,FOLLOW UP,NOPE',
                'mobile'       => 'required|string',
                'date'         => 'required|date_format:d/m/Y',
            ]);

            // Check if employee exists
            $user = User::find($request->emp_id);
            if (!$user) {
                return response()->json([
                    'status'  => false,
                    'message' => 'Employee not found',
                ], 404);
            }

            // Parse date to Y-m-d format
            $assignDate = Carbon::createFromFormat('d/m/Y', $request->date)->format('Y-m-d');

            // Create new customer
            $customer = AssignBase::create([
                'emp_id'       => $request->emp_id,
                'customer_name' => $request->name,
                'company_name' => $request->company_name,
                'status'       => $request->status,
                'mobile'       => $request->mobile,
                'assign_date'  => $assignDate,
            ]);

            return response()->json([
                'status'  => true,
                'message' => 'Customer added successfully',
                'data' => [
                    'assign_base_id' => $customer->id,
                    'emp_id'         => $customer->emp_id,
                    'customer_name'  => $customer->customer_name,
                    'company_name'   => $customer->company_name,
                    'status'         => $customer->status,
                    'mobile'         => $customer->mobile,
                    'assign_date'    => Carbon::parse($customer->assign_date)->format('d/m/Y'),
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status'  => false,
                'message' => 'Error: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function createNotification(Request $request)
    {
        try {
            // Validate input
            $request->validate([
                'emp_id'               => 'required|integer|exists:users,id',
                'user_id'              => 'required|integer|exists:users,id',
                'notification_date'    => 'required|date_format:d/m/Y',
                'notification_time'    => 'required|date_format:H:i',
                'remarks'              => 'required|string',
            ]);

            // Parse date from d/m/Y to Y-m-d
            $notificationDate = Carbon::createFromFormat('d/m/Y', $request->notification_date)->format('Y-m-d');

            // Create notification
            $notification = Notification::create([
                'emp_id'            => $request->emp_id,
                'user_id'           => $request->user_id,
                'notification_date' => $notificationDate,
                'notification_time' => $request->notification_time,
                'remarks'           => $request->remarks,
                'is_sent'           => false,
            ]);

            return response()->json([
                'status'  => true,
                'message' => 'Notification created successfully',
                'data' => [
                    'id'                   => $notification->id,
                    'emp_id'               => $notification->emp_id,
                    'user_id'              => $notification->user_id,
                    'notification_date'    => $notification->notification_date->format('d/m/Y'),
                    'notification_time'    => $notification->notification_time,
                    'remarks'              => $notification->remarks,
                    'is_sent'              => $notification->is_sent,
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status'  => false,
                'message' => 'Error: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function saveNotification(Request $request)
    {
        try {
            $request->validate([
                'id'                  => 'nullable|integer|exists:notifications,id',
                'emp_id'              => 'required|integer|exists:users,id',
                'assign_base_id'      => 'required|integer|exists:assign_bases,id',
                'notification_date'   => 'required|date_format:d/m/Y',
                'notification_time'   => 'required|date_format:H:i',
                'remarks'             => 'required|string',
            ]);

            $notificationDate = Carbon::createFromFormat('d/m/Y', $request->notification_date)->format('Y-m-d');

            if ($request->filled('id')) {
                $notification = Notification::where('id', $request->id)
                    ->where('emp_id', $request->emp_id)
                    ->first();

                if (!$notification) {
                    return response()->json([
                        'status' => false,
                        'message' => 'Notification not found for this employee',
                    ], 404);
                }

                $notification->update([
                    'assign_base_id'    => $request->assign_base_id,
                    'notification_date' => $notificationDate,
                    'notification_time' => $request->notification_time,
                    'remarks'           => $request->remarks,
                ]);

                $message = 'Notification updated successfully';
            } else {
                $notification = Notification::create([
                    'emp_id'            => $request->emp_id,
                    'assign_base_id'    => $request->assign_base_id,
                    'notification_date' => $notificationDate,
                    'notification_time' => $request->notification_time,
                    'remarks'           => $request->remarks,
                    'is_sent'           => false,
                ]);

                $message = 'Notification created successfully';
            }

            // Schedule the notification if it's in the future
            $this->scheduleNotificationIfNeeded($notification);

            // Fetch all notifications for this assign_base_id (pending + sent)
            $notificationList = Notification::where('assign_base_id', $request->assign_base_id)
                ->orderBy('notification_date', 'asc')
                ->orderBy('notification_time', 'asc')
                ->get()
                ->map(fn($n) => [
                    'id'                => $n->id,
                    'emp_id'            => $n->emp_id,
                    'assign_base_id'    => $n->assign_base_id,
                    'notification_date' => Carbon::parse($n->notification_date)->format('d/m/Y'),
                    'notification_time' => $n->notification_time,
                    'remarks'           => $n->remarks,
                    'is_sent'           => $n->is_sent,
                ]);

            return response()->json([
                'status'            => true,
                'message'           => $message,
                'data'              => [
                    'id'                => $notification->id,
                    'emp_id'            => $notification->emp_id,
                    'assign_base_id'    => $notification->assign_base_id,
                    'notification_date' => Carbon::parse($notification->notification_date)->format('d/m/Y'),
                    'notification_time' => $notification->notification_time,
                    'remarks'           => $notification->remarks,
                    'is_sent'           => $notification->is_sent,
                ],
                'notification_list' => $notificationList,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status'  => false,
                'message' => 'Error: ' . $e->getMessage(),
            ], 500);
        }
    }

     public function getNotification($assignBaseId)
    {
        try {
            $notification = Notification::where('assign_base_id', $assignBaseId)
                ->orderBy('created_at', 'desc')
                ->first();

            if (!$notification) {
                return response()->json([
                    'status' => false,
                    'message' => 'No notification found for this customer'
                ], 404);
            }

            return response()->json([
                'status' => true,
                'data' => [
                    'notification_date' => \Carbon\Carbon::parse($notification->notification_date)->format('d/m/Y'),
                    'notification_time' => $notification->notification_time,
                    'remarks' => $notification->remarks,
                    'is_sent' => $notification->is_sent
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }


    public function registerDeviceToken(Request $request)
    {
        try {
            $request->validate([
                'emp_id' => 'required|integer|exists:users,id',
                'device_token'   => 'required|string',
                'device_type'    => 'required|in:android,ios,web',
            ]);

            // Check if token already exists for this employee
            $existing = DeviceToken::where('emp_id', $request->emp_id)
                ->where('device_token', $request->device_token)
                ->first();

            if ($existing) {
                $existing->update(['is_active' => true]);
                return response()->json([
                    'status'  => true,
                    'message' => 'Device token already registered for this employee',
                ]);
            }

            // Create new device token
            DeviceToken::create([
                'emp_id' => $request->emp_id,
                'device_token'   => $request->device_token,
                'device_type'    => $request->device_type,
                'is_active'      => true,
            ]);

            return response()->json([
                'status'  => true,
                'message' => 'Device token registered successfully for employee',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status'  => false,
                'message' => 'Error: ' . $e->getMessage(),
            ], 500);
        }
    }

   
    public function sendPushNotification(Request $request)
    {
        try {
            $request->validate([
                'assign_base_id' => 'required|integer|exists:assign_bases,id',
                'date'           => 'required|date_format:d/m/Y',
                'time'           => 'required|date_format:H:i',
                'remarks'        => 'nullable|string',
            ]);

            // Find the assignment
            $assignBase = AssignBase::find($request->assign_base_id);
            if (!$assignBase) {
                return response()->json([
                    'status'  => false,
                    'message' => 'Assignment not found',
                ], 404);
            }

            // Parse the date and time in Asia/Kolkata timezone
            $notificationDate = Carbon::createFromFormat('d/m/Y', $request->date)->format('Y-m-d');
            $scheduledDateTime = Carbon::createFromFormat('Y-m-d H:i', $notificationDate . ' ' . $request->time, 'Asia/Kolkata');
            
            // Convert to UTC for storage and scheduling
            $scheduledDateTimeUTC = $scheduledDateTime->copy()->setTimezone('UTC');

            // Create scheduled notification
            $notification = Notification::create([
                'assign_base_id' => $request->assign_base_id,
                'emp_id' => $assignBase->emp_id,
                'user_id' => $assignBase->user_id,
                'notification_date' => $notificationDate,
                'notification_time' => $request->time,
                'remarks' => $request->remarks ?? 'reminder',
                'is_sent' => false,
            ]);

            // Schedule the job to run at the specified time
            SendScheduledNotification::dispatch($notification)
                ->delay($scheduledDateTimeUTC);

            return response()->json([
                'status'  => true,
                'message' => 'Notification scheduled successfully',
                'notification_info' => [
                    'notification_id' => $notification->id,
                    'assign_base_id' => $assignBase->id,
                    'customer_name' => $assignBase->customer_name,
                    'mobile' => $assignBase->mobile,
                    'scheduled_date' => $request->date,
                    'scheduled_time' => $request->time,
                    'scheduled_at' => $scheduledDateTime->format('d/m/Y H:i'),
                    'remarks' => $request->remarks,
                    'emp_id' => $assignBase->emp_id,
                    'will_send_at' => $scheduledDateTime->diffForHumans(),
                    'current_time' => Carbon::now('Asia/Kolkata')->format('H:i')
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status'  => false,
                'message' => 'Error: ' . $e->getMessage(),
            ], 500);
        }
    }


    public function sendScheduledNotification(Request $request)
    {
        try {
            $request->validate([
                'user_id'               => 'required|integer|exists:users,id',
                'title'                 => 'required|string',
                'notification_date'     => 'required|date_format:d/m/Y',
                'notification_time'     => 'required|date_format:H:i',
                'remarks'               => 'required|string',
            ]);

            // Parse date
            $notificationDate = Carbon::createFromFormat('d/m/Y', $request->notification_date)->format('Y-m-d');

            // Create notification record
            $notification = Notification::create([
                'emp_id'            => $request->user_id,
                'user_id'           => $request->user_id,
                'notification_date' => $notificationDate,
                'notification_time' => $request->notification_time,
                'remarks'           => $request->remarks,
                'is_sent'           => false,
            ]);

            return response()->json([
                'status'  => true,
                'message' => 'Scheduled notification created',
                'data' => [
                    'id' => $notification->id,
                    'scheduled_for' => $notificationDate . ' ' . $request->notification_time,
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status'  => false,
                'message' => 'Error: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Manually trigger processing of scheduled notifications
     */
    public function processScheduledNotifications(Request $request)
    {
        try {
            // Run the command programmatically
            Artisan::call('notifications:process-scheduled');
            $output = Artisan::output();

            return response()->json([
                'status' => true,
                'message' => 'Scheduled notifications processed successfully',
                'output' => trim($output)
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Error processing scheduled notifications: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Schedule notification job to run at the specified time
     */
    private function scheduleNotificationIfNeeded($notification)
    {
        try {
            // Combine date and time to create the scheduled datetime
            $scheduledDateTime = Carbon::parse($notification->notification_date->format('Y-m-d') . ' ' . $notification->notification_time);
            
            // Only schedule if the notification is in the future
            if ($scheduledDateTime->isFuture()) {
                // Schedule the job to run at the specified time
                SendScheduledNotification::dispatch($notification)->delay($scheduledDateTime);
                
                Log::info('Notification scheduled', [
                    'notification_id' => $notification->id,
                    'scheduled_for' => $scheduledDateTime->format('Y-m-d H:i:s'),
                    'emp_id' => $notification->emp_id
                ]);
            } else {
                // If the notification time has already passed, queue it immediately
                SendScheduledNotification::dispatch($notification);
                
                Log::info('Past due notification queued immediately', [
                    'notification_id' => $notification->id,
                    'scheduled_for' => $scheduledDateTime->format('Y-m-d H:i:s'),
                    'emp_id' => $notification->emp_id
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Error scheduling notification', [
                'notification_id' => $notification->id,
                'error' => $e->getMessage()
            ]);
        }
    }
}