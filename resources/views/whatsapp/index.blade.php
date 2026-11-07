@extends('layouts.app')

@section('page-title', 'WhatsApp Notification')

@section('content')

<div class="page-title"><i class="fab fa-whatsapp" style="color:#25d366;margin-right:6px;"></i>WhatsApp Notification</div>
<div class="page-subtitle">
    Upload contacts and schedule a WhatsApp template notification to all or selected contacts.
</div>

@if(session('success'))
    <div class="success-msg"><i class="fas fa-circle-check"></i> {{ session('success') }}</div>
@endif

{{-- UPLOAD EXCEL --}}
<div class="card-box">
    <div class="card-box-title"><i class="fas fa-file-excel" style="color:#10b981;"></i> Upload Contacts (Excel)</div>

    <form method="POST"
          action="{{ route('whatsapp.import') }}"
          enctype="multipart/form-data"
          style="display:flex;gap:12px;align-items:end;flex-wrap:wrap;">
        @csrf

        <div>
            <label class="form-label">Excel File (Column A: Mobile, Column B: Name)</label>
            <input type="file" name="file" accept=".xlsx,.xls" required>
        </div>

        <button type="submit" class="btn-upload"><i class="fas fa-upload"></i> Import Excel</button>
    </form>
</div>

{{-- SCHEDULE NOTIFICATION --}}
<div class="card-box">
    <div class="card-box-title"><i class="fas fa-paper-plane" style="color:#4f46e5;"></i> Schedule Notification</div>

    @if($errors->any())
        <div class="error-msg" style="color:#dc2626;margin-bottom:10px;">
            @foreach($errors->all() as $error)
                <div><i class="fas fa-circle-exclamation"></i> {{ $error }}</div>
            @endforeach
        </div>
    @endif

    {{-- <p style="color:#6b7280;font-size:12.5px;margin-bottom:10px;">
        <i class="fas fa-circle-info"></i>
        Using template <strong>{{ $templateName }}</strong> ({{ $templateLanguage }}). Customer name is filled in automatically per contact.
    </p> --}}

    <form method="POST" action="{{ route('whatsapp.store') }}" id="scheduleForm" enctype="multipart/form-data">
        @csrf

        <div style="display:flex;gap:15px;align-items:end;flex-wrap:wrap;">

            <div>
                <label class="form-label">Send To</label>
                <select name="target_type" id="target_type" onchange="onTargetTypeChange(this)">
                    <option value="all" selected>All Contacts</option>
                    <option value="selected">Selected Contacts</option>
                </select>
            </div>

            @foreach($paramLabels as $index => $label)
                <div>
                    <label class="form-label">{{ $label }}</label>
                    <input type="text" name="params[{{ $index }}]" placeholder="{{ $label }}" value="{{ old('params.'.$index) }}" required>
                </div>
            @endforeach

            <div>
                <label class="form-label">Date</label>
                <input type="date" name="scheduled_date" min="{{ now()->toDateString() }}" required>
            </div>

            <div>
                <label class="form-label">Time</label>
                <input type="time" name="scheduled_time" required>
            </div>

            <div>
                <label class="form-label">Header Image (optional)</label>
                <input type="file" name="header_image" accept=".jpg,.jpeg,.png">
            </div>

            <div>
                <button type="submit" class="btn-whatsapp"><i class="fas fa-paper-plane"></i> Schedule Notification</button>
            </div>
        </div>
    </form>
</div>

<script>
function onContactCheckChange(checkbox) {
    checkbox.closest('tr').classList.toggle('checked', checkbox.checked);
    syncSelectAllState();
}

function onSelectAllChange(masterCheckbox) {
    document.querySelectorAll('[data-contact-checkbox]').forEach(cb => {
        cb.checked = masterCheckbox.checked;
        cb.closest('tr').classList.toggle('checked', cb.checked);
    });
}

function syncSelectAllState() {
    const boxes = document.querySelectorAll('[data-contact-checkbox]');
    const master = document.getElementById('selectAllContacts');
    if (!master || boxes.length === 0) return;
    const checkedCount = document.querySelectorAll('[data-contact-checkbox]:checked').length;
    master.checked = checkedCount === boxes.length;
    master.indeterminate = checkedCount > 0 && checkedCount < boxes.length;
}

function onTargetTypeChange(select) {
    const master = document.getElementById('selectAllContacts');
    const isAll = select.value === 'all';

    document.querySelectorAll('[data-contact-checkbox]').forEach(cb => {
        cb.disabled = isAll;
        cb.checked = isAll ? true : false;
        cb.closest('tr').classList.toggle('checked', cb.checked);
    });

    if (master) {
        master.disabled = isAll;
        master.checked = isAll;
        master.indeterminate = false;
    }
}

document.addEventListener('DOMContentLoaded', function () {
    const targetType = document.getElementById('target_type');
    if (targetType) {
        onTargetTypeChange(targetType);
    }
});
</script>

{{-- CONTACTS ================= --}}
<div class="card-box" style="margin-top:20px;">
    <div class="card-box-title"><i class="fas fa-address-book" style="color:#4f46e5;"></i> Contacts ({{ $contacts->count() }})</div>

    @if($contacts->count() > 0)
        <table>
            <thead>
                <tr>
                    <th style="width:36px;">
                        <input type="checkbox" id="selectAllContacts" onchange="onSelectAllChange(this)" checked>
                    </th>
                    <th>Name</th>
                    <th>Mobile</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @foreach($contacts as $contact)
                    <tr class="checked">
                        <td>
                            <input type="checkbox" form="scheduleForm" name="contact_ids[]" value="{{ $contact->id }}"
                                   data-contact-checkbox onchange="onContactCheckChange(this)" checked>
                        </td>
                        <td>{{ $contact->name ?? '-' }}</td>
                        <td>{{ $contact->mobile }}</td>
                        <td>
                            <form method="POST" action="{{ route('whatsapp.contact.delete') }}"
                                  onsubmit="return confirm('Delete this contact?')" style="display:inline;">
                                @csrf
                                @method('DELETE')
                                <input type="hidden" name="id" value="{{ $contact->id }}">
                                <button type="submit" class="icon-btn icon-btn-delete" title="Delete">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <p style="color:#6b7280;font-size:13px;">No contacts uploaded yet.</p>
    @endif
</div>

{{-- NOTIFICATION HISTORY ================= --}}
<div class="card-box">
    <div class="card-box-title"><i class="fas fa-clock-rotate-left" style="color:#4f46e5;"></i> Scheduled / Sent Notifications</div>

    @if($notifications->count() > 0)
        <table>
            <thead>
                <tr>
                    <th>Template</th>
                    <th>Params</th>
                    <th>Image</th>
                    <th>Target</th>
                    <th>Recipients</th>
                    <th>Scheduled At</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @foreach($notifications as $notification)
                    <tr>
                        <td>{{ $notification->template_name }} ({{ $notification->template_language ?? '-' }})</td>
                        <td>{{ $notification->template_params ? implode(', ', $notification->template_params) : '-' }}</td>
                        <td>
                            @if($notification->header_image_path)
                                <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($notification->header_image_path) }}" alt="header" style="width:40px;height:40px;object-fit:cover;border-radius:4px;">
                            @else
                                -
                            @endif
                        </td>
                        <td>{{ ucfirst($notification->target_type) }}</td>
                        <td>{{ $notification->recipients_count }}</td>
                        <td>{{ \Carbon\Carbon::parse($notification->scheduled_at)->format('d/m/Y H:i') }}</td>
                        <td><span class="status-badge status-{{ $notification->status }}">{{ $notification->status }}</span></td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div style="margin-top:15px">
            {{ $notifications->onEachSide(1)->links('vendor.pagination.custom') }}
        </div>
    @else
        <p style="color:#6b7280;font-size:13px;">No notifications scheduled yet.</p>
    @endif
</div>

@endsection
