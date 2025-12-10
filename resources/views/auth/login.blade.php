<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <style>
        * { box-sizing: border-box; margin: 0; padding: 0;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; }

        body {
            background-color: #f5f7fb;
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 24px;
        }

        .card {
            background: #ffffff;
            border-radius: 18px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.08);
            width: 380px;
            padding: 28px 30px 26px;
        }

        .title {
            font-size: 22px;
            font-weight: 600;
            margin-bottom: 20px;
            color: #111827;
        }

        .field {
            margin-bottom: 16px;
        }

        .label {
            font-size: 13px;
            font-weight: 600;
            margin-bottom: 6px;
            display: block;
        }

        .input-wrapper {
            position: relative;
        }

        .input {
            width: 100%;
            padding: 11px 12px;
            border-radius: 8px;
            border: 1px solid #d1d5db;
            font-size: 14px;
            background-color: #f9fafb;
            outline: none;
            transition: border-color 0.15s, box-shadow 0.15s, background-color 0.15s;
        }

        .input:focus {
            border-color: #2563eb;
            box-shadow: 0 0 0 1px rgba(37,99,235,0.25);
            background: #ffffff;
        }

        .show-toggle {
            position: absolute;
            top: 50%;
            right: 10px;
            transform: translateY(-50%);
            font-size: 12px;
            color: #2563eb;
            cursor: pointer;
            user-select: none;
        }

        .forgot {
            text-align: right;
            font-size: 11px;
            margin-top: 4px;
        }

        .forgot a {
            color: #2563eb;
            text-decoration: none;
        }

        .btn-primary {
            width: 100%;
            padding: 11px 0;
            margin-top: 18px;
            border-radius: 6px;
            border: none;
            background: #2563eb;
            color: #ffffff;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
        }

        .btn-primary:hover {
            background: #1d4ed8;
        }

        .otp-link {
            text-align: center;
            margin-top: 8px;
            font-size: 12px;
        }

        .otp-link a {
            color: #2563eb;
            text-decoration: none;
            font-weight: 500;
        }

        .divider {
            display: flex;
            align-items: center;
            gap: 8px;
            margin: 16px 0 10px;
            font-size: 11px;
            color: #9ca3af;
        }

        .divider::before,
        .divider::after {
            content: "";
            flex: 1;
            height: 1px;
            background: #e5e7eb;
        }

        .google-btn {
            width: 100%;
            border-radius: 999px;
            border: 1px solid #d1d5db;
            padding: 9px 0;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            font-size: 13px;
            cursor: pointer;
            background: #ffffff;
        }

        .google-circle {
            width: 18px;
            height: 18px;
            border-radius: 999px;
            border: 2px solid #2563eb;
        }

        .alert-error {
            background: #fef2f2;
            border: 1px solid #f87171;
            color: #b91c1c;
            padding: 8px 10px;
            border-radius: 8px;
            font-size: 12px;
            margin-bottom: 10px;
        }
    </style>
</head>
<body>

<div class="card">
    <div class="title">Login</div>

    {{-- Login errors --}}
    @if($errors->any())
        <div class="alert-error">
            @foreach($errors->all() as $error)
                <div>{{ $error }}</div>
            @endforeach
        </div>
    @endif

    <form method="POST" action="{{ route('login.post') }}">
        @csrf

        {{-- Email --}}
        <div class="field">
            <label class="label">Email ID / Username</label>
            <div class="input-wrapper">
                <input class="input"
                       type="text"
                       name="email"
                       value="{{ old('email') }}"
                       placeholder="Enter Email ID / Username"
                       required>
            </div>
        </div>

        {{-- Password --}}
        <div class="field">
            <label class="label">Password</label>
            <div class="input-wrapper">
                <input class="input"
                       type="password"
                       id="password"
                       name="password"
                       placeholder="Enter Password"
                       required>
                <span class="show-toggle" onclick="togglePassword()">Show</span>
            </div>
            <div class="forgot">
                <a href="#">Forgot Password?</a>
            </div>
        </div>

        {{-- Login button --}}
        <button type="submit" class="btn-primary">
            Login
        </button>

        <div class="otp-link">
            <a href="#">Use OTP to Login</a>
        </div>
    </form>

    <div class="divider">
        Or
    </div>

    <button type="button" class="google-btn">
        <span class="google-circle"></span>
        <span>Sign in with Google</span>
    </button>
</div>

<script>
    function togglePassword() {
        const input = document.getElementById('password');
        const toggle = document.querySelector('.show-toggle');
        if (input.type === 'password') {
            input.type = 'text';
            toggle.textContent = 'Hide';
        } else {
            input.type = 'password';
            toggle.textContent = 'Show';
        }
    }
</script>

</body>
</html>
