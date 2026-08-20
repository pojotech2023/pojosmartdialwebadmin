
@extends('layouts.app')

@section('page-title', 'Loan Application Details')

@section('content')
<div class="container">
    <div class="top-bar">
        <div class="page-title" style="margin-bottom:0;">Loan Application Details</div>
        <a href="{{ route('dashboard') }}" class="btn-cancel-small"><i class="fas fa-arrow-left"></i> Back to Dashboard</a>
    </div>

    @push('styles')
    <style>
        tbody tr:nth-child(even) { background: #f9fafb; }
        .section-title {
            font-size: 16px;
            font-weight: 700;
            color: #1f2937;
            margin-bottom: 16px;
            border-bottom: 2px solid #e5e7eb;
            padding-bottom: 10px;
        }
        .badge-success { background: var(--success); color: white; }
        .badge-primary { background: var(--primary); color: white; }
        .file-link {
            color: var(--primary);
            text-decoration: none;
            font-weight: 600;
        }
        .file-link:hover { text-decoration: underline; }
        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border: 1px solid #bee5eb;
            border-radius: 12px;
            background-color: #d1ecf1;
            color: #0c5460;
        }
    </style>
    @endpush

    @if($loanApplication)
    <!-- Customer & Loan Information Table -->
    <div class="card-box">
        <h3 class="section-title">Loan Information</h3>
        <table>
            <thead>
                <tr>
                    <th style="width: 25%;">Field</th>
                    <th style="width: 75%;">Details</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><strong>Login Date</strong></td>
                    <td>{{ $loanApplication->login_date ? \Carbon\Carbon::parse($loanApplication->login_date)->format('d/m/Y') : 'N/A' }}</td>
                </tr>
                <tr>
                    <td><strong>App Number</strong></td>
                    <td>{{ $loanApplication->app_number ?? 'N/A' }}</td>
                </tr>
                <tr>
                    <td><strong>Logged Bank</strong></td>
                    <td>{{ $loanApplication->logged_bank ?? 'N/A' }}</td>
                </tr>
                <tr>
                    <td><strong>Promo Code</strong></td>
                    <td>{{ $loanApplication->promo_code ?? 'N/A' }}</td>
                </tr>
                <tr>
                    <td><strong>Requested Amount</strong></td>
                    <td><strong style="color: #dc2626;">₹{{ number_format($loanApplication->req_amount ?? 0) }}</strong></td>
                </tr>
                <tr>
                    <td><strong>Approved Amount</strong></td>
                    <td><strong style="color: #059669;">₹{{ number_format($loanApplication->approved_amount ?? 0) }}</strong></td>
                </tr>
                <tr>
                    <td><strong>Loan Status</strong></td>
                    <td><span class="badge badge-success">{{ $loanApplication->status ?? 'N/A' }}</span></td>
                </tr>
                <tr>
                    <td><strong>Remarks</strong></td>
                    <td>{{ $loanApplication->remarks ?? 'N/A' }}</td>
                </tr>
                <tr>
                    <td><strong>Net Salary</strong></td>
                    <td>₹{{ number_format($loanApplication->net_salary ?? 0) }}</td>
                </tr>
                <tr>
                    <td><strong>Bank A/C</strong></td>
                    <td>{{ $loanApplication->bank_ac ?? 'N/A' }}</td>
                </tr>
                <tr>
                    <td><strong>Category</strong></td>
                    <td>{{ $loanApplication->category ?? 'N/A' }}</td>
                </tr>
                <tr>
                    <td><strong>Bank Manager</strong></td>
                    <td>{{ $loanApplication->bank_manager ?? 'N/A' }}</td>
                </tr>
                <tr>
                    <td><strong>DSA Name</strong></td>
                    <td>{{ $loanApplication->dsa_name ?? 'N/A' }}</td>
                </tr>
                <tr>
                    <td><strong>Reference 1</strong></td>
                    <td>{{ $loanApplication->ref_1 ?? 'N/A' }}</td>
                </tr>
                <tr>
                    <td><strong>Reference 2</strong></td>
                    <td>{{ $loanApplication->ref_2 ?? 'N/A' }}</td>
                </tr>
                <tr>
                    <td><strong>Tenure</strong></td>
                    <td>{{ $loanApplication->tenure ?? 'N/A' }} months</td>
                </tr>
                <tr>
                    <td><strong>Location</strong></td>
                    <td>{{ $loanApplication->location ?? 'N/A' }}</td>
                </tr>
                <tr>
                    <td><strong>Date</strong></td>
                    <td>{{ $loanApplication->date ? \Carbon\Carbon::parse($loanApplication->date)->format('d/m/Y') : 'N/A' }}</td>
                </tr>
                <tr>
                    <td><strong>Mode</strong></td>
                    <td>{{ $loanApplication->mode ?? 'N/A' }}</td>
                </tr>
            </tbody>
        </table>
    </div>

    <!-- Uploaded Documents Table -->
    @if($loanApplication->aadhar_card_front_upload || $loanApplication->aadhar_card_back_upload || $loanApplication->pan_upload || $loanApplication->bank_statement_upload || $loanApplication->salary_slip_upload)
    <div class="card-box">
        <h3 class="section-title">Uploaded Documents</h3>
        <table>
            <thead>
                <tr>
                    <th>Document Type</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @if($loanApplication->aadhar_card_front_upload)
                <tr>
                    <td><strong>Aadhar Card (Front)</strong></td>
                    <td><a href="{{ url('storage/' . $loanApplication->aadhar_card_front_upload) }}" class="file-link" target="_blank">📄 View Front Photo</a></td>
                </tr>
                @endif
                
                @if($loanApplication->aadhar_card_back_upload)
                <tr>
                    <td><strong>Aadhar Card (Back)</strong></td>
                    <td><a href="{{ url('storage/' . $loanApplication->aadhar_card_back_upload) }}" class="file-link" target="_blank">📄 View Back Photo</a></td>
                </tr>
                @endif
                
                @if($loanApplication->pan_upload)
                <tr>
                    <td><strong>PAN Card</strong></td>
                    <td><a href="{{ url('storage/' . $loanApplication->pan_upload) }}" class="file-link" target="_blank">📄 View Document</a></td>
                </tr>
                @endif
                
                @if($loanApplication->bank_statement_upload)
                <tr>
                    <td><strong>Bank Statement</strong></td>
                    <td><a href="{{ url('storage/' . $loanApplication->bank_statement_upload) }}" class="file-link" target="_blank">📄 View Document</a></td>
                </tr>
                @endif
                
                @if($loanApplication->salary_slip_upload)
                <tr>
                    <td><strong>Salary Slip</strong></td>
                    <td><a href="{{ url('storage/' . $loanApplication->salary_slip_upload) }}" class="file-link" target="_blank">📄 View Document</a></td>
                </tr>
                @endif
            </tbody>
        </table>
    </div>
    @endif

    @else
    <div class="card-box">
        <div class="alert">
            <h4><strong>No Loan Application Found</strong></h4>
            <p>This customer has not submitted a loan application yet.</p>
        </div>
    </div>
    @endif
</div>
@endsection