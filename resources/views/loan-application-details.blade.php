@extends('layouts.app')

@section('page-title', 'Project & Lead Details')

@section('content')
<div class="container" style="max-width: 900px; margin: 0 auto; padding: 20px 15px;">
    
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px;">
        <div>
            <div class="page-title" style="margin-bottom: 4px; font-size: 24px; font-weight: 800; color: #0f172a;">
                Lead #{{ $customer->id }} &bull; {{ $customer->customer_name }}
            </div>
            <div style="font-size: 13.5px; color: #64748b; font-weight: 600;">
                <i class="fas fa-phone-alt" style="color: #059669; margin-right: 4px;"></i> {{ $customer->mobile }}
                &bull; {{ $customer->company_name ?? '—' }}
            </div>
        </div>
        <a href="{{ route('dashboard') }}" class="btn-cancel-small" style="background:#f1f5f9; color:#334155; padding:8px 16px; border-radius:10px; text-decoration:none; font-weight:700; font-size:13px; display:inline-flex; align-items:center; gap:6px;">
            <i class="fas fa-arrow-left"></i> Back to Dashboard
        </a>
    </div>

    @php
        $p = $leadDetail ?? null;
        $isGstYes = ($p && ($p->gst === 'Y' || $p->gst === 'YES'));
    @endphp

    <div style="display: flex; flex-direction: column; gap: 20px;">

        {{-- Mobile App Styled Project Details Card --}}
        <div style="background: #ffffff; border-radius: 24px; padding: 28px 24px; box-shadow: 0 10px 30px rgba(0,0,0,0.06); border: 1px solid #f1f5f9; max-width: 540px; margin: 0 auto; width: 100%;">
            
            {{-- Card Title with Coral Icon --}}
            <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 22px;">
                <div style="width: 38px; height: 38px; border-radius: 12px; background: #fee2e2; color: #ef4444; display: flex; align-items: center; justify-content: center; font-size: 17px;">
                    <i class="far fa-file-alt"></i>
                </div>
                <div style="font-size: 20px; font-weight: 800; color: #1e293b;">Project Details</div>
            </div>

            {{-- 1. Project Type --}}
            <div style="position: relative; margin-bottom: 15px;">
                <label style="position: absolute; top: -8px; left: 14px; background: #fff; padding: 0 6px; font-size: 11px; font-weight: 700; color: #64748b; text-transform: capitalize;">Project Type</label>
                <div style="display: flex; align-items: center; gap: 12px; border: 1.5px solid #e2e8f0; border-radius: 14px; padding: 13px 16px; background: #fff;">
                    <i class="fas fa-shapes" style="color: #ef4444; font-size: 15px; width: 18px; text-align: center;"></i>
                    <span style="font-size: 14.5px; font-weight: 700; color: #0f172a;">{{ $p->project_type ?? $customer->product_type ?? '—' }}</span>
                </div>
            </div>

            {{-- 2. Customer Name --}}
            <div style="position: relative; margin-bottom: 15px;">
                <label style="position: absolute; top: -8px; left: 14px; background: #fff; padding: 0 6px; font-size: 11px; font-weight: 700; color: #64748b; text-transform: capitalize;">Customer Name</label>
                <div style="display: flex; align-items: center; gap: 12px; border: 1.5px solid #e2e8f0; border-radius: 14px; padding: 13px 16px; background: #fff;">
                    <i class="far fa-user" style="color: #ef4444; font-size: 15px; width: 18px; text-align: center;"></i>
                    <span style="font-size: 14.5px; font-weight: 700; color: #0f172a;">{{ $customer->customer_name }}</span>
                </div>
            </div>

            {{-- 3. Company Name --}}
            <div style="position: relative; margin-bottom: 15px;">
                <label style="position: absolute; top: -8px; left: 14px; background: #fff; padding: 0 6px; font-size: 11px; font-weight: 700; color: #64748b; text-transform: capitalize;">Company Name</label>
                <div style="display: flex; align-items: center; gap: 12px; border: 1.5px solid #e2e8f0; border-radius: 14px; padding: 13px 16px; background: #fff;">
                    <i class="far fa-building" style="color: #ef4444; font-size: 15px; width: 18px; text-align: center;"></i>
                    <span style="font-size: 14.5px; font-weight: 700; color: #0f172a;">{{ $p->cus_company_name ?? $customer->company_name ?? '—' }}</span>
                </div>
            </div>

            {{-- 4. Customer Mail --}}
            <div style="position: relative; margin-bottom: 15px;">
                <label style="position: absolute; top: -8px; left: 14px; background: #fff; padding: 0 6px; font-size: 11px; font-weight: 700; color: #64748b; text-transform: capitalize;">Customer Mail</label>
                <div style="display: flex; align-items: center; gap: 12px; border: 1.5px solid #e2e8f0; border-radius: 14px; padding: 13px 16px; background: #fff;">
                    <i class="far fa-envelope" style="color: #ef4444; font-size: 15px; width: 18px; text-align: center;"></i>
                    <span style="font-size: 14.5px; font-weight: 600; color: {{ ($p && $p->cus_mail) ? '#2563eb' : '#94a3b8' }};">
                        @if($p && $p->cus_mail)
                            <a href="mailto:{{ $p->cus_mail }}" style="color:#2563eb;text-decoration:none;">{{ $p->cus_mail }}</a>
                        @else
                            Customer Mail (Not provided)
                        @endif
                    </span>
                </div>
            </div>

            {{-- 5. Price --}}
            <div style="position: relative; margin-bottom: 16px;">
                <label style="position: absolute; top: -8px; left: 14px; background: #fff; padding: 0 6px; font-size: 11px; font-weight: 700; color: #64748b; text-transform: capitalize;">Price</label>
                <div style="display: flex; align-items: center; gap: 12px; border: 1.5px solid #e2e8f0; border-radius: 14px; padding: 13px 16px; background: #fff;">
                    <span style="color: #ef4444; font-size: 17px; font-weight: 800; width: 18px; text-align: center;">₹</span>
                    <span style="font-size: 16px; font-weight: 800; color: {{ ($p && $p->price) ? '#15803d' : '#94a3b8' }};">
                        @if($p && $p->price)
                            ₹ {{ number_format((float) preg_replace('/[^\d.]/', '', $p->price)) }}
                        @else
                            Price (Not provided)
                        @endif
                    </span>
                </div>
            </div>

            {{-- 6. GST Radio Selection --}}
            <div style="margin-bottom: 15px;">
                <label style="font-size: 12.5px; font-weight: 800; color: #0f172a; display: block; margin-bottom: 8px;">GST</label>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                    {{-- Yes Radio --}}
                    <div style="border: 1.5px solid {{ $isGstYes ? '#fca5a5' : '#e2e8f0' }}; background: {{ $isGstYes ? '#fef2f2' : '#fff' }}; border-radius: 14px; padding: 12px 16px; display: flex; align-items: center; gap: 10px;">
                        <span style="width: 18px; height: 18px; border-radius: 50%; border: 2px solid {{ $isGstYes ? '#ef4444' : '#cbd5e1' }}; display: flex; align-items: center; justify-content: center;">
                            @if($isGstYes)
                                <span style="width: 10px; height: 10px; border-radius: 50%; background: #ef4444;"></span>
                            @endif
                        </span>
                        <span style="font-size: 14px; font-weight: 700; color: {{ $isGstYes ? '#b91c1c' : '#64748b' }};">Yes</span>
                    </div>

                    {{-- No Radio --}}
                    <div style="border: 1.5px solid {{ !$isGstYes ? '#fca5a5' : '#e2e8f0' }}; background: {{ !$isGstYes ? '#fef2f2' : '#fff' }}; border-radius: 14px; padding: 12px 16px; display: flex; align-items: center; gap: 10px;">
                        <span style="width: 18px; height: 18px; border-radius: 50%; border: 2px solid {{ !$isGstYes ? '#ef4444' : '#cbd5e1' }}; display: flex; align-items: center; justify-content: center;">
                            @if(!$isGstYes)
                                <span style="width: 10px; height: 10px; border-radius: 50%; background: #ef4444;"></span>
                            @endif
                        </span>
                        <span style="font-size: 14px; font-weight: 700; color: {{ !$isGstYes ? '#b91c1c' : '#64748b' }};">No</span>
                    </div>
                </div>
            </div>

            {{-- 7. GST No --}}
            <div style="position: relative; margin-bottom: 22px;">
                <label style="position: absolute; top: -8px; left: 14px; background: #fff; padding: 0 6px; font-size: 11px; font-weight: 700; color: #64748b; text-transform: capitalize;">GST No</label>
                <div style="display: flex; align-items: center; gap: 12px; border: 1.5px solid #e2e8f0; border-radius: 14px; padding: 13px 16px; background: #fff;">
                    <i class="far fa-file-alt" style="color: #ef4444; font-size: 15px; width: 18px; text-align: center;"></i>
                    <span style="font-size: 14px; font-weight: 700; color: {{ ($p && $p->gst_no) ? '#0f172a' : '#94a3b8' }}; font-family: {{ ($p && $p->gst_no) ? 'monospace' : 'inherit' }};">
                        {{ $p->gst_no ?? 'GST No (Not provided)' }}
                    </span>
                </div>
            </div>

            {{-- Mobile App Data Badge --}}
            <div style="background: linear-gradient(135deg, #f87171, #ef4444); color: #fff; border-radius: 16px; padding: 14px; text-align: center; font-size: 14px; font-weight: 800; box-shadow: 0 6px 18px rgba(239,68,68,0.25); display: flex; align-items: center; justify-content: center; gap: 8px;">
                <i class="fas fa-check-circle"></i> Mobile App Submitted Data
            </div>

        </div>

        {{-- Telecaller Assignment & Status Card --}}
        <div style="background: #ffffff; border-radius: 20px; padding: 20px 22px; border: 1px solid #f1f5f9; box-shadow: 0 4px 16px rgba(0,0,0,0.03); max-width: 540px; margin: 0 auto; width: 100%;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 14px;">
                <div style="font-size: 12.5px; font-weight: 800; color: #64748b; text-transform: uppercase;">Lead Assignment & Status</div>
                <span class="status-badge status-{{ \Illuminate\Support\Str::slug($customer->status) }}">{{ $customer->status ?? 'NEW' }}</span>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px; font-size: 13px;">
                <div style="background: #f8fafc; padding: 12px 14px; border-radius: 12px;">
                    <div style="color: #64748b; font-weight: 600; font-size: 11px;">Assigned Telecaller</div>
                    <div style="font-weight: 800; color: #1e293b; margin-top: 2px;">{{ $customer->employee->name ?? '—' }}</div>
                    <div style="color: #64748b; font-size: 11px;">{{ $customer->employee->mobile ?? '' }}</div>
                </div>

                <div style="background: #f8fafc; padding: 12px 14px; border-radius: 12px;">
                    <div style="color: #64748b; font-weight: 600; font-size: 11px;">Assigned Account</div>
                    <div style="font-weight: 800; color: #047857; margin-top: 2px;">{{ $customer->assignedUser->name ?? '—' }}</div>
                    <div style="color: #64748b; font-size: 11px;">{{ $customer->assignedUser->mobile ?? '' }}</div>
                </div>
            </div>
        </div>

    </div>

</div>
@endsection
