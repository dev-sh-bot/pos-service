@php
    $tableBranchId = $tableBranchId ?? null;
    $filteredStaffSales = $tableBranchId
        ? $staffSalesData->filter(fn($r) => (int) $r['branch_id'] === (int) $tableBranchId)->values()
        : $staffSalesData;

    $tableId = 'staffSalesTable_' . ($tableBranchId ?: 'all');
    $searchInputId = 'staffSearch_' . ($tableBranchId ?: 'all');
    $branchSelectId = 'staffBranchSelect_' . ($tableBranchId ?: 'all');
    $counterSelectId = 'staffCounterSelect_' . ($tableBranchId ?: 'all');
    $quickFilterGroupId = 'quickFilters_' . ($tableBranchId ?: 'all');
    $countBadgeId = 'staffRowCount_' . ($tableBranchId ?: 'all');

    // Current user's records
    $myRecords = $staffSalesData->filter(fn($r) => $r['is_me'])->values();
    $myGross = (float) $myRecords->sum('gross_sales');
    $myNet = (float) $myRecords->sum('net_sales');
    $myOrders = (int) $myRecords->sum('orders_count');
    $myToday = (float) $myRecords->sum('sales_today');
    $myMonth = (float) $myRecords->sum('sales_month');
    $myRefunds = (float) $myRecords->sum('returns_total');
@endphp

<style>
    .staff-matrix-wrapper {
        --zp-primary: var(--snd-primary-deep);
        --zp-primary-light: var(--snd-primary-soft);
        --zp-primary-border: var(--snd-border-strong);
        --zp-text-main: var(--snd-ink);
        --zp-text-muted: var(--snd-muted);
        --zp-bg-card: var(--snd-surface-solid);
        --zp-border-subtle: var(--snd-border);
        --zp-border-light: var(--snd-border);
        --zp-success: var(--snd-success);
        --zp-success-bg: var(--snd-success-soft);
        --zp-success-border: var(--snd-border-strong);
        color: var(--snd-ink);
        background: var(--snd-surface-solid);
        border: 1px solid var(--snd-border);
        border-radius: var(--snd-radius-lg);
        box-shadow: var(--snd-shadow);
        overflow: hidden;
        grid-column: 1 / -1;
        width: 100%;
        min-width: 0;
        box-sizing: border-box;
    }
    .staff-matrix-wrapper .decent-card-header {
        padding: 16px 20px;
        border-bottom: 1px solid var(--snd-border);
        background: var(--snd-surface-solid);
    }
    .staff-matrix-wrapper .staff-matrix-toolbar {
        padding: 14px 18px;
        border-bottom: 1px solid var(--snd-border);
        background: var(--snd-surface-soft);
    }
    .staff-matrix-wrapper .staff-search-box { position: relative; display: flex; align-items: center; }
    .staff-matrix-wrapper .staff-search-box i { position: absolute; left: 13px; color: var(--snd-muted); pointer-events: none; }
    .staff-matrix-wrapper .staff-search-box input {
        width: 100%; height: 38px; padding: 6px 12px 6px 36px; border: 1px solid var(--snd-border-strong);
        border-radius: 10px; color: var(--snd-ink); background: #fff; outline: none;
    }
    .staff-matrix-wrapper .staff-search-box input:focus { border-color: var(--snd-primary); box-shadow: 0 0 0 3px var(--snd-primary-ring); }
    .staff-matrix-wrapper .staff-filter-btn {
        min-height: 36px; padding: 0 12px; border: 1px solid var(--snd-border-strong); border-radius: 9px;
        color: var(--snd-ink-soft); background: #fff; font-size: .76rem; font-weight: 600; cursor: pointer;
    }
    .staff-matrix-wrapper .staff-filter-btn.active { color: #fff; border-color: var(--snd-primary-deep); background: var(--snd-primary-deep); }
    .staff-matrix-wrapper .decent-table { width: 100%; border-collapse: collapse; margin: 0; }
    .staff-matrix-wrapper .decent-table thead th {
        padding: 12px 14px; color: var(--snd-muted); background: var(--snd-workspace);
        border-bottom: 1px solid var(--snd-border); font-size: .68rem; font-weight: 700; letter-spacing: .04em; text-transform: uppercase;
    }
    .staff-matrix-wrapper .decent-table tbody td { padding: 12px 14px; border-bottom: 1px solid var(--snd-border); color: var(--snd-ink); vertical-align: middle; }
    .staff-matrix-wrapper .decent-table tbody tr:hover { background: var(--snd-primary-soft); }
    .staff-matrix-wrapper .decent-table tfoot td { background: var(--snd-workspace); }
    @media (max-width: 767.98px) {
        .staff-matrix-wrapper .decent-card-header { align-items: flex-start; }
        .staff-matrix-wrapper .staff-filter-btn { min-height: 32px; padding: 0 8px; font-size: .68rem; }
    }
</style>

<div class="decent-card mb-4 staff-matrix-wrapper" id="card_{{ $tableId }}">
    {{-- Card Header --}}
    <div class="decent-card-header">
        <div class="d-flex align-items-center flex-wrap gap-2">
            <div style="width: 38px; height: 38px; border-radius: 10px; background: {{ $isAdmin ? '#f0edff' : '#eaf9ee' }}; color: {{ $isAdmin ? 'var(--zp-primary)' : '#1f7a3d' }}; display: flex; align-items: center; justify-content: center; font-size: 1.1rem;">
                <i class="fas {{ $isAdmin ? 'fa-users-cog' : 'fa-user-check' }}"></i>
            </div>
            <div>
                <h3 style="margin: 0; font-size: 1rem; font-weight: 800; color: var(--zp-text-main); display: flex; align-items: center; gap: 8px;">
                    @if($isAdmin)
                        <span>Cashier & Counter Sales Matrix</span>
                        <span class="badge" style="background:#f0edff;color:var(--zp-primary);border:1px solid var(--zp-primary-border);font-size:0.7rem;font-weight:700;padding:2px 8px;border-radius:6px;">
                            Branch &bull; Counter &bull; Staff
                        </span>
                    @else
                        <span>My Sales & Register Performance</span>
                        <span class="badge" style="background:#eaf9ee;color:#1f7a3d;border:1px solid #c7ebd0;font-size:0.7rem;font-weight:700;padding:2px 8px;border-radius:6px;">
                            <i class="fas fa-id-badge mr-1"></i> {{ auth()->user()?->getFullname() ?? 'Cashier' }}
                        </span>
                    @endif
                </h3>
                <span class="header-meta" style="margin-left: 0; margin-top: 2px;">
                    @if($isAdmin)
                        Comprehensive breakdown of sales, orders, and returns processed by each user at their assigned branch and counter
                    @else
                        Your personalized sales volume, invoice throughput, and return logs across all assigned registers
                    @endif
                </span>
            </div>
        </div>

        <div class="d-flex align-items-center gap-2 flex-wrap mt-2 mt-md-0">
            @if($isAdmin)
                <span class="badge" style="background:var(--snd-surface-soft);color:#505469;border:1px solid var(--snd-border);padding:6px 12px;border-radius:8px;font-weight:700;font-size:0.75rem;">
                    <i class="fas fa-user-friends mr-1 text-primary"></i> <span id="{{ $countBadgeId }}">{{ count($filteredStaffSales) }}</span> Register {{ \Illuminate\Support\Str::plural('Record', count($filteredStaffSales)) }}
                </span>
                <span class="badge" style="background:#eaf9ee;color:#1f7a3d;border:1px solid #c7ebd0;padding:6px 12px;border-radius:8px;font-weight:700;font-size:0.75rem;">
                    <i class="fas fa-check-circle mr-1"></i> Net: {{ config('settings.currency_symbol') }} {{ number_format($filteredStaffSales->sum('net_sales'), 2) }}
                </span>
            @else
                <span class="badge" style="background:#eaf9ee;color:#1f7a3d;border:1px solid #c7ebd0;padding:6px 12px;border-radius:8px;font-weight:700;font-size:0.75rem;">
                    <i class="fas fa-coins mr-1"></i> Total Net: {{ config('settings.currency_symbol') }} {{ number_format($myNet, 2) }}
                </span>
            @endif
        </div>
    </div>

    {{-- Non-Admin Personal KPI Strip --}}
    @if(!$isAdmin)
        <div class="px-3 pt-3">
            <div class="row g-3">
                <div class="col-md-3 col-6 mb-3">
                    <div style="background:var(--snd-surface-soft); border:1px solid var(--snd-border); border-radius:10px; padding:12px 14px;">
                        <span style="font-size:0.72rem; font-weight:700; color:var(--zp-text-muted); text-transform:uppercase;">Today's Sales</span>
                        <div style="font-size:1.15rem; font-weight:800; color:var(--zp-success); font-family:monospace; margin-top:2px;">
                            {{ config('settings.currency_symbol') }} {{ number_format($myToday, 2) }}
                        </div>
                    </div>
                </div>
                <div class="col-md-3 col-6 mb-3">
                    <div style="background:var(--snd-surface-soft); border:1px solid var(--snd-border); border-radius:10px; padding:12px 14px;">
                        <span style="font-size:0.72rem; font-weight:700; color:var(--zp-text-muted); text-transform:uppercase;">Month Sales (MTD)</span>
                        <div style="font-size:1.15rem; font-weight:800; color:var(--zp-primary); font-family:monospace; margin-top:2px;">
                            {{ config('settings.currency_symbol') }} {{ number_format($myMonth, 2) }}
                        </div>
                    </div>
                </div>
                <div class="col-md-3 col-6 mb-3">
                    <div style="background:var(--snd-surface-soft); border:1px solid var(--snd-border); border-radius:10px; padding:12px 14px;">
                        <span style="font-size:0.72rem; font-weight:700; color:var(--zp-text-muted); text-transform:uppercase;">My Total Invoices</span>
                        <div style="font-size:1.15rem; font-weight:800; color:var(--zp-text-main); margin-top:2px;">
                            {{ $myOrders }} <small style="font-size:0.75rem; color:var(--zp-text-muted); font-weight:500;">orders</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 col-6 mb-3">
                    <div style="background:var(--snd-surface-soft); border:1px solid var(--snd-border); border-radius:10px; padding:12px 14px;">
                        <span style="font-size:0.72rem; font-weight:700; color:var(--zp-text-muted); text-transform:uppercase;">Returns Handled</span>
                        <div style="font-size:1.15rem; font-weight:800; color:{{ $myRefunds > 0 ? '#e11d48' : '#626b7b' }}; font-family:monospace; margin-top:2px;">
                            {{ config('settings.currency_symbol') }} {{ number_format($myRefunds, 2) }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- Filter Toolbar (Admin & Multi-branch search) --}}
    @if($isAdmin)
        <div class="staff-matrix-toolbar">
            <div class="row align-items-center g-2">
                {{-- Search Box --}}
                <div class="col-md-4 col-12 mb-2 mb-md-0">
                    <div class="staff-search-box">
                        <i class="fas fa-search"></i>
                        <input type="text"
                               id="{{ $searchInputId }}"
                               class="staff-search-input"
                               placeholder="Search cashier name, branch, or counter..."
                               onkeyup="filterStaffMatrixTable('{{ $tableId }}')">
                    </div>
                </div>

                {{-- Branch Filter (Select2) --}}
                @if(!$tableBranchId && count($branches) > 1)
                    <div class="col-md-3 col-sm-6 col-12 mb-2 mb-md-0">
                        <select id="{{ $branchSelectId }}"
                                class="staff-branch-select2 form-control"
                                data-table-id="{{ $tableId }}"
                                onchange="filterStaffMatrixTable('{{ $tableId }}')"
                                style="width: 100%;">
                            <option value="">All Branches ({{ count($branches) }})</option>
                            @foreach($branches as $b)
                                <option value="{{ strtolower($b->name) }}">{{ $b->name }} ({{ $b->code }})</option>
                            @endforeach
                        </select>
                    </div>
                @endif

                {{-- Quick Filter Pills --}}
                <div class="col-md-{{ (!$tableBranchId && count($branches) > 1) ? '5' : '8' }} col-12 d-flex align-items-center justify-content-start justify-content-md-end gap-2 flex-wrap" id="{{ $quickFilterGroupId }}">
                    <button type="button"
                            class="staff-filter-btn active"
                            data-filter="all"
                            onclick="setStaffQuickFilter('{{ $tableId }}', 'all', this)">
                        All Records
                    </button>
                    <button type="button"
                            class="staff-filter-btn"
                            data-filter="today"
                            onclick="setStaffQuickFilter('{{ $tableId }}', 'today', this)">
                        <i class="fas fa-bolt text-warning mr-1"></i> Active Today
                    </button>
                    <button type="button"
                            class="staff-filter-btn"
                            data-filter="me"
                            onclick="setStaffQuickFilter('{{ $tableId }}', 'me', this)">
                        <i class="fas fa-user-check text-success mr-1"></i> My Sales Only
                    </button>
                    <button type="button"
                            class="staff-filter-btn"
                            data-filter="returns"
                            onclick="setStaffQuickFilter('{{ $tableId }}', 'returns', this)">
                        <i class="fas fa-undo text-danger mr-1"></i> Has Returns
                    </button>
                </div>
            </div>
        </div>
    @endif

    {{-- Table Responsive Area --}}
    <div class="table-responsive">
        <table class="decent-table mb-0" id="{{ $tableId }}" style="min-width: 980px;">
            <thead>
                <tr>
                    <th style="width: 22%;">User / Cashier</th>
                    <th style="width: 15%;">Branch Location</th>
                    <th style="width: 13%;">Counter / Register</th>
                    <th style="width: 12%;">Today's Sales</th>
                    <th style="width: 12%;">Month (MTD)</th>
                    <th style="width: 8%;" class="text-center">Invoices</th>
                    <th style="width: 10%;">Gross Sales</th>
                    <th style="width: 8%;">Returns</th>
                    <th style="width: 12%;">Net Sales</th>
                    <th style="width: 10%;" class="text-right">Last Sale</th>
                </tr>
            </thead>
            <tbody>
                @forelse($filteredStaffSales as $row)
                    @php
                        $userInitials = collect(explode(' ', $row['user_name']))
                            ->map(fn($w) => strtoupper(substr($w, 0, 1)))
                            ->take(2)
                            ->implode('');
                    @endphp
                    <tr class="staff-row"
                        data-user-name="{{ strtolower($row['user_name']) }}"
                        data-user-email="{{ strtolower($row['user_email']) }}"
                        data-branch-name="{{ strtolower($row['branch_name']) }}"
                        data-branch-code="{{ strtolower($row['branch_code']) }}"
                        data-counter-name="{{ strtolower($row['counter_name']) }}"
                        data-counter-code="{{ strtolower($row['counter_code']) }}"
                        data-is-me="{{ $row['is_me'] ? '1' : '0' }}"
                        data-has-today="{{ $row['sales_today'] > 0 ? '1' : '0' }}"
                        data-has-returns="{{ $row['returns_count'] > 0 ? '1' : '0' }}">

                        {{-- 1. User / Cashier --}}
                        <td>
                            <div class="d-flex align-items-center">
                                <div style="width: 36px; height: 36px; border-radius: 50%; background: {{ $row['is_me'] ? '#eaf9ee' : '#f0edff' }}; color: {{ $row['is_me'] ? '#1f7a3d' : 'var(--zp-primary)' }}; display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 0.82rem; margin-right: 10px; border: 1.5px solid {{ $row['is_me'] ? '#86efac' : 'var(--zp-primary-border)' }}; flex-shrink: 0;">
                                    {{ $userInitials ?: 'U' }}
                                </div>
                                <div style="min-width: 0;">
                                    <div class="d-flex align-items-center gap-1 flex-wrap">
                                        <span style="font-weight: 700; color: var(--zp-text-main); font-size: 0.88rem;">
                                            {{ $row['user_name'] }}
                                        </span>
                                        @if($row['is_me'])
                                            <span class="badge" style="background:#eaf9ee; color:#1f7a3d; border:1px solid #c7ebd0; font-size:0.62rem; font-weight:800; padding:2px 5px; border-radius:4px;">
                                                YOU
                                            </span>
                                        @endif
                                    </div>
                                    @if($row['user_email'])
                                        <div style="font-size: 0.72rem; color: var(--zp-text-muted); overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                                            {{ $row['user_email'] }}
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </td>

                        {{-- 2. Branch --}}
                        <td>
                            <div class="d-flex align-items-center">
                                <i class="fas fa-store text-muted mr-2" style="font-size: 0.8rem;"></i>
                                <div>
                                    <div style="font-weight: 600; color: var(--zp-text-main); font-size: 0.84rem;">
                                        {{ $row['branch_name'] }}
                                    </div>
                                    <span class="badge badge-light border text-muted px-1" style="font-size: 0.65rem; font-weight: 700;">
                                        {{ $row['branch_code'] }}
                                    </span>
                                </div>
                            </div>
                        </td>

                        {{-- 3. Counter / Register --}}
                        <td>
                            <div class="d-flex align-items-center">
                                <i class="fas fa-desktop text-primary mr-2" style="font-size: 0.8rem;"></i>
                                <div>
                                    <div style="font-weight: 600; color: var(--zp-text-main); font-size: 0.84rem;">
                                        {{ $row['counter_name'] }}
                                    </div>
                                    <span class="badge badge-light border text-muted px-1" style="font-size: 0.65rem;">
                                        {{ $row['counter_code'] }}
                                    </span>
                                </div>
                            </div>
                        </td>

                        {{-- 4. Today's Sales --}}
                        <td>
                            <div style="font-weight: 800; color: {{ $row['sales_today'] > 0 ? 'var(--zp-success)' : '#626b7b' }}; font-family: monospace; font-size: 0.9rem;">
                                {{ config('settings.currency_symbol') }} {{ number_format($row['sales_today'], 2) }}
                            </div>
                            <small style="color: var(--zp-text-muted); font-size: 0.7rem;">
                                {{ $row['orders_today'] }} {{ \Illuminate\Support\Str::plural('order', $row['orders_today']) }}
                            </small>
                        </td>

                        {{-- 5. Month's Sales (MTD) --}}
                        <td>
                            <div style="font-weight: 800; color: {{ $row['sales_month'] > 0 ? 'var(--zp-primary)' : '#626b7b' }}; font-family: monospace; font-size: 0.9rem;">
                                {{ config('settings.currency_symbol') }} {{ number_format($row['sales_month'], 2) }}
                            </div>
                            <small style="color: var(--zp-text-muted); font-size: 0.7rem;">
                                {{ $row['orders_month'] }} {{ \Illuminate\Support\Str::plural('order', $row['orders_month']) }}
                            </small>
                        </td>

                        {{-- 6. Total Invoices --}}
                        <td class="text-center">
                            <span class="badge" style="background:var(--snd-surface-soft); color:#505469; font-weight:700; font-size:0.75rem; padding:4px 8px; border-radius:6px; border:1px solid var(--snd-border);">
                                {{ $row['orders_count'] }}
                            </span>
                        </td>

                        {{-- 7. Gross Sales --}}
                        <td>
                            <div style="font-weight: 700; color: var(--zp-text-main); font-family: monospace; font-size: 0.88rem;">
                                {{ config('settings.currency_symbol') }} {{ number_format($row['gross_sales'], 2) }}
                            </div>
                            @if($row['orders_count'] > 0)
                                <small style="color: var(--zp-text-muted); font-size: 0.68rem;">
                                    Avg: {{ config('settings.currency_symbol') }} {{ number_format($row['avg_order'], 0) }}
                                </small>
                            @endif
                        </td>

                        {{-- 8. Returns / Refunds --}}
                        <td>
                            @if($row['returns_count'] > 0)
                                <div style="font-weight: 700; color: #e11d48; font-family: monospace; font-size: 0.82rem;">
                                    -{{ config('settings.currency_symbol') }} {{ number_format($row['returns_total'], 2) }}
                                </div>
                                <span class="badge" style="background:#fff0ef; color:#c9342d; font-size:0.65rem; border:1px solid #f2b8b5; padding:1px 4px; border-radius:4px;">
                                    {{ $row['returns_count'] }} ret
                                </span>
                            @else
                                <span style="color: #626b7b; font-size: 0.78rem; font-family: monospace;">-</span>
                            @endif
                        </td>

                        {{-- 9. Net Sales --}}
                        <td>
                            <div class="d-inline-block px-2 py-1" style="background: #eaf9ee; border: 1px solid #c7ebd0; border-radius: 6px;">
                                <strong style="color: #1f7a3d; font-family: monospace; font-size: 0.88rem;">
                                    {{ config('settings.currency_symbol') }} {{ number_format($row['net_sales'], 2) }}
                                </strong>
                            </div>
                        </td>

                        {{-- 10. Last Active / Sale Time --}}
                        <td class="text-right">
                            @if($row['last_order_at'])
                                <span style="font-weight: 600; color: var(--zp-text-main); font-size: 0.78rem;">
                                    {{ $row['last_order_at']->diffForHumans() }}
                                </span>
                                <small class="text-muted d-block" style="font-size: 0.68rem;">
                                    {{ $row['last_order_at']->format('d M, h:i A') }}
                                </small>
                            @else
                                <span class="text-muted" style="font-size: 0.75rem;">No sales yet</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr id="emptyRow_{{ $tableId }}">
                        <td colspan="10" class="text-center py-5 text-muted">
                            <i class="fas fa-cash-register fa-2x mb-2 text-muted" style="opacity: 0.5;"></i>
                            <div style="font-weight: 600; font-size: 0.9rem;">No sales records found for this register view.</div>
                            <div class="small mt-1">Start selling at the POS terminal to see live performance tracking.</div>
                            <div class="mt-3">
                                <a href="{{ route('cart.index') }}" class="btn btn-sm btn-primary" style="border-radius: 8px; font-weight: 700;">
                                    <i class="fas fa-shopping-cart mr-1"></i> Open POS Register
                                </a>
                            </div>
                        </td>
                    </tr>
                @endforelse

                {{-- No Matching Rows (Filter State) --}}
                <tr id="noMatchRow_{{ $tableId }}" style="display: none;">
                    <td colspan="10" class="text-center py-4 text-muted">
                        <i class="fas fa-filter mr-1"></i> No staff sales matching the selected filters.
                        <button type="button" class="btn btn-link btn-sm p-0 ml-1 font-weight-bold" onclick="resetStaffFilters('{{ $tableId }}')">Reset filters</button>
                    </td>
                </tr>
            </tbody>

            {{-- Summary Footer --}}
            @if(count($filteredStaffSales) > 0)
                <tfoot>
                    <tr style="background: var(--snd-surface-soft); font-weight: 800; border-top: 2px solid var(--zp-border-subtle);">
                        <td colspan="3" style="padding: 12px 18px; color: var(--zp-text-main); font-size: 0.85rem;">
                            <span class="d-inline-flex align-items-center gap-1">
                                <i class="fas fa-calculator text-primary mr-1"></i> TOTAL SUMMARY
                            </span>
                        </td>
                        <td style="padding: 12px 18px; font-family: monospace; font-size: 0.9rem; color: var(--zp-success);">
                            {{ config('settings.currency_symbol') }} {{ number_format($filteredStaffSales->sum('sales_today'), 2) }}
                        </td>
                        <td style="padding: 12px 18px; font-family: monospace; font-size: 0.9rem; color: var(--zp-primary);">
                            {{ config('settings.currency_symbol') }} {{ number_format($filteredStaffSales->sum('sales_month'), 2) }}
                        </td>
                        <td class="text-center" style="padding: 12px 18px; font-size: 0.85rem; color: var(--zp-text-main);">
                            {{ $filteredStaffSales->sum('orders_count') }}
                        </td>
                        <td style="padding: 12px 18px; font-family: monospace; font-size: 0.9rem; color: var(--zp-text-main);">
                            {{ config('settings.currency_symbol') }} {{ number_format($filteredStaffSales->sum('gross_sales'), 2) }}
                        </td>
                        <td style="padding: 12px 18px; font-family: monospace; font-size: 0.85rem; color: #e11d48;">
                            @if($filteredStaffSales->sum('returns_total') > 0)
                                -{{ config('settings.currency_symbol') }} {{ number_format($filteredStaffSales->sum('returns_total'), 2) }}
                            @else
                                -
                            @endif
                        </td>
                        <td style="padding: 12px 18px; font-family: monospace; font-size: 0.95rem; color: #1f7a3d;">
                            {{ config('settings.currency_symbol') }} {{ number_format($filteredStaffSales->sum('net_sales'), 2) }}
                        </td>
                        <td class="text-right" style="padding: 12px 18px; font-size: 0.75rem; color: var(--zp-text-muted);">
                            Active Staff: {{ $filteredStaffSales->pluck('user_id')->unique()->count() }}
                        </td>
                    </tr>
                </tfoot>
            @endif
        </table>
    </div>
</div>
