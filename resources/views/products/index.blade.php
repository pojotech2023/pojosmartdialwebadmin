@extends('layouts.app')

@section('page-title', 'Product Types')

@section('content')
<div style="max-width: 1200px; margin: 0 auto;">

    {{-- Top Header --}}
    <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px; margin-bottom: 20px;">
        <div>
            <div class="page-title"><i class="fas fa-boxes-stacked" style="color:var(--primary);margin-right:8px;"></i> Product Types</div>
            <div class="page-subtitle">Manage products that appear in the mobile app dropdown and customer assign base</div>
        </div>
        <button type="button" class="btn-upload" onclick="openAddModal()" style="display:inline-flex;align-items:center;gap:8px;">
            <i class="fas fa-plus"></i> Add New Product
        </button>
    </div>

    @if(session('success'))
        <div class="success-msg" style="margin-bottom: 20px;">{{ session('success') }}</div>
    @endif

    @if($errors->any())
        <div style="background:#fee2e2;color:#991b1b;padding:14px 18px;border-radius:12px;margin-bottom:20px;font-size:13.5px;">
            <ul style="margin:0;padding-left:18px;">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

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
                                onclick="openEditModal({{ $product->id }}, '{{ addslashes($product->name) }}', '{{ addslashes($product->description ?? '') }}', {{ $product->is_active ? 1 : 0 }})"
                                title="Edit Product">
                                <i class="fas fa-edit"></i>
                            </button>

                            <form method="POST" action="{{ route('products.destroy', $product->id) }}" onsubmit="return confirm('Are you sure you want to delete this product?');" style="display:inline;">
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
                        <p style="margin:0;font-weight:600;">No products created yet.</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

</div>

{{-- ============ ADD PRODUCT MODAL ============ --}}
<div id="addModal" class="product-modal-overlay" onclick="if(event.target.id === 'addModal') closeAddModal()">
    <div class="product-modal-box">
        <div class="product-modal-header">
            <div style="font-size:17px;font-weight:800;"><i class="fas fa-plus-circle" style="margin-right:8px;"></i> Add New Product</div>
            <button type="button" class="product-modal-close" onclick="closeAddModal()">&times;</button>
        </div>
        <form method="POST" action="{{ route('products.store') }}" style="padding:24px;">
            @csrf
            <div style="margin-bottom:16px;">
                <label class="form-label">Product Name *</label>
                <input type="text" name="name" class="cal-select" style="width:100%;" placeholder="e.g. Website, Ecommerce, CRM, Mobile App..." required>
            </div>

            <div style="margin-bottom:20px;">
                <label class="form-label">Description (Optional)</label>
                <textarea name="description" rows="3" class="cal-select" style="width:100%;height:auto;padding:10px;" placeholder="Brief description of the product..."></textarea>
            </div>

            <div style="display:flex;justify-content:flex-end;gap:10px;">
                <button type="button" class="btn-cancel-small" onclick="closeAddModal()">Cancel</button>
                <button type="submit" class="btn-upload"><i class="fas fa-save"></i> Save Product</button>
            </div>
        </form>
    </div>
</div>

{{-- ============ EDIT PRODUCT MODAL ============ --}}
<div id="editModal" class="product-modal-overlay" onclick="if(event.target.id === 'editModal') closeEditModal()">
    <div class="product-modal-box">
        <div class="product-modal-header">
            <div style="font-size:17px;font-weight:800;"><i class="fas fa-edit" style="margin-right:8px;"></i> Edit Product</div>
            <button type="button" class="product-modal-close" onclick="closeEditModal()">&times;</button>
        </div>
        <form id="editForm" method="POST" action="" style="padding:24px;">
            @csrf
            @method('PUT')
            <div style="margin-bottom:16px;">
                <label class="form-label">Product Name *</label>
                <input type="text" id="editName" name="name" class="cal-select" style="width:100%;" required>
            </div>

            <div style="margin-bottom:16px;">
                <label class="form-label">Description</label>
                <textarea id="editDesc" name="description" rows="3" class="cal-select" style="width:100%;height:auto;padding:10px;"></textarea>
            </div>

            <div style="margin-bottom:20px;">
                <label style="display:flex;align-items:center;gap:8px;font-size:13px;font-weight:600;cursor:pointer;">
                    <input type="checkbox" id="editIsActive" name="is_active" value="1">
                    <span>Active (visible in mobile app dropdown)</span>
                </label>
            </div>

            <div style="display:flex;justify-content:flex-end;gap:10px;">
                <button type="button" class="btn-cancel-small" onclick="closeEditModal()">Cancel</button>
                <button type="submit" class="btn-upload"><i class="fas fa-save"></i> Update Product</button>
            </div>
        </form>
    </div>
</div>

<style>
.product-modal-overlay {
    display: none;
    position: fixed; inset: 0; z-index: 99999;
    background: rgba(15,23,42,0.55);
    backdrop-filter: blur(4px);
    align-items: center; justify-content: center;
    padding: 20px;
}
.product-modal-box {
    background: #ffffff;
    border-radius: 18px;
    width: 100%; max-width: 520px;
    box-shadow: 0 20px 45px rgba(0,0,0,0.2);
    overflow: hidden;
    animation: slideUp 0.25s ease;
}
.product-modal-header {
    background: linear-gradient(135deg, #1e1b4b 0%, #312e81 100%);
    color: #ffffff;
    padding: 18px 24px;
    display: flex; justify-content: space-between; align-items: center;
}
.product-modal-close {
    background: transparent; border: none; color: #fff; font-size: 22px; cursor: pointer;
}
</style>

<script>
function openAddModal() {
    document.getElementById('addModal').style.display = 'flex';
}
function closeAddModal() {
    document.getElementById('addModal').style.display = 'none';
}
function openEditModal(id, name, desc, isActive) {
    document.getElementById('editForm').action = '/products/' + id;
    document.getElementById('editName').value = name;
    document.getElementById('editDesc').value = desc;
    document.getElementById('editIsActive').checked = (isActive == 1);
    document.getElementById('editModal').style.display = 'flex';
}
function closeEditModal() {
    document.getElementById('editModal').style.display = 'none';
}
</script>
@endsection
