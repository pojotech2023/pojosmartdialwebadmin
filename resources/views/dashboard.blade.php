<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Dashboard - Users</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <style>
        * { box-sizing: border-box; margin: 0; padding: 0;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; }

        body {
            background-color: #f5f7fb;
            padding: 32px 16px;
            display: flex;
            justify-content: center;
        }

        .container {
            max-width: 900px;
            width: 100%;
        }

        .top-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 18px;
        }

        .welcome {
            font-size: 20px;
            font-weight: 600;
        }

        .subtitle {
            font-size: 13px;
            color: #6b7280;
            margin-top: 4px;
        }

        .logout-btn {
            border: none;
            background: #ef4444;
            color: white;
            padding: 8px 16px;
            border-radius: 999px;
            font-size: 13px;
            cursor: pointer;
        }

        .card {
            background: #ffffff;
            border-radius: 14px;
            box-shadow: 0 8px 24px rgba(0,0,0,0.06);
            padding: 20px 22px 18px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 8px;
            font-size: 14px;
        }

        th, td {
            text-align: left;
            padding: 10px 8px;
        }

        th {
            font-size: 13px;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            color: #6b7280;
            border-bottom: 1px solid #e5e7eb;
        }

        tr:nth-child(even) td {
            background: #f9fafb;
        }

        .badge {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 999px;
            font-size: 11px;
            background: #e0f2fe;
            color: #0369a1;
        }

        .empty {
            font-size: 13px;
            color: #6b7280;
            margin-top: 8px;
        }
    </style>
</head>
<body>
<div class="container">

    <div class="top-bar">
        <div>
            <div class="welcome">
                Hi, {{ auth()->user()->name }} 👋
            </div>
            <div class="subtitle">
                Here are the latest 5 registered users.
            </div>
        </div>

        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="logout-btn">Logout</button>
        </form>
    </div>

    <div class="card">
        @if($users->count())
            <table>
                <thead>
                <tr>
                    <th>#</th>
                    <th>Full Name</th>
                    <th>Email</th>
                    <th>Joined</th>
                </tr>
                </thead>
                <tbody>
                @foreach($users as $index => $user)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ $user->name }}</td>
                        <td>{{ $user->email }}</td>
                        <td>
                            <span class="badge">
                                {{ $user->created_at->format('d M Y') }}
                            </span>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        @else
            <div class="empty">
                No users found yet.
            </div>
        @endif
    </div>

</div>
</body>
</html>
