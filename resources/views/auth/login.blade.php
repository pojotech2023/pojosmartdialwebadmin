<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Font Awesome -->
    <link rel="stylesheet"
          href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
        }

        body {
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 24px;
            background: radial-gradient(circle at 15% 20%, #4338ca 0%, transparent 45%),
                        radial-gradient(circle at 85% 80%, #06b6d4 0%, transparent 45%),
                        linear-gradient(135deg, #0f172a, #1e1b4b);
        }

        .auth-shell {
            display: grid;
            grid-template-columns: 1.1fr 1fr;
            max-width: 900px;
            width: 100%;
            background: #ffffff;
            border-radius: 24px;
            box-shadow: 0 30px 80px rgba(0,0,0,0.35);
            overflow: hidden;
        }

        @media (max-width: 760px) {
            .auth-shell { grid-template-columns: 1fr; max-width: 420px; }
            .brand-panel { display: none; }
        }

        .brand-panel {
            background: linear-gradient(160deg, #4f46e5, #4338ca 55%, #1e1b4b);
            color: #fff;
            padding: 44px 38px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            position: relative;
            overflow: hidden;
        }

        .brand-panel::after {
            content: "";
            position: absolute;
            width: 320px;
            height: 320px;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(255,255,255,0.12), transparent 70%);
            right: -120px;
            bottom: -120px;
        }

        .brand-logo {
            display: flex;
            align-items: center;
            gap: 12px;
            font-size: 18px;
            font-weight: 700;
        }

        .brand-logo .logo-badge {
            width: 38px;
            height: 38px;
            border-radius: 11px;
            background: rgba(255,255,255,0.15);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 17px;
        }

        .brand-copy {
            position: relative;
            z-index: 1;
        }

        .brand-copy h2 {
            font-size: 26px;
            font-weight: 700;
            line-height: 1.3;
            margin-bottom: 10px;
        }

        .brand-copy p {
            font-size: 13.5px;
            color: rgba(255,255,255,0.75);
            line-height: 1.6;
        }

        .brand-foot {
            font-size: 12px;
            color: rgba(255,255,255,0.55);
            position: relative;
            z-index: 1;
        }

        .form-panel {
            padding: 48px 42px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .title {
            font-size: 24px;
            font-weight: 700;
            margin-bottom: 4px;
            color: #111827;
        }

        .subtitle {
            font-size: 13px;
            color: #6b7280;
            margin-bottom: 26px;
        }

        .field { margin-bottom: 18px; }

        .label {
            font-size: 13px;
            font-weight: 600;
            margin-bottom: 6px;
            display: block;
            color: #374151;
        }

        .input-wrapper { position: relative; }

        .input {
            width: 100%;
            padding: 12px 40px 12px 14px;
            border-radius: 10px;
            border: 1px solid #d1d5db;
            font-size: 14px;
            background-color: #f9fafb;
            outline: none;
            transition: border-color 0.15s ease, box-shadow 0.15s ease, background 0.15s ease;
        }

        .input:focus {
            border-color: #4f46e5;
            box-shadow: 0 0 0 3px rgba(79,70,229,0.12);
            background: #ffffff;
        }

        .show-toggle {
            position: absolute;
            top: 50%;
            right: 13px;
            transform: translateY(-50%);
            cursor: pointer;
        }

        .show-toggle i { font-size: 14px; color: #4f46e5; }

        .btn-primary {
            width: 100%;
            padding: 12px 0;
            margin-top: 8px;
            border-radius: 10px;
            border: none;
            background: linear-gradient(135deg, #4f46e5, #6366f1);
            color: #ffffff;
            font-size: 14px;
            font-weight: 700;
            cursor: pointer;
            box-shadow: 0 8px 20px rgba(79,70,229,0.3);
            transition: transform 0.12s ease, box-shadow 0.12s ease;
        }

        .btn-primary:hover {
            transform: translateY(-1px);
            box-shadow: 0 10px 24px rgba(79,70,229,0.4);
        }

        .alert-error {
            background: #fef2f2;
            border: 1px solid #fecaca;
            color: #b91c1c;
            padding: 10px 12px;
            border-radius: 10px;
            font-size: 12.5px;
            margin-bottom: 16px;
        }
    </style>
</head>
<body>

<div class="auth-shell">

    <div class="brand-panel">
        <div class="brand-logo">
            <div class="logo-badge"><i class="fas fa-bolt"></i></div>
            <span>Admin Panel</span>
        </div>

        <div class="brand-copy">
            <h2>Manage your operations from one dashboard.</h2>
            <p>Employees, assigned bases, and WhatsApp notifications — all in one clean, unified workspace.</p>
        </div>

        <div class="brand-foot">&copy; {{ date('Y') }} Admin Panel. All rights reserved.</div>
    </div>

    <div class="form-panel">
        <div class="title">Welcome back</div>
        <div class="subtitle">Sign in to continue to your dashboard</div>

        @if($errors->any())
            <div class="alert-error">
                @foreach($errors->all() as $error)
                    <div>{{ $error }}</div>
                @endforeach
            </div>
        @endif

        <form method="POST" action="{{ route('login.post') }}">
            @csrf

            <div class="field">
                <label class="label">Email ID / Mobile Number</label>
                <div class="input-wrapper">
                    <input class="input"
                           type="text"
                           name="email"
                           value="{{ old('email') }}"
                           placeholder="Enter registered email or mobile number"
                           required>
                </div>
            </div>

            <div class="field">
                <label class="label">Password</label>
                <div class="input-wrapper">
                    <input class="input"
                           type="password"
                           id="password"
                           name="password"
                           placeholder="Enter password"
                           required>
                    <span class="show-toggle" onclick="togglePassword()">
                        <i class="fa-solid fa-eye" id="eyeIcon"></i>
                    </span>
                </div>
            </div>

            <button type="submit" class="btn-primary">
                Login
            </button>
        </form>
    </div>

</div>

<script>
    function togglePassword() {
        const input = document.getElementById('password');
        const icon = document.getElementById('eyeIcon');

        if (input.type === 'password') {
            input.type = 'text';
            icon.classList.remove('fa-eye');
            icon.classList.add('fa-eye-slash');
        } else {
            input.type = 'password';
            icon.classList.remove('fa-eye-slash');
            icon.classList.add('fa-eye');
        }
    }
</script>

</body>
</html>
