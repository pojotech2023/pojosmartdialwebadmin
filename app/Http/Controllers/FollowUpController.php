<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Contracts\Encryption\DecryptException;

class FollowUpController extends Controller
{
    public function index(Request $request)
    {
        $currentUser = Auth::user();

        if (!$currentUser->isAdmin() && !$currentUser->isAccount()) {
            return redirect()->route('dashboard')->with('error', 'Access restricted to Admin and Account users.');
        }

        $notifications = $this->notificationsQuery($request, $currentUser)
            ->orderBy('notification_date')
            ->orderBy('notification_time')
            ->paginate(20)
            ->withQueryString();

        return view('follow-ups.index', compact('notifications'));
    }

    public function data(Request $request)
    {
        $currentUser = Auth::user();

        if (!$currentUser->isAdmin() && !$currentUser->isAccount()) {
            return response()->json([
                'status' => false,
                'message' => 'Access restricted to Admin and Account users.',
            ], 403);
        }

        $validated = $request->validate([
            'search' => 'nullable|string|max:255',
            'per_page' => 'nullable|integer|min:1|max:100',
        ]);

        $notifications = $this->notificationsQuery($request, $currentUser)
            ->orderBy('notification_date')
            ->orderBy('notification_time')
            ->paginate((int) ($validated['per_page'] ?? 20))
            ->withQueryString();

        $notifications->getCollection()->transform(function (Notification $notification) {
            $lead = $notification->assignBase;

            return [
                'id' => $notification->id,
                'assign_base_id' => $notification->assign_base_id,
                'customer_name' => $lead->customer_name,
                'company_name' => $lead->company_name,
                'mobile' => $lead->mobile,
                'telecaller_name' => $notification->employee->name,
                'notification_status' => $notification->status ?? '',
                'remarks' => $notification->remarks ?? '',
                'notification_date' => $notification->notification_date?->format('Y-m-d'),
                'notification_time' => $notification->notification_time
                    ? Carbon::parse($notification->notification_time)->format('H:i:s')
                    : null,
                'is_sent' => (bool) $notification->is_sent,
            ];
        });

        return response()->json([
            'status' => true,
            'data' => $notifications->items(),
            'meta' => [
                'current_page' => $notifications->currentPage(),
                'last_page' => $notifications->lastPage(),
                'per_page' => $notifications->perPage(),
                'total' => $notifications->total(),
                'from' => $notifications->firstItem(),
                'to' => $notifications->lastItem(),
            ],
        ]);
    }

    public function apiData(Request $request)
    {
        $validated = $request->validate([
            'emp_id' => 'required|integer|exists:users,id',
            'search' => 'nullable|string|max:255',
            'page' => 'nullable|integer|min:1',
            'per_page' => 'nullable|integer|min:1|max:100',
        ]);

        $token = $request->bearerToken();
        if (!$token) {
            return response()->json([
                'status' => false,
                'message' => 'A mobile login token is required.',
            ], 401);
        }

        try {
            $claims = explode('|', Crypt::decryptString($token));
        } catch (DecryptException $exception) {
            return response()->json([
                'status' => false,
                'message' => 'The mobile login token is invalid or expired.',
            ], 401);
        }

        if (
            count($claims) !== 3
            || $claims[0] !== 'mobile-follow-ups'
            || !ctype_digit($claims[1])
            || !ctype_digit($claims[2])
            || (int) $claims[2] <= now()->timestamp
        ) {
            return response()->json([
                'status' => false,
                'message' => 'The mobile login token is invalid or expired.',
            ], 401);
        }

        if ((int) $validated['emp_id'] !== (int) $claims[1]) {
            return response()->json([
                'status' => false,
                'message' => 'The requested employee does not match the mobile login.',
            ], 403);
        }

        $user = User::find((int) $claims[1]);
        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'The mobile login token is invalid or expired.',
            ], 401);
        }

        if (!$user->isAdmin() && !$user->isTelecaller()) {
            return response()->json([
                'status' => false,
                'message' => 'Only Admin and Telecaller users can access follow-up reminders.',
            ], 403);
        }

        $query = $this->notificationsQuery($request, $user);
        if (!$user->isAdmin()) {
            $query->where('emp_id', $user->id);
        }

        $notifications = $query
            ->orderBy('notification_date')
            ->orderBy('notification_time')
            ->paginate((int) ($validated['per_page'] ?? 20));

        return response()->json([
            'status' => true,
            'data' => $this->formatNotifications($notifications->getCollection()),
            'pagination' => [
                'current_page' => $notifications->currentPage(),
                'last_page' => $notifications->lastPage(),
                'per_page' => $notifications->perPage(),
                'total' => $notifications->total(),
            ],
        ]);
    }

    private function notificationsQuery(Request $request, User $currentUser): Builder
    {
        $query = Notification::with(['employee', 'assignBase.assignedUser', 'assignBase.product'])
            ->whereHas('assignBase')
            ->whereHas('employee', function ($query) {
                $query->where(function ($roleQuery) {
                    $roleQuery->whereRaw("LOWER(TRIM(COALESCE(role, ''))) = ?", ['telecaller'])
                        ->orWhereRaw("TRIM(COALESCE(role, '')) = ''");
                })
                    ->whereRaw("LOWER(TRIM(COALESCE(name, ''))) <> ?", ['admin'])
                    ->whereRaw("LOWER(TRIM(COALESCE(email, ''))) <> ?", ['admin@gmail.com'])
                    ->where('id', '<>', 10);
            })
            ->where(function ($query) {
                $query->whereNull('status')
                    ->orWhereRaw("UPPER(TRIM(COALESCE(status, ''))) <> ?", ['NOPE']);
            });

        if ($currentUser->isAccount()) {
            $query->whereHas('assignBase', function ($query) use ($currentUser) {
                $query->where('assigned_to', $currentUser->id);
            });
        }

        if ($request->filled('search')) {
            $search = trim($request->input('search'));
            $query->where(function ($query) use ($search) {
                $query->where('remarks', 'like', '%' . $search . '%')
                    ->orWhere('status', 'like', '%' . $search . '%')
                    ->orWhereHas('employee', function ($employeeQuery) use ($search) {
                        $employeeQuery->where('name', 'like', '%' . $search . '%');
                    })
                    ->orWhereHas('assignBase', function ($leadQuery) use ($search) {
                        $leadQuery->where('customer_name', 'like', '%' . $search . '%')
                            ->orWhere('company_name', 'like', '%' . $search . '%')
                            ->orWhere('mobile', 'like', '%' . $search . '%');
                    });
            });
        }

        return $query;
    }

    private function formatNotifications($notifications): array
    {
        return $notifications->map(function (Notification $notification) {
            $lead = $notification->assignBase;

            return [
                'id' => $notification->id,
                'assign_base_id' => $notification->assign_base_id,
                'customer_name' => $lead->customer_name,
                'company_name' => $lead->company_name,
                'mobile' => $lead->mobile,
                'telecaller_name' => $notification->employee->name,
                'notification_status' => $notification->status ?? '',
                'remarks' => $notification->remarks ?? '',
                'notification_date' => $notification->notification_date?->format('Y-m-d'),
                'notification_time' => $notification->notification_time
                    ? Carbon::parse($notification->notification_time)->format('H:i:s')
                    : null,
                'is_sent' => (bool) $notification->is_sent,
            ];
        })->all();
    }
}
