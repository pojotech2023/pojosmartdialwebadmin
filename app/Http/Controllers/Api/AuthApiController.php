<?php

namespace App\Http\Controllers\Api;
use App\Models\AssignBase;
use App\Models\Notification;
use App\Models\DeviceToken;
use App\Models\LeadDetail;
use App\Models\TelecallerStatus;
use App\Models\Product;
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
use Illuminate\Support\Facades\Auth;

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

        // Success response
        return response()->json([
            'status'           => true,
            'message'          => 'Login successful',
            'application_id'   => $user->application_id,
            'application_type' => $user->application->name ?? null,
            'role'             => $user->role ?? 'Telecaller',
            'is_telecaller'    => $user->isTelecaller(),
            'is_account'       => $user->isAccount(),
            'data' => [
                'emp_id' => $user->id,
                'name'   => $user->name,
                'mobile' => $user->mobile,
                'role'   => $user->role ?? 'Telecaller',
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
            'status' => 'nullable|string',
        ]);

        // Check if employee exists
        $user = User::find($request->emp_id);
        if (!$user) {
            return response()->json([
                'status'  => false,
                'message' => 'Employee not found',
            ], 404);
        }

        // Accept assign_date or date in d/m/Y or Y-m-d format
        $rawDate = $request->input('assign_date', $request->input('date'));
        if (!$rawDate) {
            return response()->json([
                'status'  => false,
                'message' => 'The assign_date or date field is required.',
            ], 422);
        }

        try {
            if (str_contains($rawDate, '/')) {
                $assignDate = Carbon::createFromFormat('d/m/Y', $rawDate)->format('Y-m-d');
            } else {
                $assignDate = Carbon::parse($rawDate)->format('Y-m-d');
            }
        } catch (\Exception $e) {
            $assignDate = Carbon::parse($rawDate)->format('Y-m-d');
        }

        $defaultProduct = \App\Models\Product::where('is_active', true)->first();

        // Query with filter: emp_id or assigned_to, assign_date, and optional status
        $data = AssignBase::with(['product', 'employee', 'assignedUser', 'leadDetail'])
            ->where(function ($q) use ($request) {
                $q->where('emp_id', $request->emp_id)
                  ->orWhere('assigned_to', $request->emp_id);
            })
            ->where('assign_date', '=', $assignDate)
            ->when($request->filled('status') && strtoupper($request->status) !== 'ALL', fn ($q) => $q->where('status', $request->status))
            ->get()
            ->map(function($row) use ($defaultProduct) {
                $productId = $row->product_id ?? $row->product?->id;
                $productType = $row->product?->name ?? $row->product_type;

                // Ensure non-null return value as requested
                if (!$productId && $defaultProduct) {
                    $productId = $defaultProduct->id;
                }
                if (!$productType) {
                    $productType = $defaultProduct?->name ?? 'Website';
                }

                $empRole = $row->employee?->role ?? 'Telecaller';
                $isAccount = strtolower($empRole) === 'account';

                return [
                    'id'                  => $row->id,
                    'emp_id'              => $row->emp_id,
                    'assigned_to'         => $row->assigned_to ? (int)$row->assigned_to : null,
                    'assigned_to_name'    => $row->assignedUser?->name,
                    'employee_name'       => $row->employee?->name ?? '—',
                    'employee_role'       => $empRole,
                    'is_account'          => $isAccount,
                    'assigned_to_account' => $isAccount,
                    'customer_name'       => $row->customer_name,
                    'company_name'        => $row->company_name,
                    'product_id'          => (int) $productId,
                    'product_type'        => (string) $productType,
                    'status'              => $row->status ?? 'NEW',
                    'mobile'              => $row->mobile ?? '—',
                    'date'                => Carbon::parse($row->assign_date)->format('d/m/Y'),
                ];
            });

        return response()->json([
            'status' => true,
            'data'   => $data,
        ]);
    }
  
    public function updateAssignBaseStatus(Request $request)
    {
        $request->validate([
            'id'     => 'required|integer',
            'emp_id' => 'nullable|integer',
            'status' => 'required|string',
        ]);

        $query = AssignBase::where('id', $request->id);
        if ($request->filled('emp_id')) {
            $empId = $request->emp_id;
            $query->where(function($q) use ($empId) {
                $q->where('emp_id', $empId)
                  ->orWhere('assigned_to', $empId);
            });
        }

        $assignBase = $query->first();
        if (!$assignBase) {
            return response()->json([
                'status'  => false,
                'message' => 'Assign Base record not found or does not belong to the user',
            ], 404);
        }

        $assignBase->status = strtoupper(trim($request->status));
        $assignBase->save();

        return response()->json([
            'status'  => true,
            'message' => 'Status updated successfully to ' . $assignBase->status,
            'data'    => [
                'id'     => $assignBase->id,
                'status' => $assignBase->status,
            ],
        ]);
    }

    /**
     * Update Lead Status API (for Mobile App & Web)
     * POST /api/update-lead-status
     * POST /api/update-status
     */
    public function updateLeadStatus(Request $request)
    {
        try {
            $id = $request->input('assign_base_id', $request->input('id'));
            if (!$id) {
                return response()->json([
                    'status'  => false,
                    'message' => 'assign_base_id or id is required',
                ], 422);
            }

            $status = strtoupper(trim((string) $request->input('status', '')));
            if (!$status) {
                return response()->json([
                    'status'  => false,
                    'message' => 'status is required',
                ], 422);
            }

            $assignBase = AssignBase::findOrFail($id);
            $assignBase->status = $status;
            $assignBase->save();

            // Optional: if remarks passed, add to notifications history
            if ($request->filled('remarks')) {
                $empId = $request->input('emp_id', $request->input('assigned_to', $assignBase->assigned_to ?? $assignBase->emp_id));
                Notification::create([
                    'assign_base_id'    => $assignBase->id,
                    'emp_id'            => $empId,
                    'notification_date' => Carbon::now()->toDateString(),
                    'notification_time' => Carbon::now()->toTimeString(),
                    'remarks'           => $request->input('remarks'),
                    'status'            => $status,
                ]);
            }

            return response()->json([
                'status'  => true,
                'message' => 'Status updated successfully to ' . $status,
                'data'    => [
                    'id'     => $assignBase->id,
                    'status' => $assignBase->status,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status'  => false,
                'message' => 'Error: ' . $e->getMessage(),
            ], 500);
        }
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
                'company_name' => 'required|string',
                'product_id'   => 'nullable|integer|exists:products,id',
                'product_type' => 'nullable|string',
                'status'       => 'nullable|string',
                'mobile'       => 'required|string',
            ]);

            // Accept name or customer_name
            $customerName = $request->input('customer_name', $request->input('name'));
            if (!$customerName) {
                return response()->json([
                    'status'  => false,
                    'message' => 'The customer name is required.',
                ], 422);
            }

            // Accept date or assign_date in d/m/Y or Y-m-d format
            $rawDate = $request->input('date', $request->input('assign_date'));
            if (!$rawDate) {
                return response()->json([
                    'status'  => false,
                    'message' => 'The date field is required.',
                ], 422);
            }

            // Check if employee exists
            $user = User::find($request->emp_id);
            if (!$user) {
                return response()->json([
                    'status'  => false,
                    'message' => 'Employee not found',
                ], 404);
            }

            // Determine product_id and product_type
            $productId = $request->input('product_id');
            $productType = $request->input('product_type');

            if ($productId && !$productType) {
                $p = \App\Models\Product::find($productId);
                $productType = $p?->name;
            } elseif ($productType && !$productId) {
                $p = \App\Models\Product::firstOrCreate(
                    ['name' => trim($productType)],
                    ['is_active' => true]
                );
                $productId = $p->id;
                $productType = $p->name;
            } elseif ($productId && $productType) {
                // If both provided, ensure product exists and name matches
                $p = \App\Models\Product::find($productId);
                if ($p) {
                    $productType = $p->name;
                } else {
                    $p = \App\Models\Product::firstOrCreate(
                        ['name' => trim($productType)],
                        ['is_active' => true]
                    );
                    $productId = $p->id;
                }
            } else {
                // Fallback to active default product if neither sent
                $defaultProd = \App\Models\Product::where('is_active', true)->first();
                if ($defaultProd) {
                    $productId = $defaultProd->id;
                    $productType = $defaultProd->name;
                }
            }

            // Parse date to Y-m-d format
            try {
                if (str_contains($rawDate, '/')) {
                    $assignDate = Carbon::createFromFormat('d/m/Y', $rawDate)->format('Y-m-d');
                } else {
                    $assignDate = Carbon::parse($rawDate)->format('Y-m-d');
                }
            } catch (\Exception $e) {
                $assignDate = Carbon::parse($rawDate)->format('Y-m-d');
            }

            // Create new customer with both product_id and product_type
            $customer = AssignBase::create([
                'emp_id'        => $request->emp_id,
                'customer_name' => $customerName,
                'company_name'  => $request->company_name,
                'product_id'    => $productId ? (int) $productId : null,
                'product_type'  => $productType ? (string) $productType : null,
                'status'        => $request->input('status', 'NEW'),
                'mobile'        => $request->mobile,
                'assign_date'   => $assignDate,
            ]);

            return response()->json([
                'status'  => true,
                'message' => 'Customer added successfully',
                'data' => [
                    'assign_base_id'      => $customer->id,
                    'emp_id'              => $customer->emp_id,
                    'employee_name'       => $user->name,
                    'employee_role'       => $user->role ?? 'Telecaller',
                    'is_account'          => strtolower($user->role ?? '') === 'account',
                    'assigned_to_account' => strtolower($user->role ?? '') === 'account',
                    'customer_name'       => $customer->customer_name,
                    'company_name'        => $customer->company_name,
                    'product_id'          => $customer->product_id,
                    'product_type'        => $customer->product_type,
                    'status'              => $customer->status,
                    'mobile'              => $customer->mobile,
                    'assign_date'         => Carbon::parse($customer->assign_date)->format('d/m/Y'),
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
     * Mobile / Frontend API to fetch active products for dropdown list
     */
    public function getProducts(Request $request)
    {
        try {
            $query = \App\Models\Product::query();
            if (!$request->boolean('all')) {
                $query->where('is_active', true);
            }

            $products = $query->orderBy('name')
                ->select('id', 'name', 'description', 'is_active')
                ->get();

            return response()->json([
                'status'  => true,
                'message' => 'Products fetched successfully',
                'data'    => $products,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status'  => false,
                'message' => 'Error: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Mobile / Frontend API to create product
     */
    public function createProduct(Request $request)
    {
        try {
            $request->validate([
                'name'        => 'required|string|max:255|unique:products,name',
                'description' => 'nullable|string|max:500',
                'is_active'   => 'nullable|boolean',
            ]);

            $product = \App\Models\Product::create([
                'name'        => trim($request->name),
                'description' => trim($request->description),
                'is_active'   => $request->has('is_active') ? (bool) $request->is_active : true,
            ]);

            return response()->json([
                'status'  => true,
                'message' => 'Product created successfully',
                'data'    => $product,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status'  => false,
                'message' => 'Error: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Mobile / Frontend API to update product
     */
    public function updateProduct(Request $request, $id = null)
    {
        try {
            $productId = $id ?? $request->input('id');
            $product = \App\Models\Product::find($productId);

            if (!$product) {
                return response()->json([
                    'status'  => false,
                    'message' => 'Product not found',
                ], 404);
            }

            $request->validate([
                'name'        => 'required|string|max:255|unique:products,name,' . $product->id,
                'description' => 'nullable|string|max:500',
                'is_active'   => 'nullable|boolean',
            ]);

            $product->update([
                'name'        => trim($request->name),
                'description' => trim($request->description),
                'is_active'   => $request->has('is_active') ? (bool) $request->is_active : true,
            ]);

            return response()->json([
                'status'  => true,
                'message' => 'Product updated successfully',
                'data'    => $product,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status'  => false,
                'message' => 'Error: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Mobile / Frontend API to delete product
     */
    public function deleteProduct(Request $request, $id = null)
    {
        try {
            $productId = $id ?? $request->input('id');
            $product = \App\Models\Product::find($productId);

            if (!$product) {
                return response()->json([
                    'status'  => false,
                    'message' => 'Product not found',
                ], 404);
            }

            $name = $product->name;
            $product->delete();

            return response()->json([
                'status'  => true,
                'message' => "Product '{$name}' deleted successfully",
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status'  => false,
                'message' => 'Error: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Mobile / Frontend API to fetch users with role 'Account'
     * Used by mobile app to display accounts for lead assignment
     */
    public function getAccountUsers(Request $request)
    {
        try {
            $accounts = User::whereRaw("LOWER(TRIM(role)) = 'account'")
                ->orderBy('name')
                ->select('id', 'name', 'mobile', 'email', 'role')
                ->get()
                ->map(fn($u) => [
                    'id'     => $u->id,
                    'name'   => $u->name,
                    'mobile' => $u->mobile,
                    'email'  => $u->email,
                    'role'   => $u->role ?? 'Account',
                ]);

            return response()->json([
                'status'  => true,
                'message' => 'Account users fetched successfully',
                'data'    => $accounts,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status'  => false,
                'message' => 'Error: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Mobile / Frontend API to fetch telecaller statuses for remarks and appointments
     */
    public function getTelecallerStatuses(Request $request)
    {
        try {
            $query = \App\Models\TelecallerStatus::query();

            // Default to active only unless all=1 or all=true
            if (!$request->boolean('all')) {
                $query->where('is_active', true);
            }

            $statuses = $query->orderBy('sort_order', 'asc')
                ->orderBy('id', 'asc')
                ->get()
                ->map(fn($s) => [
                    'id'                   => $s->id,
                    'name'                 => $s->name,
                    'slug'                 => $s->slug,
                    'color'                => $s->color,
                    'description'          => $s->description,
                    'requires_appointment' => (bool) $s->requires_appointment,
                    'requires_location'    => (bool) $s->requires_location,
                    'is_active'            => (bool) $s->is_active,
                    'sort_order'           => (int) $s->sort_order,
                ]);

            return response()->json([
                'status'  => true,
                'message' => 'Telecaller statuses fetched successfully',
                'data'    => $statuses,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status'  => false,
                'message' => 'Error: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Mobile / Frontend API to create telecaller status
     */
    public function createTelecallerStatus(Request $request)
    {
        try {
            $request->validate([
                'name'                 => 'required|string|max:255|unique:telecaller_statuses,name',
                'color'                => 'nullable|string|max:30',
                'description'          => 'nullable|string|max:500',
                'requires_appointment' => 'nullable|boolean',
                'requires_location'    => 'nullable|boolean',
                'is_active'            => 'nullable|boolean',
                'sort_order'           => 'nullable|integer',
            ]);

            $name = trim($request->name);

            $status = \App\Models\TelecallerStatus::create([
                'name'                 => $name,
                'slug'                 => \Illuminate\Support\Str::slug($name),
                'color'                => $request->filled('color') ? trim($request->color) : '#6366f1',
                'description'          => trim($request->description),
                'requires_appointment' => $request->boolean('requires_appointment'),
                'requires_location'    => $request->boolean('requires_location'),
                'is_active'            => $request->has('is_active') ? (bool) $request->is_active : true,
                'sort_order'           => (int) $request->input('sort_order', 0),
            ]);

            return response()->json([
                'status'  => true,
                'message' => 'Telecaller status created successfully',
                'data'    => $status,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status'  => false,
                'message' => 'Error: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Mobile / Frontend API to update telecaller status
     */
    public function updateTelecallerStatus(Request $request, $id = null)
    {
        try {
            $statusId = $id ?? $request->input('id');
            $status = \App\Models\TelecallerStatus::find($statusId);

            if (!$status) {
                return response()->json([
                    'status'  => false,
                    'message' => 'Telecaller status not found',
                ], 404);
            }

            $request->validate([
                'name'                 => 'required|string|max:255|unique:telecaller_statuses,name,' . $status->id,
                'color'                => 'nullable|string|max:30',
                'description'          => 'nullable|string|max:500',
                'requires_appointment' => 'nullable|boolean',
                'requires_location'    => 'nullable|boolean',
                'is_active'            => 'nullable|boolean',
                'sort_order'           => 'nullable|integer',
            ]);

            $name = trim($request->name);

            $status->update([
                'name'                 => $name,
                'slug'                 => \Illuminate\Support\Str::slug($name),
                'color'                => $request->filled('color') ? trim($request->color) : ($status->color ?: '#6366f1'),
                'description'          => trim($request->description),
                'requires_appointment' => $request->boolean('requires_appointment'),
                'requires_location'    => $request->boolean('requires_location'),
                'is_active'            => $request->has('is_active') ? (bool) $request->is_active : true,
                'sort_order'           => (int) $request->input('sort_order', $status->sort_order),
            ]);

            return response()->json([
                'status'  => true,
                'message' => 'Telecaller status updated successfully',
                'data'    => $status,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status'  => false,
                'message' => 'Error: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Mobile / Frontend API to delete telecaller status
     */
    public function deleteTelecallerStatus(Request $request, $id = null)
    {
        try {
            $statusId = $id ?? $request->input('id');
            $status = \App\Models\TelecallerStatus::find($statusId);

            if (!$status) {
                return response()->json([
                    'status'  => false,
                    'message' => 'Telecaller status not found',
                ], 404);
            }

            $name = $status->name;
            $status->delete();

            return response()->json([
                'status'  => true,
                'message' => "Telecaller status '{$name}' deleted successfully",
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status'  => false,
                'message' => 'Error: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function scheduleWhatsappNotification(Request $request)
    {
        try {
            $request->validate([
                'emp_id'          => 'required|integer|exists:users,id',
                'assign_base_id'  => 'required|integer|exists:assign_bases,id',
                'amount'          => 'required|numeric',
                'due_date'        => 'required|date_format:d/m/Y',
                'scheduled_date'  => 'required|date_format:d/m/Y',
                'scheduled_time'  => 'required|date_format:H:i',
                'header_image'    => 'required|file|image|mimes:jpg,jpeg,png|max:5120',
            ]);

            $assignBase = AssignBase::where('id', $request->assign_base_id)
                ->where('emp_id', $request->emp_id)
                ->first();

            if (!$assignBase) {
                return response()->json([
                    'status'  => false,
                    'message' => 'This customer is not assigned to the specified employee',
                ], 404);
            }

            $contact = \App\Models\WhatsappContact::firstOrCreate(
                ['mobile' => $assignBase->mobile],
                ['name' => $assignBase->customer_name]
            );

            $headerImagePath = $request->file('header_image')->store('whatsapp/headers', 'public');

            $scheduledAt = Carbon::createFromFormat('d/m/Y H:i', $request->scheduled_date . ' ' . $request->scheduled_time);
            $dueDate = Carbon::createFromFormat('d/m/Y', $request->due_date)->format('d/m/Y');

            $notification = \App\Models\WhatsappNotification::create([
                'emp_id'            => $request->emp_id,
                'assign_base_id'    => $request->assign_base_id,
                'template_name'     => config('services.whatsapp.template_name'),
                'template_language' => config('services.whatsapp.template_language'),
                'template_params'   => [$request->amount, $dueDate],
                'header_image_path' => $headerImagePath,
                'source'            => 'mobile',
                'target_type'       => 'selected',
                'scheduled_at'      => $scheduledAt,
                'status'            => 'pending',
            ]);

            \App\Models\WhatsappNotificationRecipient::create([
                'whatsapp_notification_id' => $notification->id,
                'whatsapp_contact_id'      => $contact->id,
                'status'                   => 'pending',
            ]);

            return response()->json([
                'status'  => true,
                'message' => 'WhatsApp notification scheduled successfully',
                'data' => [
                    'notification_id' => $notification->id,
                    'customer_name'   => $assignBase->customer_name,
                    'mobile'          => $assignBase->mobile,
                    'scheduled_at'    => $scheduledAt->format('d/m/Y H:i'),
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status'  => false,
                'message' => 'Error: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function whatsappNotificationsData(Request $request)
    {
        try {
            $request->validate([
                'emp_id'         => 'required|integer|exists:users,id',
                'assign_base_id' => 'required|integer|exists:assign_bases,id',
            ]);

            $assignBase = AssignBase::where('id', $request->assign_base_id)
                ->where('emp_id', $request->emp_id)
                ->first();

            if (!$assignBase) {
                return response()->json([
                    'status'  => false,
                    'message' => 'This customer is not assigned to the specified employee',
                ], 404);
            }

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
                            ? Storage::disk('public')->url($notification->header_image_path)
                            : null,
                        'scheduled_at' => $notification->scheduled_at ? $notification->scheduled_at->format('d/m/Y H:i') : '—',
                        'status'       => $recipient->status,
                        'error'        => $recipient->error_message,
                    ];
                }
            }

            return response()->json([
                'status' => true,
                'data' => [
                    'customer_name' => $assignBase->customer_name,
                    'mobile'        => $assignBase->mobile,
                    'items'         => $items,
                ],
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
                'notification_date'   => 'required|string',
                'notification_time'   => 'required|string',
                'remarks'             => 'required|string',
                'status'              => 'nullable|string|max:100',
                'appointment_date'    => 'nullable|string|max:50',
                'appointment_time'    => 'nullable|string|max:50',
                'location'            => 'nullable|string',
                'alternate_number'    => 'nullable|string|max:50',
            ]);

            $rawDate = $request->notification_date;
            if (strpos($rawDate, '/') !== false) {
                $notificationDate = Carbon::createFromFormat('d/m/Y', $rawDate)->format('Y-m-d');
            } else {
                $notificationDate = Carbon::parse($rawDate)->format('Y-m-d');
            }

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
                    'status'            => $request->status,
                    'appointment_date'  => $request->appointment_date,
                    'appointment_time'  => $request->appointment_time,
                    'location'          => $request->location,
                    'alternate_number'  => $request->alternate_number,
                ]);

                $message = 'Notification updated successfully';
            } else {
                $notification = Notification::create([
                    'emp_id'            => $request->emp_id,
                    'assign_base_id'    => $request->assign_base_id,
                    'notification_date' => $notificationDate,
                    'notification_time' => $request->notification_time,
                    'remarks'           => $request->remarks,
                    'status'            => $request->status,
                    'appointment_date'  => $request->appointment_date,
                    'appointment_time'  => $request->appointment_time,
                    'location'          => $request->location,
                    'alternate_number'  => $request->alternate_number,
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
                    'notification_date' => $n->notification_date ? Carbon::parse($n->notification_date)->format('d/m/Y') : null,
                    'notification_time' => $n->notification_time,
                    'remarks'           => $n->remarks,
                    'is_sent'           => (bool) $n->is_sent,
                    'status'            => $n->status,
                    'appointment_date'  => $n->appointment_date,
                    'appointment_time'  => $n->appointment_time,
                    'location'          => $n->location,
                    'alternate_number'  => $n->alternate_number,
                ]);

            return response()->json([
                'status'            => true,
                'message'           => $message,
                'data'              => [
                    'id'                => $notification->id,
                    'emp_id'            => $notification->emp_id,
                    'assign_base_id'    => $notification->assign_base_id,
                    'notification_date' => $notification->notification_date ? Carbon::parse($notification->notification_date)->format('d/m/Y') : null,
                    'notification_time' => $notification->notification_time,
                    'remarks'           => $notification->remarks,
                    'is_sent'           => (bool) $notification->is_sent,
                    'status'            => $notification->status,
                    'appointment_date'  => $notification->appointment_date,
                    'appointment_time'  => $notification->appointment_time,
                    'location'          => $notification->location,
                    'alternate_number'  => $notification->alternate_number,
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
                    'notification_date' => $notification->notification_date ? \Carbon\Carbon::parse($notification->notification_date)->format('d/m/Y') : null,
                    'notification_time' => $notification->notification_time,
                    'remarks'           => $notification->remarks,
                    'is_sent'           => (bool) $notification->is_sent,
                    'status'            => $notification->status,
                    'appointment_date'  => $notification->appointment_date,
                    'appointment_time'  => $notification->appointment_time,
                    'location'          => $notification->location,
                    'alternate_number'  => $notification->alternate_number,
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

    public function processScheduledWhatsappNotifications(Request $request)
    {
        try {
            Artisan::call('app:send-scheduled-mobile-whatsapp-notifications');
            $output = Artisan::output();

            return response()->json([
                'status' => true,
                'message' => 'Scheduled mobile-app WhatsApp notifications processed successfully',
                'output' => trim($output)
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Error processing scheduled WhatsApp notifications: ' . $e->getMessage()
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

    /**
     * Mobile Calendar Events API
     * Returns reminders and WhatsApp notifications.
     * Non-admin mobile users only see what they created.
     * Admin mobile users can see all or filter by employee.
     */
    public function calendarEvents(Request $request)
    {
        try {
            $empId = $request->input('emp_id');
            $user = $empId ? User::find($empId) : null;
            $isAdmin = $user ? $user->isAdmin() : false;

            $targetEmpId = null;
            if ($user && !$isAdmin) {
                // Non-admin employee: ONLY see what they created
                $targetEmpId = (int) $user->id;
            } elseif ($request->filled('employee_id') && $request->employee_id !== 'all') {
                $targetEmpId = (int) $request->employee_id;
            } elseif ($empId && !$isAdmin) {
                $targetEmpId = (int) $empId;
            }

            // Determine date range
            if ($request->filled('from_date') && $request->filled('to_date')) {
                // Support d/m/Y or Y-m-d format
                $startDate = str_contains($request->from_date, '/')
                    ? Carbon::createFromFormat('d/m/Y', $request->from_date)->startOfDay()
                    : Carbon::parse($request->from_date)->startOfDay();

                $endDate = str_contains($request->to_date, '/')
                    ? Carbon::createFromFormat('d/m/Y', $request->to_date)->endOfDay()
                    : Carbon::parse($request->to_date)->endOfDay();
            } else {
                $year = (int) $request->input('year', date('Y'));
                $month = (int) $request->input('month', date('n'));
                $carbonMonth = Carbon::createFromDate($year, $month, 1);
                $startDate = $carbonMonth->copy()->startOfMonth()->startOfWeek(Carbon::SUNDAY);
                $endDate = $carbonMonth->copy()->endOfMonth()->endOfWeek(Carbon::SATURDAY);
            }

            $typeFilter = $request->input('type', 'all');
            $items = [];
            $now = Carbon::now();
            $defaultProduct = \App\Models\Product::where('is_active', true)->first();

            // 1. Reminders
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
                    
                    // Format time cleanly in 12-hour AM/PM and 24-hour
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
                    $isSent = (bool) $n->is_sent;
                    $isDue = false;

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

                    $creatorName = $n->employee?->name ?? 'Unknown';
                    $loanApp = $n->assignBase?->loanApplication;

                    // Ensure non-null product_id and product_type
                    $prodId = $n->assignBase?->product_id ?? $n->assignBase?->product?->id;
                    $prodName = $n->assignBase?->product?->name ?? $n->assignBase?->product_type;
                    if (!$prodId && $defaultProduct) {
                        $prodId = $defaultProduct->id;
                    }
                    if (!$prodName) {
                        $prodName = $defaultProduct?->name ?? 'Website';
                    }

                    $items[] = [
                        'id'               => 'rem_' . $n->id,
                        'raw_id'           => $n->id,
                        'type'             => 'reminder',
                        'type_label'       => 'Reminder',
                        'icon'             => 'fas fa-bell',
                        'title'            => $n->assignBase?->customer_name ?? 'Reminder',
                        'date'             => $dateStr,
                        'display_date'     => $n->notification_date->format('d/m/Y'),
                        'time'             => $time12,       // 12-hour AM/PM format (e.g. 02:00 PM)
                        'time_12'          => $time12,       // 12-hour AM/PM format (e.g. 02:00 PM)
                        'time_24'          => $time24,       // 24-hour format (e.g. 14:00)
                        'customer_name'    => $n->assignBase?->customer_name ?? '—',
                        'company_name'     => $n->assignBase?->company_name ?? '—',
                        'product_id'       => (int) $prodId,
                        'product_type'     => (string) $prodName,
                        'customer_status'  => $n->assignBase?->status ?? 'NEW',
                        'mobile'           => $n->assignBase?->mobile ?? '—',
                        'remarks'          => $n->remarks ?? '',
                        'status'           => $statusLabel,
                        'status_class'     => $statusClass,
                        'note_status'      => $n->status,
                        'appointment_date' => $n->appointment_date,
                        'appointment_time' => $n->appointment_time,
                        'location'         => $n->location,
                        'alternate_number' => $n->alternate_number,
                        'is_sent'          => $isSent,
                        'is_due'           => $isDue,
                        'emp_id'           => $n->emp_id,
                        'added_by'         => $creatorName,
                        'assign_base_id'   => $n->assign_base_id,
                        'loan_details_url' => $n->assign_base_id ? url('/loan-application-details/' . $n->assign_base_id) : null,
                        'loan_application' => $loanApp ? [
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

            // 2. WhatsApp Notifications
            if ($typeFilter === 'all' || $typeFilter === 'whatsapp') {
                $waQuery = \App\Models\WhatsappNotification::with(['employee.application', 'assignBase.product', 'assignBase.loanApplication', 'recipients.contact'])
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

                    $customerName = $wa->assignBase?->customer_name;
                    $companyName = $wa->assignBase?->company_name ?? '—';
                    $mobile = $wa->assignBase?->mobile;

                    if (!$customerName && $wa->recipients->isNotEmpty()) {
                        $firstRecipient = $wa->recipients->first();
                        $customerName = $firstRecipient->contact?->name ?? 'Recipient';
                        $mobile = $firstRecipient->contact?->mobile ?? '—';
                    }

                    $params = $wa->template_params ?? [];
                    $creatorName = $wa->employee?->name ?? ($wa->source === 'mobile' ? 'Mobile User' : 'Admin');
                    $loanApp = $wa->assignBase?->loanApplication;

                    $time24 = $wa->scheduled_at->format('H:i');
                    $time12 = $wa->scheduled_at->format('h:i A');
                    $status = ucfirst($wa->status ?? 'pending');
                    $statusClass = strtolower($wa->status ?? 'pending');

                    $prodId = $wa->assignBase?->product_id ?? $wa->assignBase?->product?->id;
                    $prodName = $wa->assignBase?->product?->name ?? $wa->assignBase?->product_type;
                    if (!$prodId && $defaultProduct) {
                        $prodId = $defaultProduct->id;
                    }
                    if (!$prodName) {
                        $prodName = $defaultProduct?->name ?? 'Website';
                    }

                    $items[] = [
                        'id'               => 'wa_' . $wa->id,
                        'raw_id'           => $wa->id,
                        'type'             => 'whatsapp',
                        'type_label'       => 'WhatsApp',
                        'icon'             => 'fab fa-whatsapp',
                        'title'            => $customerName ?: 'WhatsApp',
                        'date'             => $wa->scheduled_at->format('Y-m-d'),
                        'display_date'     => $wa->scheduled_at->format('d/m/Y'),
                        'time'             => $time12,       // 12-hour AM/PM format (e.g. 02:00 PM)
                        'time_12'          => $time12,       // 12-hour AM/PM format (e.g. 02:00 PM)
                        'time_24'          => $time24,       // 24-hour format (e.g. 14:00)
                        'customer_name'    => $customerName ?: 'WhatsApp Notification',
                        'company_name'     => $companyName,
                        'product_id'       => (int) $prodId,
                        'product_type'     => (string) $prodName,
                        'customer_status'  => $wa->assignBase?->status ?? 'NEW',
                        'mobile'           => $mobile ?: '—',
                        'amount'           => $params[0] ?? null,
                        'due_date'         => $params[1] ?? null,
                        'template_name'    => $wa->template_name,
                        'header_image'     => $wa->header_image_path ? Storage::disk('public')->url($wa->header_image_path) : null,
                        'source'           => $wa->source ?? 'web',
                        'status'           => $status,
                        'status_class'     => $statusClass,
                        'emp_id'           => $wa->emp_id,
                        'added_by'         => $creatorName,
                        'assign_base_id'   => $wa->assign_base_id,
                        'loan_details_url' => $wa->assign_base_id ? url('/loan-application-details/' . $wa->assign_base_id) : null,
                        'loan_application' => $loanApp ? [
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

            // Group by date
            $byDate = [];
            foreach ($items as $item) {
                $d = $item['date'];
                if (!isset($byDate[$d])) {
                    $byDate[$d] = [];
                }
                $byDate[$d][] = $item;
            }

            return response()->json([
                'status'         => true,
                'is_admin'       => $isAdmin,
                'filtered_emp_id'=> $targetEmpId,
                'total_events'   => count($items),
                'events_by_date' => $byDate,
                'events'         => $items,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status'  => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Mark one or more reminder notifications as seen/completed from mobile
     */
    public function markNotificationSeen(Request $request)
    {
        try {
            $ids = $request->input('ids', []);
            if ($request->has('id') && empty($ids)) {
                $ids = [$request->input('id')];
            }
            if (!empty($ids)) {
                \App\Models\Notification::whereIn('id', $ids)->update(['is_sent' => true]);
            }

            return response()->json([
                'status'  => true,
                'message' => 'Notification(s) marked as completed successfully',
                'marked_ids' => $ids
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status'  => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get all customer notes / reminder history for an assigned base
     */
    public function getCustomerNotes(Request $request, $assignBaseId = null)
    {
        try {
            $assignBaseId = $assignBaseId ?? $request->input('assign_base_id');

            $notes = Notification::with('employee:id,name')
                        ->where('assign_base_id', $assignBaseId)
                        ->orderBy('id', 'desc')
                        ->get()
                        ->map(function ($n) {
                            return [
                                'id'                => $n->id,
                                'assign_base_id'    => $n->assign_base_id,
                                'emp_id'            => $n->emp_id,
                                'notification_date' => $n->notification_date ? Carbon::parse($n->notification_date)->format('d/m/Y') : null,
                                'notification_time' => $n->notification_time,
                                'remarks'           => $n->remarks,
                                'is_sent'           => (bool) $n->is_sent,
                                'status'            => $n->status,
                                'appointment_date'  => $n->appointment_date,
                                'appointment_time'  => $n->appointment_time,
                                'location'          => $n->location,
                                'alternate_number'  => $n->alternate_number,
                                'employee'          => $n->employee ? [
                                    'id'   => $n->employee->id,
                                    'name' => $n->employee->name,
                                ] : null,
                                'created_at'        => $n->created_at ? $n->created_at->toDateTimeString() : null,
                                'updated_at'        => $n->updated_at ? $n->updated_at->toDateTimeString() : null,
                            ];
                        });

            return response()->json([
                'status' => true,
                'data'   => $notes
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status'  => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * A. Remark Select Status Dropdown API
     * GET /api/remark-statuses (also supports POST)
     */
    public function getRemarkStatuses(Request $request)
    {
        try {
            $query = TelecallerStatus::where('is_active', true)
                ->orderBy('sort_order', 'asc')
                ->orderBy('id', 'asc');

            $statuses = $query->get();

            if ($statuses->isEmpty()) {
                $statusList = [
                    'Quotation Shared',
                    'Appointment-Online',
                    'Appointment-Offline',
                ];
                $items = collect($statusList)->map(fn($name, $idx) => [
                    'id'   => $idx + 1,
                    'name' => $name,
                    'slug' => \Illuminate\Support\Str::slug($name),
                ])->values()->all();
            } else {
                $statusList = $statuses->pluck('name')->values()->all();
                $items = $statuses->map(fn($s) => [
                    'id'                   => $s->id,
                    'name'                 => $s->name,
                    'slug'                 => $s->slug,
                    'color'                => $s->color,
                    'requires_appointment' => (bool) $s->requires_appointment,
                    'requires_location'    => (bool) $s->requires_location,
                ])->values()->all();
            }

            // If caller explicitly asks for objects format (?format=objects or ?objects=1)
            $isObjects = $request->input('format') === 'objects' || $request->boolean('objects');
            $data = $isObjects ? $items : $statusList;

            return response()->json([
                'status' => true,
                'data'   => $data,
                'items'  => $items,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status'  => false,
                'message' => 'Error: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * B. Assigned To Employee Dropdown List API
     * GET /api/employees & GET /api/employee-list (also supports POST)
     */
    public function getEmployeesList(Request $request)
    {
        try {
            // Mobile app uses this API for "Assigned To" dropdown -> STRICTLY ONLY show Account role users!
            // Exclude Admin, Telecaller, etc. completely so mobile Assigned To only lists Account staff.
            $query = User::query()->whereRaw("LOWER(TRIM(role)) = 'account'");

            $employees = $query->orderBy('name', 'asc')
                ->get()
                ->map(fn($u) => [
                    'id'     => $u->id,
                    'name'   => $u->name,
                    'role'   => $u->role ?? 'Account',
                    'mobile' => $u->mobile,
                    'email'  => $u->email,
                ]);

            return response()->json([
                'status' => true,
                'data'   => $employees,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status'  => false,
                'message' => 'Error: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Account Statuses API (for Mobile App & Web)
     * GET /api/account-statuses (also supports POST)
     */
    public function getAccountStatuses(Request $request)
    {
        try {
            $statuses = [
                'NEW',
                'IN PROGRESS',
                'QUOTATION SHARED',
                'CLIENT REPLIED',
            ];

            $items = [
                ['id' => 1, 'name' => 'NEW', 'slug' => 'new'],
                ['id' => 2, 'name' => 'IN PROGRESS', 'slug' => 'in-progress'],
                ['id' => 3, 'name' => 'QUOTATION SHARED', 'slug' => 'quotation-shared'],
                ['id' => 4, 'name' => 'CLIENT REPLIED', 'slug' => 'client-replied'],
            ];

            $isObjects = $request->input('format') === 'objects' || $request->boolean('objects');
            $data = $isObjects ? $items : $statuses;

            return response()->json([
                'status' => true,
                'data'   => $data,
                'items'  => $items,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status'  => false,
                'message' => 'Error: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * C. Assigned To Save API
     * POST /api/assign-lead
     */
    public function assignLead(Request $request)
    {
        try {
            $request->validate([
                'assign_base_id' => 'required|integer|exists:assign_bases,id',
                'emp_id'         => 'nullable|integer|exists:users,id',
                'assigned_to'    => 'required|integer|exists:users,id',
            ]);

            $assignBase = AssignBase::with(['employee', 'assignedUser'])->findOrFail($request->assign_base_id);

            // Update assigned_to on assign_bases table
            $assignBase->assigned_to = $request->assigned_to;
            if ($request->filled('emp_id')) {
                $assignBase->emp_id = $request->emp_id;
            }
            $assignBase->save();

            // Sync with lead_details table
            $leadDetail = LeadDetail::updateOrCreate(
                ['assign_base_id' => $assignBase->id],
                [
                    'emp_id'      => $assignBase->emp_id,
                    'assigned_to' => $request->assigned_to,
                ]
            );

            $assignedUser = User::find($request->assigned_to);

            return response()->json([
                'status'  => true,
                'message' => 'Lead assigned successfully',
                'data'    => [
                    'assign_base_id'   => $assignBase->id,
                    'emp_id'           => $assignBase->emp_id,
                    'assigned_to'      => (int) $assignBase->assigned_to,
                    'assigned_to_name' => $assignedUser?->name,
                    'assigned_to_role' => $assignedUser?->role,
                ],
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'status'  => false,
                'message' => $e->validator->errors()->first(),
                'errors'  => $e->validator->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'status'  => false,
                'message' => 'Error: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * D. Lead Project Details Submit API
     * POST /api/save-lead-details
     */
    public function saveLeadDetails(Request $request)
    {
        try {
            $request->validate([
                'assign_base_id'   => 'required|integer|exists:assign_bases,id',
                'emp_id'           => 'nullable|integer|exists:users,id',
                'assigned_to'      => 'nullable|integer|exists:users,id',
                'project_type'     => 'nullable|string|max:255',
                'cus_company_name' => 'nullable|string|max:255',
                'cus_mail'         => 'nullable|string|max:255',
                'price'            => 'nullable|string|max:50',
                'gst'              => 'nullable|string|max:5',
                'gst_no'           => 'nullable|string|max:50',
            ]);

            $assignBase = AssignBase::findOrFail($request->assign_base_id);

            $gst = strtoupper(trim((string) $request->input('gst', 'N')));
            if ($gst !== 'Y' && $gst !== 'YES') {
                $gst = 'N';
                $gstNo = '';
            } else {
                $gst = 'Y';
                $gstNo = trim((string) $request->input('gst_no', ''));
            }

            $empId = $request->emp_id ?? $assignBase->emp_id;
            $assignedTo = $request->assigned_to ?? $assignBase->assigned_to;
            $projectType = $request->input('project_type', $assignBase->product_type);
            $cusCompanyName = $request->input('cus_company_name', $assignBase->company_name);
            $cusMail = $request->input('cus_mail');
            $price = $request->input('price');

            // Save or update lead_details
            $leadDetail = LeadDetail::updateOrCreate(
                ['assign_base_id' => $assignBase->id],
                [
                    'emp_id'           => $empId,
                    'assigned_to'      => $assignedTo,
                    'project_type'     => $projectType,
                    'cus_company_name' => $cusCompanyName,
                    'cus_mail'         => $cusMail,
                    'price'            => $price,
                    'gst'              => $gst,
                    'gst_no'           => $gstNo,
                ]
            );

            // Sync with assign_bases
            if ($assignedTo) {
                $assignBase->assigned_to = $assignedTo;
            }
            if ($projectType) {
                $assignBase->product_type = $projectType;
                $matchedProduct = Product::where('name', $projectType)->first();
                if ($matchedProduct) {
                    $assignBase->product_id = $matchedProduct->id;
                }
            }
            if ($cusCompanyName) {
                $assignBase->company_name = $cusCompanyName;
            }

            // Determine account status:
            // If already set to a non-NEW status by Accounts team (e.g. IN PROGRESS, QUOTATION SHARED, CLIENT REPLIED), PRESERVE IT!
            // Otherwise, default to NEW (or requested status).
            $existingStatus = $assignBase->account_status ?: ($leadDetail->account_status ?: $assignBase->status);
            $preserveList = ['IN PROGRESS', 'QUOTATION SHARED', 'CLIENT REPLIED'];
            if (!empty($existingStatus) && in_array(strtoupper(trim($existingStatus)), $preserveList)) {
                $finalStatus = strtoupper(trim($existingStatus));
            } else {
                $requestedStatus = strtoupper(trim((string) $request->input('status', '')));
                $finalStatus = !empty($requestedStatus) ? $requestedStatus : 'NEW';
            }

            $assignBase->status = $finalStatus;
            $assignBase->account_status = $finalStatus;
            $assignBase->save();

            // Also keep leadDetail in sync
            $leadDetail->account_status = $finalStatus;
            $leadDetail->save();

            return response()->json([
                'status'  => true,
                'message' => 'Lead details saved successfully',
                'data'    => [
                    'assign_base_id'   => $assignBase->id,
                    'status'           => (string) $assignBase->status,
                    'account_status'   => (string) $assignBase->account_status,
                    'assigned_to'      => $leadDetail->assigned_to ? (int)$leadDetail->assigned_to : null,
                    'project_type'     => (string) $leadDetail->project_type,
                    'cus_company_name' => (string) $leadDetail->cus_company_name,
                    'cus_mail'         => (string) ($leadDetail->cus_mail ?? ''),
                    'price'            => (string) ($leadDetail->price ?? ''),
                    'gst'              => (string) ($leadDetail->gst ?? 'N'),
                    'gst_no'           => (string) ($leadDetail->gst_no ?? ''),
                ],
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'status'  => false,
                'message' => $e->validator->errors()->first(),
                'errors'  => $e->validator->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'status'  => false,
                'message' => 'Error: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * D. Lead Project Details Prefill API
     * GET /api/lead-details/{assignBaseId?} (also supports POST)
     */
    public function getLeadDetails(Request $request, $assignBaseId = null)
    {
        try {
            $id = $assignBaseId ?? $request->input('assign_base_id');

            if (!$id) {
                return response()->json([
                    'status'  => false,
                    'message' => 'assign_base_id is required',
                ], 422);
            }

            $assignBase = AssignBase::with(['assignedUser', 'employee'])->find($id);

            if (!$assignBase) {
                return response()->json([
                    'status'  => false,
                    'message' => 'Assign base lead not found',
                ], 404);
            }

            $leadDetail = LeadDetail::with('assignedTo')->where('assign_base_id', $id)->first();

            if ($leadDetail) {
                $assignedToId = $leadDetail->assigned_to ?? $assignBase->assigned_to;
                $projectType = $leadDetail->project_type ?? $assignBase->product_type ?? '';
                $companyName = $leadDetail->cus_company_name ?? $assignBase->company_name ?? '';
                $cusMail = $leadDetail->cus_mail ?? '';
                $price = $leadDetail->price ?? '';
                $gst = $leadDetail->gst ?? 'N';
                $gstNo = $leadDetail->gst_no ?? '';
            } else {
                // Graceful fallback from assign_bases for initial form opening
                $assignedToId = $assignBase->assigned_to;
                $projectType = $assignBase->product_type ?? '';
                $companyName = $assignBase->company_name ?? '';
                $cusMail = '';
                $price = '';
                $gst = 'N';
                $gstNo = '';
            }

            $accountStatus = $leadDetail?->account_status ?? $assignBase->account_status ?? $assignBase->status ?? 'NEW';
            $accountRemarks = $leadDetail?->account_remarks ?? $assignBase->account_remarks ?? '';
            $accountUpdater = $leadDetail?->accountUpdatedBy?->name ?? $assignBase->accountUpdatedBy?->name ?? $assignBase->assignedUser?->name ?? '—';
            $accountUpdatedAt = $leadDetail?->account_updated_at ?? $assignBase->account_updated_at;

            return response()->json([
                'status' => true,
                'data'   => [
                    'assigned_to'        => $assignedToId ? (int)$assignedToId : null,
                    'project_type'       => (string) $projectType,
                    'cus_company_name'   => (string) $companyName,
                    'cus_mail'           => (string) $cusMail,
                    'price'              => (string) $price,
                    'gst'                => (string) $gst,
                    'gst_no'             => (string) $gstNo,
                    'account_status'     => (string) $accountStatus,
                    'account_remarks'    => (string) $accountRemarks,
                    'account_updated_by' => (string) $accountUpdater,
                    'account_updated_at' => $accountUpdatedAt ? Carbon::parse($accountUpdatedAt)->format('d/m/Y h:i A') : null,
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
     * 2. Get Lead Account Status API (For Mobile App)
     * GET /api/lead-account-status/{assign_base_id}?emp_id={emp_id}
     * Also supports POST /api/lead-account-status
     */
    public function getLeadAccountStatus(Request $request, $assignBaseId = null)
    {
        try {
            $id = $assignBaseId ?? $request->input('assign_base_id', $request->input('id'));
            if (!$id) {
                return response()->json([
                    'status'  => false,
                    'message' => 'assign_base_id is required',
                ], 422);
            }

            $assignBase = AssignBase::with(['leadDetail.accountUpdatedBy', 'accountUpdatedBy', 'assignedUser'])->find($id);
            if (!$assignBase) {
                return response()->json([
                    'status'  => false,
                    'message' => 'Lead not found',
                ], 404);
            }

            $leadDetail = $assignBase->leadDetail;

            $accountStatus = $leadDetail?->account_status
                ?? $assignBase->account_status
                ?? $assignBase->status
                ?? 'NEW';

            $accountRemarks = $leadDetail?->account_remarks
                ?? $assignBase->account_remarks
                ?? '';

            $updater = $leadDetail?->accountUpdatedBy
                ?? $assignBase->accountUpdatedBy
                ?? $assignBase->assignedUser;

            $updatedAt = $leadDetail?->account_updated_at
                ?? $assignBase->account_updated_at
                ?? $leadDetail?->updated_at
                ?? $assignBase->updated_at;

            return response()->json([
                'status' => true,
                'data'   => [
                    'assign_base_id'  => (int) $assignBase->id,
                    'account_status'  => (string) $accountStatus,
                    'account_remarks' => (string) $accountRemarks,
                    'updated_by'      => $updater?->name ?? '—',
                    'updated_at'      => $updatedAt ? Carbon::parse($updatedAt)->format('d/m/Y h:i A') : null,
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
     * 3. Update Account Status API (For Account Web / Mobile App)
     * POST /api/update-account-status
     */
    public function updateAccountStatus(Request $request)
    {
        try {
            $request->validate([
                'assign_base_id'  => 'required|integer|exists:assign_bases,id',
                'account_status'  => 'nullable|string|max:100',
                'status'          => 'nullable|string|max:100',
                'account_remarks' => 'nullable|string',
                'emp_id'          => 'nullable|integer',
            ]);

            $id = $request->assign_base_id;
            $assignBase = AssignBase::findOrFail($id);

            // Supports account_status or status parameter
            $statusInput = $request->input('account_status', $request->input('status', 'NEW'));
            $status = strtoupper(trim((string) $statusInput));
            if (empty($status)) {
                $status = 'NEW';
            }

            $remarks = $request->input('account_remarks', $request->input('remarks', ''));
            $updaterId = $request->input('emp_id', $request->input('assigned_to', Auth::id() ?? $assignBase->assigned_to));
            $now = Carbon::now();

            // 1. Update assign_bases table
            $assignBase->account_status = $status;
            $assignBase->status = $status;
            $assignBase->account_remarks = $remarks;
            if ($updaterId) {
                $assignBase->account_updated_by = $updaterId;
            }
            $assignBase->account_updated_at = $now;
            $assignBase->save();

            // 2. Update lead_details table
            $leadDetail = LeadDetail::updateOrCreate(
                ['assign_base_id' => $assignBase->id],
                [
                    'account_status'     => $status,
                    'account_remarks'    => $remarks,
                    'account_updated_by' => $updaterId,
                    'account_updated_at' => $now,
                ]
            );

            // 3. Log into notifications history timeline
            $updaterName = 'Account User';
            if ($updaterId) {
                $u = User::find($updaterId);
                if ($u) $updaterName = $u->name;
            }
            Notification::create([
                'assign_base_id'    => $assignBase->id,
                'emp_id'            => $updaterId ?? $assignBase->emp_id,
                'notification_date' => $now->toDateString(),
                'notification_time' => $now->toTimeString(),
                'remarks'           => 'Account status: ' . $status . ($remarks ? ' (' . $remarks . ')' : ''),
                'status'            => $status,
            ]);

            return response()->json([
                'status'  => true,
                'message' => 'Account status updated successfully',
                'data'    => [
                    'assign_base_id'  => (int) $assignBase->id,
                    'account_status'  => (string) $status,
                    'account_remarks' => (string) $remarks,
                    'updated_by'      => $updaterName,
                    'updated_at'      => $now->format('d/m/Y h:i A'),
                ],
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'status'  => false,
                'message' => $e->validator->errors()->first(),
                'errors'  => $e->validator->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'status'  => false,
                'message' => 'Error: ' . $e->getMessage(),
            ], 500);
        }
    }
}