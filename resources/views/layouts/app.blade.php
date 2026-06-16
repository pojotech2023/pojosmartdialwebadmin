<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Panel</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Font Awesome -->
    <link rel="stylesheet"
          href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <style>
        body {
            margin: 0;
            font-family: 'Segoe UI', sans-serif;
            background: #f5f7fb;
        }

        /* ================= SIDEBAR ================= */
        .sidebar {
            width: 60px;
            height: 100vh;
            background: #0f172a;
            position: fixed;
            left: 0;
            top: 0;
            transition: width 0.3s ease;
            overflow: hidden;
            z-index: 1000;
        }

        /* EXPAND ON HOVER */
        .sidebar:hover {
            width: 240px;
        }

        .sidebar-header {
            height: 60px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #ffffff;
            font-size: 20px;
        }

        .menu {
            list-style: none;
            padding: 0;
            margin: 10px 0 0;
        }

        .menu li {
            margin: 4px 0;
        }

        .menu a {
            display: flex;
            align-items: center;
            gap: 14px;
            padding: 12px 18px;
            color: #e5e7eb;
            text-decoration: none;
            font-size: 14px;
            white-space: nowrap;
            transition: background 0.2s ease;
        }

        .menu a i {
            font-size: 16px;
            min-width: 22px;
            text-align: center;
        }

        .menu a:hover {
            background: #1e293b;
            border-radius: 8px;
        }

        /* HIDE TEXT BY DEFAULT */
        .menu span {
            opacity: 0;
            transition: opacity 0.2s ease;
        }

        /* SHOW TEXT ON HOVER */
        .sidebar:hover .menu span {
            opacity: 1;
        }

        /* ================= CONTENT ================= */
        .content {
            margin-left: 60px;
            padding: 20px;
            transition: margin-left 0.3s ease;
        }

        /* SHIFT CONTENT WHEN SIDEBAR HOVER */
        .sidebar:hover ~ .content {
            margin-left: 240px;
        }
    </style>
</head>

<body>

{{-- ================= SIDEBAR ================= --}}
<div class="sidebar">

    <div class="sidebar-header">
        <i class="fas fa-bars"></i>
    </div>

    <ul class="menu">
        <li>
            <a href="{{ route('dashboard') }}">
                <i class="fas fa-chart-line"></i>
                <span>Dashboard</span>
            </a>
        </li>

        <li>
            <a href="{{ route('employees.index') }}">
                <i class="fas fa-users"></i>
                <span>Employees</span>
            </a>
        </li>

        <li>
            <a href="{{ route('assign-base.index') }}">
                <i class="fas fa-database"></i>
                <span>Assign Base</span>
            </a>
        </li>

        <li>
            <a href="{{ route('logout') }}"
               onclick="event.preventDefault();document.getElementById('logout-form').submit();">
                <i class="fas fa-sign-out-alt"></i>
                <span>Logout</span>
            </a>
        </li>
    </ul>
</div>

{{-- ================= MAIN CONTENT ================= --}}
<div class="content">
    @yield('content')
</div>

<form id="logout-form" action="{{ route('logout') }}" method="POST" style="display:none;">
    @csrf
</form>

</body>
</html>
