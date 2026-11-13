@extends('layouts.app')

@section('page-title', 'All Follow Up List')

@section('content')
<section class="follow-up-page">
    <header class="follow-up-heading">
        <div>
            <div class="page-title">All Follow Up List</div>
            <div class="page-subtitle">Telecaller reminders for customers who still need follow-up. Notifications marked Nope are excluded.</div>
        </div>
        <div class="follow-up-total" aria-label="{{ $notifications->total() }} follow-up reminders">
            <i class="fas fa-bell" aria-hidden="true"></i>
            <span>{{ $notifications->total() }} reminders</span>
        </div>
    </header>

    <form class="follow-up-search" method="GET" action="{{ route('follow-ups.index') }}">
        <label class="follow-up-search-field" for="follow-up-search">
            <span class="form-label">Search reminders</span>
            <span class="follow-up-input-wrap">
                <i class="fas fa-search" aria-hidden="true"></i>
                <input
                    id="follow-up-search"
                    type="search"
                    name="search"
                    value="{{ request('search') }}"
                    placeholder="Customer, company, phone, telecaller, status or remarks"
                >
            </span>
        </label>
        <div class="follow-up-search-actions">
            <button type="submit" class="btn-upload"><i class="fas fa-search" aria-hidden="true"></i> Search</button>
            @if(request()->filled('search'))
                <a href="{{ route('follow-ups.index') }}" class="btn-cancel-small"><i class="fas fa-rotate-left" aria-hidden="true"></i> Clear</a>
            @endif
        </div>
    </form>

    <section class="card-box follow-up-card" aria-labelledby="follow-up-table-title">
        <div class="follow-up-card-heading">
            <div>
                <h2 id="follow-up-table-title">Reminder schedule</h2>
                <p>Showing telecaller-created reminders only.</p>
            </div>
            <span class="follow-up-count">{{ $notifications->count() }} on this page</span>
        </div>

        @if($notifications->count())
            <div class="follow-up-table-wrap">
                <table class="follow-up-table">
                    <thead>
                        <tr>
                            <th scope="col">Reminder date</th>
                            <th scope="col">Customer</th>
                            <th scope="col">Company</th>
                            <th scope="col">Mobile</th>
                            <th scope="col">Telecaller</th>
                            <th scope="col">Notification status</th>
                            <th scope="col">Remarks</th>
                            <th scope="col">Reminder time</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($notifications as $notification)
                            @php
                                $status = trim((string) $notification->status);
                                $statusSlug = \Illuminate\Support\Str::slug($status ?: 'not-selected');
                                $lead = $notification->assignBase;
                            @endphp
                            <tr>
                                <td class="follow-up-date">
                                    {{ $notification->notification_date?->format('d M Y') ?? '—' }}
                                </td>
                                <td class="follow-up-customer">{{ $lead->customer_name ?? '—' }}</td>
                                <td>{{ $lead->company_name ?? '—' }}</td>
                                <td>
                                    @if($lead?->mobile)
                                        <a class="follow-up-phone" href="tel:{{ $lead->mobile }}">{{ $lead->mobile }}</a>
                                    @else
                                        —
                                    @endif
                                </td>
                                <td>{{ $notification->employee->name ?? '—' }}</td>
                                <td>
                                    <span class="notification-status-pill status-{{ $statusSlug }}">
                                        {{ $status !== '' ? $status : 'Not selected' }}
                                    </span>
                                </td>
                                <td class="follow-up-remarks">{{ $notification->remarks ?: '—' }}</td>
                                <td class="follow-up-time">
                                    {{ $notification->notification_time ? \Illuminate\Support\Carbon::parse($notification->notification_time)->format('h:i A') : '—' }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="follow-up-pagination">
                {{ $notifications->onEachSide(1)->links('vendor.pagination.custom') }}
            </div>
        @else
            <div class="follow-up-empty">
                <span class="follow-up-empty-icon"><i class="fas fa-calendar-check" aria-hidden="true"></i></span>
                <h3>No follow-up reminders found</h3>
                <p>
                    @if(request()->filled('search'))
                        Try another search term or clear the search.
                    @else
                        Telecaller reminders will appear here. Account status updates are shown in Accounts Team instead.
                    @endif
                </p>
            </div>
        @endif
    </section>
</section>
@endsection

@push('styles')
<style>
    .follow-up-page { max-width: 1600px; margin: 0 auto; }
    .follow-up-heading {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 20px;
        margin-bottom: 20px;
    }
    .follow-up-heading .page-title { margin-bottom: 6px; }
    .follow-up-heading .page-subtitle { max-width: 760px; margin: 0; line-height: 1.55; }
    .follow-up-total {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        flex: 0 0 auto;
        padding: 9px 13px;
        border: 1px solid #c7d2fe;
        border-radius: 999px;
        background: #eef2ff;
        color: #4338ca;
        font-size: 12px;
        font-weight: 750;
    }
    .follow-up-search {
        display: flex;
        align-items: flex-end;
        gap: 12px;
        margin-bottom: 18px;
        padding: 16px;
        border: 1px solid #e6eaf2;
        border-radius: 14px;
        background: #fff;
        box-shadow: 0 4px 16px rgba(15, 23, 42, .04);
    }
    .follow-up-search-field { flex: 1; min-width: 220px; }
    .follow-up-input-wrap { position: relative; display: block; }
    .follow-up-input-wrap > i {
        position: absolute;
        top: 50%;
        left: 13px;
        transform: translateY(-50%);
        color: #94a3b8;
        font-size: 13px;
    }
    .follow-up-input-wrap input {
        width: 100%;
        min-height: 42px;
        padding-left: 38px;
        background: #fff;
    }
    .follow-up-search-actions { display: flex; align-items: center; gap: 9px; }
    .follow-up-search-actions .btn-upload,
    .follow-up-search-actions .btn-cancel-small { min-height: 42px; }
    .follow-up-card {
        width: 100%;
        padding: 0;
        overflow: hidden;
        border: 1px solid #e6eaf2;
        border-radius: 16px;
        box-shadow: 0 8px 26px rgba(15, 23, 42, .06);
    }
    .follow-up-card-heading {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
        padding: 20px 22px;
        border-bottom: 1px solid #edf0f5;
    }
    .follow-up-card-heading h2 { margin: 0; color: #172033; font-size: 16px; font-weight: 750; }
    .follow-up-card-heading p { margin: 5px 0 0; color: #788397; font-size: 12px; }
    .follow-up-count {
        padding: 6px 10px;
        border-radius: 999px;
        background: #f1f5f9;
        color: #475569;
        font-size: 11px;
        font-weight: 700;
        white-space: nowrap;
    }
    .follow-up-table-wrap { overflow-x: auto; }
    .follow-up-table { width: 100%; min-width: 1060px; border-collapse: separate; border-spacing: 0; }
    .follow-up-table th {
        padding: 12px 14px;
        background: #f8fafc;
        color: #64748b;
        font-size: 10px;
        font-weight: 750;
        letter-spacing: .055em;
        text-align: left;
        text-transform: uppercase;
        white-space: nowrap;
    }
    .follow-up-table td {
        padding: 13px 14px;
        border-top: 1px solid #f0f2f6;
        color: #475569;
        font-size: 12px;
        vertical-align: middle;
    }
    .follow-up-table tbody tr { transition: background .15s ease; }
    .follow-up-table tbody tr:hover { background: #fafbff; }
    .follow-up-table .follow-up-date,
    .follow-up-table .follow-up-time { color: #64748b; white-space: nowrap; }
    .follow-up-table .follow-up-customer { color: #172033; font-weight: 700; }
    .follow-up-phone { color: #047857; font-weight: 650; text-decoration: none; white-space: nowrap; }
    .follow-up-phone:hover { text-decoration: underline; }
    .follow-up-remarks { min-width: 220px; max-width: 360px; line-height: 1.5; }
    .notification-status-pill {
        display: inline-flex;
        align-items: center;
        padding: 5px 9px;
        border-radius: 999px;
        background: #eef2ff;
        color: #4338ca;
        font-size: 10px;
        font-weight: 750;
        white-space: nowrap;
    }
    .notification-status-pill.status-not-selected { background: #f1f5f9; color: #64748b; }
    .follow-up-pagination { padding: 16px 20px; border-top: 1px solid #edf0f5; }
    .follow-up-empty { padding: 54px 20px; text-align: center; }
    .follow-up-empty-icon {
        display: inline-grid;
        width: 52px;
        height: 52px;
        place-items: center;
        margin-bottom: 12px;
        border-radius: 16px;
        background: #eef2ff;
        color: #6366f1;
        font-size: 21px;
    }
    .follow-up-empty h3 { margin: 0; color: #1e293b; font-size: 15px; }
    .follow-up-empty p { margin: 6px 0 0; color: #64748b; font-size: 12px; }
    @media (max-width: 640px) {
        .follow-up-heading { flex-direction: column; gap: 12px; }
        .follow-up-search { align-items: stretch; flex-direction: column; }
        .follow-up-search-actions { flex-wrap: wrap; }
        .follow-up-card-heading { align-items: flex-start; padding: 16px; }
        .follow-up-table th, .follow-up-table td { padding: 11px 12px; }
    }
</style>
@endpush
