@php
    $canCreate = auth()->check() && (
        auth()->user()->can('payment_methods.edit') ||
        auth()->user()->can('banks.create') ||
        auth()->user()->can('banks.edit') ||
        auth()->user()->hasRole('Admin') ||
        auth()->user()->hasRole('admin')
    );
    $canEdit = auth()->check() && (
        auth()->user()->can('payment_methods.edit') ||
        auth()->user()->can('banks.edit') ||
        auth()->user()->hasRole('Admin') ||
        auth()->user()->hasRole('admin')
    );
    $canDelete = auth()->check() && (
        auth()->user()->can('payment_methods.delete') ||
        auth()->user()->can('banks.delete') ||
        auth()->user()->hasRole('Admin') ||
        auth()->user()->hasRole('admin')
    );
@endphp

<div class="table-responsive payment-methods-table-wrap">
    <table class="table payment-methods-table mb-0" id="paymentMethodsTable">
        <thead>
            <tr>
                <th scope="col">ID</th>
                <th scope="col">Bank / card terminal</th>
                <th scope="col">Account title</th>
                <th scope="col">Account &amp; IBAN</th>
                <th scope="col">Branch &amp; SWIFT</th>
                <th scope="col" class="text-center">Status</th>
                <th scope="col" class="text-center">Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($paymentMethods as $method)
            @php
                [$methodLabel, $methodIcon] = match (strtolower($method->type ?? 'other')) {
                    'cash' => ['Cash', 'fa-money-bill-wave'],
                    'bank' => ['Bank', 'fa-university'],
                    'card' => ['Card', 'fa-credit-card'],
                    'wallet' => ['Wallet', 'fa-wallet'],
                    default => [ucfirst($method->type ?? 'Other'), 'fa-money-check-alt'],
                };
            @endphp
            <tr>
                <td class="text-muted font-weight-bold">#{{ $method->id }}</td>
                <td>
                    <div class="d-flex align-items-center">
                        <span class="pm-type-icon-wrapper">
                            <i class="fas {{ $methodIcon }}"></i>
                        </span>
                        <div class="min-w-0">
                            <div class="pm-bank-name">{{ $method->bank_name ?: $method->name }}</div>
                            <span class="pm-card-badge"><i class="fas {{ $methodIcon }}"></i> {{ $methodLabel }}</span>
                        </div>
                    </div>
                </td>
                <td>
                    @if($method->account_title)
                        <span class="pm-account-title"><i class="fas fa-user-circle"></i> {{ $method->account_title }}</span>
                    @else
                        <span class="text-muted">—</span>
                    @endif
                </td>
                <td>
                    @if($method->account_number || $method->iban)
                        <div class="pm-account-lines">
                            @if($method->account_number)
                                <div class="pm-account-line">
                                    <span class="pm-account-label">A/C</span>
                                    <code class="pm-account-code">{{ $method->account_number }}</code>
                                    <button type="button" class="pm-copy-btn btn-copy" data-clipboard-text="{{ $method->account_number }}" title="Copy account number" aria-label="Copy account number">
                                        <x-snd-icon name="copy" class="pm-copy-icon" />
                                        <x-snd-icon name="check" class="pm-copied-icon" />
                                    </button>
                                </div>
                            @endif
                            @if($method->iban)
                                <div class="pm-account-line">
                                    <span class="pm-account-label">IBAN</span>
                                    <code class="pm-account-code">{{ $method->iban }}</code>
                                    <button type="button" class="pm-copy-btn btn-copy" data-clipboard-text="{{ $method->iban }}" title="Copy IBAN" aria-label="Copy IBAN">
                                        <x-snd-icon name="copy" class="pm-copy-icon" />
                                        <x-snd-icon name="check" class="pm-copied-icon" />
                                    </button>
                                </div>
                            @endif
                        </div>
                    @else
                        <span class="text-muted">—</span>
                    @endif
                </td>
                <td>
                    @if($method->branch_name || $method->branch_code || $method->swift_code)
                        @if($method->branch_name)
                            <div class="pm-branch-name">{{ $method->branch_name }}</div>
                        @endif
                        @if($method->branch_code || $method->swift_code)
                            <div class="pm-branch-meta">
                                @if($method->branch_code)
                                    <span class="badge">Code: {{ $method->branch_code }}</span>
                                @endif
                                @if($method->swift_code)
                                    <span class="badge">SWIFT: {{ $method->swift_code }}</span>
                                @endif
                            </div>
                        @endif
                    @else
                        <span class="text-muted">—</span>
                    @endif
                </td>
                <td class="text-center">
                    @php
                        $statusClass = $method->status ? 'pm-status--active' : 'pm-status--inactive';
                        $statusIcon = $method->status ? 'circle-check' : 'circle-pause';
                    @endphp
                    @if($canEdit)
                        <button type="button"
                                class="btn pm-status {{ $statusClass }} btn-toggle-status"
                                data-url="{{ route('payment-methods.toggle-status', $method) }}"
                                aria-label="{{ $method->status ? 'Deactivate' : 'Activate' }} {{ $method->bank_name ?: $method->name }}">
                            <x-snd-icon :name="$statusIcon" />
                            <span>{{ $method->status ? 'Active' : 'Inactive' }}</span>
                        </button>
                    @else
                        <span class="pm-status {{ $statusClass }}">
                            <x-snd-icon :name="$statusIcon" />
                            {{ $method->status ? 'Active' : 'Inactive' }}
                        </span>
                    @endif
                </td>
                <td class="text-center">
                    <div class="action-btn-group">
                        @if($canEdit)
                            <a href="{{ route('payment-methods.edit', $method) }}" class="action-btn action-btn-edit snd-table-action" title="Edit payment method" aria-label="Edit payment method">
                                <x-snd-icon name="square-pen" />
                            </a>
                        @endif
                        @if($canDelete)
                            <button type="button" class="action-btn action-btn-delete snd-table-action snd-table-action--danger btn-delete" data-url="{{ route('payment-methods.destroy', $method) }}" title="Delete payment method" aria-label="Delete payment method">
                                <x-snd-icon name="trash" />
                            </button>
                        @endif
                    </div>
                </td>
            </tr>
            @empty
            <tr class="snd-empty-row">
                <td colspan="7" class="snd-table-empty-cell">
                    <div class="pm-empty-state">
                        <span class="pm-empty-icon"><i class="fas fa-credit-card"></i></span>
                        <span class="pm-empty-kicker">Payment methods</span>
                        <h3>No payment methods found</h3>
                        <p>
                            @if(request('search'))
                                No payment method matches “{{ request('search') }}”. Try another bank name or account detail.
                            @else
                                Add a bank account or POS terminal to make it available at checkout.
                            @endif
                        </p>
                        @if($canCreate)
                            <a href="{{ route('payment-methods.create') }}" class="btn btn-primary">
                                <i class="fas fa-plus"></i> Add payment method
                            </a>
                        @endif
                    </div>
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

@if ($paymentMethods->hasPages())
    <div class="pm-pagination-footer snd-pagination-inline" id="paginationInfo">
        <span>Showing <strong>{{ $paymentMethods->firstItem() ?? 0 }}</strong> to <strong>{{ $paymentMethods->lastItem() ?? 0 }}</strong> of <strong>{{ number_format($paymentMethods->total()) }}</strong> methods</span>
        <div class="payment-methods-pagination">{{ $paymentMethods->withQueryString()->links() }}</div>
    </div>
@elseif ($paymentMethods->total() > 0)
    <div class="pm-pagination-footer snd-pagination-inline" id="paginationInfo">
        <span>Showing all <strong>{{ number_format($paymentMethods->total()) }}</strong> payment methods</span>
    </div>
@endif
