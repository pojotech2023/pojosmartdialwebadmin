@extends('layouts.app')

@section('page-title', 'Settings')

@section('content')
<div style="max-width: 1200px; margin: 0 auto;">

    {{-- Top Header --}}
    <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px; margin-bottom: 22px;">
        <div>
            <div class="page-title">
                <i class="fas fa-sliders-h" style="color:var(--primary);margin-right:8px;"></i> Settings
            </div>
            <div class="page-subtitle">Configure Product Types and Telecaller Statuses for mobile app & admin panel</div>
        </div>

        {{-- Nav Tabs --}}
        <div class="settings-tabs-nav">
            <button type="button" class="settings-tab-btn {{ $activeTab === 'products' ? 'active' : '' }}" onclick="switchTab('products')">
                <i class="fas fa-boxes-stacked"></i> Product Types
                <span class="tab-count-pill">{{ $products->count() }}</span>
            </button>
            <button type="button" class="settings-tab-btn {{ $activeTab === 'statuses' ? 'active' : '' }}" onclick="switchTab('statuses')">
                <i class="fas fa-headset"></i> Telecaller Status
                <span class="tab-count-pill">{{ $statuses->count() }}</span>
            </button>
        </div>
    </div>

    @if(session('success'))
        <div class="success-msg" style="margin-bottom: 20px;">
            <i class="fas fa-check-circle" style="margin-right:6px;"></i> {{ session('success') }}
        </div>
    @endif

    @if($errors->any())
        <div style="background:#fee2e2;color:#991b1b;padding:14px 18px;border-radius:12px;margin-bottom:20px;font-size:13.5px;font-weight:600;">
            <ul style="margin:0;padding-left:18px;">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- ========================================================================= --}}
    {{-- TAB 1: PRODUCT TYPES --}}
    {{-- ========================================================================= --}}
    <div id="tabContentProducts" style="{{ $activeTab === 'products' ? '' : 'display:none;' }}">
        {{-- Section Header --}}
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:18px;flex-wrap:wrap;gap:12px;">
            <div>
                <h3 style="margin:0;font-size:17px;font-weight:800;color:#1e293b;">Product Types</h3>
                <p style="margin:3px 0 0;font-size:12.5px;color:#64748b;">Categories used when assigning customer leads and logging interactions</p>
            </div>
            <button type="button" class="btn-upload" onclick="openAddProductModal()" style="display:inline-flex;align-items:center;gap:8px;">
                <i class="fas fa-plus"></i> Add New Product
            </button>
        </div>

        {{-- Stats Row --}}
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 16px; margin-bottom: 20px;">
            <div class="card-box" style="display:flex;align-items:center;gap:15px;padding:18px 22px;margin:0;">
                <div style="width:46px;height:46px;border-radius:12px;background:linear-gradient(135deg,#6366f1,#4f46e5);color:#fff;display:flex;align-items:center;justify-content:center;font-size:20px;">
                    <i class="fas fa-cubes"></i>
                </div>
                <div>
                    <div style="font-size:24px;font-weight:800;color:#0f172a;">{{ $products->count() }}</div>
                    <div style="font-size:12px;font-weight:600;color:#64748b;">Total Products</div>
                </div>
            </div>

            <div class="card-box" style="display:flex;align-items:center;gap:15px;padding:18px 22px;margin:0;">
                <div style="width:46px;height:46px;border-radius:12px;background:linear-gradient(135deg,#10b981,#059669);color:#fff;display:flex;align-items:center;justify-content:center;font-size:20px;">
                    <i class="fas fa-check-circle"></i>
                </div>
                <div>
                    <div style="font-size:24px;font-weight:800;color:#0f172a;">{{ $products->where('is_active', true)->count() }}</div>
                    <div style="font-size:12px;font-weight:600;color:#64748b;">Active in Mobile App</div>
                </div>
            </div>

            <div class="card-box" style="display:flex;align-items:center;gap:15px;padding:18px 22px;margin:0;">
                <div style="width:46px;height:46px;border-radius:12px;background:linear-gradient(135deg,#f59e0b,#d97706);color:#fff;display:flex;align-items:center;justify-content:center;font-size:20px;">
                    <i class="fas fa-database"></i>
                </div>
                <div>
                    <div style="font-size:24px;font-weight:800;color:#0f172a;">{{ $products->sum('assign_bases_count') }}</div>
                    <div style="font-size:12px;font-weight:600;color:#64748b;">Customers Assigned</div>
                </div>
            </div>
        </div>

        {{-- Products Table --}}
        <div class="card-box">
            <table>
                <thead>
                    <tr>
                        <th style="width:60px;">#</th>
                        <th>Product Name</th>
                        <th>Description</th>
                        <th style="text-align:center;">Assigned Customers</th>
                        <th style="text-align:center;">Mobile Status</th>
                        <th style="text-align:center;width:120px;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($products as $product)
                    <tr>
                        <td style="font-weight:700;color:#64748b;">{{ $product->id }}</td>
                        <td>
                            <strong style="color:#0f172a;font-size:14.5px;">{{ $product->name }}</strong>
                        </td>
                        <td style="color:#64748b;font-size:13px;">
                            {{ $product->description ?: '—' }}
                        </td>
                        <td style="text-align:center;">
                            <span style="background:#eef2ff;color:#4338ca;padding:4px 10px;border-radius:20px;font-weight:700;font-size:12px;">
                                <i class="fas fa-user-tag" style="margin-right:4px;"></i> {{ $product->assign_bases_count }} customers
                            </span>
                        </td>
                        <td style="text-align:center;">
                            @if($product->is_active)
                                <span class="status-badge status-confirmed">Active</span>
                            @else
                                <span class="status-badge status-nope">Inactive</span>
                            @endif
                        </td>
                        <td style="text-align:center;">
                            <div style="display:flex;justify-content:center;gap:6px;">
                                <button type="button" class="icon-btn" style="background:#e0e7ff;color:#4338ca;"
                                    onclick="openEditProductModal({{ $product->id }}, '{{ addslashes($product->name) }}', '{{ addslashes($product->description ?? '') }}', {{ $product->is_active ? 1 : 0 }})"
                                    title="Edit Product">
                                    <i class="fas fa-edit"></i>
                                </button>

                                <form method="POST" action="{{ route('settings.products.destroy', $product->id) }}" onsubmit="return confirm('Are you sure you want to delete this product?');" style="display:inline;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="icon-btn icon-btn-delete" title="Delete Product">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" style="text-align:center;padding:40px;color:#94a3b8;">
                            <i class="fas fa-box-open" style="font-size:36px;margin-bottom:10px;color:#cbd5e1;"></i>
                            <p style="margin:0;font-weight:600;">No product types created yet.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- ========================================================================= --}}
    {{-- TAB 2: TELECALLER STATUS --}}
    {{-- ========================================================================= --}}
    <div id="tabContentStatuses" style="{{ $activeTab === 'statuses' ? '' : 'display:none;' }}">
        {{-- Section Header --}}
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:18px;flex-wrap:wrap;gap:12px;">
            <div>
                <h3 style="margin:0;font-size:17px;font-weight:800;color:#1e293b;">Telecaller Statuses</h3>
                <p style="margin:3px 0 0;font-size:12.5px;color:#64748b;">Statuses telecallers can choose when adding customer remarks and scheduling appointments</p>
            </div>
            <button type="button" class="btn-upload" onclick="openAddStatusModal()" style="display:inline-flex;align-items:center;gap:8px;">
                <i class="fas fa-plus"></i> Add New Status
            </button>
        </div>

        {{-- Stats Row --}}
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 16px; margin-bottom: 20px;">
            <div class="card-box" style="display:flex;align-items:center;gap:15px;padding:18px 22px;margin:0;">
                <div style="width:46px;height:46px;border-radius:12px;background:linear-gradient(135deg,#3b82f6,#1d4ed8);color:#fff;display:flex;align-items:center;justify-content:center;font-size:20px;">
                    <i class="fas fa-tags"></i>
                </div>
                <div>
                    <div style="font-size:24px;font-weight:800;color:#0f172a;">{{ $statuses->count() }}</div>
                    <div style="font-size:12px;font-weight:600;color:#64748b;">Total Statuses</div>
                </div>
            </div>

            <div class="card-box" style="display:flex;align-items:center;gap:15px;padding:18px 22px;margin:0;">
                <div style="width:46px;height:46px;border-radius:12px;background:linear-gradient(135deg,#10b981,#059669);color:#fff;display:flex;align-items:center;justify-content:center;font-size:20px;">
                    <i class="fas fa-check-double"></i>
                </div>
                <div>
                    <div style="font-size:24px;font-weight:800;color:#0f172a;">{{ $statuses->where('is_active', true)->count() }}</div>
                    <div style="font-size:12px;font-weight:600;color:#64748b;">Active in App</div>
                </div>
            </div>

            <div class="card-box" style="display:flex;align-items:center;gap:15px;padding:18px 22px;margin:0;">
                <div style="width:46px;height:46px;border-radius:12px;background:linear-gradient(135deg,#8b5cf6,#6d28d9);color:#fff;display:flex;align-items:center;justify-content:center;font-size:20px;">
                    <i class="fas fa-calendar-check"></i>
                </div>
                <div>
                    <div style="font-size:24px;font-weight:800;color:#0f172a;">{{ $statuses->where('requires_appointment', true)->count() }}</div>
                    <div style="font-size:12px;font-weight:600;color:#64748b;">Requires Appointment</div>
                </div>
            </div>
        </div>

        {{-- Statuses Table --}}
        <div class="card-box">
            <table>
                <thead>
                    <tr>
                        <th style="width:60px;">#</th>
                        <th>Status Name</th>
                        <th>Description</th>
                        <th style="text-align:center;">Appointment Needed?</th>
                        <th style="text-align:center;">Location Needed?</th>
                        <th style="text-align:center;">Status</th>
                        <th style="text-align:center;width:120px;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($statuses as $st)
                    <tr>
                        <td style="font-weight:700;color:#64748b;">{{ $st->id }}</td>
                        <td>
                            <div style="display:flex;align-items:center;gap:8px;">
                                <span style="display:inline-block;width:12px;height:12px;border-radius:50%;background:{{ $st->color ?: '#6366f1' }};"></span>
                                <span style="font-weight:700;color:#0f172a;font-size:14px;background:{{ $st->color ? $st->color.'18' : '#eef2ff' }};color:{{ $st->color ?: '#4338ca' }};padding:3px 10px;border-radius:14px;border:1px solid {{ $st->color ? $st->color.'40' : '#c7d2fe' }};">
                                    {{ $st->name }}
                                </span>
                            </div>
                        </td>
                        <td style="color:#64748b;font-size:13px;">
                            {{ $st->description ?: '—' }}
                        </td>
                        <td style="text-align:center;">
                            @if($st->requires_appointment)
                                <span style="background:#fef3c7;color:#92400e;padding:3px 8px;border-radius:12px;font-size:11.5px;font-weight:700;">
                                    <i class="fas fa-calendar-alt" style="margin-right:3px;"></i> Yes (Date & Time)
                                </span>
                            @else
                                <span style="color:#94a3b8;font-size:12px;">No</span>
                            @endif
                        </td>
                        <td style="text-align:center;">
                            @if($st->requires_location)
                                <span style="background:#e0e7ff;color:#3730a3;padding:3px 8px;border-radius:12px;font-size:11.5px;font-weight:700;">
                                    <i class="fas fa-map-marker-alt" style="margin-right:3px;"></i> Yes (Location)
                                </span>
                            @else
                                <span style="color:#94a3b8;font-size:12px;">No</span>
                            @endif
                        </td>
                        <td style="text-align:center;">
                            @if($st->is_active)
                                <span class="status-badge status-confirmed">Active</span>
                            @else
                                <span class="status-badge status-nope">Inactive</span>
                            @endif
                        </td>
                        <td style="text-align:center;">
                            <div style="display:flex;justify-content:center;gap:6px;">
                                <button type="button" class="icon-btn" style="background:#e0e7ff;color:#4338ca;"
                                    onclick="openEditStatusModal({{ $st->id }}, '{{ addslashes($st->name) }}', '{{ $st->color }}', '{{ addslashes($st->description ?? '') }}', {{ $st->requires_appointment ? 1 : 0 }}, {{ $st->requires_location ? 1 : 0 }}, {{ $st->is_active ? 1 : 0 }}, {{ $st->sort_order }})"
                                    title="Edit Status">
                                    <i class="fas fa-edit"></i>
                                </button>

                                <form method="POST" action="{{ route('settings.statuses.destroy', $st->id) }}" onsubmit="return confirm('Are you sure you want to delete status \'{{ $st->name }}\'?');" style="display:inline;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="icon-btn icon-btn-delete" title="Delete Status">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" style="text-align:center;padding:40px;color:#94a3b8;">
                            <i class="fas fa-tags" style="font-size:36px;margin-bottom:10px;color:#cbd5e1;"></i>
                            <p style="margin:0;font-weight:600;">No telecaller statuses configured yet.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>

{{-- ========================================================================= --}}
{{-- MODALS: PRODUCTS --}}
{{-- ========================================================================= --}}

{{-- Add Product Modal --}}
<div id="addProductModal" class="custom-modal-overlay" onclick="if(event.target.id === 'addProductModal') closeAddProductModal()">
    <div class="custom-modal-box">
        <div class="custom-modal-header">
            <div style="font-size:16.5px;font-weight:800;"><i class="fas fa-plus-circle" style="margin-right:8px;"></i> Add New Product Type</div>
            <button type="button" class="custom-modal-close" onclick="closeAddProductModal()">&times;</button>
        </div>
        <form method="POST" action="{{ route('settings.products.store') }}" style="padding:22px;">
            @csrf
            <div style="margin-bottom:16px;">
                <label class="form-label">Product Name *</label>
                <input type="text" name="name" class="cal-select" style="width:100%;" placeholder="e.g. Website, Ecommerce, CRM, Mobile App..." required>
            </div>

            <div style="margin-bottom:16px;">
                <label class="form-label">Description (Optional)</label>
                <textarea name="description" rows="3" class="cal-select" style="width:100%;height:auto;padding:10px;" placeholder="Brief description of the product..."></textarea>
            </div>

            <div style="margin-bottom:20px;">
                <label style="display:flex;align-items:center;gap:8px;font-size:13px;font-weight:600;cursor:pointer;">
                    <input type="checkbox" name="is_active" value="1" checked>
                    <span>Active in Mobile App Dropdowns</span>
                </label>
            </div>

            <div style="display:flex;justify-content:flex-end;gap:10px;">
                <button type="button" class="btn-cancel-small" onclick="closeAddProductModal()">Cancel</button>
                <button type="submit" class="btn-upload"><i class="fas fa-save"></i> Save Product</button>
            </div>
        </form>
    </div>
</div>

{{-- Edit Product Modal --}}
<div id="editProductModal" class="custom-modal-overlay" onclick="if(event.target.id === 'editProductModal') closeEditProductModal()">
    <div class="custom-modal-box">
        <div class="custom-modal-header">
            <div style="font-size:16.5px;font-weight:800;"><i class="fas fa-edit" style="margin-right:8px;"></i> Edit Product Type</div>
            <button type="button" class="custom-modal-close" onclick="closeEditProductModal()">&times;</button>
        </div>
        <form id="editProductForm" method="POST" action="" style="padding:22px;">
            @csrf
            @method('PUT')
            <div style="margin-bottom:16px;">
                <label class="form-label">Product Name *</label>
                <input type="text" id="editProductName" name="name" class="cal-select" style="width:100%;" required>
            </div>

            <div style="margin-bottom:16px;">
                <label class="form-label">Description</label>
                <textarea id="editProductDesc" name="description" rows="3" class="cal-select" style="width:100%;height:auto;padding:10px;"></textarea>
            </div>

            <div style="margin-bottom:20px;">
                <label style="display:flex;align-items:center;gap:8px;font-size:13px;font-weight:600;cursor:pointer;">
                    <input type="checkbox" id="editProductIsActive" name="is_active" value="1">
                    <span>Active in Mobile App Dropdowns</span>
                </label>
            </div>

            <div style="display:flex;justify-content:flex-end;gap:10px;">
                <button type="button" class="btn-cancel-small" onclick="closeEditProductModal()">Cancel</button>
                <button type="submit" class="btn-upload"><i class="fas fa-save"></i> Update Product</button>
            </div>
        </form>
    </div>
</div>

{{-- ========================================================================= --}}
{{-- MODALS: TELECALLER STATUS --}}
{{-- ========================================================================= --}}

{{-- Add Status Modal --}}
<div id="addStatusModal" class="custom-modal-overlay" onclick="if(event.target.id === 'addStatusModal') closeAddStatusModal()">
    <div class="custom-modal-box">
        <div class="custom-modal-header">
            <div style="font-size:16.5px;font-weight:800;"><i class="fas fa-plus-circle" style="margin-right:8px;"></i> Add Telecaller Status</div>
            <button type="button" class="custom-modal-close" onclick="closeAddStatusModal()">&times;</button>
        </div>
        <form method="POST" action="{{ route('settings.statuses.store') }}" style="padding:22px;">
            @csrf
            <div style="margin-bottom:16px;">
                <label class="form-label">Status Name *</label>
                <input type="text" name="name" class="cal-select" style="width:100%;" placeholder="e.g. Quotation Shared, Appointment-Online, Interested..." required>
            </div>

            <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;margin-bottom:16px;">
                <div>
                    <label class="form-label">Color / Badge</label>
                    <div style="display:flex;align-items:center;gap:8px;">
                        <input type="color" name="color" id="addColorPicker" value="#2563eb" style="width:42px;height:38px;padding:2px;border:1px solid #cbd5e1;border-radius:8px;cursor:pointer;">
                        <input type="text" id="addColorText" class="cal-select" value="#2563eb" style="width:100%;" oninput="document.getElementById('addColorPicker').value = this.value">
                    </div>
                </div>
                <div>
                    <label class="form-label">Sort Order</label>
                    <input type="number" name="sort_order" class="cal-select" style="width:100%;" value="0" min="0">
                </div>
            </div>

            <div style="margin-bottom:16px;">
                <label class="form-label">Description (Optional)</label>
                <textarea name="description" rows="2" class="cal-select" style="width:100%;height:auto;padding:10px;" placeholder="Details about when this status should be picked..."></textarea>
            </div>

            <div style="background:#f8fafc;padding:14px;border-radius:10px;border:1px solid #e2e8f0;margin-bottom:20px;display:flex;flex-direction:column;gap:10px;">
                <label style="display:flex;align-items:center;gap:8px;font-size:13px;font-weight:600;cursor:pointer;color:#1e293b;">
                    <input type="checkbox" name="requires_appointment" value="1">
                    <span><i class="fas fa-calendar-alt" style="color:#f59e0b;margin-right:4px;"></i> Requires Appointment (Date & Time)</span>
                </label>

                <label style="display:flex;align-items:center;gap:8px;font-size:13px;font-weight:600;cursor:pointer;color:#1e293b;">
                    <input type="checkbox" name="requires_location" value="1">
                    <span><i class="fas fa-map-marker-alt" style="color:#6366f1;margin-right:4px;"></i> Requires Location / Meeting Link</span>
                </label>

                <label style="display:flex;align-items:center;gap:8px;font-size:13px;font-weight:600;cursor:pointer;color:#1e293b;">
                    <input type="checkbox" name="is_active" value="1" checked>
                    <span><i class="fas fa-check-circle" style="color:#10b981;margin-right:4px;"></i> Active in Mobile App Remarks Dropdown</span>
                </label>
            </div>

            <div style="display:flex;justify-content:flex-end;gap:10px;">
                <button type="button" class="btn-cancel-small" onclick="closeAddStatusModal()">Cancel</button>
                <button type="submit" class="btn-upload"><i class="fas fa-save"></i> Save Status</button>
            </div>
        </form>
    </div>
</div>

{{-- Edit Status Modal --}}
<div id="editStatusModal" class="custom-modal-overlay" onclick="if(event.target.id === 'editStatusModal') closeEditStatusModal()">
    <div class="custom-modal-box">
        <div class="custom-modal-header">
            <div style="font-size:16.5px;font-weight:800;"><i class="fas fa-edit" style="margin-right:8px;"></i> Edit Telecaller Status</div>
            <button type="button" class="custom-modal-close" onclick="closeEditStatusModal()">&times;</button>
        </div>
        <form id="editStatusForm" method="POST" action="" style="padding:22px;">
            @csrf
            @method('PUT')
            <div style="margin-bottom:16px;">
                <label class="form-label">Status Name *</label>
                <input type="text" id="editStatusName" name="name" class="cal-select" style="width:100%;" required>
            </div>

            <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;margin-bottom:16px;">
                <div>
                    <label class="form-label">Color / Badge</label>
                    <div style="display:flex;align-items:center;gap:8px;">
                        <input type="color" id="editStatusColorPicker" name="color" style="width:42px;height:38px;padding:2px;border:1px solid #cbd5e1;border-radius:8px;cursor:pointer;" onchange="document.getElementById('editStatusColorText').value = this.value">
                        <input type="text" id="editStatusColorText" class="cal-select" style="width:100%;" oninput="document.getElementById('editStatusColorPicker').value = this.value">
                    </div>
                </div>
                <div>
                    <label class="form-label">Sort Order</label>
                    <input type="number" id="editStatusSortOrder" name="sort_order" class="cal-select" style="width:100%;" min="0">
                </div>
            </div>

            <div style="margin-bottom:16px;">
                <label class="form-label">Description (Optional)</label>
                <textarea id="editStatusDesc" name="description" rows="2" class="cal-select" style="width:100%;height:auto;padding:10px;"></textarea>
            </div>

            <div style="background:#f8fafc;padding:14px;border-radius:10px;border:1px solid #e2e8f0;margin-bottom:20px;display:flex;flex-direction:column;gap:10px;">
                <label style="display:flex;align-items:center;gap:8px;font-size:13px;font-weight:600;cursor:pointer;color:#1e293b;">
                    <input type="checkbox" id="editStatusRequiresAppointment" name="requires_appointment" value="1">
                    <span><i class="fas fa-calendar-alt" style="color:#f59e0b;margin-right:4px;"></i> Requires Appointment (Date & Time)</span>
                </label>

                <label style="display:flex;align-items:center;gap:8px;font-size:13px;font-weight:600;cursor:pointer;color:#1e293b;">
                    <input type="checkbox" id="editStatusRequiresLocation" name="requires_location" value="1">
                    <span><i class="fas fa-map-marker-alt" style="color:#6366f1;margin-right:4px;"></i> Requires Location / Meeting Link</span>
                </label>

                <label style="display:flex;align-items:center;gap:8px;font-size:13px;font-weight:600;cursor:pointer;color:#1e293b;">
                    <input type="checkbox" id="editStatusIsActive" name="is_active" value="1">
                    <span><i class="fas fa-check-circle" style="color:#10b981;margin-right:4px;"></i> Active in Mobile App Remarks Dropdown</span>
                </label>
            </div>

            <div style="display:flex;justify-content:flex-end;gap:10px;">
                <button type="button" class="btn-cancel-small" onclick="closeEditStatusModal()">Cancel</button>
                <button type="submit" class="btn-upload"><i class="fas fa-save"></i> Update Status</button>
            </div>
        </form>
    </div>
</div>

<style>
/* Settings Navigation Tabs */
.settings-tabs-nav {
    display: flex;
    background: #e2e8f0;
    padding: 4px;
    border-radius: 12px;
    gap: 4px;
}
.settings-tab-btn {
    border: none;
    background: transparent;
    padding: 8px 16px;
    border-radius: 9px;
    font-size: 13.5px;
    font-weight: 700;
    color: #475569;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    transition: all 0.2s ease;
}
.settings-tab-btn:hover {
    color: #1e293b;
}
.settings-tab-btn.active {
    background: #ffffff;
    color: #4338ca;
    box-shadow: 0 2px 6px rgba(0,0,0,0.08);
}
.tab-count-pill {
    background: #f1f5f9;
    color: #64748b;
    padding: 1px 7px;
    border-radius: 10px;
    font-size: 11px;
    font-weight: 800;
}
.settings-tab-btn.active .tab-count-pill {
    background: #eef2ff;
    color: #4338ca;
}

/* Modals */
.custom-modal-overlay {
    display: none;
    position: fixed; inset: 0; z-index: 99999;
    background: rgba(15,23,42,0.55);
    backdrop-filter: blur(4px);
    align-items: center; justify-content: center;
    padding: 20px;
}
.custom-modal-box {
    background: #ffffff;
    border-radius: 18px;
    width: 100%; max-width: 520px;
    box-shadow: 0 20px 45px rgba(0,0,0,0.2);
    overflow: hidden;
    animation: modalSlideUp 0.25s ease;
}
@keyframes modalSlideUp {
    from { opacity: 0; transform: translateY(12px) scale(0.98); }
    to { opacity: 1; transform: translateY(0) scale(1); }
}
.custom-modal-header {
    background: linear-gradient(135deg, #1e1b4b 0%, #312e81 100%);
    color: #ffffff;
    padding: 18px 24px;
    display: flex; justify-content: space-between; align-items: center;
}
.custom-modal-close {
    background: transparent; border: none; color: #fff; font-size: 22px; cursor: pointer;
}
</style>

<script>
// Switch Tabs
function switchTab(tab) {
    document.querySelectorAll('.settings-tab-btn').forEach(b => b.classList.remove('active'));
    document.getElementById('tabContentProducts').style.display = 'none';
    document.getElementById('tabContentStatuses').style.display = 'none';

    if (tab === 'products') {
        event.currentTarget.classList.add('active');
        document.getElementById('tabContentProducts').style.display = 'block';
        history.replaceState(null, '', '?tab=products');
    } else {
        event.currentTarget.classList.add('active');
        document.getElementById('tabContentStatuses').style.display = 'block';
        history.replaceState(null, '', '?tab=statuses');
    }
}

// Product Modals
function openAddProductModal() {
    document.getElementById('addProductModal').style.display = 'flex';
}
function closeAddProductModal() {
    document.getElementById('addProductModal').style.display = 'none';
}
function openEditProductModal(id, name, desc, isActive) {
    document.getElementById('editProductForm').action = '/settings/products/' + id;
    document.getElementById('editProductName').value = name;
    document.getElementById('editProductDesc').value = desc;
    document.getElementById('editProductIsActive').checked = (isActive == 1);
    document.getElementById('editProductModal').style.display = 'flex';
}
function closeEditProductModal() {
    document.getElementById('editProductModal').style.display = 'none';
}

// Status Modals
function openAddStatusModal() {
    document.getElementById('addStatusModal').style.display = 'flex';
}
function closeAddStatusModal() {
    document.getElementById('addStatusModal').style.display = 'none';
}
function openEditStatusModal(id, name, color, desc, reqApp, reqLoc, isActive, sortOrder) {
    document.getElementById('editStatusForm').action = '/settings/statuses/' + id;
    document.getElementById('editStatusName').value = name;
    document.getElementById('editStatusColorPicker').value = color || '#2563eb';
    document.getElementById('editStatusColorText').value = color || '#2563eb';
    document.getElementById('editStatusDesc').value = desc;
    document.getElementById('editStatusRequiresAppointment').checked = (reqApp == 1);
    document.getElementById('editStatusRequiresLocation').checked = (reqLoc == 1);
    document.getElementById('editStatusIsActive').checked = (isActive == 1);
    document.getElementById('editStatusSortOrder').value = sortOrder || 0;
    document.getElementById('editStatusModal').style.display = 'flex';
}
function closeEditStatusModal() {
    document.getElementById('editStatusModal').style.display = 'none';
}

// Keep color picker in sync
document.getElementById('addColorPicker').addEventListener('input', function() {
    document.getElementById('addColorText').value = this.value;
});
</script>
@endsection
