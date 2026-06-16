@extends('layouts.app')

@section('content')

<style>
    body {
        background-color: #f5f7fb;
    }

    .page-title {
        font-size: 20px;
        font-weight: 600;
        margin-bottom: 4px;
    }

    .page-subtitle {
        color: #6b7280;
        font-size: 13px;
        margin-bottom: 20px;
    }

    .card-box {
        background: #ffffff;
        border-radius: 16px;
        padding: 20px 22px;
        box-shadow: 0 8px 25px rgba(0,0,0,0.05);
        width: 98%;
    }

    table {
        width: 100%;
        border-collapse: collapse;
        font-size: 14px;
    }

    thead th {
        text-align: left;
        font-size: 12px;
        letter-spacing: 0.04em;
        color: #6b7280;
        padding-bottom: 10px;
        border-bottom: 1px solid #e5e7eb;
    }

    tbody tr {
        border-bottom: 1px solid #f1f5f9;
    }

    tbody tr:last-child {
        border-bottom: none;
    }

    tbody td {
        padding: 12px 0;
        color: #111827;
        vertical-align: middle;
    }

    .badge-date {
        background: #e0f2fe;
        color: #0369a1;
        font-size: 11px;
        padding: 4px 8px;
        border-radius: 999px;
        font-weight: 600;
    }

    .action-btn {
        padding: 5px 10px;
        font-size: 12px;
        border-radius: 6px;
        border: none;
        cursor: pointer;
    }

    .btn-edit {
        background: #facc15;
        color: #000;
        margin-right: 6px;
    }

    .btn-delete {
        background: #ef4444;
        color: #fff;
    }

    .top-bar {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 14px;
    }

    .btn-add {
        background: #2563eb;
        color: #fff;
        border: none;
        padding: 7px 14px;
        border-radius: 8px;
        font-size: 13px;
        font-weight: 600;
        cursor: pointer;
    }
</style>

<div class="page-title">Employee List</div>
<div class="page-subtitle">Here are all registered employees.</div>

<div class="card-box">

    <div class="top-bar">
        <div></div>
        <a href="{{ route('employees.create') }}">
            <button class="btn-add">+ Add Employee</button>
        </a>
    </div>

    <table>
        <thead>
            <tr>
                <th>EMP ID</th>
                <th>FULL NAME</th>
                <th>MOBILE</th>
                <th>JOINED</th>
                <th>ACTIONS</th>
            </tr>
        </thead>

        <tbody>
            @foreach($users as $index => $user)
            <tr>
                <td>{{ $user->id  }}</td>
                <td>{{ $user->name }}</td>
                <td>{{ $user->mobile }}</td>
                <td>
                    <span class="badge-date">
                        {{ $user->created_at->format('d M Y') }}
                    </span>
                </td>
                <td>
                    <a href="{{ route('employees.edit', $user->id) }}">
                        <button class="action-btn btn-edit">Edit</button>
                    </a>

                    <form action="{{ route('employees.destroy', $user->id) }}"
                          method="POST"
                          style="display:inline;">
                        @csrf
                        @method('DELETE')
                        <button class="action-btn btn-delete"
                                onclick="return confirm('Are you sure?')">
                            Delete
                        </button>
                    </form>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

</div>

@endsection
