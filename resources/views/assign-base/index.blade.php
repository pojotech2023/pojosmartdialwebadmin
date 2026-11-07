@extends('layouts.app')

@section('page-title', 'Assign Base')

@section('content')

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

        {{-- Product Type --}}
        <div>
            <label class="form-label">Product Type</label>
            <select name="product_id">
                <option value="">All Products</option>
                @foreach($products as $pId => $pName)
                    <option value="{{ $pId }}" {{ request('product_id') == $pId ? 'selected' : '' }}>
                        {{ $pName }}
                    </option>
                @endforeach
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


        <button type="submit" class="btn-upload"><i class="fas fa-filter"></i> Filter</button>
        <button type="submit"
                name="action"
                value="delete"
                class="btn-cancel-small"
                onclick="return confirm('Are you sure you want to delete filtered records?')">
            <i class="fas fa-trash"></i> Delete
        </button>
        <a href="{{ route('assign-base.index') }}" class="btn-cancel-small">
            <i class="fas fa-rotate-left"></i> Cancel
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
                <th>Product Type</th>
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
                    <td>
                        @if($row->product || $row->product_type)
                            <span style="background:#eef2ff;color:#4338ca;padding:3px 9px;border-radius:12px;font-size:11.5px;font-weight:700;">
                                <i class="fas fa-tag" style="font-size:10px;margin-right:3px;"></i> {{ $row->product->name ?? $row->product_type }}
                            </span>
                        @else
                            <span style="color:#94a3b8;font-size:12px;">—</span>
                        @endif
                    </td>
                    <td><span class="status-badge status-{{ \Illuminate\Support\Str::slug($row->status ?? 'NEW') }}">{{ $row->status ?? 'NEW' }}</span></td>
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

                            <button type="submit" class="icon-btn icon-btn-delete" title="Delete">
                                <i class="fas fa-trash"></i>
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
