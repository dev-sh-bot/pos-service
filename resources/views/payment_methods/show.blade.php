@extends('layouts.admin')

@section('title', 'Payment Method Details')
@section('content-eyebrow', 'Payments')
@section('content-header', 'Payment Method Details')
@section('content-subtitle', 'Review the bank account and terminal details available at checkout.')
@section('content-actions')
    <a href="{{ route('payment-methods.index') }}" class="btn btn-outline-secondary font-weight-bold">
        <i class="fas fa-arrow-left mr-1"></i> Back to Payment Methods
    </a>
    @if(auth()->user()->can('payment_methods.edit') || auth()->user()->hasRole('Admin') || auth()->user()->hasRole('admin'))
        <a href="{{ route('payment-methods.edit', $paymentMethod) }}" class="btn btn-primary font-weight-bold">
            <i class="fas fa-edit mr-1"></i> Edit Method
        </a>
    @endif
@endsection

@section('css')
    <style>
        .pm-detail-card {
            overflow: hidden;
            border: 1px solid var(--snd-border);
            border-radius: var(--snd-radius-lg);
            background: var(--snd-surface);
            box-shadow: var(--snd-shadow);
        }

        .pm-detail-heading {
            display: flex;
            align-items: center;
            gap: 14px;
            padding: 20px 24px;
            border-bottom: 1px solid var(--snd-border);
            background: linear-gradient(135deg, #fff 0%, #faf9ff 100%);
        }

        .pm-detail-icon {
            display: inline-flex;
            width: 48px;
            height: 48px;
            align-items: center;
            justify-content: center;
            flex: 0 0 auto;
            border: 1px solid #ded8ff;
            border-radius: 14px;
            background: #f0edff;
            color: var(--snd-primary-deep);
            font-size: 1.1rem;
        }

        .pm-detail-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 14px;
            padding: 22px 24px;
        }

        .pm-detail-field {
            min-width: 0;
            padding: 14px 16px;
            border: 1px solid var(--snd-border);
            border-radius: 12px;
            background: #fff;
        }

        .pm-detail-label {
            display: block;
            margin-bottom: 5px;
            color: var(--snd-text-muted);
            font-size: .7rem;
            font-weight: 700;
            letter-spacing: .055em;
            text-transform: uppercase;
        }

        .pm-detail-value {
            overflow-wrap: anywhere;
            color: var(--snd-text);
            font-size: .9rem;
            font-weight: 600;
        }

        .pm-detail-status {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 5px 10px;
            border: 1px solid {{ $paymentMethod->status ? '#bce8ca' : '#f3c1be' }};
            border-radius: 999px;
            background: {{ $paymentMethod->status ? '#eaf8ee' : '#fff0ef' }};
            color: {{ $paymentMethod->status ? '#1f7a3d' : '#c9342d' }};
            font-size: .75rem;
            font-weight: 700;
        }

        @media (max-width: 767.98px) {
            .pm-detail-grid { grid-template-columns: 1fr; padding: 16px; }
            .pm-detail-heading { padding: 16px; }
        }
    </style>
@endsection

@section('content')
    <section class="pm-detail-card" aria-labelledby="payment-method-name">
        <header class="pm-detail-heading">
            <span class="pm-detail-icon" aria-hidden="true"><i class="{{ $paymentMethod->getTypeIcon() }}"></i></span>
            <div class="min-w-0">
                <h2 id="payment-method-name" class="mb-1" style="font-size:1.05rem;font-weight:800;color:var(--snd-text);">
                    {{ $paymentMethod->display_name }}
                </h2>
                <div class="d-flex align-items-center flex-wrap" style="gap:8px;">
                    <span class="text-muted small">{{ $paymentMethod->getTypeLabel() }} payment method · #{{ $paymentMethod->id }}</span>
                    <span class="pm-detail-status"><i class="fas {{ $paymentMethod->status ? 'fa-check-circle' : 'fa-pause-circle' }}"></i>{{ $paymentMethod->status ? 'Active' : 'Inactive' }}</span>
                </div>
            </div>
        </header>

        <div class="pm-detail-grid">
            <div class="pm-detail-field"><span class="pm-detail-label">Bank / card terminal</span><div class="pm-detail-value">{{ $paymentMethod->bank_name ?: ($paymentMethod->name ?: '—') }}</div></div>
            <div class="pm-detail-field"><span class="pm-detail-label">Account title</span><div class="pm-detail-value">{{ $paymentMethod->account_title ?: '—' }}</div></div>
            <div class="pm-detail-field"><span class="pm-detail-label">Account number</span><div class="pm-detail-value">{{ $paymentMethod->account_number ?: '—' }}</div></div>
            <div class="pm-detail-field"><span class="pm-detail-label">IBAN</span><div class="pm-detail-value">{{ $paymentMethod->iban ?: '—' }}</div></div>
            <div class="pm-detail-field"><span class="pm-detail-label">Branch</span><div class="pm-detail-value">{{ $paymentMethod->branch_name ?: '—' }}</div></div>
            <div class="pm-detail-field"><span class="pm-detail-label">Branch code</span><div class="pm-detail-value">{{ $paymentMethod->branch_code ?: '—' }}</div></div>
            <div class="pm-detail-field"><span class="pm-detail-label">SWIFT code</span><div class="pm-detail-value">{{ $paymentMethod->swift_code ?: '—' }}</div></div>
            <div class="pm-detail-field"><span class="pm-detail-label">Notes</span><div class="pm-detail-value">{{ $paymentMethod->notes ?: '—' }}</div></div>
        </div>
    </section>
@endsection
