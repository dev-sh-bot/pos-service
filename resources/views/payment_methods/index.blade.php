@extends('layouts.admin')

@section('title', 'Payment Methods')
@section('content-eyebrow', 'Payments')
@section('content-header', 'Payment Methods')
@section('content-subtitle', 'Manage card accounts, bank details, and POS terminals.')
@section('content-actions')
@if(auth()->check() && (auth()->user()->can('payment_methods.edit') || auth()->user()->can('banks.create') || auth()->user()->can('banks.edit') || auth()->user()->hasRole('Admin') || auth()->user()->hasRole('admin')))
<a href="{{ route('payment-methods.create') }}" class="btn btn-primary font-weight-bold" style="border-radius: 8px;">
    <i class="fas fa-plus mr-1"></i> Add Payment Method
</a>
@endif
@endsection

@section('css')
<link rel="stylesheet" href="{{ asset('plugins/sweetalert2/sweetalert2.min.css') }}">
<style>
/* Stat Cards */
.pm-stat-card {
    border-radius: 12px;
    border: 1px solid var(--snd-border);
    box-shadow: 0 2px 8px rgba(15, 23, 42, 0.04);
    background: #ffffff;
    padding: 14px 18px;
    display: flex;
    align-items: center;
    gap: 14px;
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}
.pm-stat-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 16px rgba(15, 23, 42, 0.08);
}
.pm-stat-icon {
    width: 44px;
    height: 44px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.25rem;
    flex-shrink: 0;
}
.pm-stat-value {
    font-size: 1.35rem;
    font-weight: 800;
    color: #1a1a2e;
    line-height: 1.1;
}
.pm-stat-label {
    font-size: 0.78rem;
    font-weight: 600;
    color: #626b7b;
    text-transform: uppercase;
    letter-spacing: 0.04em;
    margin-top: 2px;
}

/* Card Styling */
.pm-table-card {
    border-radius: 14px;
    border: 1.5px solid var(--snd-border);
    box-shadow: 0 4px 16px rgba(15, 23, 42, 0.04);
    background: #ffffff;
    overflow: hidden;
}

/* Unified Luxury Search Box */
.pm-search-box {
    position: relative;
    display: flex;
    align-items: center;
    width: 100%;
    height: 38px;
    background: var(--snd-surface-soft);
    border: 1.5px solid var(--snd-border-strong);
    border-radius: 9999px;
    padding: 0 36px 0 36px;
    transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
    box-shadow: 0 1px 3px rgba(15, 23, 42, 0.03);
}
.pm-search-box:hover {
    background: #ffffff;
    border-color: #626b7b;
}
.pm-search-box:focus-within {
    background: #ffffff;
    border-color: var(--snd-primary) !important;
    box-shadow: 0 0 0 3px var(--snd-primary-ring), 0 2px 8px rgba(15, 23, 42, 0.05) !important;
}
.pm-search-icon {
    position: absolute;
    left: 13px;
    top: 50%;
    transform: translateY(-50%);
    font-size: 0.85rem;
    color: #626b7b;
    pointer-events: none;
    transition: color 0.2s ease;
}
.pm-search-box:focus-within .pm-search-icon {
    color: #6758bd;
}
.pm-search-input {
    width: 100%;
    height: 100%;
    background: transparent !important;
    border: none !important;
    outline: none !important;
    box-shadow: none !important;
    padding: 0 !important;
    margin: 0 !important;
    font-size: 0.86rem;
    font-weight: 500;
    color: #1a1a2e;
    line-height: normal;
}
.pm-search-input::-webkit-search-cancel-button,
.pm-search-input::-webkit-search-decoration,
.pm-search-input::-webkit-search-results-button,
.pm-search-input::-webkit-search-results-decoration {
    -webkit-appearance: none;
    appearance: none;
    display: none;
}
.pm-search-input::-ms-clear {
    display: none;
}
.pm-search-input::placeholder {
    color: #626b7b;
    font-weight: 400;
    font-size: 0.82rem;
}
.pm-search-spinner {
    position: absolute;
    right: 32px;
    top: 50%;
    transform: translateY(-50%);
    font-size: 0.82rem;
    color: #6758bd;
}
.pm-search-clear {
    position: absolute;
    right: 10px;
    top: 50%;
    transform: translateY(-50%);
    width: 20px;
    height: 20px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.75rem;
    color: #626b7b;
    border-radius: 50%;
    border: none;
    background: transparent;
    cursor: pointer;
    transition: all 0.15s ease;
    padding: 0;
}
.pm-search-clear:hover {
    color: #c9342d;
    background: #fff0ef;
}

/* Laser Progress Bar */
.laser-bar-container {
    height: 3px;
    width: 100%;
    background: var(--snd-surface-soft);
    overflow: hidden;
    display: none;
}
.laser-bar-container.active {
    display: block;
}
.laser-bar {
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent 0%, #6758bd 50%, transparent 100%);
    animation: laserScan 1.1s infinite linear;
}
@keyframes laserScan {
    0% { transform: translateX(-100%); }
    100% { transform: translateX(100%); }
}

/* Type Icon Circle in Table */
.pm-type-icon-wrapper {
    width: 34px;
    height: 34px;
    border-radius: 8px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 0.95rem;
    background: var(--snd-surface-soft);
    border: 1px solid var(--snd-border);
    color: #6758bd;
    flex-shrink: 0;
}

/* Action Button Standards */
.action-btn-group {
    display: inline-flex;
    align-items: center;
    gap: 6px;
}
.action-btn {
    width: 32px !important;
    height: 32px !important;
    min-width: 32px !important;
    min-height: 32px !important;
    padding: 0 !important;
    border-radius: 6px !important;
    display: inline-flex !important;
    align-items: center !important;
    justify-content: center !important;
    border: 0 !important;
    background: transparent !important;
    color: var(--snd-primary-deep) !important;
    transition: all 0.15s cubic-bezier(0.16, 1, 0.3, 1) !important;
    cursor: pointer;
    text-decoration: none !important;
}
.action-btn i,
.action-btn .snd-icon {
    font-size: 14px !important;
    line-height: 14px !important;
    width: 14px;
    height: 14px;
}
.action-btn-edit {
    background: transparent !important;
    color: var(--snd-primary-deep) !important;
}
.action-btn-edit:hover {
    background: var(--snd-primary-soft) !important;
    color: var(--snd-primary-deep) !important;
    box-shadow: none !important;
}
.action-btn-delete {
    background: transparent !important;
    color: var(--snd-danger) !important;
}
.action-btn-delete:hover {
    background: var(--snd-danger-soft) !important;
    color: var(--snd-danger) !important;
    box-shadow: none !important;
}

/* SND payment methods layout */
.payment-methods-page { display: grid; gap: 16px; }
.payment-methods-page > .alert { margin: 0; border-radius: var(--snd-radius-md); }
.pm-overview-grid { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 14px; }
.payment-methods-page .pm-stat-card {
    --pm-accent: var(--snd-primary-deep);
    position: relative; display: grid; grid-template-columns: 42px minmax(0, 1fr); align-items: center; gap: 12px;
    min-height: 94px; padding: 15px 18px; overflow: hidden; border: 1px solid var(--snd-border);
    border-radius: var(--snd-radius-lg); background: var(--snd-surface); box-shadow: var(--snd-shadow);
    transition: box-shadow .18s ease, transform .18s ease;
}
.payment-methods-page .pm-stat-card:hover { transform: translateY(-1px); box-shadow: var(--snd-shadow-hover); }
.payment-methods-page .pm-stat-card::before { position: absolute; top: 15px; bottom: 15px; left: 0; width: 3px; border-radius: 0 4px 4px 0; background: var(--pm-accent); content: ''; }
.payment-methods-page .pm-stat-card--active { --pm-accent: var(--snd-success); }
.payment-methods-page .pm-stat-card--inactive { --pm-accent: var(--snd-danger); }
.payment-methods-page .pm-stat-icon { width: 42px; height: 42px; border-radius: 13px; display: inline-flex; align-items: center; justify-content: center; background: var(--snd-primary-wash); color: var(--snd-primary-deep); font-size: 1rem; }
.payment-methods-page .pm-stat-card--active .pm-stat-icon { background: var(--snd-success-soft); color: var(--snd-success); }
.payment-methods-page .pm-stat-card--inactive .pm-stat-icon { background: var(--snd-danger-soft); color: var(--snd-danger); }
.payment-methods-page .pm-stat-copy { min-width: 0; display: grid; grid-template-columns: auto minmax(0, 1fr); align-items: baseline; column-gap: 8px; row-gap: 3px; }
.payment-methods-page .pm-stat-label { grid-column: 1 / -1; margin: 0; color: var(--snd-muted); font-size: .62rem; font-weight: var(--snd-font-semibold); letter-spacing: .075em; line-height: 1.25; text-transform: uppercase; }
.payment-methods-page .pm-stat-value { grid-column: 1; color: var(--pm-accent); font-size: 1.45rem; font-weight: var(--snd-font-semibold); letter-spacing: -.04em; line-height: 1; }
.payment-methods-page .pm-stat-help { grid-column: 2; color: var(--snd-muted); font-size: .64rem; line-height: 1.3; }

.payment-methods-page .pm-table-card { margin: 0; overflow: hidden; border: 1px solid var(--snd-border); border-radius: var(--snd-radius-lg); background: var(--snd-surface); box-shadow: var(--snd-shadow); }
.payment-methods-page .pm-table-header { display: flex; align-items: center; justify-content: space-between; gap: 14px; padding: 14px 18px !important; border-bottom: 1px solid var(--snd-border) !important; background: rgba(255,255,255,.34) !important; }
.pm-table-heading { display: flex; align-items: center; gap: 10px; min-width: 0; }
.pm-table-heading-icon { width: 34px; height: 34px; flex: 0 0 34px; display: inline-flex; align-items: center; justify-content: center; border-radius: 10px; background: var(--snd-primary-wash); color: var(--snd-primary-deep); }
.pm-table-heading-icon .snd-icon { width: 16px; height: 16px; }
.pm-table-heading h2 { margin: 0; color: var(--snd-ink); font-size: .84rem; font-weight: var(--snd-font-semibold); line-height: 1.2; }
.pm-table-heading p { margin: 3px 0 0; color: var(--snd-muted); font-size: .67rem; line-height: 1.35; }
.pm-type-pill { display: inline-flex; align-items: center; gap: 7px; flex: 0 0 auto; padding: 6px 10px; border: 1px solid rgba(151,134,238,.2); border-radius: 999px; background: var(--snd-primary-wash); color: var(--snd-primary-deep); font-size: .65rem; font-weight: var(--snd-font-medium); white-space: nowrap; }
.pm-search-toolbar { display: flex; align-items: center; justify-content: space-between; gap: 16px; padding: 14px 18px; border-bottom: 1px solid var(--snd-border); }
.pm-search-box { flex: 1 1 auto; width: auto; max-width: none; height: 40px; padding: 0 66px 0 38px; border: 1px solid var(--snd-border-strong); border-radius: 11px; background: rgba(248,249,253,.78); box-shadow: none; }
.pm-search-box:hover { border-color: rgba(151,134,238,.45); background: #fff; }
.pm-search-box:focus-within { border-color: var(--snd-primary) !important; background: #fff; box-shadow: 0 0 0 3px var(--snd-primary-ring) !important; }
.pm-search-icon { left: 13px; color: var(--snd-muted); }
.pm-search-box:focus-within .pm-search-icon { color: var(--snd-primary-deep); }
.pm-search-input { color: var(--snd-ink); font-size: .72rem; font-weight: var(--snd-font-regular); }
.pm-search-input::placeholder { color: var(--snd-muted); font-size: .7rem; }
.pm-search-spinner { right: 38px; color: var(--snd-primary-deep); }
.pm-search-clear { right: 10px; color: var(--snd-muted); }
.pm-search-clear:hover { color: var(--snd-danger); background: var(--snd-danger-soft); }
.pm-search-progress { height: 3px; width: 100%; overflow: hidden; background: transparent; }
.pm-search-progress.active { display: block; background: var(--snd-primary-soft); }
.pm-search-progress .laser-bar { background: linear-gradient(90deg, transparent 0%, var(--snd-primary) 50%, transparent 100%); }

.payment-methods-table-wrap { width: 100%; }
.payment-methods-table { min-width: 1120px; }
.payment-methods-table thead th { padding: 11px 12px; }
.payment-methods-table tbody td { padding: 12px; color: var(--snd-ink-soft); font-size: .72rem; }
.payment-methods-table tbody tr:hover { background: var(--snd-primary-wash); }
.payment-methods-table th:first-child, .payment-methods-table td:first-child { width: 60px; text-align: center; white-space: nowrap; }
.payment-methods-table th:last-child, .payment-methods-table td:last-child { width: 100px; text-align: center !important; }
.payment-methods-table .min-w-0 { min-width: 0; }
.payment-methods-table .pm-type-icon-wrapper { width: 36px; height: 36px; margin-right: 10px !important; border: 1px solid rgba(151,134,238,.2); border-radius: 11px; background: var(--snd-primary-wash); color: var(--snd-primary-deep); font-size: .9rem; }
.payment-methods-table .pm-bank-name { color: var(--snd-ink); font-size: .75rem; font-weight: var(--snd-font-semibold); line-height: 1.3; }
.payment-methods-table .pm-card-badge { display: inline-flex; align-items: center; gap: 5px; margin-top: 4px; padding: 3px 7px; border: 1px solid rgba(151,134,238,.2); border-radius: 999px; background: var(--snd-primary-wash); color: var(--snd-primary-deep); font-size: .59rem; font-weight: var(--snd-font-medium); line-height: 1; }
.payment-methods-table .pm-account-title { display: inline-flex; align-items: center; gap: 7px; color: var(--snd-ink-soft); font-size: .69rem; font-weight: var(--snd-font-medium); }
.payment-methods-table .pm-account-title i { color: var(--snd-muted); }
.payment-methods-table .pm-account-lines { display: grid; gap: 5px; }
.payment-methods-table .pm-account-line { display: flex; align-items: center; flex-wrap: wrap; gap: 4px; min-width: 0; }
.payment-methods-table .pm-account-label { color: var(--snd-muted); font-size: .58rem; font-weight: var(--snd-font-medium); }
.payment-methods-table .pm-account-code { max-width: 100%; padding: 3px 6px; overflow-wrap: anywhere; border: 1px solid var(--snd-border); border-radius: 7px; background: rgba(248,249,253,.85); color: var(--snd-ink); font-size: .62rem; line-height: 1.2; }
.payment-methods-table .pm-copy-btn { display: inline-flex; align-items: center; justify-content: center; width: 22px; height: 22px; padding: 0; border: 0; border-radius: 6px; background: transparent; color: var(--snd-primary); }
.payment-methods-table .pm-copy-btn .snd-icon { width: 13px; height: 13px; }
.payment-methods-table .pm-copy-btn .pm-copied-icon { display: none; }
.payment-methods-table .pm-copy-btn.is-copied { color: var(--snd-success); }
.payment-methods-table .pm-copy-btn.is-copied:hover { background: var(--snd-success-soft); color: var(--snd-success); }
.payment-methods-table .pm-copy-btn.is-copied .pm-copy-icon { display: none; }
.payment-methods-table .pm-copy-btn.is-copied .pm-copied-icon { display: block; }
.payment-methods-table .pm-copy-btn:hover { background: var(--snd-primary-wash); color: var(--snd-primary-deep); }
.payment-methods-table .pm-branch-name { margin-bottom: 4px; color: var(--snd-ink-soft); font-size: .68rem; font-weight: var(--snd-font-medium); }
.payment-methods-table .pm-branch-meta { display: flex; flex-wrap: wrap; gap: 4px; }
.payment-methods-table .pm-branch-meta .badge { padding: 4px 7px; border: 1px solid var(--snd-border); background: var(--snd-surface-soft); color: var(--snd-muted); font-size: .56rem; font-weight: var(--snd-font-medium); }
.payment-methods-table .pm-status { display: inline-flex; align-items: center; justify-content: center; gap: 5px; min-height: 28px; padding: 0 9px !important; border: 1px solid transparent; border-radius: 999px !important; font-size: .62rem; font-weight: var(--snd-font-medium); white-space: nowrap; }
.payment-methods-table .pm-status .snd-icon { width: 13px; height: 13px; }
.payment-methods-table .pm-status--active { border-color: rgba(31,122,61,.18); background: var(--snd-success-soft); color: var(--snd-success); }
.payment-methods-table .pm-status--inactive { border-color: rgba(201,52,45,.18); background: var(--snd-danger-soft); color: var(--snd-danger); }
.payment-methods-table .action-btn-group { justify-content: center; gap: 5px; }
.payment-methods-table .action-btn { border: 0 !important; border-radius: 6px !important; background: transparent !important; box-shadow: none !important; }
.payment-methods-table .action-btn-edit { color: var(--snd-primary-deep) !important; }
.payment-methods-table .action-btn-edit:hover { background: var(--snd-primary-soft) !important; color: var(--snd-primary-deep) !important; }
.payment-methods-table .action-btn-delete { color: var(--snd-danger) !important; }
.payment-methods-table .action-btn-delete:hover { background: var(--snd-danger-soft) !important; color: var(--snd-danger) !important; }
.payment-methods-table .snd-table-empty-cell { padding: 0 !important; }
.pm-empty-state { display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 8px; min-height: 250px; padding: 28px 20px; text-align: center; }
.pm-empty-icon { display: inline-flex; align-items: center; justify-content: center; width: 52px; height: 52px; margin-bottom: 3px; border-radius: 16px; background: var(--snd-primary-soft); color: var(--snd-primary-deep); font-size: 1.2rem; }
.pm-empty-kicker { color: var(--snd-primary-deep); font-size: .6rem; font-weight: var(--snd-font-semibold); letter-spacing: .1em; text-transform: uppercase; }
.pm-empty-state h3 { margin: 0; color: var(--snd-ink); font-size: .9rem; font-weight: var(--snd-font-semibold); }
.pm-empty-state p { max-width: 400px; margin: 0; color: var(--snd-muted); font-size: .68rem; line-height: 1.5; }
.pm-empty-state .btn { display: inline-flex; align-items: center; justify-content: center; gap: 7px; min-height: 38px; margin-top: 5px; padding: 0 14px; border-radius: 10px; white-space: nowrap; }
.payment-methods-page .payment-methods-pagination { display: flex; align-items: center; }
.payment-methods-page .payment-methods-pagination .pagination { margin: 0; }
.pm-pagination-footer { display: flex; align-items: center; justify-content: space-between; gap: 12px; min-height: 50px; padding: 12px 18px; border-top: 1px solid var(--snd-border); color: var(--snd-muted); font-size: .66rem; }
.pm-pagination-footer:empty { display: none; }

@media (max-width: 991.98px) { .pm-overview-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
@media (max-width: 767.98px) {
    .pm-search-toolbar { align-items: stretch; flex-direction: column; }
    .pm-search-box { width: 100%; }
    .pm-type-pill { align-self: flex-start; }
}
@media (max-width: 575.98px) {
    .pm-overview-grid { grid-template-columns: minmax(0, 1fr); }
    .payment-methods-page .pm-table-header { align-items: flex-start; flex-direction: column; }
    .pm-pagination-footer { align-items: flex-start; flex-direction: column; }
}
</style>
@endsection

@section('content')
<div class="snd-page payment-methods-page">
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle mr-1"></i> {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    <section class="pm-overview-grid" aria-label="Payment method summary">
        <article class="pm-stat-card">
            <span class="pm-stat-icon">
                    <i class="fas fa-credit-card"></i>
            </span>
            <div class="pm-stat-copy">
                <span class="pm-stat-label">Total payment methods</span>
                <strong class="pm-stat-value">{{ $totalMethods }}</strong>
                <span class="pm-stat-help">Card accounts and POS terminals</span>
            </div>
        </article>
        <article class="pm-stat-card pm-stat-card--active">
            <span class="pm-stat-icon">
                    <i class="fas fa-check-circle"></i>
            </span>
            <div class="pm-stat-copy">
                <span class="pm-stat-label">Active methods</span>
                <strong class="pm-stat-value">{{ $activeMethods }}</strong>
                <span class="pm-stat-help">Ready to accept payments</span>
            </div>
        </article>
        <article class="pm-stat-card pm-stat-card--inactive">
            <span class="pm-stat-icon">
                    <i class="fas fa-pause-circle"></i>
            </span>
            <div class="pm-stat-copy">
                <span class="pm-stat-label">Inactive methods</span>
                <strong class="pm-stat-value">{{ $inactiveMethods }}</strong>
                <span class="pm-stat-help">Not available at checkout</span>
            </div>
        </article>
    </section>

    <section class="card pm-table-card" aria-labelledby="pm-table-title">
        <div class="card-header pm-table-header">
            <div class="pm-table-heading">
                <span class="pm-table-heading-icon"><x-snd-icon name="wallet" /></span>
                <div>
                    <h2 id="pm-table-title">Payment method directory</h2>
                    <p>Bank receiving accounts and connected card terminals.</p>
                </div>
            </div>
        </div>

        <div class="pm-search-toolbar">
            <div class="pm-search-box">
                <i class="fas fa-search pm-search-icon"></i>
                <input type="text"
                       id="pmSearchInput"
                       class="pm-search-input"
                       placeholder="Search by bank, account title, account number, or IBAN…"
                       value="{{ request('search') }}"
                       autocomplete="off"
                       aria-label="Search payment methods">
                <span class="pm-search-spinner" id="pmSearchSpinner" style="display: none;" aria-hidden="true">
                    <i class="fas fa-spinner fa-spin"></i>
                </span>
                <button type="button" class="pm-search-clear" id="pmSearchClear" style="display: {{ request('search') ? 'flex' : 'none' }};" title="Clear search" aria-label="Clear search">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <span class="pm-type-pill"><i class="fas fa-wallet"></i> All payment methods</span>
        </div>

        <div class="laser-bar-container pm-search-progress" id="laserProgressBar" aria-hidden="true">
            <div class="laser-bar"></div>
        </div>

        <div class="card-body p-0" id="tableContainer" aria-live="polite">
            @include('payment_methods.partials.table', ['paymentMethods' => $paymentMethods])
        </div>
    </section>
</div>
@endsection

@section('js')
<script src="{{ asset('plugins/sweetalert2/sweetalert2.all.min.js') }}"></script>
<script>
(function() {
    const initializePaymentMethods = function($) {
        $(document).ready(function() {
    let searchTimeout = null;
    const $searchInput  = $('#pmSearchInput');
    const $searchSpinner= $('#pmSearchSpinner');
    const $searchClear  = $('#pmSearchClear');
    const $progressBar  = $('#laserProgressBar');
    const $tableContainer = $('#tableContainer');

    function performSearch(url = null) {
        const query = $searchInput.val().trim();
        const fetchUrl = url || "{{ route('payment-methods.index') }}";

        $searchSpinner.show();
        $progressBar.addClass('active');

        $.ajax({
            url: fetchUrl,
            type: 'GET',
            data: {
                search: query,
                ajax_table: 1
            },
            success: function(response) {
                $tableContainer.html(response);
                $searchSpinner.hide();
                $progressBar.removeClass('active');
            },
            error: function(err) {
                console.error("Search failed:", err);
                $searchSpinner.hide();
                $progressBar.removeClass('active');
            }
        });
    }

    // Live search input handler
    $searchInput.on('input', function() {
        const val = $(this).val().trim();
        if (val.length > 0) {
            $searchClear.css('display', 'flex');
        } else {
            $searchClear.hide();
        }

        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(function() {
            performSearch();
        }, 300);
    });

    // Clear search button
    $searchClear.on('click', function() {
        $searchInput.val('');
        $searchClear.hide();
        performSearch();
    });

    // Pagination link AJAX click
    $(document).on('click', '.payment-methods-pagination a', function(e) {
        e.preventDefault();
        const url = $(this).attr('href');
        if (url) {
            performSearch(url);
        }
    });

    // Toggle Status
    $(document).on('click', '.btn-toggle-status', function(e) {
        e.preventDefault();
        const $btn = $(this);
        const url = $btn.data('url');

        $btn.prop('disabled', true);

        $.ajax({
            url: url,
            type: 'POST',
            data: {
                _token: '{{ csrf_token() }}'
            },
            success: function(res) {
                if (res.success) {
                    performSearch();
                }
            },
            error: function() {
                $btn.prop('disabled', false);
            }
        });
    });

    // Copy to clipboard
    $(document).on('click', '.btn-copy', function(e) {
        e.preventDefault();
        const textToCopy = $(this).data('clipboard-text');
        const $button = $(this);

        if (navigator.clipboard && window.isSecureContext) {
            navigator.clipboard.writeText(textToCopy);
        } else {
            const temp = $("<input>");
            $("body").append(temp);
            temp.val(textToCopy).select();
            document.execCommand("copy");
            temp.remove();
        }

        $button.addClass('is-copied');
        setTimeout(function() {
            $button.removeClass('is-copied');
        }, 1500);
    });

    // Delete with SweetAlert2
    $(document).on('click', '.btn-delete', function() {
        const url = $(this).data('url');

        Swal.fire({
            title: 'Delete Payment Method?',
            text: "This payment method will be permanently deleted.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#c9342d',
            cancelButtonColor: '#626b7b',
            confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: url,
                    type: 'DELETE',
                    data: {
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        if (response.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Deleted!',
                                text: 'Payment Method deleted successfully.',
                                timer: 1500,
                                showConfirmButton: false
                            });
                            performSearch();
                        }
                    },
                    error: function(err) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error!',
                            text: 'Failed to delete payment method.'
                        });
                    }
                });
            }
        });
    });
        });
    };

    if (window.jQuery) {
        initializePaymentMethods(window.jQuery);
        return;
    }

    const jqueryScript = document.createElement('script');
    jqueryScript.src = "{{ asset('js/jquery.min.js') }}";
    jqueryScript.onload = function() {
        if (window.jQuery) initializePaymentMethods(window.jQuery);
    };
    document.head.appendChild(jqueryScript);
})();
</script>
@endsection
