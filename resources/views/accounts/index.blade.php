@extends('layouts.app')

@section('page-title', 'Accounts Team')

@section('content')
<div class="page-title">Accounts Team</div>
<div class="page-subtitle">Leads assigned to Account users — manage account status here</div>

{{-- ============ FULL-SCREEN LEAD DETAILS PANEL (reused) ============ --}}
<div id="accLeadDetailsPanel" class="notif-panel">
    <div class="notif-panel-header" style="background:linear-gradient(135deg,#047857,#059669 60%,#10b981);box-shadow:0 8px 24px rgba(5,150,105,0.25);">
        <div class="notif-panel-header-left">
            <div class="notif-panel-icon" style="background:rgba(255,255,255,0.2);">
                <i class="fas fa-user-tie"></i>
            </div>
            <div>
                <div id="accLeadPanelTitle" class="notif-panel-title">Lead Details</div>
                <div id="accLeadPanelSubtitle" class="notif-panel-subtitle">Account Status Management</div>
            </div>
        </div>
        <button onclick="closeAccLeadPanel()" class="notif-close-btn">
            <i class="fas fa-times"></i> Close
        </button>
    </div>

    <div id="acc-lead-details-content" class="notif-panel-content">
        <div class="nl-loading">
            <i class="fas fa-spinner fa-spin"></i>
            <p>Loading details...</p>
        </div>
    </div>
</div>

{{-- Toast --}}
<div id="accToastContainer" style="position:fixed;bottom:24px;right:24px;z-index:99999;display:flex;flex-direction:column;gap:10px"></div>

<style>
    #accLeadDetailsPanel {
        display: none;
        position: fixed;
        inset: 0;
        z-index: 9999;
        overflow-y: auto;
        background: #f1f5f9;
    }
    #accLeadDetailsPanel .notif-panel-header {
        position: sticky;
        top: 0;
        z-index: 10;
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 12px 24px;
        color: #0f172a;
        background: rgba(255,255,255,.96) !important;
        box-shadow: 0 1px 3px rgba(15,23,42,.06) !important;
        border-bottom: 1px solid #e2e8f0;
    }
    #accLeadDetailsPanel .notif-panel-header-left { display: flex; align-items: center; gap: 14px; }
    #accLeadDetailsPanel .notif-panel-icon {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 44px;
        height: 44px;
        border-radius: 14px;
        color: #047857;
        background: #d1fae5 !important;
        font-size: 17px;
    }
    #accLeadDetailsPanel .notif-panel-title { font-size: 16px; font-weight: 800; }
    #accLeadDetailsPanel .notif-panel-subtitle { margin-top: 2px; color: #64748b; font-size: 12px; }
    #accLeadDetailsPanel .notif-close-btn {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 8px 13px;
        border: 1px solid #e2e8f0;
        border-radius: 9px;
        background: #fff;
        color: #334155;
        cursor: pointer;
        font-size: 13px;
        font-weight: 600;
    }
    #accLeadDetailsPanel .notif-panel-content { max-width: 1040px !important; padding: 22px 20px 36px !important; margin: 0 auto !important; }

    .acc-detail-card { background:#fff; border:1px solid #e2e8f0; border-radius:16px; box-shadow:0 8px 28px rgba(15,23,42,.06); overflow:hidden; }
    .acc-detail-header { padding:18px 22px; display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:16px; background:#fff; border-bottom:1px solid #edf2f7; }
    .acc-detail-identity { display:flex; align-items:center; gap:13px; min-width:250px; }
    .acc-detail-avatar { width:46px; height:46px; border-radius:13px; background:#fee2e2; color:#ef4444; display:flex; align-items:center; justify-content:center; font-size:20px; font-weight:800; flex-shrink:0; }
    .acc-detail-name { font-size:18px; font-weight:800; color:#172033; line-height:1.25; }
    .acc-detail-meta { display:flex; flex-wrap:wrap; align-items:center; gap:7px; margin-top:5px; color:#64748b; font-size:12px; }
    .acc-detail-product { background:#eef2ff; color:#4338ca; padding:3px 8px; border-radius:7px; font-weight:700; font-size:11px; }
    .acc-detail-actions { display:flex; align-items:center; justify-content:flex-end; flex-wrap:wrap; gap:9px; }
    .acc-detail-status { display:flex; flex-direction:column; gap:4px; min-width:155px; }
    .acc-detail-status label, .acc-info-label { color:#718096; font-size:10px; font-weight:800; letter-spacing:.04em; text-transform:uppercase; }
    .acc-detail-body { padding:20px 22px 22px; }
    .acc-info-grid { display:grid; grid-template-columns:repeat(4,minmax(0,1fr)); gap:10px; margin-bottom:18px; }
    .acc-info-item { min-width:0; padding:11px 12px; border:1px solid #edf2f7; border-radius:11px; background:#f8fafc; }
    .acc-info-label { display:block; margin-bottom:5px; }
    .acc-info-label i { margin-right:4px; }
    .acc-info-value { color:#1e293b; font-size:13px; font-weight:700; line-height:1.4; overflow-wrap:anywhere; }
    .acc-info-subvalue { margin-top:2px; color:#64748b; font-size:11px; }
    .acc-note-box { padding:13px 14px; margin-bottom:18px; border:1px solid #d1fae5; border-radius:12px; background:#f0fdf4; }
    .acc-note-heading { margin-bottom:9px; color:#065f46; font-size:12px; font-weight:800; text-transform:uppercase; }
    .acc-note-controls { display:flex; align-items:center; gap:9px; }
    .acc-note-input { flex:1; min-width:0; padding:9px 11px; border:1px solid #cbd5e1; border-radius:8px; background:#fff; color:#1e293b; font-size:13px; outline:none; }
    .acc-note-input:focus { border-color:#10b981; box-shadow:0 0 0 3px rgba(16,185,129,.12); }
    .acc-note-save { padding:9px 14px; border:0; border-radius:8px; background:#059669; color:#fff; font-size:12px; font-weight:700; cursor:pointer; white-space:nowrap; }
    .acc-remarks-section { padding-top:16px; border-top:1px solid #edf2f7; }
    .acc-remarks-heading { display:flex; justify-content:space-between; align-items:center; gap:10px; margin-bottom:10px; color:#1e293b; font-size:14px; font-weight:800; }
    .acc-remarks-count { padding:3px 9px; border-radius:20px; background:#f1f5f9; color:#64748b; font-size:11px; font-weight:700; }
    .acc-remarks-list { display:flex; flex-direction:column; gap:8px; max-height:260px; overflow-y:auto; padding-right:4px; }
    .acc-remark { padding:11px 13px; border:1px solid #edf2f7; border-radius:10px; background:#f8fafc; }
    .acc-remark-meta { display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:6px; margin-bottom:5px; font-size:11px; }
    .acc-remark-author { color:#1e293b; font-weight:800; }
    .acc-remark-time { color:#94a3b8; }
    .acc-remark-text { color:#475569; font-size:12.5px; line-height:1.5; overflow-wrap:anywhere; }

    @media (max-width: 760px) {
        #accLeadDetailsPanel .notif-panel-content { padding:14px 12px 24px !important; }
        .acc-detail-header, .acc-detail-body { padding:16px; }
        .acc-detail-actions { justify-content:flex-start; width:100%; }
        .acc-detail-status { flex:1; }
        .acc-info-grid { grid-template-columns:repeat(2,minmax(0,1fr)); }
    }
    @media (max-width: 460px) {
        .acc-info-grid { grid-template-columns:1fr; }
        .acc-note-controls { align-items:stretch; flex-direction:column; }
        .acc-note-save { width:100%; }
        .acc-detail-actions > a { flex:1; justify-content:center; }
    }

    .acc-status-badge {
        display: inline-block;
        padding: 3px 10px;
        border-radius: 20px;
        font-size: 11px;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        white-space: nowrap;
    }
    .acc-status-badge.new            { background:#dbeafe; color:#1d4ed8; }
    .acc-status-badge.in-progress    { background:#fef9c3; color:#854d0e; }
    .acc-status-badge.quotation-shared { background:#ede9fe; color:#6d28d9; }
    .acc-status-badge.client-replied { background:#dcfce7; color:#15803d; }
    #accLeadDetailsPanel .account-status-select {
        width:100%;
        min-width:155px;
        padding:8px 30px 8px 11px;
        border:1px solid #cbd5e1;
        border-radius:9px;
        background-color:#fff;
        font-size:12px;
        font-weight:800;
        cursor:pointer;
        outline:none;
        transition:border-color .15s ease, box-shadow .15s ease;
    }
    #accLeadDetailsPanel .account-status-select:focus {
        border-color:#10b981;
        box-shadow:0 0 0 3px rgba(16,185,129,.12);
    }
    #accLeadDetailsPanel .account-status-select:disabled { opacity:.65; cursor:wait; }
    #accLeadDetailsPanel .account-status-select.status-new { background-color:#eff6ff; color:#1d4ed8; }
    #accLeadDetailsPanel .account-status-select.status-in-progress { background-color:#fffbeb; color:#b45309; }
    #accLeadDetailsPanel .account-status-select.status-quotation-shared { background-color:#f5f3ff; color:#7e22ce; }
    #accLeadDetailsPanel .account-status-select.status-client-replied { background-color:#ecfdf5; color:#047857; }
    .acc-detail-actions a { transition:transform .15s ease, box-shadow .15s ease; }
    .acc-detail-actions a:hover { transform:translateY(-1px); box-shadow:0 4px 10px rgba(15,23,42,.12); }
    .acc-note-save:hover { background:#047857; }
</style>

{{-- ============ FILTER FORM ============ --}}
<form method="GET" action="{{ route('accounts.index') }}">
    <div class="filter-row" style="display:flex;flex-wrap:wrap;gap:12px;align-items:flex-end;margin-bottom:12px;">

        @if($isAdmin)
        <div>
            <label class="form-label">Account User</label>
            <select name="account_user_id">
                <option value="">All Accounts</option>
                @foreach($accountUsers as $uid => $uname)
                    <option value="{{ $uid }}" {{ request('account_user_id') == $uid ? 'selected' : '' }}>{{ $uname }}</option>
                @endforeach
            </select>
        </div>
        @endif

        <div>
            <label class="form-label">Product Type</label>
            <select name="product_id">
                <option value="">All Products</option>
                @foreach($products as $pid => $pname)
                    <option value="{{ $pid }}" {{ request('product_id') == $pid ? 'selected' : '' }}>{{ $pname }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="form-label">Account Status</label>
            <select name="account_status">
                <option value="">All</option>
                <option value="NEW"              {{ request('account_status') == 'NEW'              ? 'selected' : '' }}>NEW</option>
                <option value="IN PROGRESS"      {{ request('account_status') == 'IN PROGRESS'      ? 'selected' : '' }}>IN PROGRESS</option>
                <option value="QUOTATION SHARED" {{ request('account_status') == 'QUOTATION SHARED' ? 'selected' : '' }}>QUOTATION SHARED</option>
                <option value="CLIENT REPLIED"   {{ request('account_status') == 'CLIENT REPLIED'   ? 'selected' : '' }}>CLIENT REPLIED</option>
                <option value="CONFIRMED"        {{ request('account_status') == 'CONFIRMED'        ? 'selected' : '' }}>CONFIRMED</option>
            </select>
        </div>

        <div>
            <label class="form-label">From Date</label>
            <input type="date" name="from_date" value="{{ request('from_date') }}" max="{{ now()->toDateString() }}">
        </div>

        <div>
            <label class="form-label">To Date</label>
            <input type="date" name="to_date" value="{{ request('to_date') }}" max="{{ now()->toDateString() }}">
        </div>

        <button type="submit" class="btn-upload"><i class="fas fa-filter"></i> Filter</button>
        <a href="{{ route('accounts.index') }}" class="btn-cancel-small"><i class="fas fa-rotate-left"></i> Cancel</a>
    </div>
</form>

{{-- ============ TABLE ============ --}}
@if(isset($paginatedRows) && $paginatedRows->count() > 0)
<div class="card-box" style="margin-top:12px">
<table>
    <thead>
        <tr>
            <th>Lead ID</th>
            <th>Telecaller</th>
            <th>Customer Name</th>
            <th>Company Name</th>
            <th>Product Type</th>
            <th>Assigned Account</th>
            <th>Account Status</th>
            <th>Mobile</th>
            <th>Date</th>
            <th>Action</th>
        </tr>
    </thead>
    <tbody>
        @foreach($paginatedRows as $row)
        @php
            $accountStatus = $row->account_status ?? $row->leadDetail->account_status ?? 'NEW';
            $statusSlug    = \Illuminate\Support\Str::slug($accountStatus);
        @endphp
        <tr>
            <td>#{{ $row->id }}</td>
            <td>
                <div style="font-weight:600;color:#1e293b;">{{ $row->employee->name ?? '—' }}</div>
            </td>
            <td>{{ $row->customer_name }}</td>
            <td>{{ $row->company_name }}</td>
            <td>
                @if($row->product || $row->product_type)
                    <span style="background:#eef2ff;color:#4338ca;padding:3px 9px;border-radius:12px;font-size:11.5px;font-weight:700;">
                        <i class="fas fa-tag" style="font-size:10px;margin-right:3px;"></i>
                        {{ $row->product->name ?? $row->product_type }}
                    </span>
                @else
                    <span style="color:#94a3b8;font-size:12px;">—</span>
                @endif
            </td>
            <td>
                <div style="font-weight:700;color:#047857;">{{ $row->assignedUser->name ?? '—' }}</div>
                <div style="font-size:11px;color:#64748b;">{{ $row->assignedUser->mobile ?? '' }}</div>
            </td>
            <td>
                <span id="acc-table-status-{{ $row->id }}" class="acc-status-badge {{ $statusSlug }}">
                    {{ $accountStatus }}
                </span>
            </td>
            <td>{{ $row->mobile }}</td>
            <td>{{ \Carbon\Carbon::parse($row->assign_date)->format('d/m/Y') }}</td>
            <td>
                <button class="icon-btn icon-btn-view" onclick="openAccLeadPanel({{ $row->id }})" title="View & Update Account Status">
                    <i class="fas fa-eye"></i>
                </button>
            </td>
        </tr>
        @endforeach
    </tbody>
</table>
</div>
<div style="margin-top:15px">
    {{ $paginatedRows->onEachSide(1)->links('vendor.pagination.custom') }}
</div>
@else
<div class="card-box" style="text-align:center;padding:60px 20px;color:#94a3b8;">
    <i class="fas fa-user-tie" style="font-size:40px;margin-bottom:12px;display:block;color:#cbd5e1;"></i>
    <div style="font-size:16px;font-weight:700;">No assigned leads found</div>
    <div style="font-size:13px;margin-top:4px;">Leads will appear here once a telecaller assigns them to an Account user.</div>
</div>
@endif

<script>
const IS_ADMIN_ACC  = {{ $isAdmin ? 'true' : 'false' }};
const IS_ACCOUNT    = {{ $isAccount ? 'true' : 'false' }};
const LEAD_DETAILS_ROUTE_ACC = "{{ url('/dashboard/lead-details-data') }}";
const UPD_STATUS_ROUTE_ACC   = "{{ route('dashboard.update-lead-status') }}";

function escHtml(str) {
    if (str === null || str === undefined) return '';
    return String(str)
        .replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;').replace(/'/g, '&#039;');
}

// ---- Panel open/close ----
function openAccLeadPanel(leadId) {
    document.getElementById('accLeadDetailsPanel').style.display = 'block';
    document.body.style.overflow = 'hidden';
    loadAccLeadDetails(leadId);
}

function closeAccLeadPanel() {
    document.getElementById('accLeadDetailsPanel').style.display = 'none';
    document.body.style.overflow = '';
}

document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') closeAccLeadPanel();
});

function loadAccLeadDetails(leadId) {
    document.getElementById('acc-lead-details-content').innerHTML =
        '<div class="nl-loading"><i class="fas fa-spinner fa-spin"></i><p>Loading...</p></div>';

    fetch(LEAD_DETAILS_ROUTE_ACC + '/' + leadId)
        .then(r => r.ok ? r.json() : r.json().then(e => Promise.reject(e.message || 'Error')))
        .then(res => {
            if (res.status && res.data) renderAccLeadDetails(res.data);
            else throw new Error(res.message || 'Failed');
        })
        .catch(err => {
            document.getElementById('acc-lead-details-content').innerHTML =
                `<p style="color:#ef4444;text-align:center;padding:40px"><b>Error:</b> ${err}</p>`;
        });
}

function renderAccLeadDetails(d) {
    const title = document.getElementById('accLeadPanelTitle');
    const sub   = document.getElementById('accLeadPanelSubtitle');
    if (title) title.innerHTML = escHtml(d.customer_name || 'Lead Details');
    if (sub)   sub.innerHTML   = `<i class="fas fa-phone-alt" style="font-size:11px;margin-right:4px;"></i> ${escHtml(d.mobile || '—')} &bull; Lead #${d.id}`;

    const p     = d.project_details || {};
    const appt  = d.appointment_details || {};
    const notes = Array.isArray(d.notes) ? d.notes : [];
    const isGstYes      = (p.gst === 'Y' || p.gst === 'YES');
    const acStatus      = p.account_status || 'NEW';
    const acStatusSlug  = acStatus.toLowerCase().replace(/\s+/g, '-');
    let html = `
    <div class="acc-detail-card">

        {{-- Header --}}
        <div class="acc-detail-header">
            <div class="acc-detail-identity">
                <div class="acc-detail-avatar">
                    ${escHtml((d.customer_name || 'C').charAt(0).toUpperCase())}
                </div>
                <div>
                    <div style="display:flex;align-items:center;gap:8px;flex-wrap:wrap;">
                        <span class="acc-detail-name">${escHtml(d.customer_name || 'Customer')}</span>
                        <span class="acc-detail-product">
                            <i class="fas fa-tag" style="font-size:10px;"></i> ${escHtml(p.project_type || d.product_type || '—')}
                        </span>
                    </div>
                    <div class="acc-detail-meta">
                        <span><i class="far fa-building"></i> ${escHtml(p.cus_company_name || d.company_name || '—')}</span>
                        <span aria-hidden="true">&bull;</span>
                        <span><i class="far fa-calendar-alt"></i> ${escHtml(d.assign_date || '—')}</span>
                    </div>
                </div>
            </div>
            <div class="acc-detail-actions">
                <div class="acc-detail-status">
                    <label for="accStatusSelect-${d.id}">Account Status</label>
                    ${IS_ACCOUNT ? `
                    <select id="accStatusSelect-${d.id}" class="account-status-select status-${acStatusSlug}"
                            data-lead-id="${d.id}" data-current="${escHtml(acStatus)}"
                            onchange="accChangeStatus(${d.id}, this.value, this)">
                        <option value="NEW" ${acStatus.toUpperCase()==='NEW'?'selected':''}>NEW</option>
                        <option value="IN PROGRESS" ${acStatus.toUpperCase()==='IN PROGRESS'?'selected':''}>IN PROGRESS</option>
                        <option value="QUOTATION SHARED" ${acStatus.toUpperCase()==='QUOTATION SHARED'?'selected':''}>QUOTATION SHARED</option>
                        <option value="CLIENT REPLIED" ${acStatus.toUpperCase()==='CLIENT REPLIED'?'selected':''}>CLIENT REPLIED</option>
                        <option value="CONFIRMED" ${acStatus.toUpperCase()==='CONFIRMED'?'selected':''}>CONFIRMED</option>
                    </select>
                    ` : `
                    <span id="acc-modal-badge-${d.id}" class="acc-status-badge ${acStatusSlug}">${escHtml(acStatus)}</span>
                    `}
                </div>
                <a href="tel:${escHtml(d.mobile)}" style="background:#f1f5f9;color:#0f172a;padding:9px 13px;border-radius:9px;text-decoration:none;font-size:12px;font-weight:700;display:inline-flex;align-items:center;gap:6px;">
                    <i class="fas fa-phone-alt" style="color:#059669;"></i> Call
                </a>
                <a href="https://wa.me/${escHtml(d.mobile).replace(/[^\d]/g,'')}" target="_blank" rel="noopener noreferrer" style="background:#16a34a;color:#fff;padding:9px 13px;border-radius:9px;text-decoration:none;font-size:12px;font-weight:700;display:inline-flex;align-items:center;gap:6px;">
                    <i class="fab fa-whatsapp" style="font-size:14px;"></i> WhatsApp
                </a>
            </div>
        </div>

        <div class="acc-detail-body">

            {{-- Info Grid --}}
            <div class="acc-info-grid">

                <div class="acc-info-item">
                    <span class="acc-info-label"><i class="fas fa-headset" style="color:#6366f1;"></i> Origin Telecaller</span>
                    <div class="acc-info-value">${escHtml(d.telecaller?.name || '—')}</div>
                    ${d.telecaller?.mobile ? `<div class="acc-info-subvalue">${escHtml(d.telecaller.mobile)}</div>` : ''}
                </div>

                <div class="acc-info-item">
                    <span class="acc-info-label"><i class="fas fa-user-shield" style="color:#059669;"></i> Assigned Account</span>
                    <div class="acc-info-value" style="color:#047857;">${escHtml(d.assigned_to?.name || '—')}</div>
                    ${d.assigned_to?.mobile ? `<div class="acc-info-subvalue">${escHtml(d.assigned_to.mobile)}</div>` : ''}
                </div>

                <div class="acc-info-item">
                    <span class="acc-info-label"><i class="fas fa-shapes" style="color:#ef4444;"></i> Project Type</span>
                    <div class="acc-info-value">${escHtml(p.project_type || d.product_type || '—')}</div>
                </div>

                <div class="acc-info-item">
                    <span class="acc-info-label"><span style="font-weight:800;color:#16a34a;margin-right:4px;">₹</span> Price / Budget</span>
                    <div class="acc-info-value" style="color:${p.price_raw||(p.price&&p.price!=='—')?'#15803d':'#94a3b8'};">
                        ${p.price && p.price !== '—' ? escHtml(p.price) : (p.price_raw ? '₹ '+escHtml(p.price_raw) : '—')}
                    </div>
                </div>

                <div class="acc-info-item">
                    <span class="acc-info-label"><i class="far fa-envelope" style="color:#d97706;"></i> Customer Email</span>
                    <div class="acc-info-value" style="color:${p.cus_mail?'#2563eb':'#94a3b8'};">
                        ${p.cus_mail ? `<a href="mailto:${escHtml(p.cus_mail)}" style="color:#2563eb;text-decoration:none;">${escHtml(p.cus_mail)}</a>` : '—'}
                    </div>
                </div>

                <div class="acc-info-item">
                    <span class="acc-info-label"><i class="far fa-calendar-alt" style="color:#64748b;"></i> Assigned Date</span>
                    <div class="acc-info-value">${escHtml(d.assign_date || '—')}</div>
                </div>

                <div class="acc-info-item">
                    <span class="acc-info-label"><i class="far fa-building" style="color:#0ea5e9;"></i> Company</span>
                    <div class="acc-info-value">${escHtml(p.cus_company_name || d.company_name || '—')}</div>
                </div>

                <div class="acc-info-item">
                    <span class="acc-info-label"><i class="fas fa-receipt" style="color:#8b5cf6;"></i> GST</span>
                    <div class="acc-info-value">${isGstYes ? 'Yes' : (p.gst ? escHtml(p.gst) : 'No')}</div>
                </div>

            </div>

            {{-- Account users can update status; Admins see the saved status only. --}}
            ${IS_ACCOUNT ? `
            <div class="acc-note-box">
                <div class="acc-note-heading"><i class="fas fa-sticky-note" style="margin-right:5px;"></i> Account Note</div>
                <div class="acc-note-controls">
                    <input type="text" id="accRemarksInput-${d.id}" class="acc-note-input" value="${escHtml(p.account_remarks || '')}"
                           placeholder="Add a note about this lead...">
                    <button class="acc-note-save" onclick="accSaveRemarks(${d.id})">
                        <i class="fas fa-save" style="margin-right:5px;"></i> Save Note
                    </button>
                </div>
                ${d.account_status_details?.updated_by && d.account_status_details.updated_by !== '—' ? `
                    <div style="font-size:11px;color:#64748b;margin-top:8px;">
                        Last updated by <strong>${escHtml(d.account_status_details.updated_by)}</strong>
                        on ${escHtml(d.account_status_details.updated_at || '—')}
                    </div>
                ` : ''}
            </div>
            ` : `
            <div style="padding:11px 13px;margin-bottom:18px;border:1px solid #edf2f7;border-radius:10px;background:#f8fafc;">
                <div class="acc-info-label"><i class="fas fa-sticky-note" style="color:#059669;"></i> Account Note</div>
                <div class="acc-info-value" style="font-weight:500;">${p.account_remarks ? escHtml(p.account_remarks) : 'No account note added.'}</div>
                ${d.account_status_details?.updated_by && d.account_status_details.updated_by !== '—' ? `
                    <div style="font-size:11px;color:#64748b;margin-top:7px;">
                        Last updated by <strong>${escHtml(d.account_status_details.updated_by)}</strong>
                        on ${escHtml(d.account_status_details.updated_at || '—')}
                    </div>
                ` : ''}
            </div>
            `}

            {{-- Remarks History --}}
            <div class="acc-remarks-section">
                <div class="acc-remarks-heading">
                    <span><i class="fas fa-history" style="color:#6366f1;margin-right:6px;"></i> Telecaller Remarks History</span>
                    <span class="acc-remarks-count">
                        ${notes.length} ${notes.length===1?'Remark':'Remarks'}
                    </span>
                </div>
                ${notes.length === 0 ? `
                    <div style="text-align:center;padding:16px;color:#94a3b8;font-size:12px;background:#f8fafc;border-radius:10px;">
                        No telecaller remarks yet.
                    </div>
                ` : `
                    <div class="acc-remarks-list">
                        ${notes.map(n => `
                            <div class="acc-remark">
                                <div class="acc-remark-meta">
                                    <span class="acc-remark-author">${escHtml(n.employee_name)}
                                        ${n.status ? `<span class="acc-status-badge ${String(n.status).toLowerCase().replace(/\s+/g,'-')}" style="margin-left:5px;padding:2px 6px;">${escHtml(n.status)}</span>` : ''}
                                    </span>
                                    <span class="acc-remark-time">${escHtml(n.created_at || (n.notification_date+' '+(n.notification_time||'')))}</span>
                                </div>
                                <div class="acc-remark-text">${escHtml(n.remarks || 'No remarks text')}</div>
                                ${n.location ? `<div class="acc-info-subvalue"><i class="fas fa-map-marker-alt" style="color:#ef4444;"></i> ${escHtml(n.location)}</div>` : ''}
                                ${n.appointment_date ? `<div class="acc-info-subvalue" style="color:#7c3aed;"><i class="far fa-calendar-alt"></i> ${escHtml(n.appointment_date)} ${escHtml(n.appointment_time||'')}</div>` : ''}
                            </div>
                        `).join('')}
                    </div>
                `}
            </div>

        </div>
    </div>
    `;

    document.getElementById('acc-lead-details-content').innerHTML = html;
}

// ---- Account Status Update ----
function accChangeStatus(leadId, newStatus, selectEl, remarks = null) {
    if (!leadId || !newStatus) return;
    const oldStatus = selectEl ? (selectEl.getAttribute('data-current') || '') : '';
    if (selectEl) selectEl.disabled = true;

    if (remarks === null) {
        const ri = document.getElementById('accRemarksInput-' + leadId);
        remarks = ri ? ri.value.trim() : '';
    }

    fetch(UPD_STATUS_ROUTE_ACC, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
        body: JSON.stringify({ assign_base_id: leadId, status: newStatus, account_status: newStatus, account_remarks: remarks })
    })
    .then(r => r.json())
    .then(res => {
        if (selectEl) selectEl.disabled = false;
        if (res.status) {
            showAccToast('Status Updated', 'Lead #' + leadId + ' → ' + newStatus, true);

            const slug = newStatus.toLowerCase().replace(/\s+/g, '-');

            // Update the modal badge
            const mb = document.getElementById('acc-modal-badge-' + leadId);
            if (mb) { mb.className = 'status-badge status-' + slug; mb.textContent = newStatus; }
            if (selectEl) { selectEl.className = 'account-status-select status-' + slug; selectEl.setAttribute('data-current', newStatus); }

            // Update the table row badge
            const tb = document.getElementById('acc-table-status-' + leadId);
            if (tb) { tb.className = 'acc-status-badge ' + slug; tb.textContent = newStatus; }
        } else {
            throw new Error(res.message || 'Failed to update');
        }
    })
    .catch(err => {
        if (selectEl) { selectEl.disabled = false; if (oldStatus) selectEl.value = oldStatus; }
        showAccToast('Update Failed', err.message || err, false);
    });
}

function accSaveRemarks(leadId) {
    const sel    = document.getElementById('accStatusSelect-' + leadId);
    const status = sel ? sel.value : 'NEW';
    const ri     = document.getElementById('accRemarksInput-' + leadId);
    const remarks = ri ? ri.value.trim() : '';
    accChangeStatus(leadId, status, sel, remarks);
}

function showAccToast(title, message, isSuccess = true) {
    const container = document.getElementById('accToastContainer');
    if (!container) return;
    const toast = document.createElement('div');
    toast.style.cssText = `background:${isSuccess?'linear-gradient(135deg,#059669,#047857)':'linear-gradient(135deg,#ef4444,#dc2626)'};color:#fff;padding:12px 18px;border-radius:14px;box-shadow:0 10px 25px rgba(0,0,0,0.18);display:flex;align-items:center;gap:12px;font-size:13.5px;font-weight:600;min-width:280px;z-index:100000;`;
    toast.innerHTML = `<i class="fas ${isSuccess?'fa-check-circle':'fa-exclamation-circle'}" style="font-size:20px;"></i><div><div style="font-weight:800;">${escHtml(title)}</div><div style="font-size:12px;opacity:0.92;margin-top:1px;">${escHtml(message)}</div></div>`;
    container.appendChild(toast);
    setTimeout(() => { toast.style.transition = 'opacity 0.3s,transform 0.3s'; toast.style.opacity = '0'; toast.style.transform = 'translateY(-6px)'; setTimeout(() => toast.remove(), 300); }, 3200);
}
</script>

@endsection
