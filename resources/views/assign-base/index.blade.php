@extends('layouts.app')

@section('content')

<style>
    body {
        background-color: #f5f7fb;
    }

    .page-title {
        font-size: 20px;
        font-weight: 600;
        margin-bottom: 6px;
    }

    .page-subtitle {
        color: #6b7280;
        font-size: 13px;
        margin-bottom: 18px;
    }

    .card-box {
        background: #ffffff;
        border-radius: 16px;
        padding: 22px;
        box-shadow: 0 8px 25px rgba(0,0,0,0.06);
        width: 98%;
    }

    .form-label {
        font-size: 13px;
        font-weight: 600;
        margin-bottom: 6px;
        display: block;
        color: #374151;
    }

    input[type="file"],
    input[type="date"],
    select {
        padding: 8px;
        border-radius: 8px;
        border: 1px solid #d1d5db;
        font-size: 13px;
        background: #f9fafb;
    }
    

    .btn-upload {
        background: #2563eb;
        color: #ffffff;
        border: none;
        padding: 9px 18px;
        border-radius: 8px;
        font-size: 14px;
        font-weight: 600;
        cursor: pointer;
    }

    .btn-cancel-small {
        background: #ef4444;
        color: #ffffff;
        border: none;
        padding: 8px 14px;
        border-radius: 8px;
        font-size: 13px;
        font-weight: 600;
        cursor: pointer;
        text-decoration: none;
        display: inline-block;
    }

    .btn-cancel-small:hover {
        background: #dc2626;
    }

    .success-msg {
        background: #ecfdf5;
        color: #065f46;
        border: 1px solid #10b981;
        padding: 8px 10px;
        border-radius: 8px;
        font-size: 12px;
        margin-bottom: 12px;
    }

    table {
        width: 100%;
        border-collapse: collapse;
        font-size: 13px;
    }

    th, td {
        padding: 8px;
        text-align: left;
    }

    thead {
        border-bottom: 1px solid #e5e7eb;
    }

    tbody tr {
        border-bottom: 1px solid #f1f5f9;
    }
</style>

<div class="page-title">Assign Base</div>
<div class="page-subtitle">
    Downloadable format →
    <a href="{{ asset('sample/assign_base_sample.xlsx') }}"
       download
       style="color:#2563eb;font-weight:600;text-decoration:none;">
        assign_base_sample.xlsx
    </a>
</div>

{{-- UPLOAD EXCEL --}}
<div class="card-box">

    @if(session('success'))
        <div class="success-msg">{{ session('success') }}</div>
    @endif

    <form method="POST"
          action="{{ route('assign-base.import') }}"
          enctype="multipart/form-data">
        @csrf

        <label class="form-label">Upload Excel File</label>
        <input type="file" name="file" accept=".xlsx,.xls" required>

        <br><br>

        <button type="submit" class="btn-upload">Import Excel</button>
    </form>

</div>
{{-- FILTER FORM --}}
<div class="card-box" style="margin-top:20px">

<form method="GET" action="{{ route('assign-base.index') }}">

    <div style="display:flex;gap:15px;align-items:end;flex-wrap:wrap">

        {{-- Employee --}}
        <div>
            <label class="form-label">Employee</label>
            <select name="employee_id">
                <option value="">All</option>
                @foreach($employees as $id => $name)
                    <option value="{{ $id }}"
                        {{ request('employee_id') == $id ? 'selected' : '' }}>
                        {{ $name }}
                    </option>
                @endforeach
            </select>
        </div>
        {{-- Status --}}
        <div>
            <label class="form-label">Status</label>
            <select name="status">
                <option value="">All</option>
                <option value="NEW" {{ request('status') == 'NEW' ? 'selected' : '' }}>NEW</option>
                <option value="FOLLOW UP" {{ request('status') == 'FOLLOW UP' ? 'selected' : '' }}>FOLLOW UP</option>
                <option value="CONFIRMED" {{ request('status') == 'CONFIRMED' ? 'selected' : '' }}>CONFIRMED</option>
                <option value="NOPE" {{ request('status') == 'NOPE' ? 'selected' : '' }}>NOPE</option>
            </select>
        </div>

        {{-- From Date --}}
        <div>
            <label class="form-label">From Date</label>
            <input type="date"
                name="from_date"
                value="{{ request('from_date', now()->toDateString()) }}"
                max="{{ now()->toDateString() }}">
        </div>

        {{-- To Date --}}
        <div>
            <label class="form-label">To Date</label>
            <input type="date"
                name="to_date"
                value="{{ request('to_date', now()->toDateString()) }}"
                max="{{ now()->toDateString() }}">
        </div>


        <button type="submit" class="btn-upload">Filter</button>
        <button type="submit"
                name="action"
                value="delete"
                class="btn-cancel-small"
                onclick="return confirm('Are you sure you want to delete filtered records?')">
            Delete
        </button>
        <a href="{{ route('assign-base.index') }}" class="btn-cancel-small">
            Cancel
        </a>

    </div>

</form>

</div>
{{-- TABLE (ONLY WHEN DATA EXISTS) --}}
@if(isset($paginatedRows) && $paginatedRows->count() > 0)

<div class="card-box" style="margin-top:12px">

    <table>
        <thead>
            <tr>
                <th>Emp ID</th>
                <th>Customer Name</th>
                <th>Company Name</th>
                <th>Status</th>
                <th>Mobile</th>
                <th>Date</th>
                <th>Action</th>
            </tr>
        </thead>

        <tbody>
            @foreach($paginatedRows as $row)
                <tr>
                    <td>{{ $row->emp_id }}</td>
                    <td>{{ $row->customer_name }}</td>
                    <td>{{ $row->company_name }}</td>
                    <td>{{ $row->status ?? 'NEW' }}</td>
                    <td>{{ $row->mobile }}</td>
                    <td>{{ \Carbon\Carbon::parse($row->assign_date)->format('d/m/Y') }}</td>

                    {{-- ACTION COLUMN --}}
                    <td>
                        <form method="POST"
                            action="{{ route('assign-base.delete') }}"
                            onsubmit="return confirm('Are you sure you want to delete this record?')">
                            @csrf
                            @method('DELETE')

                            <input type="hidden" name="id" value="{{ $row->id }}">

                            <button type="submit"
                                    class="btn-cancel-small"
                                    style="padding:4px 10px;font-size:12px;">
                                Delete
                            </button>
                        </form>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

</div>

<div style="margin-top:15px">
    {{ $paginatedRows->onEachSide(1)->links('vendor.pagination.custom') }}
</div>
@endif

@endsection
