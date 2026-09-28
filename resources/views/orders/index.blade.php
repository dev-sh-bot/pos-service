@extends('layouts.admin')

@section('title', __('order.Orders_List'))
@section('content-header', __('order.Orders_List'))
@section('content-actions')
    <a href="{{route('cart.index')}}" class="btn btn-primary">{{ __('cart.title') }}</a>
@endsection
@section('content')

    <div class="card snd-order-filter-card">
        <div class="card-header">
            <h3 class="card-title">
                <x-snd-icon name="filter" class="mr-1" /> {{ __('Filters') }}
            </h3>
        </div>
        <div class="card-body">
            <form action="{{ route('orders.index') }}" class="snd-order-filter-form">
                <div class="snd-order-filter-grid">
                    <div class="form-group">
                        <label for="order-start-date">{{ __('Date From') }}</label>
                        <input id="order-start-date" type="date" name="start_date" class="form-control" value="{{ request('start_date') }}" />
                    </div>
                    <div class="form-group">
                        <label for="order-end-date">{{ __('Date To') }}</label>
                        <input id="order-end-date" type="date" name="end_date" class="form-control" value="{{ request('end_date') }}" />
                    </div>
                    <div class="form-group snd-order-filter-action">
                        <button class="btn btn-primary" type="submit">
                            <x-snd-icon name="search" class="mr-1" /> {{ __('order.submit') }}
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
    <div class="card-body">
        <div class="table-responsive snd-table-scroll">
            <table class="table">
                <thead>
                <tr>
                    <th>{{ __('order.ID') }}</th>
                    <th>{{ __('order.Customer_Name') }}</th>
                    <th>Branch</th>
                    <th>Counter</th>
                    <th>{{ __('order.Total') }}</th>
                    <th>{{ __('order.Received_Amount') }}</th>
                    <th>{{ __('order.Status') }}</th>
                    <th>{{ __('order.To_Pay') }}</th>
                    <th>{{ __('order.Created_At') }}</th>
                    <th>{{ __('order.Actions') }}</th>
                </tr>
                </thead>
                <tbody>
                @forelse ($orders as $order)
                    @php
                        $orderTotal = $order->total();
                        $orderReceived = $order->receivedAmount();
                        $orderRemaining = $orderTotal - $orderReceived;
                    @endphp
                    <tr>
                        <td>{{$order->id}}</td>
                        <td>{{$order->getCustomerName()}}</td>
                        <td>{{ $order->branch?->name ?? '—' }} <small class="text-muted">{{ $order->branch?->code }}</small></td>
                        <td>{{ $order->counter?->name ?? '—' }} <small class="text-muted">{{ $order->counter?->code }}</small></td>
                        <td>{{ config('settings.currency_symbol') }} {{number_format($orderTotal, 2)}}</td>
                        <td>{{ config('settings.currency_symbol') }} {{number_format($orderReceived, 2)}}</td>
                        <td>
                            @if($orderReceived == 0)
                                <span class="badge badge-danger">{{ __('order.Not_Paid') }}</span>
                            @elseif($orderReceived < $orderTotal)
                                <span class="badge badge-warning">{{ __('order.Partial') }}</span>
                            @elseif($orderReceived >= $orderTotal)
                                <span class="badge badge-success">{{ __('order.Paid') }}</span>
                            @endif
                        </td>
                        <td>{{config('settings.currency_symbol')}} {{number_format($orderRemaining, 2)}}</td>
                        <td>{{$order->created_at}}</td>
                        <td>
                            <button
                                class="btn btn-sm btn-secondary btnShowInvoice"
                                data-toggle="modal"
                                data-target="#modalInvoice"
                                data-order-id="{{ $order->id }}"
                                data-invoice-no="{{ $order->invoice_no ?? 'POS-' . str_pad($order->id, 4, '0', STR_PAD_LEFT) }}"
                                data-customer-name="{{ $order->getCustomerName() }}"
                                data-branch-name="{{ $order->branch?->name ?? '—' }}"
                                data-branch-code="{{ $order->branch?->code ?? '' }}"
                                data-counter-name="{{ $order->counter?->name ?? '—' }}"
                                data-counter-code="{{ $order->counter?->code ?? '' }}"
                                data-total="{{ $orderTotal }}"
                                data-subtotal="{{ $order->subtotal ?? $orderTotal }}"
                                data-tax-percent="{{ $order->tax_percent ?? 0 }}"
                                data-tax-amount="{{ $order->tax_amount ?? 0 }}"
                                data-discount-percent="{{ $order->discount_percent ?? 0 }}"
                                data-discount-amount="{{ $order->discount_amount ?? 0 }}"
                                data-srb-invoice-id="{{ $order->srb_invoice_id ?? '' }}"
                                data-srb-qr-link="{{ $order->srb_qr_code_link ?? '' }}"
                                data-received="{{ $orderReceived }}"
                                data-items="{{ base64_encode($order->items->toJson()) }}"
                                data-created-at="{{ $order->created_at }}">
                                <x-snd-icon name="eye" />
                            </button>
                            <button
                                type="button"
                                class="btn btn-sm btn-primary btnPrintOrder"
                                data-order-id="{{ $order->id }}"
                                data-invoice-no="{{ $order->invoice_no ?? 'POS-' . str_pad($order->id, 4, '0', STR_PAD_LEFT) }}"
                                data-customer-name="{{ $order->getCustomerName() }}"
                                data-branch-name="{{ $order->branch?->name ?? '-' }}"
                                data-counter-name="{{ $order->counter?->name ?? '-' }}"
                                data-total="{{ $orderTotal }}"
                                data-subtotal="{{ $order->subtotal ?? $orderTotal }}"
                                data-tax-percent="{{ $order->tax_percent ?? 0 }}"
                                data-tax-amount="{{ $order->tax_amount ?? 0 }}"
                                data-discount-percent="{{ $order->discount_percent ?? 0 }}"
                                data-discount-amount="{{ $order->discount_amount ?? 0 }}"
                                data-received="{{ $orderReceived }}"
                                data-items="{{ base64_encode($order->items->toJson()) }}"
                                data-created-at="{{ $order->created_at }}"
                                data-srb-invoice-id="{{ $order->srb_invoice_id ?? '' }}"
                                data-srb-qr-link="{{ $order->srb_qr_code_link ?? '' }}"
                                title="Print receipt">
                                <x-snd-icon name="printer" />
                            </button>

                            @if($orderRemaining > 0)
                                <button type="button"
                                        class="btn btn-sm btn-primary btnPartialPayment"
                                        data-toggle="modal"
                                        data-target="#partialPaymentModal"
                                        data-order-id="{{ $order->id }}"
                                        data-remaining-amount="{{ $orderRemaining }}"
                                        title="Pay partial amount"
                                        aria-label="Pay partial amount">
                                    <x-snd-icon name="wallet" />
                                </button>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr class="snd-empty-row">
                        <td colspan="10" class="snd-table-empty-cell">
                            <x-snd-empty-state
                                icon="receipt"
                                message="No orders found."
                                :url="route('cart.index')"
                                action-label="Create First Order"
                            />
                        </td>
                    </tr>
                @endforelse
                </tbody>
                <tfoot>
                <tr>
                    <th></th>
                    <th></th>
                    <th></th>
                    <th></th>
                    <th>{{ config('settings.currency_symbol') }} {{ number_format($total, 2) }}</th>
                    <th>{{ config('settings.currency_symbol') }} {{ number_format($receivedAmount, 2) }}</th>
                    <th></th>
                    <th></th>
                    <th></th>
                    <th></th>
                </tr>
                </tfoot>
            </table>
            </div>
            <x-snd-pagination :paginator="$orders" />
        </div>
    </div>

    <!-- Partial Payment Modal -->
    <div class="modal fade" id="partialPaymentModal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Pay Partial Amount</h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <form method="POST" action="{{ route('orders.partial-payment') }}">
                    @csrf
                    <div class="modal-body">
                        <input type="hidden" name="order_id" id="modalOrderId">
                        <div class="form-group">
                            <label for="partialAmount">Enter Amount to Pay</label>
                            <input type="number" class="form-control" step="0.01" id="partialAmount" name="amount" required>
                            <small class="form-text text-muted">Remaining: <span id="remainingAmount"></span></small>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Submit Payment</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

@endsection

@section('model')
    <!-- Invoice Modal -->
    <div class="modal fade" id="modalInvoice" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Invoice</h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <!-- Dynamic content will be inserted here -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary" id="btnPrintInvoice">
                        <x-snd-icon name="printer" class="mr-1" /> Print Receipt
                    </button>
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('js')
    <script type="module" src="https://unpkg.com/ionicons@4.5.10-0/dist/ionicons/ionicons.esm.js"></script>
    <script nomodule src="https://unpkg.com/ionicons@4.5.10-0/dist/ionicons/ionicons.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        jQuery(document).ready(function($) {
            var currencySymbol = '{{ config("settings.currency_symbol") }}';
            var currentInvoiceButton = null;

            function getOrderItems(button) {
                try {
                    return JSON.parse(atob(button.attr('data-items') || '')) || [];
                } catch (error) {
                    return [];
                }
            }

            // Invoice Modal
            $(document).on('click', '.btnShowInvoice', function() {
                var button = $(this);
                currentInvoiceButton = button;
                var orderId = button.data('order-id');
                var invoiceNo = button.data('invoice-no');
                var invoiceTitleNo = 'Order ID-' + String(orderId).padStart(4, '0');
                var customerName = button.data('customer-name');
                var branchName = button.data('branch-name');
                var branchCode = button.data('branch-code');
                var counterName = button.data('counter-name');
                var counterCode = button.data('counter-code');
                var totalAmount = button.data('total');
                var subtotalAmount = button.data('subtotal');
                var taxPercent = button.data('tax-percent') || 0;
                var taxAmount = button.data('tax-amount') || 0;
                var discountPercent = button.data('discount-percent') || 0;
                var discountAmount = button.data('discount-amount') || 0;
                var receivedAmount = button.data('received');
                var createdAt = button.data('created-at');
                var items = getOrderItems(button);

                var statusBadge = '';
                if (receivedAmount == 0) {
                    statusBadge = '<span class="badge badge-danger">Not Paid</span>';
                } else if (receivedAmount < totalAmount) {
                    statusBadge = '<span class="badge badge-warning">Partial</span>';
                } else {
                    statusBadge = '<span class="badge badge-success">Paid</span>';
                }

                var itemsHTML = '';
                if (items && Array.isArray(items) && items.length > 0) {
                    items.forEach(function(item, index) {
                        var isService = Number(item.item_type) === 1 || item.item_type === 'service';
                        var product = item.product || {};
                        var itemName = item.item_name || (isService ? 'Service' : (product.name || 'N/A'));
                        var quantity = item.quantity || 0;
                        var itemTotal = item.price || 0;

                        itemsHTML += '<tr>' +
                            '<td>' + (index + 1) + '</td>' +
                            '<td>' + itemName + '</td>' +
                            '<td>' + (isService ? 'Service' : 'Product') + '</td>' +
                            '<td>' + currencySymbol + ' ' + (quantity ? (parseFloat(itemTotal / quantity)).toFixed(2) : '0.00') + '</td>' +
                            '<td>' + quantity + '</td>' +
                            '<td>' + currencySymbol + ' ' + parseFloat(itemTotal).toFixed(2) + '</td>' +
                            '</tr>';
                    });
                } else {
                    itemsHTML = '<tr><td colspan="6" class="text-center">No items found</td></tr>';
                }

                var modalBody = $('#modalInvoice').find('.modal-body');
                modalBody.html(
                    '<div class="card">' +
                    '<div class="card-header">' +
                    'Invoice <strong>#' + orderId + '</strong>' +
                    '<span class="ml-3">Invoice No: <strong>' + invoiceNo + '</strong></span>' +
                    '<span class="float-right"><strong>Status:</strong> ' + statusBadge + '</span>' +
                    '</div>' +
                    '<div class="card-body">' +
                    '<div class="row mb-4">' +
                    '<div class="col-sm-6">' +
                    '<h6 class="mb-3">To: <strong>' + customerName + '</strong></h6>' +
                    '<div>Date: ' + createdAt + '</div>' +
                    '</div>' +
                    '<div class="col-sm-6 text-sm-right">' +
                    '<div>Branch: <strong>' + branchName + '</strong>' + (branchCode ? ' (' + branchCode + ')' : '') + '</div>' +
                    '<div>Counter: <strong>' + counterName + '</strong>' + (counterCode ? ' (' + counterCode + ')' : '') + '</div>' +
                    '</div>' +
                    '</div>' +
                    '<div class="table-responsive">' +
                    '<table class="table table-striped">' +
                    '<thead>' +
                    '<tr>' +
                    '<th>#</th>' +
                    '<th>Item</th>' +
                    '<th>Description</th>' +
                    '<th>Unit Cost</th>' +
                    '<th>Qty</th>' +
                    '<th>Total</th>' +
                    '</tr>' +
                    '</thead>' +
                    '<tbody>' + itemsHTML + '</tbody>' +
                    '<tfoot>' +
                    '<tr>' +
                    '<th colspan="5" class="text-right">Subtotal</th>' +
                    '<th>' + currencySymbol + ' ' + parseFloat(subtotalAmount).toFixed(2) + '</th>' +
                    '</tr>' +
                    '<tr>' +
                    '<th colspan="5" class="text-right">Tax (' + parseFloat(taxPercent).toFixed(2) + '%)</th>' +
                    '<th>' + currencySymbol + ' ' + parseFloat(taxAmount).toFixed(2) + '</th>' +
                    '</tr>' +
                    '<tr>' +
                    '<th colspan="5" class="text-right">Discount (' + parseFloat(discountPercent).toFixed(2) + '%)</th>' +
                    '<th>- ' + currencySymbol + ' ' + parseFloat(discountAmount).toFixed(2) + '</th>' +
                    '</tr>' +
                    '<tr>' +
                    '<th colspan="5" class="text-right">Total</th>' +
                    '<th>' + currencySymbol + ' ' + parseFloat(totalAmount).toFixed(2) + '</th>' +
                    '</tr>' +
                    '<tr>' +
                    '<th colspan="5" class="text-right">Paid</th>' +
                    '<th>' + currencySymbol + ' ' + parseFloat(receivedAmount).toFixed(2) + '</th>' +
                    '</tr>' +
                    '<tr>' +
                    '<th colspan="5" class="text-right">Balance</th>' +
                    '<th>' + currencySymbol + ' ' + parseFloat(totalAmount - receivedAmount).toFixed(2) + '</th>' +
                    '</tr>' +
                    '</tfoot>' +
                    '</table>' +
                    '</div>' +
                    '</div>' +
                    '</div>'
                );
            });

            $('#btnPrintInvoice').on('click', function() {
                printThermalReceipt(currentInvoiceButton);
            });

            function buildBarcodeSvg(reference) {
    reference = reference || 'SRB-000000';
    var displayRef = String(reference).toUpperCase();
    var cleanRef = displayRef.replace(/[^A-Z0-9]/g, '') || 'SRB000000';
    var bars = [
        '101001101101', '110100101011', '110101001011', '110101011001', '101101001011',
        '110011010011', '110110100101', '110110101001', '110010101011', '110010110101'
    ];
    var pattern = '';
    for (var i = 0; i < cleanRef.length; i++) {
        var index = (cleanRef.charCodeAt(i) % 10 + i) % 10;
        pattern += bars[index % bars.length];
    }
    var barSegments = [];
    for (var j = 0; j < pattern.length; j++) {
        if (pattern[j] === '1') {
            var x = barSegments.length * 2.2;
            barSegments.push('<rect x="' + x + '" y="8" width="2" height="44" fill="#111827" />');
        }
    }
    return '<svg xmlns="http://www.w3.org/2000/svg" width="240" height="72" viewBox="0 0 240 72" role="img" aria-label="Barcode">' +
           '<g fill="#111827">' + barSegments.join('') + '</g>' +
           '<text x="120" y="67" text-anchor="middle" font-family="Arial, sans-serif" font-size="10" fill="#111827">' + displayRef + '</text>' +
           '</svg>';
}

function printThermalReceipt(button) {
    if (!button || !button.length) return;
    var orderId = button.data('order-id');
    var invoiceNo = button.data('invoice-no');
    var invoiceTitleNo = 'Order ID-' + String(orderId).padStart(4, '0');
    var customerName = button.data('customer-name') || 'Walk-in Customer';
    var branchName = button.data('branch-name') || 'Main Branch';
    var counterName = button.data('counter-name') || 'Counter 1';
    var subtotal = parseFloat(button.data('subtotal')) || 0;
    var taxPercent = parseFloat(button.data('tax-percent')) || 0;
    var taxAmount = parseFloat(button.data('tax-amount')) || 0;
    var discountPercent = parseFloat(button.data('discount-percent')) || 0;
    var discountAmount = parseFloat(button.data('discount-amount')) || 0;
    var totalAmount = parseFloat(button.data('total')) || 0;
    var receivedAmount = parseFloat(button.data('received')) || 0;
    var changeDue = receivedAmount > totalAmount ? receivedAmount - totalAmount : 0;
    var valueForSales = subtotal - discountAmount;
    var paymentStatus = receivedAmount > 0 ? 'PAID (CASH)' : null;
    var createdAt = button.data('created-at');
    var srbInvoiceId = button.attr('data-srb-invoice-id') || '';
    var srbQrLink = button.attr('data-srb-qr-link') || '';
    var items = getOrderItems(button);
    var imageUrl = window.location.origin + '/images/srb.jfif';
    var itemsHTML = items.map(function(item) {
        var isService = Number(item.item_type) === 1 || item.item_type === 'service';
        var quantity = Number(item.quantity || 0);
        var lineTotal = Number(item.price || 0);
        var unitPrice = quantity ? (lineTotal / quantity) : lineTotal;
        var name = item.item_name || (isService ? 'Service' : ((item.product && item.product.name) || 'Product'));
        return '<div class="receipt-item-block">' +
               '<div class="receipt-item-name">' + name + '</div>' +
               '<div class="receipt-item-row"><span class="col-item"></span><span class="col-qty">' + quantity + '</span><span class="col-price">' + unitPrice.toFixed(2) + '</span><span class="col-total">' + lineTotal.toFixed(2) + '</span></div>' +
               '</div>';
    }).join('');

    var headerHTML = window.CommonHelper ? window.CommonHelper.getReceiptHeaderHTML({ branchName: branchName }) : '';
    var footerHTML = window.CommonHelper ? window.CommonHelper.getReceiptFooterHTML(createdAt) : '';
    var printStyles = window.CommonHelper ? window.CommonHelper.getThermalPrintStyles() : '';
    var barcodeSvg = buildBarcodeSvg(invoiceNo);

    var metaRows = [
        { label: 'Receipt No.', value: invoiceNo },
        // { label: 'Order ID', value: '#' + orderId }, 
        paymentStatus ? { label: 'Payment Status', value: paymentStatus } : null,
        { label: 'Date', value: createdAt },
        { label: 'Terminal', value: counterName },
    { label: 'Customer', value: customerName },
    ].filter(Boolean);

    var metaHTML = metaRows.map(function(row) {
        return '<div class="meta-row"><span class="meta-label">' + row.label + '</span><span class="meta-value">' + row.value + '</span></div>';
    }).join('');

    var srbLogo = window.APP && window.APP.srb_pos_logo_url && srbInvoiceId ? '<img class="srb-logo" src="' + window.APP.srb_pos_logo_url + '" style="max-width:60px;max-height:60px;width:auto;height:auto;">' : '';
    var srbQr = srbInvoiceId && srbQrLink ? '<div><img class="srb-qr" src="https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=' + encodeURIComponent(srbQrLink) + '"></div>' : '';
    var srbSection = srbInvoiceId ? '<div class="srb-footer"><div class="srb-row"><div class="logos-srb"><img src="' + imageUrl + '" alt="SRB Logo" style="max-width:200px;max-height:60px;width:auto;height:auto;"></div><div class="srb-code-cell"><div class="srb-label">SRB Invoice No.</div><div class="srb-invoice-id">' + srbInvoiceId + '</div>' + srbQr + '</div></div><div class="srb-verify">Scan to verify this invoice</div></div>' : '';

    var receiptContent = '<div class="thermal-receipt">' +
        headerHTML +
        '<div class="receipt-divider"></div>' +
        '<div class="invoice-number">*' + invoiceTitleNo + '*</div>' +
        '<div class="receipt-divider-double"></div>' +
        '<div class="meta-grid">' + metaHTML + '</div>' +
        '<div class="receipt-divider-double"></div>' +
        '<div class="receipt-table-head"><span class="col-item">ITEM DESCRIPTION</span><span class="col-qty">QTY</span><span class="col-price">PRICE</span><span class="col-total">AMOUNT</span></div>' +
        '<div class="receipt-divider"></div>' +
        itemsHTML +
        '<div class="receipt-divider"></div>' +
        '<div class="receipt-summary">' +
        '<div class="summary-row"><span>SUBTOTAL:</span><span>' + currencySymbol + ' ' + subtotal.toFixed(2) + '</span></div>' +
        (discountAmount > 0 ? '<div class="summary-row"><span>Total Discount (' + discountPercent.toFixed(2) + '%)</span><span>-' + currencySymbol + ' ' + discountAmount.toFixed(2) + '</span></div>' : '') +
        (discountAmount > 0 ? '<div class="summary-row"><span>Value for Sales</span><span>' + currencySymbol + ' ' + valueForSales.toFixed(2) + '</span></div>' : '') +
        (taxAmount > 0 ? '<div class="summary-row"><span>Total Sales Tax (' + taxPercent.toFixed(2) + '%)</span><span>' + currencySymbol + ' ' + taxAmount.toFixed(2) + '</span></div>' : '') +
        '<div class="receipt-divider"></div>' +
        '<div class="summary-row"><span>Total Value Including Sales Tax</span><span>' + currencySymbol + ' ' + subtotal.toFixed(2) + '</span></div>' +
        '<div class="receipt-divider"></div>' +
        '<div class="summary-row grand-total"><span>NET TOTAL</span><span>' + currencySymbol + ' ' + totalAmount.toFixed(2) + '</span></div>' +
        '<div class="receipt-divider"></div>' +
        '<div class="summary-row"><span>Cash</span><span>' + currencySymbol + ' ' + receivedAmount.toFixed(2) + '</span></div>' +
        '<div class="summary-row"><span>Change Due</span><span>' + currencySymbol + ' ' + changeDue.toFixed(2) + '</span></div>' +
        '</div>' +
        '<div class="receipt-divider-double"></div>' +
        '<div class="terms-block">' +
        '<div class="terms-title">Terms &amp; Conditions of Sale</div>' +
        'No Refund.<br>' +
        'Exchanges on unused products within 10 days only from the outlet where purchased.<br>' +
        'Claim will not be accepted without Order List.' +
        '</div>' +
        srbSection +
        footerHTML +
        '</div>';

    var printStyle = '<style>' + printStyles + '</style>';

    var printWindow = window.open('', '_blank', 'width=450,height=700');
    printWindow.document.write('<html><head><title>Receipt #' + orderId + '</title>' + printStyle + '</head><body>' + receiptContent + '</body></html>');
    printWindow.document.close();
    printWindow.onload = function() {
        var images = printWindow.document.images;
        var pendingImages = Array.prototype.filter.call(images, function(image) { return !image.complete; });
        var doPrint = function() {
            printWindow.focus();
            printWindow.print();
            printWindow.close();
        };
        if (!pendingImages.length) {
            setTimeout(doPrint, 100);
            return;
        }
        var loaded = 0;
        pendingImages.forEach(function(image) {
            image.onload = image.onerror = function() {
                loaded += 1;
                if (loaded === pendingImages.length) doPrint();
            };
        });
    };
}

            $(document).on('click', '.btnPrintOrder', function() {
                printThermalReceipt($(this));
            });

            // Partial Payment Modal
            $(document).on('click', '.btnPartialPayment', function() {
                var button = $(this);
                var orderId = button.data('order-id');
                var remainingAmount = button.data('remaining-amount');

                $('#modalOrderId').val(orderId);
                $('#partialAmount').val(remainingAmount).attr('max', remainingAmount);
                $('#remainingAmount').text(currencySymbol + ' ' + parseFloat(remainingAmount).toFixed(2));
            });
        });
    </script>
@endsection
