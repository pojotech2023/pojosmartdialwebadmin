<?php

namespace App\Http\Controllers;

use App\Models\AssignBase;
use App\Models\Notification;
use App\Models\User;
use App\Models\WhatsappNotification;
use App\Models\Product;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class CalendarController extends Controller
{
    /**
     * Show the main Calendar page.
     */
    public function index(Request $request)
    {
        $currentUser = Auth::user();
        $isAdmin = $currentUser ? $currentUser->isAdmin() : false;

        if ($isAdmin) {
            // Admin can see and filter across all employees
            $employees = User::orderBy('name')->pluck('name', 'id');
        } else {
            // Regular employee: locked to their own account
            $employees = collect([$currentUser->id => $currentUser->name]);
        }

        // Determine default date (if there are events in specific month or current)
        $initialDate = Carbon::now();

        return view('calendar.index', compact(
            'employees',
            'isAdmin',
            'currentUser',
            'initialDate'
        ));
    }

    /**
     * Return JSON events for calendar grid / list view.
     */
    public function events(Request $request)
    {
        try {
            $currentUser = Auth::user();
            $isAdmin = $currentUser ? $currentUser->isAdmin() : false;

            // Enforce role-based access:
            // If user is Admin, they can filter by employee or view ALL
            // If user is NOT Admin, they can ONLY view what they created
            $targetEmpId = null;
            if (!$isAdmin) {
                $targetEmpId = $currentUser->id;
            } elseif ($request->filled('employee_id') && $request->employee_id !== 'all') {
                $targetEmpId = (int) $request->employee_id;
            }

            // Date Range
            $year = (int) $request->input('year', date('Y'));
            $month = (int) $request->input('month', date('n'));

            if ($request->filled('start_date') && $request->filled('end_date')) {
                $startDate = Carbon::parse($request->start_date)->startOfDay();
                $endDate = Carbon::parse($request->end_date)->endOfDay();
            } else {
                $carbonMonth = Carbon::createFromDate($year, $month, 1);
                $startDate = $carbonMonth->copy()->startOfMonth()->startOfWeek(Carbon::SUNDAY);
                $endDate = $carbonMonth->copy()->endOfMonth()->endOfWeek(Carbon::SATURDAY);
            }

            $typeFilter = $request->input('type', 'all'); // 'all', 'reminder', 'whatsapp'
            $statusFilter = $request->input('status', 'all'); // 'all', 'pending', 'sent'

            $items = [];
            $now = Carbon::now();

            // Total assigned bases count per employee
            $empBaseCounts = AssignBase::selectRaw('emp_id, count(*) as total')
                ->groupBy('emp_id')
                ->pluck('total', 'emp_id');

            $defaultProduct = Product::where('is_active', true)->first();

            // 1. Reminders (from notifications table)
            if ($typeFilter === 'all' || $typeFilter === 'reminder') {
                $notifQuery = Notification::with(['employee.application', 'assignBase.product', 'assignBase.loanApplication'])
                    ->whereBetween('notification_date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')]);

                if ($targetEmpId) {
                    $notifQuery->where('emp_id', $targetEmpId);
                }

                $notifications = $notifQuery->orderBy('notification_date')->orderBy('notification_time')->get();

                foreach ($notifications as $n) {
                    if (!$n->notification_date) continue;

                    $dateStr = $n->notification_date->format('Y-m-d');
                    $time24 = '00:00';
                    $time12 = '—';
                    if ($n->notification_time) {
                        try {
                            $time12 = Carbon::parse($n->notification_time)->format('h:i A');
                            $time24 = Carbon::parse($n->notification_time)->format('H:i');
                        } catch (\Exception $e) {
                            $time24 = substr($n->notification_time, 0, 5);
                            $time12 = $time24;
                        }
                    }
                    $notifDatetime = Carbon::parse($dateStr . ' ' . $time24);

                    $isDue = false;
                    $isSent = (bool) $n->is_sent;

                    if ($isSent) {
                        $statusLabel = 'Completed';
                        $statusClass = 'completed';
                    } elseif ($notifDatetime->lte($now)) {
                        $statusLabel = 'Due';
                        $statusClass = 'due';
                        $isDue = true;
                    } else {
                        $statusLabel = 'Upcoming';
                        $statusClass = 'upcoming';
                    }

                    // Status filter check
                    if ($statusFilter === 'pending' && $isSent) continue;
                    if ($statusFilter === 'sent' && !$isSent) continue;

                    $creatorName = $n->employee?->name ?? 'Unknown';
                    $empId = $n->emp_id ?? $n->employee?->id;
                    $loanApp = $n->assignBase?->loanApplication;

                    $prodId = $n->assignBase?->product_id ?? $n->assignBase?->product?->id;
                    $prodName = $n->assignBase?->product?->name ?? $n->assignBase?->product_type;
                    if (!$prodId && $defaultProduct) {
                        $prodId = $defaultProduct->id;
                    }
                    if (!$prodName) {
                        $prodName = $defaultProduct?->name ?? 'Website';
                    }

                    $items[] = [
                        'id'                => 'rem_' . $n->id,
                        'raw_id'            => $n->id,
                        'type'              => 'reminder',
                        'type_label'        => 'Reminder',
                        'icon'              => 'fas fa-bell',
                        'color'             => '#f59e0b',
                        'badge_color'       => 'warning',
                        'title'             => ($n->assignBase?->customer_name ?? 'Reminder') . ' (' . $creatorName . ')',
                        'date'              => $dateStr,
                        'display_date'      => $n->notification_date->format('d/m/Y'),
                        'time'              => $time12,
                        'time_24'           => $time24,
                        'time_12'           => $time12,
                        'customer_name'     => $n->assignBase?->customer_name ?? '—',
                        'company_name'      => $n->assignBase?->company_name ?? '—',
                        'product_id'        => (int) $prodId,
                        'product_type'      => (string) $prodName,
                        'mobile'            => $n->assignBase?->mobile ?? '—',
                        'customer_status'   => $n->assignBase?->status ?? '—',
                        'remarks'           => $n->remarks ?? '',
                        'status'            => $statusLabel,
                        'status_class'      => $statusClass,
                        'note_status'       => $n->status,
                        'appointment_date'  => $n->appointment_date,
                        'appointment_time'  => $n->appointment_time,
                        'location'          => $n->location,
                        'alternate_number'  => $n->alternate_number,
                        'is_sent'           => $isSent,
                        'is_due'            => $isDue,
                        'emp_id'            => $empId,
                        'added_by'          => $creatorName,
                        'added_by_avatar'   => strtoupper(substr($creatorName, 0, 1)),
                        'emp_mobile'        => $n->employee?->mobile ?? '—',
                        'emp_email'         => $n->employee?->email ?? '—',
                        'emp_application'   => $n->employee?->application?->name ?? 'Staff',
                        'emp_total_bases'   => $empId ? ($empBaseCounts[$empId] ?? 0) : 0,
                        'assign_base_id'    => $n->assign_base_id,
                        'loan_details_url'  => $n->assign_base_id ? url('/loan-application-details/' . $n->assign_base_id) : null,
                        'loan_application'  => $loanApp ? [
                            'id'              => $loanApp->id,
                            'app_number'      => $loanApp->app_number ?? '—',
                            'logged_bank'     => $loanApp->logged_bank ?? '—',
                            'category'        => $loanApp->category ?? '—',
                            'promo_code'      => $loanApp->promo_code ?? '—',
                            'req_amount'      => $loanApp->req_amount ? number_format($loanApp->req_amount) : '—',
                            'approved_amount' => $loanApp->approved_amount ? number_format($loanApp->approved_amount) : '—',
                            'status'          => $loanApp->status ?? '—',
                            'login_date'      => $loanApp->login_date ? $loanApp->login_date->format('d/m/Y') : '—',
                            'bank_manager'    => $loanApp->bank_manager ?? '—',
                            'location'        => $loanApp->location ?? '—',
                            'remarks'         => $loanApp->remarks ?? '—',
                        ] : null,
                    ];
                }
            }

            // 2. WhatsApp Notifications (from whatsapp_notifications table)
            if ($typeFilter === 'all' || $typeFilter === 'whatsapp') {
                $waQuery = WhatsappNotification::with(['employee.application', 'assignBase.product', 'assignBase.loanApplication', 'recipients.contact'])
                    ->whereBetween('scheduled_at', [$startDate, $endDate]);

                if ($targetEmpId) {
                    $waQuery->where(function($q) use ($targetEmpId) {
                        $q->where('emp_id', $targetEmpId)
                          ->orWhereHas('assignBase', function($ab) use ($targetEmpId) {
                              $ab->where('emp_id', $targetEmpId);
                          });
                    });
                }

                $whatsappList = $waQuery->orderBy('scheduled_at')->get();

                foreach ($whatsappList as $wa) {
                    if (!$wa->scheduled_at) continue;

                    $dateStr = $wa->scheduled_at->format('Y-m-d');
                    $timeStr = $wa->scheduled_at->format('H:i');
                    $time12  = $wa->scheduled_at->format('h:i A');

                    $statusRaw = strtolower($wa->status ?? 'pending');
                    $statusClass = match($statusRaw) {
                        'completed', 'sent' => 'completed',
                        'processing'        => 'processing',
                        'failed'            => 'failed',
                        default             => 'pending',
                    };
                    $statusLabel = ucfirst($statusRaw);

                    // Status filter check
                    if ($statusFilter === 'pending' && in_array($statusRaw, ['completed', 'sent'])) continue;
                    if ($statusFilter === 'sent' && !in_array($statusRaw, ['completed', 'sent'])) continue;

                    $customerName = $wa->assignBase?->customer_name;
                    $companyName = $wa->assignBase?->company_name ?? '—';
                    $mobile = $wa->assignBase?->mobile;

                    if (!$customerName && $wa->recipients->isNotEmpty()) {
                        $firstRecipient = $wa->recipients->first();
                        $customerName = $firstRecipient->contact?->name ?? 'Recipient';
                        $mobile = $firstRecipient->contact?->mobile ?? '—';
                    }

                    $params = $wa->template_params ?? [];
                    $amount = $params[0] ?? null;
                    $dueDate = $params[1] ?? null;

                    $creatorName = $wa->employee?->name;
                    if (!$creatorName) {
                        $creatorName = ($wa->source === 'mobile') ? 'Mobile User' : 'Admin';
                    }

                    $headerImageUrl = null;
                    if ($wa->header_image_path) {
                        $headerImageUrl = Storage::disk('public')->url($wa->header_image_path);
                    }

                    $empId = $wa->emp_id ?? $wa->employee?->id;
                    $loanApp = $wa->assignBase?->loanApplication;

                    $prodId = $wa->assignBase?->product_id ?? $wa->assignBase?->product?->id;
                    $prodName = $wa->assignBase?->product?->name ?? $wa->assignBase?->product_type;
                    if (!$prodId && $defaultProduct) {
                        $prodId = $defaultProduct->id;
                    }
                    if (!$prodName) {
                        $prodName = $defaultProduct?->name ?? 'Website';
                    }

                    $items[] = [
                        'id'                => 'wa_' . $wa->id,
                        'raw_id'            => $wa->id,
                        'type'              => 'whatsapp',
                        'type_label'        => 'WhatsApp',
                        'icon'              => 'fab fa-whatsapp',
                        'color'             => '#25d366',
                        'badge_color'       => 'success',
                        'title'             => ($customerName ?: 'WhatsApp') . ' (' . $creatorName . ')',
                        'date'              => $dateStr,
                        'display_date'      => $wa->scheduled_at->format('d/m/Y'),
                        'time'              => $time12,
                        'time_24'           => $time24,
                        'time_12'           => $time12,
                        'customer_name'     => $customerName ?: 'WhatsApp Notification',
                        'company_name'      => $companyName,
                        'product_id'        => (int) $prodId,
                        'product_type'      => (string) $prodName,
                        'mobile'            => $mobile ?: '—',
                        'amount'            => $amount,
                        'due_date'          => $dueDate,
                        'template_name'     => $wa->template_name,
                        'header_image'      => $headerImageUrl,
                        'source'            => $wa->source ?? 'web',
                        'status'            => $statusLabel,
                        'status_class'      => $statusClass,
                        'emp_id'            => $empId,
                        'added_by'          => $creatorName,
                        'added_by_avatar'   => strtoupper(substr($creatorName, 0, 1)),
                        'emp_mobile'        => $wa->employee?->mobile ?? '—',
                        'emp_email'         => $wa->employee?->email ?? '—',
                        'emp_application'   => $wa->employee?->application?->name ?? 'Staff',
                        'emp_total_bases'   => $empId ? ($empBaseCounts[$empId] ?? 0) : 0,
                        'assign_base_id'    => $wa->assign_base_id,
                        'loan_details_url'  => $wa->assign_base_id ? url('/loan-application-details/' . $wa->assign_base_id) : null,
                        'loan_application'  => $loanApp ? [
                            'id'              => $loanApp->id,
                            'app_number'      => $loanApp->app_number ?? '—',
                            'logged_bank'     => $loanApp->logged_bank ?? '—',
                            'category'        => $loanApp->category ?? '—',
                            'promo_code'      => $loanApp->promo_code ?? '—',
                            'req_amount'      => $loanApp->req_amount ? number_format($loanApp->req_amount) : '—',
                            'approved_amount' => $loanApp->approved_amount ? number_format($loanApp->approved_amount) : '—',
                            'status'          => $loanApp->status ?? '—',
                            'login_date'      => $loanApp->login_date ? $loanApp->login_date->format('d/m/Y') : '—',
                            'bank_manager'    => $loanApp->bank_manager ?? '—',
                            'location'        => $loanApp->location ?? '—',
                            'remarks'         => $loanApp->remarks ?? '—',
                        ] : null,
                    ];
                }
            }

            // Group events by date (for calendar day cells)
            $byDate = [];
            $reminderCount = 0;
            $whatsappCount = 0;
            $dueCount = 0;
            $completedCount = 0;

            foreach ($items as $item) {
                $d = $item['date'];
                if (!isset($byDate[$d])) {
                    $byDate[$d] = [];
                }
                $byDate[$d][] = $item;

                if ($item['type'] === 'reminder') {
                    $reminderCount++;
                    if ($item['status_class'] === 'due') $dueCount++;
                    if ($item['status_class'] === 'completed') $completedCount++;
                } else {
                    $whatsappCount++;
                    if ($item['status_class'] === 'completed') $completedCount++;
                    if ($item['status_class'] === 'pending') $dueCount++;
                }
            }

            return response()->json([
                'success'        => true,
                'is_admin'       => $isAdmin,
                'logged_in_user' => [
                    'id'   => $currentUser?->id,
                    'name' => $currentUser?->name,
                ],
                'filter_emp_id'  => $targetEmpId,
                'counts'         => [
                    'total'      => count($items),
                    'reminders'  => $reminderCount,
                    'whatsapp'   => $whatsappCount,
                    'due'        => $dueCount,
                    'completed'  => $completedCount,
                ],
                'events_by_date' => $byDate,
                'events'         => $items,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error'   => $e->getMessage(),
            ], 500);
        }
    }
}
