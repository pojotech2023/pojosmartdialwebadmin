<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Font Awesome (Eye icon) -->
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
            padding: 11px 38px 11px 12px;
            border-radius: 8px;
            border: 1px solid #d1d5db;
            font-size: 14px;
            background-color: #f9fafb;
            outline: none;
        }

        .input:focus {
            border-color: #2563eb;
            box-shadow: 0 0 0 1px rgba(37,99,235,0.25);
            background: #ffffff;
        }

        .show-toggle {
            position: absolute;
            top: 50%;
            right: 12px;
            transform: translateY(-50%);
            cursor: pointer;
        }

        .show-toggle i {
            font-size: 14px;
            color: #2563eb;
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

    {{-- Error message --}}
    @if($errors->any())
        <div class="alert-error">
            @foreach($errors->all() as $error)
                <div>{{ $error }}</div>
            @endforeach
        </div>
    @endif

    <form method="POST" action="{{ route('login.post') }}">
        @csrf

        <!-- Email -->
        <div class="field">
            <label class="label">Email ID</label>
            <div class="input-wrapper">
                <input class="input"
                       type="email"
                       name="email"
                       value="{{ old('email') }}"
                       placeholder="Enter email ID"
                       required>
            </div>
        </div>

        <!-- Password -->
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

        <!-- Login Button -->
        <button type="submit" class="btn-primary">
            Login
        </button>
    </form>
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
