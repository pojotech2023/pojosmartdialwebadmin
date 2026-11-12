@extends('layouts.app')

@section('page-title', 'Employees')

@section('content')

<div class="page-title">Employee List</div>
<div class="page-subtitle">Here are all registered employees.</div>

<div class="card-box">

    <div class="top-bar">
        <div></div>
        <a href="{{ route('employees.create') }}" class="btn-add">
            <i class="fas fa-plus"></i> Add Employee
        </a>
    </div>

    <table>
        <thead>
            <tr>
                <th>EMP ID</th>
                <th>FULL NAME</th>
                <th>ROLE</th>
                <th>MOBILE</th>
                <th>JOINED</th>
                <th>ACTIONS</th>
            </tr>
        </thead>

        <tbody>
            @foreach($users as $index => $user)
            <tr>
                <td><span style="font-weight:700;color:#64748b;">#{{ $user->id }}</span></td>
                <td><strong style="color:#0f172a;">{{ $user->name }}</strong></td>
                <td>
                    @if(strtolower($user->role ?? '') === 'account')
                        <span style="background:#ecfdf5;color:#047857;padding:4px 10px;border-radius:14px;font-size:12px;font-weight:700;display:inline-flex;align-items:center;gap:5px;border:1px solid #a7f3d0;">
                            <i class="fas fa-laptop"></i> Account (Web)
                        </span>
                    @elseif(strtolower($user->role ?? '') === 'admin')
                        <span style="background:#fef3c7;color:#92400e;padding:4px 10px;border-radius:14px;font-size:12px;font-weight:700;display:inline-flex;align-items:center;gap:5px;border:1px solid #fde68a;">
                            <i class="fas fa-shield-alt"></i> Admin
                        </span>
                    @else
                        <span style="background:#eef2ff;color:#4338ca;padding:4px 10px;border-radius:14px;font-size:12px;font-weight:700;display:inline-flex;align-items:center;gap:5px;border:1px solid #c7d2fe;">
                            <i class="fas fa-headset"></i> Telecaller (Mobile)
                        </span>
                    @endif
                </td>
                <td>{{ $user->mobile }}</td>
                <td>
                    <span class="badge-date">
                        {{ $user->created_at->format('d M Y') }}
                    </span>
                </td>
                <td>
                    <a href="{{ route('employees.edit', $user->id) }}" class="icon-btn icon-btn-edit" title="Edit">
                        <i class="fas fa-pen"></i>
                    </a>

                    <form action="{{ route('employees.destroy', $user->id) }}"
                          method="POST"
                          style="display:inline;">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="icon-btn icon-btn-delete" title="Delete"
                                onclick="return confirm('Are you sure?')">
                            <i class="fas fa-trash"></i>
                        </button>
                    </form>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

</div>

@endsection
