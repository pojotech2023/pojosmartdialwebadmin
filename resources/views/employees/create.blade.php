@extends('layouts.app')

@section('content')

<!-- Font Awesome -->
<link rel="stylesheet"
      href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

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

    table {
        width: 100%;
        border-collapse: collapse;
        font-size: 14px;
    }

    td {
        padding: 12px 8px;
        vertical-align: middle;
    }

    td.label {
        width: 35%;
        font-weight: 600;
        color: #374151;
        background: #f9fafb;
        border-radius: 6px;
    }

    .input-wrap {
        position: relative;
        width: 100%;
    }

    input {
        width: 100%;
        box-sizing: border-box;          
        padding: 9px 32px 9px 10px;      
        border-radius: 8px;
        border: 1px solid #d1d5db;
        outline: none;
        font-size: 14px;
    }


    input:focus {
        border-color: #2563eb;
        box-shadow: 0 0 0 1px rgba(37,99,235,0.2);
    }

    .eye-icon {
        position: absolute;
        top: 50%;
        right: 12px;
        transform: translateY(-50%);
        cursor: pointer;
        color: #2563eb;
        font-size: 14px;
    }

    .btn-save {
        background: #2563eb;
        color: #fff;
        border: none;
        padding: 9px 18px;
        border-radius: 8px;
        font-size: 14px;
        font-weight: 600;
        cursor: pointer;
    }

    .btn-cancel {
        background: #e5e7eb;
        color: #111827;
        border: none;
        padding: 9px 18px;
        border-radius: 8px;
        font-size: 14px;
        font-weight: 600;
        margin-left: 8px;
    }
</style>

<div class="page-title">Add Employee</div>
<div class="page-subtitle">Fill in employee details below</div>

<div class="card-box">

    <form method="POST" action="{{ route('employees.store') }}">
        @csrf

        <table>
            <tr>
                <td class="label">Full Name</td>
                <td>
                    <input type="text"
                           name="name"
                           placeholder="Enter full name"
                           required>
                </td>
            </tr>

            <tr>
                <td class="label">Mobile Number</td>
                <td>
                    <input type="text"
                           name="mobile"
                           placeholder="Enter mobile number"
                           required>
                </td>
            </tr>

            <tr>
                <td class="label">Password</td>
                <td>
                    <div class="input-wrap">
                        <input type="password"
                               id="password"
                               name="password"
                               placeholder="Enter password"
                               required>
                        <i class="fa-solid fa-eye eye-icon"
                           id="eye1"
                           onclick="togglePassword('password','eye1')"></i>
                    </div>
                </td>
            </tr>

            <tr>
                <td class="label">Confirm Password</td>
                <td>
                    <div class="input-wrap">
                        <input type="password"
                               id="confirm_password"
                               name="password_confirmation"
                               placeholder="Confirm password"
                               required>
                        <i class="fa-solid fa-eye eye-icon"
                           id="eye2"
                           onclick="togglePassword('confirm_password','eye2')"></i>
                    </div>
                </td>
            </tr>
        </table>

        <div style="margin-top:18px; text-align:right;">
            <a href="{{ route('employees.index') }}">
                <button type="button" class="btn-cancel">Cancel</button>
            </a>
            <button type="submit" class="btn-save">Save Employee</button>
        </div>

    </form>

</div>

<script>
    function togglePassword(inputId, eyeId) {
        const input = document.getElementById(inputId);
        const eye = document.getElementById(eyeId);

        if (input.type === 'password') {
            input.type = 'text';
            eye.classList.replace('fa-eye', 'fa-eye-slash');
        } else {
            input.type = 'password';
            eye.classList.replace('fa-eye-slash', 'fa-eye');
        }
    }
</script>

@endsection
