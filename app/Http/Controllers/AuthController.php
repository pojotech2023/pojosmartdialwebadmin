<?php

namespace App\Http\Controllers;
use App\Models\AssignBase;
use App\Models\Notification;
use App\Models\User;
use App\Models\Product;
use App\Models\LeadDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Exports\AssignBaseExport;
use Maatwebsite\Excel\Facades\Excel;
use Carbon\Carbon;

class AuthController extends Controller
{
    // ---------- LOGIN ----------

    // Show login page
    public function showLoginForm()
    {
        return view('auth.login');
    }

    // Handle login
    public function login(Request $request)
    {
        $request->validate([
            'email'    => 'required|string',
            'password' => 'required|string',
        ]);

        $loginInput = trim($request->email);
        $password = $request->password;

        // Check whether loginInput is email or mobile number
        $fieldType = filter_var($loginInput, FILTER_VALIDATE_EMAIL) ? 'email' : 'mobile';

        $user = User::where($fieldType, $loginInput)->first();

        if ($user && Hash::check($password, $user->password)) {
            // Telecaller accounts are restricted to mobile app login only
            if ($user->isTelecaller() && !$user->isAdmin()) {
                return back()->withInput()->withErrors([
                    'email' => 'Telecaller accounts are restricted to mobile app login only. Web login is reserved for Account and Admin roles.',
                ]);
            }

            Auth::login($user);
            if ($request->hasSession()) {
                $request->session()->regenerate();
            }

            // Redirect to dashboard after login
            return redirect()->route('dashboard');
        }

        return back()->withInput()->withErrors([
            'email' => 'Invalid email/mobile number or password.',
        ]);
    }

    // ---------- LOGOUT ----------
    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login');
    }

    // ---------- DASHBOARD ----------
    public function dashboard(Request $request)
    {
        $currentUser = Auth::user();
        $isAdmin = $currentUser ? $currentUser->isAdmin() : false;
        $isAccount = $currentUser ? $currentUser->isAccount() : false;

        // Employee dropdown
        if ($isAdmin) {
            $employees = User::orderBy('name')->pluck('name', 'id');
        } else {
            // Account user locked to their own account
            $employees = collect([$currentUser->id => $currentUser->name]);
        }

        // Product dropdown
        $products = Product::where('is_active', true)->orderBy('name')->pluck('name', 'id');

        // ================= CARD COUNTS =================
        if (!$isAdmin) {
            $myTotal = AssignBase::where(function ($q) use ($currentUser) {
                $q->where('emp_id', $currentUser->id)
                  ->orWhere('assigned_to', $currentUser->id);
            })->count();

            $cardData = collect([
                (object)[
                    'id'    => $currentUser->id,
                    'name'  => $currentUser->name,
                    'total' => $myTotal,
                ]
            ]);
        } else {
            $cardData = User::leftJoin('assign_bases', 'users.id', '=', 'assign_bases.emp_id')
                ->select(
                    'users.id',
                    'users.name',
                    \DB::raw('COUNT(assign_bases.id) as total')
                )
                ->groupBy('users.id', 'users.name')
                ->orderBy('users.name')
                ->get();
        }

        // ================= TABLE DATA =================
        $query = AssignBase::with(['employee', 'product', 'assignedUser', 'leadDetail']);

        if (!$isAdmin) {
            // Account users see records assigned to them (either directly or assigned_to)
            $query->where(function ($q) use ($currentUser) {
                $q->where('emp_id', $currentUser->id)
                  ->orWhere('assigned_to', $currentUser->id);
            });
        } elseif ($request->filled('employee_id')) {
            $query->where(function ($q) use ($request) {
                $q->where('emp_id', $request->employee_id)
                  ->orWhere('assigned_to', $request->employee_id);
            });
        }

        if ($request->filled('product_id')) {
            $query->where('product_id', $request->product_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('from_date')) {
            $query->whereDate('assign_date', '>=', $request->from_date);
        }

        if ($request->filled('to_date')) {
            $query->whereDate('assign_date', '<=', $request->to_date);
        }

        // ✅ PAGINATION (IMPORTANT)
        $paginatedRows = $query
            ->orderBy('assign_date', 'desc')
            ->paginate(20)
            ->withQueryString();

        return view('dashboard', compact(
            'employees',
            'products',
            'paginatedRows',
            'cardData',
            'isAdmin',
            'isAccount'
        ));
    }

    // ---------- NOTIFICATIONS DATA (JSON) ----------
    public function notificationsData(Request $request)
    {
        try {
        $now = Carbon::now();

        $query = Notification::with(['employee', 'assignBase'])
            ->orderBy('notification_date', 'asc')
            ->orderBy('notification_time', 'asc');

        if ($request->filled('assign_base_id')) {
            $query->where('assign_base_id', $request->assign_base_id);
        }

        $all = $query->get();

        $due    = [];
        $upcoming = [];
        $history  = [];

        foreach ($all as $n) {
            if (!$n->notification_date) continue;
            $notifDatetime = Carbon::parse($n->notification_date->format('Y-m-d') . ' ' . ($n->notification_time ?? '00:00'));
            $row = [
                'id'                => $n->id,
                'employee_name'     => $n->employee?->name ?? 'Unknown',
                'customer_name'     => $n->assignBase?->customer_name ?? '—',
                'company_name'      => $n->assignBase?->company_name ?? '—',
                'mobile'            => $n->assignBase?->mobile ?? '—',
                'notification_date' => $n->notification_date ? $n->notification_date->format('d/m/Y') : '—',
                'notification_time' => $n->notification_time ?? '—',
                'remarks'           => $n->remarks ?? '',
                'is_sent'           => $n->is_sent,
                'datetime_epoch'    => $notifDatetime->timestamp,
            ];

            if ($n->is_sent) {
                $history[] = $row;
            } elseif ($notifDatetime->lte($now)) {
                $due[] = $row;
            } else {
                $upcoming[] = $row;
            }
        }

        // Sort history newest first
        usort($history, fn($a, $b) => $b['datetime_epoch'] - $a['datetime_epoch']);

        return response()->json([
            'due'       => $due,
            'upcoming'  => $upcoming,
            'history'   => $history,
            'due_count' => count($due),
        ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    // ---------- WHATSAPP NOTIFICATIONS DATA (VIEW ONLY, JSON) ----------
    public function whatsappNotificationsData(Request $request)
    {
        try {
            $request->validate([
                'assign_base_id' => 'required|integer|exists:assign_bases,id',
            ]);

            $assignBase = AssignBase::findOrFail($request->assign_base_id);

            $contact = \App\Models\WhatsappContact::where('mobile', $assignBase->mobile)->first();

            $items = [];

            if ($contact) {
                $recipients = \App\Models\WhatsappNotificationRecipient::with('notification')
                    ->where('whatsapp_contact_id', $contact->id)
                    ->orderByDesc('id')
                    ->get();

                foreach ($recipients as $recipient) {
                    $notification = $recipient->notification;
                    if (!$notification) continue;

                    $params = $notification->template_params ?? [];

                    $items[] = [
                        'id'           => $recipient->id,
                        'amount'       => $params[0] ?? null,
                        'due_date'     => $params[1] ?? null,
                        'header_image' => $notification->header_image_path
                            ? \Illuminate\Support\Facades\Storage::disk('public')->url($notification->header_image_path)
                            : null,
                        'scheduled_at' => $notification->scheduled_at ? $notification->scheduled_at->format('d/m/Y H:i') : '—',
                        'status'       => $recipient->status,
                        'error'        => $recipient->error_message,
                    ];
                }
            }

            return response()->json([
                'customer_name' => $assignBase->customer_name,
                'mobile'        => $assignBase->mobile,
                'items'         => $items,
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    // ---------- MARK NOTIFICATION SEEN ----------
    public function markNotificationSeen(Request $request)
    {
        $ids = $request->input('ids', []);
        if (!empty($ids)) {
            Notification::whereIn('id', $ids)->update(['is_sent' => true]);
        }
        return response()->json(['status' => true]);
    }

    // ---------- EXPORT DASHBOARD DATA ----------
    public function exportDashboard(Request $request)
    {
        $currentUser = Auth::user();
        $isAdmin = $currentUser ? $currentUser->isAdmin() : false;

        $query = AssignBase::with(['employee', 'product', 'loanApplication', 'assignedUser', 'leadDetail']);

        if (!$isAdmin) {
            $query->where(function ($q) use ($currentUser) {
                $q->where('emp_id', $currentUser->id)
                  ->orWhere('assigned_to', $currentUser->id);
            });
        } elseif ($request->filled('employee_id') && $request->employee_id != '') {
            $query->where(function ($q) use ($request) {
                $q->where('emp_id', $request->employee_id)
                  ->orWhere('assigned_to', $request->employee_id);
            });
        }

        if ($request->filled('product_id') && $request->product_id != '') {
            $query->where('product_id', $request->product_id);
        }

        if ($request->filled('status') && $request->status != '') {
            $query->where('status', $request->status);
        }

        if ($request->filled('from_date') && $request->from_date != '') {
            $query->whereDate('assign_date', '>=', $request->from_date);
        }

        if ($request->filled('to_date') && $request->to_date != '') {
            $query->whereDate('assign_date', '<=', $request->to_date);
        }

        $data = $query->orderBy('assign_date', 'desc')->get();

        $fileName = 'dashboard_data_' . now()->format('Y-m-d_H-i-s') . '.xlsx';

        return Excel::download(new AssignBaseExport($data), $fileName);
    }

    /**
     * Fetch complete mobile app submitted details for a lead
     * Used by dashboard "Eye" icon modal and lead details view
     */
    public function leadDetailsData($assignBaseId)
    {
        try {
            $lead = AssignBase::with(['employee', 'assignedUser', 'product', 'leadDetail'])->find($assignBaseId);

            if (!$lead) {
                return response()->json([
                    'status'  => false,
                    'message' => 'Lead not found',
                ], 404);
            }

            $leadDetail = $lead->leadDetail;

            $notes = Notification::with('employee:id,name')
                ->where('assign_base_id', $assignBaseId)
                ->orderBy('id', 'desc')
                ->get()
                ->map(fn($n) => [
                    'id'                => $n->id,
                    'emp_id'            => $n->emp_id,
                    'employee_name'     => $n->employee?->name ?? '—',
                    'notification_date' => $n->notification_date ? Carbon::parse($n->notification_date)->format('d/m/Y') : null,
                    'notification_time' => $n->notification_time,
                    'remarks'           => $n->remarks,
                    'status'            => $n->status,
                    'appointment_date'  => $n->appointment_date,
                    'appointment_time'  => $n->appointment_time,
                    'location'          => $n->location,
                    'alternate_number'  => $n->alternate_number,
                    'created_at'        => $n->created_at ? $n->created_at->format('d/m/Y h:i A') : null,
                ]);

            // Latest appointment details
            $latestAppointment = Notification::where('assign_base_id', $assignBaseId)
                ->where(function($q) {
                    $q->whereNotNull('appointment_date')
                      ->orWhereNotNull('status')
                      ->orWhereNotNull('location');
                })
                ->orderBy('id', 'desc')
                ->first();

            return response()->json([
                'status' => true,
                'data'   => [
                    'id'                  => $lead->id,
                    'customer_name'       => $lead->customer_name,
                    'company_name'        => $lead->company_name,
                    'mobile'              => $lead->mobile,
                    'status'              => $lead->status ?? 'NEW',
                    'product_type'        => $lead->product?->name ?? $lead->product_type ?? 'Website',
                    'assign_date'         => $lead->assign_date ? Carbon::parse($lead->assign_date)->format('d/m/Y') : '—',
                    'telecaller'          => [
                        'id'     => $lead->employee?->id,
                        'name'   => $lead->employee?->name ?? '—',
                        'mobile' => $lead->employee?->mobile ?? '—',
                        'role'   => $lead->employee?->role ?? 'Telecaller',
                    ],
                    'assigned_to'         => [
                        'id'     => $lead->assignedUser?->id,
                        'name'   => $lead->assignedUser?->name ?? '—',
                        'mobile' => $lead->assignedUser?->mobile ?? '—',
                        'email'  => $lead->assignedUser?->email ?? '—',
                        'role'   => $lead->assignedUser?->role ?? 'Account',
                    ],
                    'project_details'     => [
                        'project_type'     => $leadDetail?->project_type ?? $lead->product_type ?? '—',
                        'cus_company_name' => $leadDetail?->cus_company_name ?? $lead->company_name ?? '—',
                        'cus_mail'         => $leadDetail?->cus_mail ?? '',
                        'price'            => $leadDetail?->price ? '₹' . number_format((float) preg_replace('/[^\d.]/', '', $leadDetail->price)) : ($leadDetail?->price ?? '—'),
                        'price_raw'        => $leadDetail?->price ?? '',
                        'gst'              => $leadDetail?->gst ?? 'N',
                        'gst_no'           => $leadDetail?->gst_no ?? '',
                    ],
                    'appointment_details' => $latestAppointment ? [
                        'status'           => $latestAppointment->status,
                        'appointment_date' => $latestAppointment->appointment_date,
                        'appointment_time' => $latestAppointment->appointment_time,
                        'location'         => $latestAppointment->location,
                        'alternate_number' => $latestAppointment->alternate_number,
                        'remarks'          => $latestAppointment->remarks,
                    ] : null,
                    'notes'               => $notes,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status'  => false,
                'message' => 'Error: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update Lead Status directly from Dashboard (for Account users & Admins)
     * POST /dashboard/update-lead-status
     */
    public function updateLeadStatus(Request $request)
    {
        try {
            $request->validate([
                'assign_base_id' => 'required|integer|exists:assign_bases,id',
                'status'         => 'required|string',
            ]);

            $currentUser = Auth::user();
            $lead = AssignBase::findOrFail($request->assign_base_id);

            // If account user, verify lead ownership
            if ($currentUser && !$currentUser->isAdmin()) {
                if ($lead->assigned_to != $currentUser->id && $lead->emp_id != $currentUser->id) {
                    return response()->json([
                        'status'  => false,
                        'message' => 'Unauthorized to update this lead',
                    ], 403);
                }
            }

            $status = strtoupper(trim($request->status));
            $lead->status = $status;
            $lead->save();

            // Track in notifications remarks
            if ($currentUser) {
                Notification::create([
                    'assign_base_id'    => $lead->id,
                    'emp_id'            => $currentUser->id,
                    'notification_date' => Carbon::now()->toDateString(),
                    'notification_time' => Carbon::now()->toTimeString(),
                    'remarks'           => 'Status changed to ' . $status . ' by ' . $currentUser->name,
                    'status'            => $status,
                ]);
            }

            return response()->json([
                'status'  => true,
                'message' => 'Status updated to ' . $status,
                'data'    => [
                    'id'     => $lead->id,
                    'status' => $lead->status,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status'  => false,
                'message' => 'Error: ' . $e->getMessage(),
            ], 500);
        }
    }

}
